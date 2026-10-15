<?php
/**
 * Block Hooks: zero-configuration placement.
 *
 * The block.json files ask WordPress to insert the Reading List after every
 * Navigation block and the Save button after every Post Title. That is too
 * broad (footer menus, every title inside a Query Loop), so this filter
 * narrows each hook to where it makes sense:
 *
 * - Reading List: only in template parts in the "header" area.
 * - Save button: only in single post templates (single, single-*).
 *
 * Site owners can still move or remove the hooked blocks in the Site Editor;
 * WordPress remembers that choice.
 *
 * @package FieldnoteInteractive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit where our hooked blocks are inserted.
 *
 * @since 1.0.0
 * @param string[]                        $hooked_block_types Block types to insert.
 * @param string                          $relative_position  before|after|first_child|last_child.
 * @param string                          $anchor_block_type  Block the hook is attached to.
 * @param WP_Block_Template|WP_Post|array $context            Template, template part, pattern or post being rendered.
 * @return string[] Filtered block types.
 */
function fieldnote_interactive_limit_block_hooks( $hooked_block_types, $relative_position, $anchor_block_type, $context ) {
	$is_template = $context instanceof WP_Block_Template;

	if ( 'core/navigation' === $anchor_block_type ) {
		$is_header = $is_template && 'wp_template_part' === $context->type && 'header' === $context->area;
		if ( ! $is_header ) {
			$hooked_block_types = array_diff( $hooked_block_types, array( 'fieldnote-interactive/reading-list' ) );
		}
	}

	if ( 'core/post-title' === $anchor_block_type ) {
		$is_single = $is_template && 'wp_template' === $context->type && preg_match( '/^single(-|$)/', $context->slug );
		if ( ! $is_single ) {
			$hooked_block_types = array_diff( $hooked_block_types, array( 'fieldnote-interactive/save-button' ) );
		}
	}

	return array_values( $hooked_block_types );
}
add_filter( 'hooked_block_types', 'fieldnote_interactive_limit_block_hooks', 10, 4 );
