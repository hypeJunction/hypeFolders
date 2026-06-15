<?php

use hypeJunction\Folders\MainFolder;
$group_guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);
_elgg_services()->gatekeeper->assertAccessibleGroup($group);
elgg_set_page_owner_guid($group->guid);
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb(elgg_echo('folders'), "folders/group/{$group->guid}");
elgg_register_title_button('folders', 'add', 'object', MainFolder::SUBTYPE);
$title = elgg_echo('folders:group');
$content = elgg_view('folders/listing/group', ['entity' => $group]);
$layout = elgg_view_layout('one_sidebar', array('title' => $title, 'content' => $content));
echo elgg_view_page($title, $layout);