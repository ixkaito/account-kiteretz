(function($) {

$(function() {
	$('.js-filter > button').on('click', function() {
		$('.js-index > li').hide();
		$('.js-index > li.' + $(this).data('show')).show();
	});
});

})(jQuery)
