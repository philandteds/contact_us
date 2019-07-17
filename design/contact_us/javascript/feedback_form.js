(function(){

    var init = function() {
            attachEvents();

            // fancybox
            $(".fancybox").fancybox({

                helpers : {
                    overlay : {
                        css : {
                            'background' : 'rgba(58, 42, 45, 0.6)',
			    'width' : '100%',
			    'height' : '5000px',
			    'position' : 'fixed',
			    'top' : 0,
			    'left' : 0
                        }
                    }
                },
		closeBtn : false,
		modal : true
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
            }
            // scroll to the info box where ever it may be
            var offset = $('.fancybox-inner').offset();
            offset.left -= 20;
            offset.top -= 20;
            $('html, body').animate({
                scrollTop: offset.top,
                scrollLeft: offset.left
            });
        },

        handleChangeCountry = function() {
            $('input.country').val( $( this ).val() );
        },

        handleCancelButton = function( event ) {
            event.preventDefault();
            $.fancybox.close();
	        $('select.query_type :nth-child(1)').prop('selected', true);
        },

        handleOkButton = function( event ) {
            event.preventDefault();
            if ( $( this ).attr('href') !== '#' ) {
                document.location = $(this).attr( 'href' );
            } else {
                $.fancybox.close();
		        $('input#subject').val($('select.query_type option:selected').val());
            }
        };

    $( document ).ready( init );

})(jQuery);
