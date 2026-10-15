<?php
/**
 * Server render for the Save for Later button.
 *
 * The button is rendered with Interactivity API directives and processed on
 * the server, so the markup is complete before any JavaScript runs. It starts
 * hidden ("!state.isReady") because saving needs JavaScript and localStorage;
 * visitors without JS never see a button that does nothing.
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

$fieldnote_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();

if ( ! $fieldnote_post_id || 'publish' !== get_post_status( $fieldnote_post_id ) ) {
	return;
}

echo fieldnote_interactive_save_button_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
	$fieldnote_post_id,
	get_block_wrapper_attributes()
);
