<?php

elgg_make_sticky_form('folders/folder/edit');

$guid = get_input('guid');
$main_folder_guid = get_input('main_folder_guid');
$resource_guid = get_input('resource_guid');

$title = get_input('title');
$description = get_input('description');
$tags = get_input('tags', '');
$access_id = get_input('access_id', get_default_access());

$user = elgg_get_logged_in_user_entity();
$entity = ($guid) ? get_entity($guid) : null;
/* @var $entity ElggEntity */

$main_folder = get_entity($main_folder_guid);
$resource = get_entity($resource_guid);

if (!$title) {
	register_error(elgg_echo('folders:input:error:required', [elgg_echo('title')]));
	forward(REFERRER);
}

if (!$main_folder instanceof \hypeJunction\Folders\MainFolder || !$main_folder->canEdit()) {
	register_error(elgg_echo('folders:folder:error:no_entity'));
	forward(REFERRER);
}

if ($guid) {
	if (!$entity) {
		register_error(elgg_echo('folders:get:error:entity'));
		forward(REFERRER);
	}
} else {
	$container = $main_folder->getContainerEntity();
	if (!$container || !$container->canWriteToContainer(0, 'object', \hypeJunction\Folders\Folder::SUBTYPE)) {
		register_error(elgg_echo('folders:write:error:container'));
		forward(REFERER);
	}

	$entity = new \hypeJunction\Folders\Folder();
	$entity->container_guid = $container->guid;
	$entity->owner_guid = $user->guid;
}

$entity->title = $title;
$entity->description = $description;
$entity->tags = string_to_tag_array($tags);
$entity->access_id = $access_id;


if ($entity->save()) {

	$entity->saveIconFromUploadedFile('icon');

	$entity->setMainFolder($main_folder);
	$main_folder->addResource($entity->guid, $resource->guid);

	elgg_clear_sticky_form('folders/folder/edit');
	
	system_message(elgg_echo('folders:save:success'));
	forward($entity->getURL());
} else {
	register_error(elgg_echo('folders:save:error:generic'));
}