/**
 * Save for Later + Reading List: two blocks sharing one global store.
 */
const { test, expect } = require( '@playwright/test' );

const saveButtons = ( page ) =>
	page.locator( '.wp-block-fieldnote-interactive-save-button__button' );
const count = ( page ) =>
	page.locator( '.wp-block-fieldnote-interactive-reading-list__count' );
const toggle = ( page ) =>
	page.locator( '.wp-block-fieldnote-interactive-reading-list__toggle' );
const panel = ( page ) =>
	page.locator( '.wp-block-fieldnote-interactive-reading-list__panel' );

test.beforeEach( async ( { page } ) => {
	await page.goto( '/explore/' );
	await page.evaluate( () => localStorage.clear() );
	await page.reload();
	await expect( saveButtons( page ).first() ).toBeVisible();
} );

test( 'saving a post updates the button and the header counter', async ( {
	page,
} ) => {
	const button = saveButtons( page ).first();
	await expect( count( page ) ).toHaveText( '0' );
	await button.click();
	await expect( button ).toHaveAttribute( 'aria-pressed', 'true' );
	await expect( button ).toHaveText( 'Saved' );
	await expect( count( page ) ).toHaveText( '1' );
} );

test( 'saved state survives client-side navigation and reloads', async ( {
	page,
} ) => {
	const title = await page
		.locator( '.wp-block-fieldnote-interactive-post-filter__title' )
		.first()
		.innerText();
	await saveButtons( page ).first().click();

	await page
		.locator( '.wp-block-fieldnote-interactive-post-filter__filter', {
			hasText: 'Process',
		} )
		.click();
	await expect( page ).toHaveURL( /fi-cat=process/ );
	await page
		.locator( '.wp-block-fieldnote-interactive-post-filter__filter', {
			hasText: 'All',
		} )
		.click();
	await expect( page ).not.toHaveURL( /fi-cat/ );

	const card = page.locator(
		'.wp-block-fieldnote-interactive-post-filter__card',
		{ hasText: title }
	);
	await expect( card.locator( 'button' ) ).toHaveAttribute(
		'aria-pressed',
		'true'
	);

	await page.reload();
	await expect( count( page ) ).toHaveText( '1' );
} );

test( 'panel lists items, removes without closing, closes on Escape', async ( {
	page,
} ) => {
	await saveButtons( page ).nth( 0 ).click();
	await saveButtons( page ).nth( 1 ).click();
	await expect( count( page ) ).toHaveText( '2' );

	await toggle( page ).click();
	await expect( toggle( page ) ).toHaveAttribute( 'aria-expanded', 'true' );
	await expect( panel( page ).locator( 'li' ) ).toHaveCount( 2 );

	const remove = panel( page ).locator( 'li button' ).first();
	await expect( remove ).toHaveAttribute( 'aria-label', /^Remove / );
	await remove.click();
	await expect( count( page ) ).toHaveText( '1' );
	await expect( panel( page ) ).toBeVisible();

	await page.keyboard.press( 'Escape' );
	await expect( panel( page ) ).toBeHidden();
	await expect( toggle( page ) ).toBeFocused();
} );

test( 'clear list and outside click', async ( { page } ) => {
	await saveButtons( page ).first().click();
	await toggle( page ).click();
	await panel( page )
		.locator( '.wp-block-fieldnote-interactive-reading-list__clear' )
		.click();
	await expect( count( page ) ).toHaveText( '0' );
	await expect(
		panel( page ).locator(
			'.wp-block-fieldnote-interactive-reading-list__empty'
		)
	).toBeVisible();

	await page.locator( 'main' ).click( { position: { x: 5, y: 5 } } );
	await expect( panel( page ) ).toBeHidden();
} );

test( 'block hooks: button on single posts, not in blog loops or footer', async ( {
	page,
} ) => {
	await page.goto( '/notes/' );
	await expect(
		page.locator(
			'.wp-block-post-template .wp-block-fieldnote-interactive-save-button'
		)
	).toHaveCount( 0 );
	await expect(
		page.locator( 'footer .wp-block-fieldnote-interactive-reading-list' )
	).toHaveCount( 0 );

	await page.locator( '.wp-block-post-title a' ).first().click();
	const single = page.locator(
		'main .wp-block-fieldnote-interactive-save-button__button'
	);
	await expect( single ).toBeVisible();
	await single.click();
	await expect( count( page ) ).toHaveText( '1' );
} );

test( 'no console errors', async ( { page } ) => {
	const errors = [];
	page.on( 'pageerror', ( e ) => errors.push( e.message ) );
	await page.goto( '/explore/' );
	await saveButtons( page ).first().click();
	await page
		.locator( '.wp-block-fieldnote-interactive-post-filter__filter', {
			hasText: 'Notes',
		} )
		.click();
	await expect( page ).toHaveURL( /fi-cat=notes/ );
	expect( errors ).toEqual( [] );
} );
