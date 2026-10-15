/**
 * End-to-end tests for the front end.
 *
 * They run against any WordPress site that has the Fieldnote theme and this
 * plugin active, plus the demo content from _playground/seed.php (an
 * "Explore" page with the grid and 12 posts in 3 categories).
 *
 *   WP_BASE_URL=http://fieldnote.local npm run test:e2e
 */
const { defineConfig, devices } = require( '@playwright/test' );

module.exports = defineConfig( {
	testDir: './tests/e2e',
	timeout: 30_000,
	fullyParallel: false,
	workers: 1,
	reporter: [ [ 'list' ] ],
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8888',
		trace: 'retain-on-failure',
		...devices[ 'Desktop Chrome' ],
		viewport: { width: 1300, height: 900 },
	},
} );
