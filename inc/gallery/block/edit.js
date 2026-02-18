/**
 * Block editor component for the IU Media Folder Gallery block.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 */
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	Placeholder,
	Spinner,
	Notice,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @return {WPElement}
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( { className: 'iu-gallery-editor-preview' } );

	const {
		folderId,
		folderName,
		columns,
		imageSize,
		linkTo,
		orderby,
		order,
		lightbox,
		caption,
	} = attributes;

	const [ folders, setFolders ]         = useState( [] );
	const [ foldersLoading, setFoldersLoading ] = useState( true );
	const [ foldersError, setFoldersError ] = useState( null );

	const [ images, setImages ]           = useState( [] );
	const [ imagesLoading, setImagesLoading ] = useState( false );
	const [ imagesError, setImagesError ] = useState( null );

	// Fetch folder list on mount.
	useEffect( () => {
		apiFetch( { path: '/infinite-uploads/v1/gallery/folders' } )
			.then( ( data ) => {
				setFolders( data );
				setFoldersLoading( false );
			} )
			.catch( ( err ) => {
				setFoldersError( err.message || __( 'Failed to load folders.', 'infinite-uploads' ) );
				setFoldersLoading( false );
			} );
	}, [] );

	// Fetch preview images when folder / display settings change.
	useEffect( () => {
		if ( ! folderId ) {
			setImages( [] );
			return;
		}

		setImagesLoading( true );
		setImagesError( null );

		apiFetch( {
			path: `/infinite-uploads/v1/gallery/images?folder_id=${ folderId }&size=${ imageSize }&orderby=${ orderby }&order=${ order }&limit=12`,
		} )
			.then( ( data ) => {
				setImages( data );
				setImagesLoading( false );
			} )
			.catch( ( err ) => {
				setImagesError( err.message || __( 'Failed to load images.', 'infinite-uploads' ) );
				setImagesLoading( false );
			} );
	}, [ folderId, imageSize, orderby, order ] );

	// Build folder <SelectControl> options.
	const folderOptions = [
		{ label: __( '— Select a folder —', 'infinite-uploads' ), value: 0 },
		...( folders.map( ( f ) => ( { label: f.name, value: f.id } ) ) ),
	];

	const handleFolderChange = ( value ) => {
		const id    = parseInt( value, 10 );
		const found = folders.find( ( f ) => f.id === id );
		setAttributes( {
			folderId:   id,
			folderName: found ? found.name : '',
		} );
	};

	// Render placeholder when no folder is selected.
	const renderPlaceholder = () => (
		<Placeholder
			icon="format-gallery"
			label={ __( 'IU Media Folder Gallery', 'infinite-uploads' ) }
			instructions={ __( 'Select a media folder from the sidebar to display its images as a gallery.', 'infinite-uploads' ) }
		>
			{ foldersLoading && <Spinner /> }
			{ foldersError && (
				<Notice status="error" isDismissible={ false }>{ foldersError }</Notice>
			) }
		</Placeholder>
	);

	// Render image grid preview.
	const renderPreview = () => {
		if ( imagesLoading ) {
			return (
				<div className="iu-gallery-editor-loading">
					<Spinner />
					<span>{ __( 'Loading images…', 'infinite-uploads' ) }</span>
				</div>
			);
		}

		if ( imagesError ) {
			return (
				<Notice status="error" isDismissible={ false }>{ imagesError }</Notice>
			);
		}

		if ( ! images.length ) {
			return (
				<p className="iu-gallery-editor-empty">
					{ sprintf(
						/* translators: %s: folder name */
						__( 'No images found in "%s".', 'infinite-uploads' ),
						folderName || __( 'this folder', 'infinite-uploads' )
					) }
				</p>
			);
		}

		return (
			<div
				className={ `iu-gallery iu-gallery-columns-${ columns }` }
				style={ { '--iu-columns': columns } }
			>
				{ images.map( ( img ) => (
					<figure key={ img.id } className="iu-gallery-item">
						<img
							src={ img.src }
							alt={ img.alt }
							className="iu-gallery-img"
						/>
					</figure>
				) ) }
			</div>
		);
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery Settings', 'infinite-uploads' ) }>
					{ foldersLoading ? (
						<Spinner />
					) : (
						<SelectControl
							label={ __( 'Folder', 'infinite-uploads' ) }
							value={ folderId }
							options={ folderOptions }
							onChange={ handleFolderChange }
						/>
					) }

					<RangeControl
						label={ __( 'Columns', 'infinite-uploads' ) }
						value={ columns }
						onChange={ ( value ) => setAttributes( { columns: value } ) }
						min={ 1 }
						max={ 6 }
					/>

					<SelectControl
						label={ __( 'Image Size', 'infinite-uploads' ) }
						value={ imageSize }
						options={ [
							{ label: __( 'Thumbnail', 'infinite-uploads' ), value: 'thumbnail' },
							{ label: __( 'Medium', 'infinite-uploads' ),     value: 'medium'    },
							{ label: __( 'Large', 'infinite-uploads' ),      value: 'large'     },
							{ label: __( 'Full Size', 'infinite-uploads' ),  value: 'full'      },
						] }
						onChange={ ( value ) => setAttributes( { imageSize: value } ) }
					/>

					<SelectControl
						label={ __( 'Link To', 'infinite-uploads' ) }
						value={ linkTo }
						options={ [
							{ label: __( 'None', 'infinite-uploads' ),            value: 'none'       },
							{ label: __( 'Media File', 'infinite-uploads' ),      value: 'file'       },
							{ label: __( 'Attachment Page', 'infinite-uploads' ), value: 'attachment' },
						] }
						onChange={ ( value ) => setAttributes( { linkTo: value } ) }
					/>

					<SelectControl
						label={ __( 'Order By', 'infinite-uploads' ) }
						value={ orderby }
						options={ [
							{ label: __( 'Date', 'infinite-uploads' ),  value: 'date'  },
							{ label: __( 'Title', 'infinite-uploads' ), value: 'title' },
							{ label: __( 'Random', 'infinite-uploads' ), value: 'rand' },
						] }
						onChange={ ( value ) => setAttributes( { orderby: value } ) }
					/>

					<SelectControl
						label={ __( 'Order', 'infinite-uploads' ) }
						value={ order }
						options={ [
							{ label: __( 'Descending', 'infinite-uploads' ), value: 'DESC' },
							{ label: __( 'Ascending', 'infinite-uploads' ),  value: 'ASC'  },
						] }
						onChange={ ( value ) => setAttributes( { order: value } ) }
					/>

					<ToggleControl
						label={ __( 'Enable Lightbox', 'infinite-uploads' ) }
						help={ __( 'Open full-size images in a PhotoSwipe lightbox.', 'infinite-uploads' ) }
						checked={ lightbox }
						onChange={ ( value ) => setAttributes( { lightbox: value } ) }
					/>

					<ToggleControl
						label={ __( 'Show Captions', 'infinite-uploads' ) }
						checked={ caption }
						onChange={ ( value ) => setAttributes( { caption: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ ! folderId ? renderPlaceholder() : renderPreview() }
			</div>
		</>
	);
}
