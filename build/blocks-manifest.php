<?php
// This file is generated. Do not modify it manually.
return array(
	'post-filter' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'fieldnote-interactive/post-filter',
		'version' => '1.0.0',
		'title' => 'Filterable Post Grid',
		'category' => 'widgets',
		'icon' => 'filter',
		'description' => 'A grid of posts with category filters and pagination that update in place, without a page reload. Falls back to normal links when JavaScript is unavailable.',
		'keywords' => array(
			'posts',
			'filter',
			'category',
			'grid',
			'blog'
		),
		'textdomain' => 'fieldnote-interactive',
		'attributes' => array(
			'postsPerPage' => array(
				'type' => 'number',
				'default' => 6
			),
			'showExcerpt' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSaveButton' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'interactivity' => true,
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:./view.js'
	),
	'reading-list' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'fieldnote-interactive/reading-list',
		'version' => '1.0.0',
		'title' => 'Reading List',
		'category' => 'widgets',
		'icon' => 'book-alt',
		'description' => 'A header button showing how many posts the visitor has saved, with a panel to revisit or remove them.',
		'keywords' => array(
			'bookmarks',
			'favorites',
			'saved',
			'reading list'
		),
		'textdomain' => 'fieldnote-interactive',
		'supports' => array(
			'interactivity' => true,
			'html' => false,
			'multiple' => false,
			'typography' => array(
				'fontSize' => true
			)
		),
		'blockHooks' => array(
			'core/navigation' => 'after'
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:./view.js'
	),
	'save-button' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'fieldnote-interactive/save-button',
		'version' => '1.0.0',
		'title' => 'Save for Later',
		'category' => 'widgets',
		'icon' => 'heart',
		'description' => 'A bookmark toggle that adds the current post to the visitor\'s reading list. Works in single templates and inside Query Loops.',
		'keywords' => array(
			'bookmark',
			'favorite',
			'reading list',
			'save'
		),
		'textdomain' => 'fieldnote-interactive',
		'usesContext' => array(
			'postId',
			'postType'
		),
		'supports' => array(
			'interactivity' => true,
			'html' => false,
			'spacing' => array(
				'margin' => true
			),
			'typography' => array(
				'fontSize' => true
			)
		),
		'blockHooks' => array(
			'core/post-title' => 'after'
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:./view.js'
	)
);
