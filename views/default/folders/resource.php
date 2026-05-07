<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

$entity = elgg_extract('entity', $vars);
/* @var $entity ElggEntity */

if (!$entity instanceof ElggEntity) {
	return;
}

$folder = elgg_extract('folder', $vars);

$full_view = elgg_extract('full_view', $vars, false);

$icon = elgg_view_entity_icon($entity, 'small', [
	'use_link' => false,
	'use_hover' => false,
]);

$subtitle = [];
$subtitle[] = elgg_format_element('span', [
	'class' => 'folders-content-type-badge',
], elgg_echo("item:{$entity->getType()}:{$entity->getSubtype()}"));

$owner = $entity->getOwnerEntity();
if ($owner instanceof ElggUser) {
	$owner_link = elgg_view('output/url', [
		'href' => "folders/owner/$owner->username",
		'text' => $owner->getDisplayName(),
		'is_trusted' => true,
	]);
	$author_text = elgg_echo('byline', [$owner_link]);
	$time = elgg_get_friendly_time($entity->time_created);
	$subtitle[] = "$author_text $time";
}

$metadata = '';
if (!elgg_in_context('widgets')) {
	$metadata = elgg_view_menu('entity', [
		'entity' => $entity,
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
	]);
}

if ($full_view) {
	$summary = elgg_view('object/elements/summary', [
		'entity' => $entity,
		'title' => false,
		'subtitle' => implode(' | ', $subtitle),
		'content' => '',
		'metadata' => $metadata,
	]);

	if ($entity instanceof MainFolder || $entity instanceof Folder) {
		echo elgg_view('object/elements/full', [
			'entity' => $entity,
			'summary' => $summary,
			'icon' => $icon,
			'body' => elgg_view('output/longtext', [
				'value' => $entity->description,
			]),
		]);
	} else {
		echo elgg_view_entity($entity, [
			'full_view' => true,
		]);
	}

	echo elgg_view('folders/resources', [
		'folder' => $folder,
		'resource' => $entity,
	]);
} else {
	$query = elgg_extract('query', $vars);
	if ($query) {
		if ($entity->getVolatileData('search_matched_title')) {
			$title = $entity->getVolatileData('search_matched_title');
		} else {
			$title = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<strong>$1</strong>', $entity->getDisplayName());
		}

		if ($entity->getVolatileData('search_matched_description')) {
			$excerpt = $entity->getVolatileData('search_matched_description');
		} else {
			$excerpt = elgg_get_excerpt($entity->description, 100);
			$excerpt = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<strong>$1</strong>', $excerpt);
		}
	} else {
		$title = $entity->getDisplayName();
		$excerpt = elgg_get_excerpt($entity->description, 100);
	}

	$title = elgg_view('output/url', [
		'text' => $title,
		'href' => $entity->getURL(),
		'is_trusted' => true,
	]);

	$summary = elgg_view('object/elements/summary', [
		'entity' => $entity,
		'title' => $title,
		'subtitle' => implode(' | ', $subtitle),
		'content' => $excerpt,
		'tags' => false,
		'metadata' => $metadata,
	]);


	$input_name = elgg_extract('input_name', $vars, false);
	if ($input_name) {
		$summary .= elgg_view_input('hidden', [
			'name' => "{$input_name}[]",
			'value' => $entity->guid,
		]);
	}

	echo elgg_view_image_block($icon, $summary, [
		'class' => 'folders-content-item',
	]);
}
