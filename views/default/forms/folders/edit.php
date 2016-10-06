<?php

$entity = elgg_extract('entity', $vars);
$container = elgg_extract('container', $vars);

echo elgg_view_input('text', array(
	'name' => 'title',
	'value' => elgg_extract('title', $vars, $entity->title),
	'required' => true,
	'label' => elgg_echo('folders:folder:title'),
));

echo elgg_view_input('file', array(
	'name' => 'icon',
	'value' => $entity instanceof ElggEntity && $entity->hasIcon(),
	'label' => elgg_echo('folders:folder:icon'),
));

echo elgg_view_input('longtext', array(
	'name' => 'description',
	'value' => elgg_extract('description', $vars, $entity->description),
	'label' => elgg_echo('folders:folder:description'),
));

echo elgg_view_input('tags', array(
	'name' => 'tags',
	'value' => elgg_extract('tags', $vars, $entity->tags),
	'label' => elgg_echo('folders:folder:tags'),
));

echo elgg_view_input('category', array(
	'value' => elgg_extract('category', $vars),
	'entity' => $entity,
));

echo elgg_view('forms/folders/edit/extend', $vars);

echo elgg_view_input('access', array(
	'name' => 'access_id',
	'value' => elgg_extract('access_id', $vars, ($entity) ? $entity->access_id : get_default_access()),
	'label' => elgg_echo('folders:folder:access_id'),
));

echo elgg_view_input('hidden', array(
	'name' => 'guid',
	'value' => $entity->guid,
));
echo elgg_view_input('hidden', array(
	'name' => 'container_guid',
	'value' => $container->guid,
));

echo elgg_view_input('submit', array(
	'value' => elgg_echo('save'),
	'field_class' => 'elgg-foot',
));
