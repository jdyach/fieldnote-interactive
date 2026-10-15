import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';
import './editor.scss';

function Edit( { attributes, setAttributes } ) {
	const { postsPerPage, showExcerpt, showSaveButton } = attributes;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Grid settings', 'fieldnote-interactive' ) }
				>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Posts per page',
							'fieldnote-interactive'
						) }
						value={ postsPerPage }
						onChange={ ( value ) =>
							setAttributes( { postsPerPage: value } )
						}
						min={ 2 }
						max={ 24 }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show excerpt', 'fieldnote-interactive' ) }
						checked={ showExcerpt }
						onChange={ ( value ) =>
							setAttributes( { showExcerpt: value } )
						}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Show "Save for later" buttons',
							'fieldnote-interactive'
						) }
						checked={ showSaveButton }
						onChange={ ( value ) =>
							setAttributes( { showSaveButton: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			{ /* The PHP render is the single source of truth, so the editor
			     preview always matches the front end. */ }
			<ServerSideRender
				block={ metadata.name }
				attributes={ attributes }
			/>
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
