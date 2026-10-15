import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';
import { BookmarkIcon } from '../shared/icons';

function Edit() {
	return (
		<div { ...useBlockProps() }>
			<span
				className="wp-block-fieldnote-interactive-reading-list__toggle"
				aria-hidden="true"
			>
				<BookmarkIcon />
				<span>{ __( 'Reading list', 'fieldnote-interactive' ) }</span>
				<span className="wp-block-fieldnote-interactive-reading-list__count">
					0
				</span>
			</span>
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
