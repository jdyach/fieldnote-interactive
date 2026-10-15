/**
 * Filterable Post Grid: client-side navigation with the Interactivity Router.
 *
 * Every filter and pagination control is a real link that the server can
 * render on its own. When JavaScript is available we intercept the click,
 * fetch the next page in the background and swap only the router region,
 * so the URL, the back button and the server-rendered HTML all stay in sync.
 */
import { store, getElement, withSyncEvent } from '@wordpress/interactivity';

// Also load the reading-list store, because each card contains a
// Save for Later button that lives in the grid's router region.
import '../shared/reading-list-store';

/**
 * Should this click be left to the browser? (new tab, modified click, etc.)
 *
 * @param {MouseEvent} event Click event.
 * @return {boolean} True when we must not intercept.
 */
const isModifiedClick = ( event ) =>
	event.button !== 0 ||
	event.metaKey ||
	event.ctrlKey ||
	event.shiftKey ||
	event.altKey ||
	event.defaultPrevented;

const { state } = store( 'fieldnote-interactive/post-filter', {
	state: {
		isLoading: false,
	},

	actions: {
		/**
		 * Navigate to the link's URL without a full reload.
		 *
		 * withSyncEvent() is required because preventDefault() needs the
		 * event synchronously, while the rest of this action is async.
		 */
		navigate: withSyncEvent( function* ( event ) {
			if ( isModifiedClick( event ) ) {
				return;
			}
			event.preventDefault();

			const { ref } = getElement();
			const isPagination = !! ref.closest(
				'.wp-block-fieldnote-interactive-post-filter__pagination'
			);
			const block = ref.closest(
				'.wp-block-fieldnote-interactive-post-filter'
			);

			state.isLoading = true;
			try {
				// Imported on demand: pages that never navigate never
				// download the router.
				const { actions: router } =
					yield import( '@wordpress/interactivity-router' );
				yield router.navigate( ref.href );
			} finally {
				state.isLoading = false;
			}

			// After paging, bring the reader back to the top of the grid and
			// move focus to the result summary so keyboard and screen reader
			// users land somewhere meaningful instead of on <body>.
			if ( isPagination && block ) {
				const status = block.querySelector(
					'.wp-block-fieldnote-interactive-post-filter__status'
				);
				block.scrollIntoView( { block: 'start', behavior: 'smooth' } );
				status?.focus( { preventScroll: true } );
			}
		} ),

		/** Warm the router cache on hover/focus so clicks feel instant. */
		*prefetch() {
			const { ref } = getElement();
			const { actions: router } =
				yield import( '@wordpress/interactivity-router' );
			yield router.prefetch( ref.href );
		},
	},
} );
