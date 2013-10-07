function UKMMonstring_cSave(the_array) {
	var pl_id = document.getElementById('this_place_id').value;
	UKMN_AJAX('UKMMonstring/monstring.ajax.php', 'action|contactOrder||pl_id|'+pl_id+'||order|'+the_array);
	UKMN_AJAX('UKMMonstring/monstring.ajax.php', 'action|listMainContacts||pl_id|'+pl_id, 'eval');
	//alert('Hei ' + the_array);
}

jQuery(document).ready(function(){

	jQuery('#hugesubmit_monstring').click(function(){
		jQuery(this).find('#lagre').html('Lagrer...');
		jQuery('#place_submit').click();
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
				alert('response' + response);
		});
	}});
	jQuery( "#kontaktpersoner" ).disableSelection();
});