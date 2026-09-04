/**
 * Core Framework Admin Settings Interaction Pipeline.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

jQuery( document ).ready( function( $ ) {

	// 1. DE ALLES IN-/UITSCHAKELEN LOGICA
	$( '.js-dwp-toggle-all' ).on( 'click', function() {
		var $button = $( this );
		var state   = $button.data( 'state' );

		if ( 'select' === state ) {
			// Vink alles aan
			$( '.dwp-modules-grid input[type="checkbox"]' ).prop( 'checked', true );
			$( '.dwp-module-card' ).removeClass( 'is-disabled' );
			$button.data( 'state', 'unselect' ).text( 'Alles uitschakelen' );
		} else {
			// Vink alles uit
			$( '.dwp-modules-grid input[type="checkbox"]' ).prop( 'checked', false );
			$( '.dwp-module-card' ).addClass( 'is-disabled' );
			$button.data( 'state', 'select' ).text( 'Alles inschakelen' );
		}
	});

	// 2. DE DYNAMISCHE IN-/UITKLAP LOGICA PER MODULEKAART
	$( '.js-dwp-module-toggle' ).on( 'change', function() {
		var $checkbox = $( this );
		var $card     = $checkbox.closest( '.dwp-module-card' );
		var $features = $card.find( '.js-dwp-features-container' );

		if ( $checkbox.is( ':checked' ) ) {
			$card.removeClass( 'is-disabled' );
			$features.slideDown( 150 );
			// Vink automatisch ook alle onderliggende features aan bij inschakelen (UX service)
			$features.find( 'input[type="checkbox"]' ).prop( 'checked', true );
		} else {
			$card.addClass( 'is-disabled' );
			$features.slideUp( 150 );
			// Vink automatisch alle onderliggende features uit bij uitschakelen
			$features.find( 'input[type="checkbox"]' ).prop( 'checked', false );
		}
	});

	// Pre-flight check bij het laden van de pagina: zet de juiste kaarten direct op disabled
	$( '.js-dwp-module-toggle' ).each( function() {
		var $checkbox = $( this );
		if ( ! $checkbox.is( ':checked' ) ) {
			var $card = $checkbox.closest( '.dwp-module-card' );
			$card.addClass( 'is-disabled' );
			$card.find( '.js-dwp-features-container' ).hide();
		}
	});
});
