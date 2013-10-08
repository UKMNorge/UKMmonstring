jQuery(document).ready(function(){

	jQuery('#kontaktpersoner a.edit').click(function(){
		jQuery('#takemeto').val('contactp='+jQuery(this).parents('li').attr('id'));
		jQuery('#hugesubmit').click();
	});

	jQuery('#kontaktpersoner a.delete').click(function(){
		jQuery('#takemeto').val('delete='+jQuery(this).parents('li').attr('id'));
		jQuery('#hugesubmit').click();
	});

/* DATEPICKER */
	jQuery( ".datepicker" ).datepicker( {minDate: new Date(UKMSEASON,0,1), maxDate: new Date(UKMSEASON, 2, 31), dateFormat: 'dd.mm.yy'});

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

/* SORTABLE LIST */ 
	jQuery( "#kontaktpersoner" ).sortable({update: function(event, ui) { 
		jQuery.post(ajaxurl,
			{'action': 'UKMmonstring_save_kontaktpersoner',
			 'order': jQuery('#kontaktpersoner').sortable('toArray') },
			function(response) {
				jQuery('#hovedkontakter').html(response);
		});
	}});
	jQuery( "#kontaktpersoner" ).disableSelection();
});