var scheduledStylesnewStyleNumber=0;

function cloneBlankStyleTemplate(e){
	e.preventDefault();
	scheduledStylesnewStyleNumber=scheduledStylesnewStyleNumber+1;
	
	var html = jQuery('#newStyleTemplate').html();
	html = html.replace(/NUMZ/g,scheduledStylesnewStyleNumber);
	jQuery('#scheduledStyles').append(html);

	jQuery('#newStyle' + scheduledStylesnewStyleNumber + ' .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#newStyle' + scheduledStylesnewStyleNumber + ' .deleteLink').click(removeScheduledStyleFromDOM);
}

function markScheduledStyleForDeletion(e){
	e.preventDefault();
	jQuery(this).prev().prev().attr('checked',true);
	jQuery(this).parent().parent().hide('slow');
}

function removeScheduledStyleFromDOM(e){
	e.preventDefault();
	jQuery(this).parent().parent().hide('slow',function(){jQuery(this).remove();});
}

function initializeScheduledStyles(){
	jQuery('#scheduledStyles .datePicker').datepicker({ dateFormat: 'yy-mm-dd' });
	jQuery('#ui-datepicker-div').css('clip', 'auto');
	jQuery('.addScheduledStyle').click(cloneBlankStyleTemplate);
	jQuery('#scheduledStyles .deleteLink').click(markScheduledStyleForDeletion);
	jQuery('#scheduledStylesForm').validate();
}

jQuery(document).ready(initializeScheduledStyles);