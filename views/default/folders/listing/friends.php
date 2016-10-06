<?php

use hypeJunction\Folders\MainFolder;

$entity = elgg_extract('entity', $vars);

echo elgg_list_entities_from_relationship(array(
	'types' =>'object',
	'subtypes' => MainFolder::SUBTYPE,
	'relationship_guid' => (int) $entity->guid,
	'relationship_join_on' => 'owner_guid',
	'relationship' => 'friend',
	'no_results' => elgg_echo('folders:no_results'),
));