;(function(){

    var init = function() {
            attachEvents();
        },

        attachEvents = function() {
            $('select.query_type').change( handleChangeQueryType );
            $('select.country').change( handleChangeCountry );
            $('.buttons a.ok').click( handleOkButton );
            $('.buttons a.cancel').click( handleCancelButton );
        },

        handleChangeQueryType = function() {
            $('input.query_type').val( $( this ).val() );
            $('.help_text').hide();
            $('#'+ $( this).find('option:selected').data( 'id' )).show();
        },

        handleChangeCountry = function() {
            $('input.country').val( $( this ).val() );
        },

        handleCancelButton = function( event ) {
            event.preventDefault();
            $( this ).parents( '.help_text').hide();
        },

        handleOkButton = function( event ) {
            event.preventDefault();
            if ( $( this ).attr('href') !== '#' ) {
                document.location = $(this).attr( 'href' );
            } else {
                $( this ).parents( '.help_text').hide();
            }
        };

    $( document ).ready( init );

})();