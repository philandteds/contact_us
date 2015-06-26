;(function(){

    var init = function() {
            attachEvents();

            // fancybox
            $(".fancybox").fancybox({

                helpers : {
                    overlay : {
                        overlayOpacity  : 0.8,
                        css : {
                            'background' : 'rgba(58, 42, 45, 0.95)'
                        }
                    }
                }
            });
        },

        attachEvents = function() {
            $('select.query_type').change( handleChangeQueryType );
            $('select.country').change( handleChangeCountry );
            $('.buttons a.ok').click( handleOkButton );
            $('.buttons a.cancel').click( handleCancelButton );
        },

        handleChangeQueryType = function() {
            var id = $(this).find('option:selected').data( 'id' );
            $('input.query_type').val( $( this ).val() );
            if ( $( id ).length > 0 ) {
                $('.fancybox-link').attr( 'href', id );
                $('.fancybox-link').trigger('click');
                $( 'form input, form textarea, form select' ).attr('disabled', 'disabled');
            }
        },

        handleChangeCountry = function() {
            $('input.country').val( $( this ).val() );
        },

        handleCancelButton = function( event ) {
            event.preventDefault();
            $.fancybox.close();
	        $('select.query_type :nth-child(1)').prop('selected', true);
            $( 'form input, form textarea, form select' ).removeAttr('disabled');
        },

        handleOkButton = function( event ) {
            event.preventDefault();
            if ( $( this ).attr('href') !== '#' ) {
                document.location = $(this).attr( 'href' );
            } else {
                $.fancybox.close();
		        $('input#subject').val($('select.query_type option:selected').val());
                $( 'form input, form textarea, form select' ).removeAttr('disabled');
            }
        };

    $( document ).ready( init );

})();
