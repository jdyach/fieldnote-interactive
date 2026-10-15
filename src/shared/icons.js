/**
 * Bookmark icon shared by the editor previews. The front end renders the same
 * SVG from PHP (fieldnote_interactive_bookmark_icon()).
 */
export function BookmarkIcon() {
	return (
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path
				className="fi-icon-fill"
				d="M6 3.75h12a.75.75 0 0 1 .75.75v16.2l-6.75-4.5-6.75 4.5V4.5A.75.75 0 0 1 6 3.75Z"
				stroke="currentColor"
				strokeWidth="1.6"
				strokeLinejoin="round"
			/>
		</svg>
	);
}
