<?php

use hypeJunction\Folders\MainFolder;

$folder = elgg_extract('folder', $vars);
$resource = elgg_extract('resource', $vars);

if (!$folder instanceof MainFolder) {
	return;
}

elgg_set_page_owner_guid($folder->container_guid);

echo elgg_view_form('videolist/edit', [
	'enctype' => 'multipart/form-data',
	'class' => 'elgg-form-folders-resources-add',
], [
	'folder' => $folder,
	'resource' => $resource,
	'container_guid' => $folder->container_guid,
]);
?>
<script>
	require(['videolist/videolist']);
</script>