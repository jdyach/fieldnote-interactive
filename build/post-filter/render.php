<?php
/**
 * Server render for the Filterable Post Grid.
 *
 * Progressive enhancement is the core idea here:
 *
 * 1. Every filter and page control is a plain link with ?fi-cat= / ?fi-page=
 *    query args, and this file reads those args to build the query. With
 *    JavaScript disabled the block is a fully working, server-rendered
 *    filter.
 * 2. With JavaScript, data-wp-on--click="actions.navigate" intercepts the
 *    click, and the Interactivity Router fetches the same URL and swaps only
 *    the data-wp-router-region below. Same HTML, no full reload.
 *
 * Content inside the router region is rendered as plain server HTML rather
 * than bound to client state, so the region always shows exactly what the
 * server rendered for that URL.
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

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only, public filter parameters; nothing is changed on the server.
$fieldnote_cat   = isset( $_GET['fi-cat'] ) ? sanitize_title( wp_unslash( $_GET['fi-cat'] ) ) : '';
$fieldnote_paged = isset( $_GET['fi-page'] ) ? max( 1, absint( $_GET['fi-page'] ) ) : 1;
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$fieldnote_categories = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'name',
	)
);

// Ignore unknown category slugs instead of showing an empty grid.
$fieldnote_active_term = $fieldnote_cat ? get_category_by_slug( $fieldnote_cat ) : false;
if ( ! $fieldnote_active_term ) {
	$fieldnote_cat = '';
}

$fieldnote_per_page = isset( $attributes['postsPerPage'] ) ? max( 1, min( 24, (int) $attributes['postsPerPage'] ) ) : 6;

$fieldnote_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $fieldnote_per_page,
		'paged'               => $fieldnote_paged,
		'category_name'       => $fieldnote_cat,
		'ignore_sticky_posts' => true,
	)
);

$fieldnote_total = (int) $fieldnote_query->found_posts;
$fieldnote_pages = (int) $fieldnote_query->max_num_pages;

// Base URL for links: the current URL without our own parameters.
$fieldnote_base_url = remove_query_arg( array( 'fi-cat', 'fi-page' ) );

$fieldnote_url = static function ( $cat, $page = 1 ) use ( $fieldnote_base_url ) {
	$args = array();
	if ( $cat ) {
		$args['fi-cat'] = $cat;
	}
	if ( $page > 1 ) {
		$args['fi-page'] = $page;
	}
	return add_query_arg( $args, $fieldnote_base_url );
};

// Human-readable summary, announced by screen readers after each update.
if ( 0 === $fieldnote_total ) {
	$fieldnote_status = __( 'No posts found.', 'fieldnote-interactive' );
} else {
	$fieldnote_first = ( ( $fieldnote_paged - 1 ) * $fieldnote_per_page ) + 1;
	$fieldnote_last  = min( $fieldnote_total, $fieldnote_paged * $fieldnote_per_page );

	if ( $fieldnote_active_term ) {
		$fieldnote_status = sprintf(
			/* translators: 1: first post number, 2: last post number, 3: total posts, 4: category name. */
			__( 'Showing %1$d–%2$d of %3$d posts in %4$s', 'fieldnote-interactive' ),
			$fieldnote_first,
			$fieldnote_last,
			$fieldnote_total,
			$fieldnote_active_term->name
		);
	} else {
		$fieldnote_status = sprintf(
			/* translators: 1: first post number, 2: last post number, 3: total posts. */
			__( 'Showing %1$d–%2$d of %3$d posts', 'fieldnote-interactive' ),
			$fieldnote_first,
			$fieldnote_last,
			$fieldnote_total
		);
	}
}

$fieldnote_all_count  = (int) wp_count_posts( 'post' )->publish;
$fieldnote_show_save  = ! empty( $attributes['showSaveButton'] );
$fieldnote_show_excpt = ! empty( $attributes['showExcerpt'] );
$fieldnote_region_id  = 'fieldnote-interactive/post-filter';
?>
<div
	<?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="fieldnote-interactive/post-filter"
	data-wp-class--is-loading="state.isLoading"
	data-wp-bind--aria-busy="state.isLoading"
