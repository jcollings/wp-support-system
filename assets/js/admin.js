jQuery(document).ready(function($){
	var select_menu = $('.support_page_support-ticket-settings select#support_system_config[name="support_system_config[require_account]"]');
	var element = $('#url_redirect-login, #url_redirect-register').parent().parent();

	select_menu.show_elements(element, 1);
	
	select_menu.change(function(){
		select_menu.show_elements(element, 1);
	});

	
});

jQuery.fn.show_elements = function(element, option){
	if(jQuery(this).val() == option){
		element.show();
	}else{
		element.hide();
	}
}