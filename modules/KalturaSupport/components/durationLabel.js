( function( mw, $ ) {"use strict";

	mw.PluginManager.add( 'durationLabel', mw.KBaseComponent.extend({

		defaultConfig: {
			parent: "controlsContainer",
         	order: 4,
			prefix: ' / '
		},
		
		setup: function(){
			var _this = this;
			this.bind( 'durationChange', function(event, duration){
				var formatDuration = mw.seconds2npt( parseFloat( duration ) )
				_this.getComponent().text( _this.getConfig('prefix') + formatDuration );
			});
		},
		getComponent: function() {
			if( !this.$el ) {
				this.$el = $( '<div />' )
							.addClass ( "timers duration" )
							.text( this.getConfig('prefix') + '0:00' );
			}
			return this.$el;
		}
	}));

} )( window.mw, window.jQuery );		