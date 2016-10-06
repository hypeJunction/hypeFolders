<?php

$folder = elgg_extract('folder', $vars);
$resource = elgg_extract('resource', $vars);

$section = elgg_extract('filter_context', $vars, 'edit');

$tabs = [
	'edit' => [
		'name' => 'edit',
		'href' => "/folders/edit/$folder->guid",
		'text' => elgg_echo('folders:edit'),
		'selected' => $section == 'edit',
		'priority' => 100,
	],
	'resources/add' => [
		'name' => 'resources:add',
		'href' => "/folders/resources/add/$folder->guid/$resource->guid",
		'text' => elgg_echo('folders:resources:add'),
		'selected' => $section == 'resources/add',
		'priority' => 200,
	],
];

if (!$folder->canEdit()) {
	unset($tabs['edit']);
}

if (!$folder->canWriteToContainer()) {
	unset($tabs['resources/add']);
}

foreach ($tabs as $tab) {
	elgg_register_menu_item('filter', $tab);
}

$params = $vars;
$params['sort_by'] = 'priority';
echo elgg_view_menu('filter', $vars);