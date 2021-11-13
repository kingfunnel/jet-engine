(function( $ ) {

	'use strict';

	var JetEngineMetaBoxes = {

		init: function() {

			var self = this;

			self.initDateFields( $( '.cx-control' ) );

			$( document ).on( 'cx-control-init', function( event, data ) {
				self.initDateFields( $( data.target ) );
			} );


		},

		/**
		 * Initialize date and time pickers
		 *
		 * @return {[type]} [description]
		 */
		initDateFields: function( $scope ) {

			var isRTL = window.JetEngineMetaBoxesConfig.isRTL || false,
				i18n  = window.JetEngineMetaBoxesConfig.i18n || {};

			$( 'input[type="date"]:not(.hasDatepicker)', $scope ).each( function() {

				var $this = $( this );

				//$this.attr( 'type', 'text' );
				$this.prop( 'type', 'text' );

				$this.datepicker({
					dateFormat: 'yy-mm-dd',
					nextText: '>>',
					prevText: '<<',
					isRTL: isRTL,
					beforeShow: function( input, datepicker ) {
						datepicker.dpDiv.addClass( 'jet-engine-datepicker' );
					},
				});

			} );

			$( 'input[type="time"]:not(.hasDatepicker)', $scope ).each( function() {

				var $this = $( this );

				//$this.attr( 'type', 'text' );
				$this.prop( 'type', 'text' );

				$this.timepicker({
					isRTL: isRTL,
					timeOnlyTitle: i18n.timeOnlyTitle,
					timeText: i18n.timeText,
					hourText: i18n.hourText,
					minuteText: i18n.minuteText,
					currentText: i18n.currentText,
					closeText: i18n.closeText,
					beforeShow: function( input, datepicker ) {
						datepicker.dpDiv.addClass( 'jet-engine-datepicker' );
					},
				});

			} );

			$( 'input[type="datetime-local"]:not(.hasDatepicker)', $scope ).each( function() {

				var $this = $( this );

				//$this.attr( 'type', 'text' );
				$this.prop( 'type', 'text' );

				$this.datetimepicker({
					dateFormat: 'yy-mm-dd',
					timeFormat: 'HH:mm',
					separator: 'T',
					nextText: '>>',
					prevText: '<<',
					isRTL: isRTL,
					timeText: i18n.timeText,
					hourText: i18n.hourText,
					minuteText: i18n.minuteText,
					currentText: i18n.currentText,
					closeText: i18n.closeText,
					beforeShow: function( input, datepicker ) {
						datepicker.dpDiv.addClass( 'jet-engine-datepicker' );
					},
				});

			} );

		},

	};

	JetEngineMetaBoxes.init();

})( jQuery );
