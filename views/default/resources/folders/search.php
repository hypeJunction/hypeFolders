<?php

use hypeJunction\Folders\MainFolder;

elgg_ajax_gatekeeper();

$guid = get_input('main_folder_guid');
elgg_entity_gatekeeper($guid, 'object', MainFolder::SUBTYPE);

$folder = get_entity($guid);

$params = $vars;
$params['input_name'] = 'guids';
$params['folder'] = $folder;
echo elgg_view('folders/search_results', $params);