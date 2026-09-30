/**
 * DeWittePrins Core Framework - Gutenberg Sidebar & Command Palette Pipeline
 */
jQuery( document ).ready( function( $ ) {

	var $sidebar       = $( '#js-dwp-sidebar' );
	var $toggleBtn     = $( '.js-dwp-sidebar-toggle' );
	var $searchInput   = $( '#js-sidebar-search' );
	var $menuGroups    = $( '.dwp-menu-group' );
	var $menuItems     = $( '.dwp-menu-item' );
	var $noResults     = $( '#js-search-no-results' );

	// Command Palette Elementen
	var $palette       = $( '#js-dwp-palette' );
	var $paletteInput  = $( '#js-palette-input' );
	var $paletteResult = $( '#js-palette-results' );

	// ==========================================================================
	// 1. ZIJBALK CACHE STATUS & WATERDICHTE TOGGLE FIX (TARGETED HTML ROOT)
	// ==========================================================================
	if ( localStorage.getItem( 'dwp_gutenberg_sidebar' ) === 'collapsed' ) {
		$sidebar.addClass( 'is-collapsed' );
		$toggleBtn.addClass( 'is-pressed' );
	}

	$toggleBtn.on( 'click', function( e ) {
		e.preventDefault();

		// DEFINITIEVE FIX: Verwijder de anti-flicker klasse direct van de HTML root element!
		// Hierdoor vervallen de harde breedte-beperkingen in CSS onmiddellijk live op je scherm.
		$( 'html' ).removeClass( 'dwp-sidebar-init-collapsed' );

		$sidebar.toggleClass( 'is-collapsed' );
		$toggleBtn.toggleClass( 'is-pressed' );

		if ( $sidebar.hasClass( 'is-collapsed' ) ) {
			localStorage.setItem( 'dwp_gutenberg_sidebar', 'collapsed' );
		} else {
			localStorage.setItem( 'dwp_gutenberg_sidebar', 'expanded' );
		}
	});

	// ==========================================================================
	// 2. SNELTOETSEN: [/] VOFOR SIDEBAR EN [CTRL + SPACE] VOOR MODAL
	// ==========================================================================
	$( document ).on( 'keydown', function( e ) {
		var isTyping = [ 'INPUT', 'TEXTAREA', 'SELECT' ].includes( document.activeElement.tagName );

		// [/] Focus het gewone zoekveld in de zijbalk
		if ( e.key === '/' && ! isTyping ) {
			e.preventDefault();
			$searchInput.focus().select();
		}

		// [Ctrl + Space] of [Cmd + Space] Toggle Command Palette
		if ( ( e.ctrlKey || e.metaKey ) && e.key === ' ' ) {
			e.preventDefault(); // Voorkom scrollen van de pagina
			if ( $palette.is( ':visible' ) ) {
				closeCommandPalette();
			} else {
				openCommandPalette();
			}
		}

		// [Escape] Sluit geopende elementen
		if ( e.key === 'Escape' ) {
			if ( $palette.is( ':visible' ) ) {
				closeCommandPalette();
			} else if ( $searchInput.is( ':focus' ) ) {
				$searchInput.val( '' ).blur().trigger( 'input' );
			}
		}
	});

	// ==========================================================================
	// 3. FLITSEND INLINE SIDEBAR ZOEKFILTER (Inclusief Enter-blokkade & Auto-Route)
	// ==========================================================================
	$searchInput.on( 'input', function() {
		var filter = $( this ).val().toLowerCase().trim();
		var totalVisible = 0;

		// Forceer open zijbalk tijdens het typen voor visuele structuur
		if ( filter.length > 0 && $sidebar.hasClass( 'is-collapsed' ) ) {
			$sidebar.removeClass( 'is-collapsed' );
			$toggleBtn.removeClass( 'is-pressed' );
		}

		$menuGroups.each( function() {
			var $group = $( this );
			var groupVisible = 0;
			var $items = $group.find( '.dwp-menu-item' );

			$items.each( function() {
				var $item = $( this );
				var text = $item.find( '.dwp-menu-text' ).text().toLowerCase();
				var terms = ( $item.attr( 'data-search-term' ) || '' ).toLowerCase();

				if ( text.indexOf( filter ) !== -1 || terms.indexOf( filter ) !== -1 ) {
					$item.css( 'display', 'flex' );
					groupVisible++;
					totalVisible++;
				} else {
					$item.css( 'display', 'none' );
				}
			});

			$group.toggle( groupVisible > 0 );
		});

		$noResults.toggle( totalVisible === 0 );
	});

	// Voorkom dat Enter het formulier opslaat, maar stuur in plaats daarvan door naar het eerste resultaat!
	$searchInput.on( 'keydown', function( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault(); // Dit blokkeert de ongewenste Save-actie hardhandig!

			// Vind het allereerste menu-item dat momenteel nog zichtbaar is na het filteren
			var $firstVisibleItem = $sidebar.find( '.dwp-menu-item:visible' ).eq( 0 );
			if ( $firstVisibleItem.length ) {
				window.location.href = $firstVisibleItem.attr( 'href' ); // Navigeer direct!
			}
		}
	});

	// ==========================================================================
	// 4. COMMAND PALETTE LOGICA (ZWEVENDE MODAL MET TOETSENBORD NAVIGATIE)
	// ==========================================================================
	function openCommandPalette() {
		$palette.fadeIn( 120 );
		$paletteInput.val( '' ).focus();
		renderPaletteResults( '' );
	}

	function closeCommandPalette() {
		$palette.fadeOut( 100 );
	}

	// Sluit modal bij klik op de wazige achtergrond overlay
	$( '.dwp-palette-overlay' ).on( 'click', closeCommandPalette );

	// Realtime zoeken binnen de command palette modal
	$paletteInput.on( 'input', function() {
		renderPaletteResults( $( this ).val().toLowerCase().trim() );
	});

	// Luister naar navigatietoetsen (Pijltjes en Enter) in het modal-invoerveld
	$paletteInput.on( 'keydown', function( e ) {
		var $items = $paletteResult.find( '.dwp-palette-item' );
		if ( ! $items.length ) return;

		var $current = $items.filter( '.is-selected' );
		var index = $items.index( $current );

		if ( e.key === 'ArrowDown' ) {
			e.preventDefault();
			$items.removeClass( 'is-selected' );
			if ( index + 1 < $items.length ) {
				$items.eq( index + 1 ).addClass( 'is-selected' );
			} else {
				$items.eq( 0 ).addClass( 'is-selected' ); // Loop terug naar het begin
			}
			scrollPaletteToSelected();
		}

		if ( e.key === 'ArrowUp' ) {
			e.preventDefault();
			$items.removeClass( 'is-selected' );
			if ( index > 0 ) {
				$items.eq( index - 1 ).addClass( 'is-selected' );
			} else {
				$items.eq( $items.length - 1 ).addClass( 'is-selected' ); // Loop naar het einde
			}
			scrollPaletteToSelected();
		}

		if ( e.key === 'Enter' ) {
			e.preventDefault();
			var $selected = $items.filter( '.is-selected' );
			if ( $selected.length ) {
				window.location.href = $selected.attr( 'href' ); // Navigeer direct!
			} else if ( $items.length === 1 ) {
				window.location.href = $items.eq( 0 ).attr( 'href' ); // Fallback als er maar één match is
			}
		}
	});

	function renderPaletteResults( filter ) {
		$paletteResult.empty();
		var matches = 0;

		$menuItems.each( function() {
			var $item     = $( this );
			var name      = $item.attr( 'data-name' ) || '';
			var url       = $item.attr( 'href' ) || '#';
			var terms     = ( $item.attr( 'data-search-term' ) || '' ).toLowerCase();
			var iconClass = $item.find( '.dashicons' ).attr( 'class' ) || 'dashicons dashicons-admin-plugins';

			if ( filter === '' || terms.indexOf( filter ) !== -1 ) {
				matches++;
				$paletteResult.append(
					'<a href="' + url + '" class="dwp-palette-item">' +
						'<span class="' + iconClass + '"></span>' +
						'<span class="dwp-palette-text">' + name + '</span>' +
						'<span class="dwp-palette-badge">Go to</span>' +
					'</a>'
				);
			}
		});

		// Selecteer onvoorwaardelijk het eerste item voor instant keyboard-routing
		$paletteResult.find( '.dwp-palette-item' ).eq( 0 ).addClass( 'is-selected' );

		if ( matches === 0 ) {
			$paletteResult.append( '<div class="dwp-palette-no-results">Geen onderdelen gevonden...</div>' );
		}
	}

	// Houdt de actieve selectie altijd binnen de scroll-viewport van de modal box
	function scrollPaletteToSelected() {
		var $selected = $paletteResult.find( '.dwp-palette-item.is-selected' );
		if ( ! $selected.length ) return;

		var containerTop = $paletteResult.scrollTop();
		var containerBottom = containerTop + $paletteResult.height();
		var elemTop = $selected.position().top + containerTop;
		var elemBottom = elemTop + $selected.outerHeight();

		if ( elemTop < containerTop ) {
			$paletteResult.scrollTop( elemTop );
		} else if ( elemBottom > containerBottom ) {
			$paletteResult.scrollTop( elemBottom - $paletteResult.height() );
		}
	}
});