>
	<div
		class="wp-block-fieldnote-interactive-post-filter__region"
		data-wp-interactive="fieldnote-interactive/post-filter"
		data-wp-router-region="<?php echo esc_attr( $fieldnote_region_id ); ?>"
	>
		<?php if ( $fieldnote_categories ) : ?>
			<nav class="wp-block-fieldnote-interactive-post-filter__filters" aria-label="<?php esc_attr_e( 'Filter posts by category', 'fieldnote-interactive' ); ?>">
				<ul role="list">
					<li>
						<a
							class="wp-block-fieldnote-interactive-post-filter__filter"
							href="<?php echo esc_url( $fieldnote_url( '' ) ); ?>"
							<?php echo '' === $fieldnote_cat ? 'aria-current="true"' : ''; ?>
							data-wp-on--click="actions.navigate"
							data-wp-on--mouseenter="actions.prefetch"
							data-wp-on--focus="actions.prefetch"
						>
							<?php esc_html_e( 'All', 'fieldnote-interactive' ); ?>
							<span class="wp-block-fieldnote-interactive-post-filter__filter-count"><?php echo esc_html( number_format_i18n( $fieldnote_all_count ) ); ?></span>
						</a>
					</li>
					<?php foreach ( $fieldnote_categories as $fieldnote_term ) : ?>
						<li>
							<a
								class="wp-block-fieldnote-interactive-post-filter__filter"
								href="<?php echo esc_url( $fieldnote_url( $fieldnote_term->slug ) ); ?>"
								<?php echo $fieldnote_term->slug === $fieldnote_cat ? 'aria-current="true"' : ''; ?>
								data-wp-on--click="actions.navigate"
								data-wp-on--mouseenter="actions.prefetch"
								data-wp-on--focus="actions.prefetch"
							>
								<?php echo esc_html( $fieldnote_term->name ); ?>
								<span class="wp-block-fieldnote-interactive-post-filter__filter-count"><?php echo esc_html( number_format_i18n( $fieldnote_term->count ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<p class="wp-block-fieldnote-interactive-post-filter__status" role="status" aria-live="polite" tabindex="-1"><?php echo esc_html( $fieldnote_status ); ?></p>

		<?php if ( $fieldnote_query->have_posts() ) : ?>
			<ul class="wp-block-fieldnote-interactive-post-filter__grid" role="list">
				<?php
				while ( $fieldnote_query->have_posts() ) :
					$fieldnote_query->the_post();
					$fieldnote_post_cats = get_the_category();
					?>
					<li class="wp-block-fieldnote-interactive-post-filter__card">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="wp-block-fieldnote-interactive-post-filter__image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
							</a>
						<?php endif; ?>

						<p class="wp-block-fieldnote-interactive-post-filter__meta">
							<?php if ( $fieldnote_post_cats ) : ?>
								<span class="wp-block-fieldnote-interactive-post-filter__category"><?php echo esc_html( $fieldnote_post_cats[0]->name ); ?></span>
							<?php endif; ?>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</p>

						<h3 class="wp-block-fieldnote-interactive-post-filter__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<?php if ( $fieldnote_show_excpt ) : ?>
							<p class="wp-block-fieldnote-interactive-post-filter__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<?php endif; ?>

						<?php if ( $fieldnote_show_save ) : ?>
						<div class="wp-block-fieldnote-interactive-post-filter__footer">
							<?php
							echo fieldnote_interactive_save_button_markup( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside the helper.
							?>
						</div>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p class="wp-block-fieldnote-interactive-post-filter__empty"><?php esc_html_e( 'Nothing here yet. Try another category.', 'fieldnote-interactive' ); ?></p>
		<?php endif; ?>

		<?php if ( $fieldnote_pages > 1 ) : ?>
			<nav class="wp-block-fieldnote-interactive-post-filter__pagination" aria-label="<?php esc_attr_e( 'Posts pages', 'fieldnote-interactive' ); ?>">
				<ul role="list">
					<?php if ( $fieldnote_paged > 1 ) : ?>
						<li>
							<a href="<?php echo esc_url( $fieldnote_url( $fieldnote_cat, $fieldnote_paged - 1 ) ); ?>" data-wp-on--click="actions.navigate" data-wp-on--mouseenter="actions.prefetch">
								<span aria-hidden="true">&larr;</span>
								<span class="fi-visually-hidden"><?php esc_html_e( 'Previous page', 'fieldnote-interactive' ); ?></span>
							</a>
						</li>
					<?php endif; ?>

					<?php for ( $fieldnote_i = 1; $fieldnote_i <= $fieldnote_pages; $fieldnote_i++ ) : ?>
						<li>
							<?php if ( $fieldnote_i === $fieldnote_paged ) : ?>
								<span aria-current="page">
									<span class="fi-visually-hidden"><?php esc_html_e( 'Page', 'fieldnote-interactive' ); ?></span>
									<?php echo esc_html( number_format_i18n( $fieldnote_i ) ); ?>
								</span>
							<?php else : ?>
								<a href="<?php echo esc_url( $fieldnote_url( $fieldnote_cat, $fieldnote_i ) ); ?>" data-wp-on--click="actions.navigate" data-wp-on--mouseenter="actions.prefetch">
									<span class="fi-visually-hidden"><?php esc_html_e( 'Page', 'fieldnote-interactive' ); ?></span>
									<?php echo esc_html( number_format_i18n( $fieldnote_i ) ); ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endfor; ?>

					<?php if ( $fieldnote_paged < $fieldnote_pages ) : ?>
						<li>
							<a href="<?php echo esc_url( $fieldnote_url( $fieldnote_cat, $fieldnote_paged + 1 ) ); ?>" data-wp-on--click="actions.navigate" data-wp-on--mouseenter="actions.prefetch">
								<span class="fi-visually-hidden"><?php esc_html_e( 'Next page', 'fieldnote-interactive' ); ?></span>
								<span aria-hidden="true">&rarr;</span>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</div>
<?php
wp_reset_postdata();
