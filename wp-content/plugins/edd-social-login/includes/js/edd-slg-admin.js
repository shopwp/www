jQuery(document).ready( function($) {
	
	//sortable table
	jQuery('table.edd-slg-sortable tbody').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: 'td',
		scrollSensitivity:40,
		helper:function(e,ui){
			ui.children().each(function(){
				jQuery(this).width(jQuery(this).width());
			});
			ui.css('left', '0');
			return ui;
		},
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
		}
	});
	
});