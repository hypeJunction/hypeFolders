define(function (require) {

	var $ = require('jquery');
	require('jquery-ui');
	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	$(document).on('click.folders', '.elgg-child-menu-toggle', function (e) {
		if (!$(e.target).is('input')) {
			e.preventDefault();
			$(this).closest('li').toggleClass('elgg-menu-open elgg-menu-closed');
		}
	});

	$(document).on('click.folders', '.elgg-child-menu-toggle + label', function (e) {
		$(this).siblings('.elgg-child-menu-toggle').trigger('click');
	});

	if (typeof $.fn.sortable !== 'undefined') {
		$('.elgg-menu-folders.elgg-state-sortable ul').sortable({
			items: '> li',
			handle: '.elgg-icon-arrows',
			forcePlaceholderSize: true,
			placeholder: 'elgg-widget-placeholder',
			opacity: 0.8,
			revert: 500,
			stop: function (e, ui) {
				var $elem = ui.item,
						items = [];

				$elem.siblings().andSelf().each(function (i, item) {
					items.push({
						weight: i + 1,
						guid: $(item).data('guid'),
						parent_guid: $(item).data('parentGuid'),
						folder_guid: $(item).data('folderGuid')
					});
				});

				ajax.action('folders/reorder', {
					data: {
						items: items
					}
				});
			}
		});
	}
});

