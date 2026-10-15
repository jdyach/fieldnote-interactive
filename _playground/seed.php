<?php
/**
 * Demo content for the WordPress Playground "Try it live" link.
 *
 * Runs once inside Playground (see blueprint.json, which embeds this file).
 * Creates categorized posts with featured images, an "Explore" page holding
 * the Filterable Post Grid, and a landing page built from the Fieldnote
 * theme's own pattern.
 *
 * Not loaded by the plugin itself.
 *
 * @package FieldnoteInteractive
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride, WordPress.NamingConventions.PrefixAllGlobals -- Standalone demo script.

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

wp_set_current_user( 1 );

// Remove the default "Hello world!" post and sample page.
foreach ( get_posts(
	array(
		'post_type'   => array( 'post', 'page' ),
		'numberposts' => -1,
		'post_status' => 'any',
	)
) as $old ) {
	wp_delete_post( $old->ID, true );
}

// Featured images: reuse the theme's original artwork.
$images = array();
foreach ( array( 'studio-shapes', 'field-study', 'notebook' ) as $name ) {
	$source = get_theme_file_path( "assets/images/$name.webp" );
	if ( ! file_exists( $source ) ) {
		continue;
	}
	$upload = wp_upload_bits( "$name.webp", null, file_get_contents( $source ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! empty( $upload['error'] ) ) {
		continue;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $name,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	$images[] = $id;
}

$categories = array(
	wp_create_category( 'Process' ),
	wp_create_category( 'Block themes' ),
	wp_create_category( 'Notes' ),
);

$body = '<!-- wp:paragraph --><p>Block themes move layout decisions out of PHP and into data, which changes who can make them: designers and editors, not only developers.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Start from theme.json</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Every color, size and space on this site comes from a preset. Change one value in Global Styles and it updates everywhere it is used.</p><!-- /wp:paragraph -->
<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>Design tokens are a contract between the designer and the editor.</p><!-- /wp:paragraph --><cite>Studio notebook</cite></blockquote><!-- /wp:quote -->';

$titles = array(
	'Why we moved every client to block themes',
	'A fluid type scale you can actually reason about',
	'Section styles: color schemes without custom CSS',
	'Writing patterns editors will not break',
	'What theme.json replaced in our old functions.php',
	'Notes on handing off a site to a non-developer',
	'Designing for the person who edits the site next',
	'Patterns, not page builders',
	'A checklist for accessible color schemes',
	'Prefetching: making clicks feel instant',
	'What we learned shipping six block themes',
	'The smallest useful functions.php',
);

foreach ( $titles as $i => $title ) {
	$post_id = wp_insert_post(
		array(
			'post_title'    => $title,
			'post_content'  => $body,
			'post_excerpt'  => 'A short write-up from the studio on building maintainable WordPress sites with blocks, presets and patterns.',
			'post_status'   => 'publish',
			'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( $i + 1 ) * 4 * DAY_IN_SECONDS ),
			'post_category' => array( $categories[ $i % 3 ] ),
		)
	);
	if ( $images ) {
		set_post_thumbnail( $post_id, $images[ $i % count( $images ) ] );
	}
}

// Landing page built from the Fieldnote theme's page pattern, when available.
$landing_content = '<!-- wp:pattern {"slug":"fieldnote/page-landing"} /-->';
$home            = wp_insert_post(
	array(
		'post_title'    => 'Home',
		'post_type'     => 'page',
		'post_status'   => 'publish',
		'menu_order'    => 1,
		'post_content'  => $landing_content,
		'page_template' => 'page-landing',
	)
);

$explore = wp_insert_post(
	array(
		'post_title'   => 'Explore',
		'post_name'    => 'explore',
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'menu_order'   => 0,
		'post_content' => '<!-- wp:paragraph {"fontSize":"large"} --><p class="has-large-font-size">Filter by topic and page through posts without a reload. Save anything to your reading list.</p><!-- /wp:paragraph --><!-- wp:fieldnote-interactive/post-filter {"postsPerPage":6,"align":"wide"} /-->',
	)
);

$notes = wp_insert_post(
	array(
		'post_title'  => 'Notes',
		'post_type'   => 'page',
		'post_status' => 'publish',
		'menu_order'  => 2,
	)
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'page_for_posts', $notes );
update_option( 'blogname', 'Fieldnote' );
update_option( 'blogdescription', 'A small studio for thoughtful websites.' );
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();
