<?php

$folder = elgg_extract('folder', $vars);
if (!$folder instanceof hypeJunction\Folders\MainFolder) {
	return;
}

$resource = elgg_extract('resource', $vars);
if (!$folder->isResource($resource->guid)) {
	return;
}

$options = [];

$resources = $folder->getResources();

$tree = function($node, $level = 0) use ($folder, $resource, &$options, &$tree) {
	if ($node->guid == $resource->guid) {
		return;
	}
	if ($level > 0) {
		$label = str_pad('', $level * 2);
		$label .= '-- ';
	}
	$options[$node->guid] = $label . $node->title;

	$children = $folder->getChildren($node->guid);
	if ($children) {
		foreach ($children as $child) {
			$tree($child, $level + 1);
		}
	}
};

$tree($folder, 0);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('folders:move:parent_guid'),
	'options_values' => $options,
	'value' => 0,
	'name' => 'parent_guid',
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $resource->guid,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'folder_guid',
	'value' => $folder->guid,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
	'field_class' => 'elgg-foot',
]);
elgg_set_form_footer($footer);

