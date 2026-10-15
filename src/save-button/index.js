import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';
import { BookmarkIcon } from '../shared/icons';

/**
 * Editor preview. The real button only works on the front end (it depends on
 * the visitor's own saved list), so the editor shows a static, inert preview.
 */
function Edit() {
	return (
		<div { ...useBlockProps() }>
			<span
				className="wp-block-fieldnote-interactive-save-button__button"
				aria-hidden="true"
			>
				<BookmarkIcon />
				<span>{ __( 'Save for later', 'fieldnote-interactive' ) }</span>
			</span>
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
