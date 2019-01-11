jQuery(document).ready(function(){
/* DATEPICKER */
	jQuery( ".datepicker_kommune" ).datepicker( {minDate: new Date(UKMSEASON,0,1), maxDate: new Date(UKMSEASON, 2, 31), dateFormat: 'dd.mm.yy'});
	jQuery( ".datepicker_fylke" ).datepicker( {minDate: new Date(UKMSEASON,2,1), maxDate: new Date(UKMSEASON, 3, 30), dateFormat: 'dd.mm.yy'});
	jQuery( ".datepicker_fylke_forward" ).datepicker( {minDate: new Date(UKMSEASON,1,1), maxDate: new Date(UKMSEASON, 3, 30), dateFormat: 'dd.mm.yy'});
	jQuery( ".datepicker_land" ).datepicker( {minDate: new Date(UKMSEASON,4,1), maxDate: new Date(UKMSEASON, 6, 31), dateFormat: 'dd.mm.yy'});

    jQuery.datepicker.regional['no'] = {
		closeText: 'Lukk',
        prevText: '&laquo;Forrige',
		nextText: 'Neste&raquo;',
		currentText: 'I dag',
        monthNames: ['Januar','Februar','Mars','April','Mai','Juni',
        'Juli','August','September','Oktober','November','Desember'],
        monthNamesShort: ['Jan','Feb','Mar','Apr','Mai','Jun',
        'Jul','Aug','Sep','Okt','Nov','Des'],
		dayNamesShort: ['S&oslash;n','Man','Tir','Ons','Tor','Fre','L&oslash;r'],
		dayNames: ['S&oslash;ndag','Mandag','Tirsdag','Onsdag','Torsdag','Fredag','L&oslash;rdag'],
		dayNamesMin: ['S&oslash;','Ma','Ti','On','To','Fr','L&oslash;'],
		weekHeader: 'Uke',
        dateFormat: 'yy-mm-dd',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['no']);
});

jQuery(document).ready( function(){
    jQuery( "#kontaktpersoner" ).sortable(
        {
            update: function(event, ui) { 
                jQuery.post(ajaxurl,
                    {
                        'action': 'UKMmonstring_save_kontaktpersoner',
                        'order': jQuery('#kontaktpersoner').sortable('toArray')
                    }
                );
            }
        }
    );
});
//jQuery( "#kontaktpersoner" ).disableSelection();

jQuery(document).on('click', ".kontaktperson a.delete", function(e){
	e.preventDefault();
	var result = confirm('Sikker på at du vil slette denne kontaktpersonen?');
	if( result ) {
		window.location.href = $(this).attr('href');
	}
	return false;
});

/*
jQuery(document).on('click', ".kontaktperson:not(a.delete)", function(){
	jQuery('#goTo').val( jQuery(this).attr('data-goTo') );
	jQuery('#formSubmit').click();
});
*/

jQuery(document).on('click', '#formSubmit', function(){
	jQuery('#formSubmit').removeClass('btn-success').addClass('btn-primary').html('Lagrer...');
});


jQuery(document).on('click', '#imageedit', function(e) {
	e.preventDefault();

	var custom_uploader = wp.media({
		title: 'Velg nytt bilde for kontaktperson',
		button: {
			text: 'Bruk dette bildet'
		},
		multiple: false  // Set this to true to allow multiple files to be selected
	})
	.on('select', function() {
		var attachment = custom_uploader.state().get('selection').first().toJSON();
		jQuery('#contact_image').attr('src', attachment.url);
		jQuery('#contact_image_input').val(attachment.url);
	})
	.open();
});







jQuery(document).on('click', '.UKMmonstringer .details_show', function(){
	show = jQuery(this);
	monstring = jQuery(this).parents('li'); 
	monstring.find('.details_hide').show();
	show.hide();
	monstring.find('.details').slideDown()
});
jQuery(document).on('click', '.UKMmonstringer .details_hide', function(){
	hide = jQuery(this);
	monstring = jQuery(this).parents('li'); 
	monstring.find('.details_show').show();
	hide.hide();
	monstring.find('.details').slideUp()
});

jQuery(document).on('click', '.UKMmonstringer .wpadmin', function(){
	window.open(jQuery(this).attr('data-url'),'_blank');
});

jQuery(document).on('click','.UKMmonstringer #smstoall', function(){
	var recipients = new Array();
	jQuery('.UKMSMS').each(function(){
		recipients.push( jQuery(this).find('a').html() );
	});
	jQuery('#UKMSMS_to').val( recipients.join(',') );
	jQuery('#UKMSMS_form').submit();
});