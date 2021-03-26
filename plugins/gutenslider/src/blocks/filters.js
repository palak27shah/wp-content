/**
 * WordPress Dependencies
 */
const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { Fragment }	= wp.element;
const { InspectorAdvancedControls }	= wp.blockEditor;
const { createHigherOrderComponent } = wp.compose;
const { ToggleControl, BaseControl } = wp.components;

/**
 * Add mobile visibility controls on Advanced Block Panel.
 *
 * @param {function} BlockEdit Block edit component.
 *
 * @return {function} BlockEdit Modified block edit component.
 */
const gutensliderWithAdvancedControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const {
			attributes,
			setAttributes,
			isSelected,
		} = props;

		const {
			visibleOnDesktop,
			visibleOnTablet,
			visibleOnMobile,
			gsBlockId,
		} = attributes;

		if ( props.name === 'eedee/block-gutenslider' ) {
			return (
				<Fragment>
					<BlockEdit { ...props } />
					{ isSelected &&
						<InspectorAdvancedControls>
							<BaseControl
								label={ __( 'Slider Id', 'eedee' ) }
							>
								<code style={ { display: 'block' } }>{ `gutenslider-${ gsBlockId }` }</code>
							</BaseControl>
							<ToggleControl
								label={ __( 'Show On Desktop' ) }
								checked={ !! visibleOnDesktop }
								onChange={ () => setAttributes( { visibleOnDesktop: ! visibleOnDesktop } ) }
								help={ !! visibleOnDesktop ? __( 'Slider is shown on large screens.', 'eedee-gutenslider' ) : __( 'Slider is hidden on large screens.', 'eedee-gutenslider' ) }
							/>
							<ToggleControl
								label={ __( 'Show On Tablet' ) }
								checked={ !! visibleOnTablet }
								onChange={ () => setAttributes( { visibleOnTablet: ! visibleOnTablet } ) }
								help={ !! visibleOnTablet ? __( 'Slider is shown on medium screens.', 'eedee-gutenslider' ) : __( 'Slider is hidden on medium screens.', 'eedee-gutenslider' ) }
							/>
							<ToggleControl
								label={ __( 'Show On Mobile' ) }
								checked={ !! visibleOnMobile }
								onChange={ () => setAttributes( { visibleOnMobile: ! visibleOnMobile } ) }
								help={ !! visibleOnMobile ? __( 'Slider is shown on small screens.', 'eedee-gutenslider' ) : __( 'Slider is hidden on small screens.', 'eedee-gutenslider' ) }
							/>
						</InspectorAdvancedControls>
					}
				</Fragment>
			);
		}

		if ( props.name === 'eedee/block-gutenslide' ) {
			return (
				<Fragment>
					<BlockEdit { ...props } />
					{ isSelected &&
						<InspectorAdvancedControls>
							<BaseControl
								label={ __( 'Slide Id', 'eedee' ) }
							>
								<code style={ { display: 'block' } }>{ `gutenslide-${ gsBlockId }` }</code>
							</BaseControl>
						</InspectorAdvancedControls>
					}
				</Fragment>
			);
		}

		return <BlockEdit { ...props } />;
	};
}, 'gutensliderWithAdvancedControls' );

addFilter(
	'editor.BlockEdit',
	'eedee/gutenslider',
	gutensliderWithAdvancedControls
);
