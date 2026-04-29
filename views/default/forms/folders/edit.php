<?php

$entity = elgg_extract('entity', $vars);
$container = elgg_extract('container', $vars);

echo elgg_view_field([
	'#type' => 'text',
	'name' => 'title',
	'value' => elgg_extract('title', $vars, $entity ? $entity->title : ''),
	'required' => true,
	'#label' => elgg_echo('folders:folder:title'),
]);

echo elgg_view_field([
	'#type' => 'file',
	'name' => 'icon',
	'value' => $entity instanceof ElggEntity && $entity->hasIcon(),
	'#label' => elgg_echo('folders:folder:icon'),
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'name' => 'description',
	'value' => elgg_extract('description', $vars, $entity ? $entity->description : ''),
	'#label' => elgg_echo('folders:folder:description'),
]);

echo elgg_view_field([
	'#type' => 'tags',
	'name' => 'tags',
	'value' => elgg_extract('tags', $vars, $entity ? $entity->tags : ''),
	'#label' => elgg_echo('folders:folder:tags'),
]);

echo elgg_view_field([
	'#type' => 'category',
	'value' => elgg_extract('category', $vars),
	'entity' => $entity,
]);

echo elgg_view('forms/folders/edit/extend', $vars);

echo elgg_view_field([
	'#type' => 'access',
	'name' => 'access_id',
	'value' => elgg_extract('access_id', $vars, ($entity) ? $entity->access_id : elgg_get_default_access()),
	'#label' => elgg_echo('folders:folder:access_id'),
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity ? $entity->guid : 0,
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'container_guid',
	'value' => $container ? $container->guid : 0,
]);

echo elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
	'#class' => 'elgg-foot',
]);
