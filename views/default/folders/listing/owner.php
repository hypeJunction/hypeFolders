<?php

use hypeJunction\Folders\MainFolder;

$entity = elgg_extract('entity', $vars);

echo elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => MainFolder::SUBTYPE,
	'owner_guids' => (int) $entity->guid,
	'no_results' => elgg_echo('folders:no_results'),
));
