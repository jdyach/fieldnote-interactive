<?php
/**
 * Server render for the Reading List.
 *
 * Directive reference:
 * - data-wp-init / data-wp-on-window--storage: load the list and keep tabs in sync.
 * - data-wp-on-document--*: close on Escape or an outside click.
 * - data-wp-bind--aria-expanded / --hidden: disclosure pattern for the panel.
 * - data-wp-each: renders one <li> per saved post from state.items.
 *
 * @package FieldnoteInteractive
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

fieldnote_interactive_reading_list_state();

$fieldnote_panel_id = wp_unique_id( 'fieldnote-reading-list-' );
?>
<div
	<?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="fieldnote-interactive"
	data-wp-init="callbacks.load"
	data-wp-on-window--storage="callbacks.syncAcrossTabs"
	data-wp-on-document--keydown="callbacks.closeOnEscape"
	data-wp-on-document--click="callbacks.closeOnOutsideClick"
	data-wp-class--is-open="state.isOpen"
	data-wp-bind--hidden="!state.isReady"
>
	<button
		type="button"
		class="wp-block-fieldnote-interactive-reading-list__toggle"
		aria-controls="<?php echo esc_attr( $fieldnote_panel_id ); ?>"
		data-wp-on--click="actions.togglePanel"
		data-wp-bind--aria-expanded="state.isOpen"
	>
		<?php echo fieldnote_interactive_bookmark_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG. ?>
		<span class="wp-block-fieldnote-interactive-reading-list__label"><?php esc_html_e( 'Reading list', 'fieldnote-interactive' ); ?></span>
		<span class="wp-block-fieldnote-interactive-reading-list__count" aria-hidden="true" data-wp-text="state.count">0</span>
		<span class="fi-visually-hidden" data-wp-text="state.countLabel"></span>
	</button>

	<div
		id="<?php echo esc_attr( $fieldnote_panel_id ); ?>"
		class="wp-block-fieldnote-interactive-reading-list__panel"
		role="region"
		aria-label="<?php esc_attr_e( 'Saved posts', 'fieldnote-interactive' ); ?>"
		data-wp-bind--hidden="!state.isOpen"
	>
		<p class="wp-block-fieldnote-interactive-reading-list__empty" data-wp-bind--hidden="state.hasItems">
			<?php esc_html_e( 'Nothing saved yet. Use the Save for later button on any post to build your list.', 'fieldnote-interactive' ); ?>
		</p>

		<ul class="wp-block-fieldnote-interactive-reading-list__items" data-wp-bind--hidden="!state.hasItems">
			<template data-wp-each--item="state.items" data-wp-each-key="context.item.id">
				<li>
					<a data-wp-bind--href="context.item.url" data-wp-text="context.item.title"></a>
					<button
						type="button"
						class="wp-block-fieldnote-interactive-reading-list__remove"
						data-wp-on--click="actions.remove"
						data-wp-bind--aria-label="state.removeLabel"
					>&times;</button>
				</li>
			</template>
		</ul>

		<button
			type="button"
			class="wp-block-fieldnote-interactive-reading-list__clear"
			data-wp-on--click="actions.clear"
			data-wp-bind--hidden="!state.hasItems"
		><?php esc_html_e( 'Clear list', 'fieldnote-interactive' ); ?></button>
	</div>
</div>
