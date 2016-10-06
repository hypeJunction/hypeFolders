<?php

$folder = elgg_extract('folder', $vars);
$query = elgg_extract('query', $vars, get_input('query', ''));

echo elgg_format_element('button', [
	'type' => 'submit',
	'class' => 'elgg-button elgg-button-submit',
], elgg_view_icon('search'));

echo elgg_view_input('text', [
	'name' => 'query',
	'value' => $query,
	'placeholder' => elgg_echo('folders:query:placeholder'),
]);

echo elgg_view_input('hidden', [
	'name' => 'main_folder_guid',
	'value' => $folder->guid,
]);

