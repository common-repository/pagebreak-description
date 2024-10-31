import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { preview } = attributes;

	if ( preview ) {
		return (
			<div className="pagebreak-description-block-preview">
				<img src = { pagebreak_description_preview_block.url } alt="Preview" />
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<TextControl
				label = { __( 'Description of this page', 'pagebreak-description' ) }
				value = { attributes.thisdesc }
				onChange = { ( value ) => setAttributes( { thisdesc: value } ) }
			/>
			<span className="pagebreak-description-pagebreak">
			-------- { __( 'Page break' ) } --------
			</span>
			<TextControl
				label = { __( 'Description of next page', 'pagebreak-description' ) }
				value = { attributes.nextdesc }
				onChange = { ( value ) => setAttributes( { nextdesc: value } ) }
			/>
		</div>
	);
}
