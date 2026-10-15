/**
 * Shared "reading list" store.
 *
 * Both the Save for Later button and the Reading List block import this
 * file, so they read and write the same global state under the
 * `fieldnote-interactive` namespace. Saving a post from a button anywhere on
 * the page instantly updates the counter in the header, with no custom
 * events or prop drilling.
 *
 * Calling store() twice with the same namespace merges the definitions, so it
 * is safe for each block's view module to include this file.
 */
import {
	store,
	getContext,
	getConfig,
	getElement,
} from '@wordpress/interactivity';

const STORAGE_KEY = 'fieldnote-reading-list';
const MAX_ITEMS = 50;

/**
 * Read the saved list from localStorage.
 *
 * Storage can throw (private mode, blocked cookies) or contain junk written by
 * an older version, so every failure falls back to an empty list.
 *
 * @return {Array<{id:number,title:string,url:string}>} Saved items.
 */
const readStorage = () => {
	try {
		const parsed = JSON.parse(
			window.localStorage.getItem( STORAGE_KEY ) || '[]'
		);
		return Array.isArray( parsed )
			? parsed.filter(
					( item ) =>
						item &&
						Number.isInteger( item.id ) &&
						typeof item.title === 'string' &&
						typeof item.url === 'string'
				)
			: [];
	} catch {
		return [];
	}
};

/**
 * Write the list to localStorage. Failures are ignored: the list still works
 * for the current page view, it just won't survive a reload.
 *
 * @param {Array} items Items to persist.
 */
const writeStorage = ( items ) => {
	try {
		window.localStorage.setItem( STORAGE_KEY, JSON.stringify( items ) );
	} catch {}
};

/**
 * Replace a printf-style %s placeholder. Keeps translated strings (passed in
 * from PHP via wp_interactivity_config) in charge of word order.
 *
 * @param {string} template Translated string containing %s.
 * @param {string} value    Replacement value.
 * @return {string} Formatted string.
 */
const fillPlaceholder = ( template, value ) => template.replace( '%s', value );

const { state, actions } = store( 'fieldnote-interactive', {
	state: {
		// Client-only state. These are deliberately NOT set with
		// wp_interactivity_state() in PHP: the server cannot know what is in
		// the visitor's localStorage, and client-side navigation would
		// otherwise reset them to the server's defaults.
		items: [],
		isReady: false,
		isOpen: false,

		get count() {
			return state.items.length;
		},
		get hasItems() {
			return state.items.length > 0;
		},
		/**
		 * Derived per-button state. Each Save button provides its own
		 * context ({ id, title, url }), so the same getter answers "is THIS
		 * post saved?" for every button on the page.
		 */
		get isSaved() {
			const { id } = getContext();
			return state.items.some( ( item ) => item.id === id );
		},
		get buttonLabel() {
			const { strings } = getConfig();
			return state.isSaved ? strings.saved : strings.save;
		},
		get countLabel() {
			const { strings } = getConfig();
			return fillPlaceholder(
				state.count === 1 ? strings.countOne : strings.countMany,
				String( state.count )
			);
		},
		get removeLabel() {
			const { strings } = getConfig();
			return fillPlaceholder( strings.remove, getContext().item.title );
		},
	},

	actions: {
		/** Save or unsave the post in the current button's context. */
		toggle() {
			const { id, title, url } = getContext();
			if ( state.isSaved ) {
				state.items = state.items.filter( ( item ) => item.id !== id );
			} else {
				state.items = [ { id, title, url }, ...state.items ].slice(
					0,
					MAX_ITEMS
				);
			}
			writeStorage( state.items );
		},

		/** Remove the item rendered by the current data-wp-each iteration. */
		remove() {
			const { item } = getContext();
			state.items = state.items.filter( ( i ) => i.id !== item.id );
			writeStorage( state.items );
		},

		clear() {
			state.items = [];
			writeStorage( state.items );
		},

		togglePanel() {
			state.isOpen = ! state.isOpen;
		},

		closePanel() {
			state.isOpen = false;
		},
	},

	callbacks: {
		/**
		 * Runs via data-wp-init on every block that needs the list. The guard
		 * means storage is only read once per page, however many buttons
		 * there are.
		 */
		load() {
			if ( state.isReady ) {
				return;
			}
			state.items = readStorage();
			state.isReady = true;
		},

		/**
		 * Keep multiple open tabs in sync (window "storage" event).
		 *
		 * @param {StorageEvent} event Storage event.
		 */
		syncAcrossTabs( event ) {
			if ( event.key === STORAGE_KEY ) {
				state.items = readStorage();
			}
		},

		/**
		 * Close the panel with Escape, returning focus to the toggle.
		 *
		 * @param {KeyboardEvent} event Keydown event.
		 */
		closeOnEscape( event ) {
			if ( event.key === 'Escape' && state.isOpen ) {
				actions.closePanel();
				document
					.querySelector(
						'.wp-block-fieldnote-interactive-reading-list__toggle'
					)
					?.focus();
			}
		},

		/**
		 * Close the panel when clicking anywhere outside the block.
		 *
		 * Uses composedPath() rather than target.closest(): clicking "Remove"
		 * deletes that list item before this document-level handler runs, so
		 * the target is already detached and closest() would find nothing,
		 * wrongly closing the panel. The composed path is captured when the
		 * event is dispatched, so it still includes the block.
		 *
		 * @param {MouseEvent} event Click event.
		 */
		closeOnOutsideClick( event ) {
			const { ref } = getElement();
			if ( state.isOpen && ! event.composedPath().includes( ref ) ) {
				actions.closePanel();
			}
		},
	},
} );
