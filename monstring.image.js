jQuery(document).ready(function() {

jQuery('.upload_image_button').click(function() {
 formfield = jQuery('upload_image').attr('name');
 tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true&amp;UKMimage=true');
 return false;
});

window.send_to_editor = function(html) {
 stringparts = html.split(' ');
 imgsrc = stringparts[1];
 imgsrc = imgsrc.replace('src=','');
 imgsrc = imgsrc.replace('"','');
 imgsrc = imgsrc.replace('"','');
 //alert(imgsrc);
 //alert(jQuery('img',html).attr('src'));
 //imgurl = jQuery('img',html).attr('src');
 document.getElementById('upload_image').value = imgsrc;
 document.getElementById('upload_image_img').src = imgsrc;
// jQuery('#upload_image').val(imgsrc);
 tb_remove();
}

});

function setImage(name) {
	document.getElementById('upload_image_img').src = document.getElementById('upload_image').value;
}

function reSetImage() {
	document.getElementById('upload_image').value = '';
	document.getElementById('upload_image_img').src = document.getElementById('upload_image_default').value;
}