<?php
/**
 * Plugin Name:       Fieldnote Interactive
 * Plugin URI:        https://github.com/jdyach/fieldnote-interactive
 * Description:       Interactive blocks built with the WordPress Interactivity API: a filterable post grid with client-side navigation, and a "Save for later" reading list shared across blocks.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Jacqui Dyach
 * Author URI:        https://jacquid.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fieldnote-interactive
 *
 * @package FieldnoteInteractive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FIELDNOTE_INTERACTIVE_VERSION', '1.0.0' );
define( 'FIELDNOTE_INTERACTIVE_DIR', __DIR__ );

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/block-hooks.php';

/**
 * Register every block in build/.
 *
 * WordPress 6.8+ can register a whole collection from the generated
 * blocks-manifest.php in one call, which skips reading each block.json from
 * disk on every request.
 *
 * @since 1.0.0
 * @return void
 */
function fieldnote_interactive_register_blocks() {
	$build    = __DIR__ . '/build';
	$manifest = $build . '/blocks-manifest.php';

	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && file_exists( $manifest ) ) {
		wp_register_block_types_from_metadata_collection( $build, $manifest );
		return;
	}

	foreach ( glob( $build . '/*/block.json' ) as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}
add_action( 'init', 'fieldnote_interactive_register_blocks' );
