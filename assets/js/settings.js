jQuery( document ).ready(
	function ($) {
		let checkbox = $( '#woocommerce_tradesafe_is_marketplace' )

		function is_marketplace(element) {
			if (element.is( ':checked' )) {
				$( '.is-marketplace' ).show()
				$( '#woocommerce_tradesafe_processing_fee option[value="SELLER"]' ).text( 'Marketplace' )
				$( '#woocommerce_tradesafe_processing_fee option[value="BUYER_SELLER"]' ).text( 'Buyer / Marketplace' )
			} else {
				$( '.is-marketplace' ).hide()
				$( '#woocommerce_tradesafe_processing_fee option[value="SELLER"]' ).text( 'Seller' )
				$( '#woocommerce_tradesafe_processing_fee option[value="BUYER_SELLER"]' ).text( 'Buyer / Seller' )
			}
		}

		checkbox.on(
			"click",
			function () {
				is_marketplace( checkbox )
			}
		)

		is_marketplace( checkbox )

		$( 'a.toggle-plugin-details' ).click(
			function () {
				let table = $( '.plugin-details table' )

				if (table.is( ':visible' )) {
					jQuery( this ).text( 'show' );
				} else {
					jQuery( this ).text( 'hide' );
				}

				table.toggle()
				return false;
			}
		)

		$( 'a.toggle-application-details' ).click(
			function () {
				let table = $( '.application-details table' )

				if (table.is( ':visible' )) {
					jQuery( this ).text( 'show' );
				} else {
					jQuery( this ).text( 'hide' );
				}

				table.toggle()
				return false;
			}
		)

		if ( $( '#woocommerce_tradesafe_delivery_delay_notification' ).is( ':checked' ) ) {
			$( 'select.delivery' ).parent().parent().parent().removeClass( 'hidden' )
		} else {
			$( 'select.delivery' ).parent().parent().parent().addClass( 'hidden' )
		}

		$( '#woocommerce_tradesafe_delivery_delay_notification' ).click(
			function () {
				if ( $( this ).is( ':checked' ) ) {
					$( 'select.delivery' ).parent().parent().parent().removeClass( 'hidden' )
				} else {
					$( 'select.delivery' ).parent().parent().parent().addClass( 'hidden' )
				}
			}
		)
	}
)
