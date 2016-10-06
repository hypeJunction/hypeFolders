<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_input('select', [
	'name' => 'params[user_folders]',
	'value' => $entity->user_folders,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('folders:settings:user_folders'),
	'help' => elgg_echo('folders:settings:user_folders:help'),
]);

echo elgg_view_input('select', [
	'name' => 'params[user_folders_restrict_by_owner]',
	'value' => isset($entity->user_folders_restrict_by_owner) ? $entity->user_folders_restrict_by_owner : true,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('folders:settings:user_folders_restrict_by_owner'),
	'help' => elgg_echo('folders:settings:user_folders_restrict_by_owner:help'),
]);


echo elgg_view_input('select', [
	'name' => 'params[group_folders]',
	'value' => $entity->group_folders,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('folders:settings:group_folders'),
	'help' => elgg_echo('folders:settings:group_folders:help'),
]);

echo elgg_view_input('select', [
	'name' => 'params[group_folders_restrict_by_container]',
	'value' => isset($entity->group_folders_restrict_by_container) ? $entity->group_folders_restrict_by_container : true,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
	'label' => elgg_echo('folders:settings:group_folders_restrict_by_container'),
	'help' => elgg_echo('folders:settings:group_folders_restrict_by_container:help'),
]);