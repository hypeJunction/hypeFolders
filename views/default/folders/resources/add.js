import elgg from 'elgg';
import $ from 'jquery';
import 'jquery-ui';
import spinner from 'elgg/spinner';
import Ajax from 'elgg/Ajax';
import 'jquery.form';
import lightbox from 'elgg/lightbox';
import notify from 'elgg/notify';

const ajax = new Ajax();

function saveUpdatedContent() {
	$('.elgg-form-folders-resources-add').trigger('submit');
}

function prepareDragAndDrop() {
	$('.folders-content-area .folders-content-list').sortable({
		forcePlaceholderSize: true,
		placeholder: 'folders-draggable-placeholder',
		handle: '*:not(a)',
		stop: saveUpdatedContent,
		receive: saveUpdatedContent
	});

	$(".folders-search-results .folders-content-list > li")
			.draggable({
				connectToSortable: ".folders-content-area .folders-content-list",
				appendTo: '.folders-content-area .folders-content-list',
				containment: '.folders-content-area .folders-content-list',
				scroll: false,
				revert: 'invalid',
				handle: '*:not(a)'
			});
}

$(document).on('submit', '.elgg-form-folders-resources-search', function (e) {
	e.preventDefault();
	var $form = $(this);
	ajax.path($form.prop('action'), {
		data: ajax.objectify($form)
	}).done(function (output, statusText, jqXHR) {
		if (jqXHR.AjaxData.status === -1) {
			return;
		}
		$('.folders-search-results').html(output);
		$('.folders-search-results').find('.elgg-list').trigger('initialize');
		prepareDragAndDrop();
	});
});

$(document).on('submit', '.elgg-form-folders-resources-add', function (e) {
	e.preventDefault();
	var $form = $(this);

	$form.ajaxSubmit({
		dataType: 'json',
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		},
		beforeSend: function () {
			$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
			spinner.start();
		},
		complete: function () {
			$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
			spinner.stop();
		},
		success: function (json) {
			if (json.status >= 0) {
				if ($form.closest('#colorbox').length) {
					$('.folders-content-area .folders-content-list').trigger('refresh');
					lightbox.close();
				} else {
					$('folders-search-area .folders-content-list').trigger('refresh');
				}
			}
			if (json.system_messages) {
				notify.error(json.system_messages.error);
				notify.success(json.system_messages.success);
			}

		}
	});
});

$(document).on('click', '.elgg-menu-item-unfolder > a', function (e) {
	e.preventDefault();
	var $elem = $(this);
	$elem.closest('.elgg-list > .elgg-item').fadeOut().remove();
	saveUpdatedContent();
});

$(document).on('ready change', '.folders-content-list', prepareDragAndDrop);

prepareDragAndDrop();
