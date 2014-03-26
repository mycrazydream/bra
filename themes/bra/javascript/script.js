jQuery.noConflict();

(function($) {    
    $(document).ready(function() {
			/*var menuLis = $('header .primary ul li'),
				lastLi = menuLis.length-1;
			$(menuLis[lastLi]).addClass('last');*/
			
			if(!$('html.ie6').length){
				$('.header .primary li a').hover(function(){
					$(this).next().show().next().show();
				},function(){
					var $this = $(this);
					if(!$this.parent('li').hasClass('current')){
						$this.next().hide().next().hide();
					}
				});
			}
					 
		
			$('.header-anchor').click(function(e){
				document.location.href = $(this).attr('data-href');
			});
			
			$('.fancy').fancybox();
			$('select[title],input[title],div.main a[title],footer a[title]').tipsy({trigger:'hover', gravity:$.fn.tipsy.autoNS, opacity:.9, offset:4, fade:true});
			/*
			$('#Form_ContactUsForm_Subject').change(function(e){
				(this.value=='career' ? $('#email-attachment').show() : $('#email-attachment').hide() );
			});
			*/
    });
}(jQuery));