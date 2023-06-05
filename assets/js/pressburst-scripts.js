$ = jQuery;

$('.pressburst-grid-row li button').on('click',function(){
	$(this).parent().toggle();
	$(this).parent().next('.main-content').toggle();
})