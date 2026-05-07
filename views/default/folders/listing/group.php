<?php

use hypeJunction\Folders\MainFolder;

$entity = elgg_extract('entity', $vars);

echo elgg_list_entities([
	'types' => 'object',
	'subtypes' => MainFolder::SUBTYPE,
	'container_guids' => (int) $entity->guid,
	'no_results' => elgg_echo('folders:no_results'),
]);
