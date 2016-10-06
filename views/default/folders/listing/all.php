<?php

use hypeJunction\Folders\MainFolder;

echo elgg_list_entities(array(
	'types' => 'object',
	'subtypes' => MainFolder::SUBTYPE,
	'no_results' => elgg_echo('folders:no_results'),
));
