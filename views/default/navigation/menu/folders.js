define(['jquery'], function ($) {

	$(document).on('click.folders', '.elgg-child-menu-toggle', function (e) {
		if (!$(e.target).is('input')) {
			e.preventDefault();
			$(this).closest('li').toggleClass('elgg-menu-open elgg-menu-closed');
		}
	});

	$(document).on('click.folders', '.elgg-child-menu-toggle + label', function (e) {
		$(this).siblings('.elgg-child-menu-toggle').trigger('click');
	});

});



