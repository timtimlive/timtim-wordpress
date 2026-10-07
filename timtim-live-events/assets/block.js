/* TimTim.Live Events — block editor UI. Plain JavaScript (no build step); the
   block itself is rendered on the server (includes/class-ttle-blocks.php), so
   this file only draws the sidebar and the live preview. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'timtim-live/events', {
		apiVersion: 2,
		title: __( 'TimTim.Live Events', 'timtim-live-events' ),
		description: __( 'Live events from TimTim.Live, with your ticket links.', 'timtim-live-events' ),
		category: 'widgets',
		icon: 'tickets-alt',
		keywords: [ 'events', 'tickets', 'concerts', 'TimTim.Live' ],
		attributes: {
			city: { type: 'string', default: '' },
			country: { type: 'string', default: '' },
			category: { type: 'string', default: '' },
			limit: { type: 'number', default: 6 },
			columns: { type: 'number', default: 3 },
			commissioned: { type: 'boolean', default: false }
		},
		edit: function ( props ) {
			var a = props.attributes;
			var set = function ( name ) {
				return function ( value ) {
					var next = {};
					next[ name ] = value;
					props.setAttributes( next );
				};
			};
			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Which events', 'timtim-live-events' ) },
						el( TextControl, { label: __( 'City', 'timtim-live-events' ), help: __( 'Empty = the city in Settings.', 'timtim-live-events' ), value: a.city, onChange: set( 'city' ) } ),
						el( TextControl, { label: __( 'Country (two letters)', 'timtim-live-events' ), value: a.country, maxLength: 2, onChange: set( 'country' ) } ),
						el( SelectControl, {
							label: __( 'Category', 'timtim-live-events' ),
							value: a.category,
							options: [
								{ label: __( 'Any category', 'timtim-live-events' ), value: '' },
								{ label: __( 'Music', 'timtim-live-events' ), value: 'music' },
								{ label: __( 'Festival', 'timtim-live-events' ), value: 'festival' },
								{ label: __( 'Conference', 'timtim-live-events' ), value: 'conference' },
								{ label: __( 'Nightlife', 'timtim-live-events' ), value: 'nightlife' }
							],
							onChange: set( 'category' )
						} ),
						el( RangeControl, { label: __( 'How many events', 'timtim-live-events' ), min: 1, max: 24, value: a.limit, onChange: set( 'limit' ) } ),
						el( RangeControl, { label: __( 'Columns', 'timtim-live-events' ), min: 1, max: 4, value: a.columns, onChange: set( 'columns' ) } ),
						el( ToggleControl, { label: __( 'Only events that pay a reward', 'timtim-live-events' ), checked: a.commissioned, onChange: set( 'commissioned' ) } )
					)
				),
				el( ServerSideRender, { block: 'timtim-live/events', attributes: a } )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
