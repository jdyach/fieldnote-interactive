<?php
/**
 * Shared server-side helpers.
 *
 * @package FieldnoteInteractive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the reading list's server state and translated strings.
 *
 * Called at the top of each block's render (before WordPress processes the
 * directives in that block's HTML). The values here are only defaults for the first, server-rendered paint:
 *
 * - `isReady` is false, so `data-wp-bind--hidden="!state.isReady"` outputs a
 *   `hidden` attribute and visitors without JavaScript never see buttons that
 *   cannot work.
 * - `isSaved`, `hasItems` and `buttonLabel` are closures (derived state, WP
 *   6.7+). The server evaluates them for the initial HTML, then the matching
 *   getters in reading-list-store.js take over in the browser.
 *
 * When the Interactivity Router navigates, server state is merged without
 * overriding existing keys, so these defaults never wipe the visitor's list.
 *
 * @since 1.0.0
 * @return void
 */
function fieldnote_interactive_reading_list_state() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	wp_interactivity_state(
		'fieldnote-interactive',
		array(
			'isReady'     => false,
			'isOpen'      => false,
			'count'       => 0,
			'isSaved'     => static function () {
				return false;
			},
			'hasItems'    => static function () {
				return false;
			},
			'buttonLabel' => static function () {
				return __( 'Save for later', 'fieldnote-interactive' );
			},
		)
	);

	// Translated strings for labels built in the browser (getConfig()).
	wp_interactivity_config(
		'fieldnote-interactive',
		array(
			'strings' => array(
				'save'      => __( 'Save for later', 'fieldnote-interactive' ),
				'saved'     => __( 'Saved', 'fieldnote-interactive' ),
				/* translators: %s: number of saved posts (always 1). */
				'countOne'  => __( '%s saved post', 'fieldnote-interactive' ),
				/* translators: %s: number of saved posts. */
				'countMany' => __( '%s saved posts', 'fieldnote-interactive' ),
				/* translators: %s: post title. */
				'remove'    => __( 'Remove %s from reading list', 'fieldnote-interactive' ),
			),
		)
	);
}
/**
 * Inline bookmark icon used by the Save button and Reading List.
 *
 * @since 1.0.0
 * @return string SVG markup.
 */
function fieldnote_interactive_bookmark_icon() {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="fi-icon-fill" d="M6 3.75h12a.75.75 0 0 1 .75.75v16.2l-6.75-4.5-6.75 4.5V4.5A.75.75 0 0 1 6 3.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
}

/**
 * Markup for a Save for Later button.
 *
 * Shared by the save-button block and the cards in the Filterable Post Grid,
 * so both always output identical directives.
 *
 * Each button carries its own context ({ id, title, url }) via
 * data-wp-context; the shared `state.isSaved` getter reads that context to
 * decide whether this particular post is in the list.
 *
 * @since 1.0.0
 * @param int    $post_id            Post ID.
 * @param string $wrapper_attributes Optional pre-escaped wrapper attributes from get_block_wrapper_attributes().
 * @return string Button HTML.
 */
function fieldnote_interactive_save_button_markup( $post_id, $wrapper_attributes = '' ) {
	fieldnote_interactive_reading_list_state();

	// The grid prints buttons outside the save-button block, so make sure its
	// stylesheet is loaded too.
	wp_enqueue_style( 'fieldnote-interactive-save-button-style' );

	if ( '' === $wrapper_attributes ) {
		$wrapper_attributes = 'class="wp-block-fieldnote-interactive-save-button"';
	}

	$context = array(
		'id'    => (int) $post_id,
		'title' => html_entity_decode( wp_strip_all_tags( get_the_title( $post_id ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
		'url'   => get_permalink( $post_id ),
	);

	ob_start();
	?>
	<div
		<?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by get_block_wrapper_attributes() or a static string. ?>
		data-wp-interactive="fieldnote-interactive"
		<?php echo wp_interactivity_data_wp_context( $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
		data-wp-init="callbacks.load"
	>
		<button
			type="button"
			class="wp-block-fieldnote-interactive-save-button__button"
			data-wp-bind--hidden="!state.isReady"
			data-wp-bind--aria-pressed="state.isSaved"
			data-wp-on--click="actions.toggle"
		>
			<?php echo fieldnote_interactive_bookmark_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
			<span data-wp-text="state.buttonLabel"><?php esc_html_e( 'Save for later', 'fieldnote-interactive' ); ?></span>
		</button>
	</div>
	<?php
	return (string) ob_get_clean();
}
