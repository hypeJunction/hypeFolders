<?php

elgg_make_sticky_form('folders/folder/edit');

$guid = get_input('guid');
$main_folder_guid = get_input('main_folder_guid');
$resource_guid = get_input('resource_guid');

$title = get_input('title');
$description = get_input('description');
$tags = get_input('tags', '');
$access_id = get_input('access_id', elgg_get_default_access());

$user = elgg_get_logged_in_user_entity();
$entity = ($guid) ? get_entity($guid) : null;
/* @var $entity ElggEntity */

$main_folder = get_entity($main_folder_guid);
$resource = get_entity($resource_guid);

if (!$title) {
	return elgg_error_response(elgg_echo('folders:input:error:required', [elgg_echo('title')]));
}

if (!$main_folder instanceof \hypeJunction\Folders\MainFolder || !$main_folder->canEdit()) {
	return elgg_error_response(elgg_echo('folders:folder:error:no_entity'));
}

if ($guid) {
	if (!$entity) {
		return elgg_error_response(elgg_echo('folders:get:error:entity'));
	}
} else {
	$container = $main_folder->getContainerEntity();
	if (!$container || !$container->canWriteToContainer(0, 'object', \hypeJunction\Folders\Folder::SUBTYPE)) {
		return elgg_error_response(elgg_echo('folders:write:error:container'));
	}

	$entity = new \hypeJunction\Folders\Folder();
	$entity->container_guid = $container->guid;
	$entity->owner_guid = $user->guid;
}

$entity->title = $title;
$entity->description = $description;
$entity->tags = elgg_string_to_array($tags);
$entity->access_id = $access_id;


if ($entity->save()) {

	$entity->saveIconFromUploadedFile('icon');

	$entity->setMainFolder($main_folder);
	$main_folder->addResource($entity->guid, $resource->guid);

	elgg_clear_sticky_form('folders/folder/edit');

	return elgg_ok_response('', elgg_echo('folders:save:success'), $entity->getURL());
} else {
	return elgg_error_response(elgg_echo('folders:save:error:generic'));
}