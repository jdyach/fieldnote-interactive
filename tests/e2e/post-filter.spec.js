/**
 * Filterable Post Grid: client-side navigation with the Interactivity Router.
 */
const { test, expect } = require( '@playwright/test' );

const filter = ( page, name ) =>
	page.locator( '.wp-block-fieldnote-interactive-post-filter__filter', {
		hasText: name,
	} );
const status = ( page ) =>
	page.locator( '.wp-block-fieldnote-interactive-post-filter__status' );

test.describe( 'with JavaScript', () => {
	test.beforeEach( async ( { page } ) => {
		await page.goto( '/explore/' );
		// Survives only if there is no full page load.
		await page.evaluate( () => ( window.__noReload = true ) );
	} );

	test( 'filters update in place, URL and aria-current follow', async ( {
		page,
	} ) => {
		await expect( status( page ) ).toHaveText( /^Showing 1–6 of 12/ );
		await filter( page, 'Process' ).click();
		await expect( page ).toHaveURL( /fi-cat=process/ );
		await expect( status( page ) ).toContainText( 'in Process' );
		await expect( page.locator( '[aria-current="true"]' ) ).toContainText(
			'Process'
		);
		expect( await page.evaluate( () => window.__noReload ) ).toBe( true );
	} );

	test( 'pagination swaps results and moves focus to the summary', async ( {
		page,
	} ) => {
		await page
			.locator(
				'.wp-block-fieldnote-interactive-post-filter__pagination a',
				{
					hasText: '2',
				}
			)
			.click();
		await expect( page ).toHaveURL( /fi-page=2/ );
		await expect( status( page ) ).toContainText( '7–12' );
		await expect( status( page ) ).toBeFocused();
		expect( await page.evaluate( () => window.__noReload ) ).toBe( true );
	} );

	test( 'back button restores the previous results', async ( { page } ) => {
		await filter( page, 'Notes' ).click();
		await expect( status( page ) ).toContainText( 'in Notes' );
		await page.goBack();
		await expect( page ).not.toHaveURL( /fi-cat/ );
		await expect( status( page ) ).toHaveText( /^Showing 1–6 of 12/ );
		expect( await page.evaluate( () => window.__noReload ) ).toBe( true );
	} );
} );

test.describe( 'without JavaScript', () => {
	test.use( { javaScriptEnabled: false } );

	test( 'grid, filters and pagination work as plain links', async ( {
		page,
	} ) => {
		await page.goto( '/explore/' );
		await expect(
			page.locator( '.wp-block-fieldnote-interactive-post-filter__card' )
		).toHaveCount( 6 );
		await filter( page, 'Block themes' ).click();
		await expect( page ).toHaveURL( /fi-cat=block-themes/ );
		await expect( status( page ) ).toContainText( 'in Block themes' );
	} );

	test( 'JS-only controls stay hidden', async ( { page } ) => {
		await page.goto( '/explore/' );
		await expect(
			page
				.locator(
					'.wp-block-fieldnote-interactive-save-button__button'
				)
				.first()
		).toBeHidden();
		await expect(
			page.locator( '.wp-block-fieldnote-interactive-reading-list' )
		).toBeHidden();
	} );

	test( 'unknown or hostile parameters fall back safely', async ( {
		page,
	} ) => {
		await page.goto( '/explore/?fi-cat=nope&fi-page=abc' );
		await expect( status( page ) ).toHaveText( /^Showing 1–6 of 12/ );
		await page.goto( '/explore/?fi-cat=%3Cscript%3Ealert(1)%3C/script%3E' );
		expect( await page.content() ).not.toContain( '<script>alert' );
	} );
} );
