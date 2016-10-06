<?php

use hypeJunction\Folders\FoldersService;

$folder = elgg_extract('folder', $vars);
$resource = elgg_extract('resource', $vars);
?>
<div class="elgg-foot">
	<?php
	$svc = new FoldersService();
	$subtypes = $svc->getContentTypes();
	foreach ($subtypes as $subtype) {
		if (
			elgg_view_exists("folders/resources/new/$subtype")
			&& $folder->canWriteToContainer()
		) {
			echo elgg_view('output/url', [
				'text' => elgg_view_icon('plus') . elgg_echo("folders:new:$subtype"),
				'href' => "folders/resources/new/$folder->guid/$resource->guid/$subtype",
				'class' => 'elgg-button elgg-button-action elgg-lightbox',
				'data-colorbox-opts' => json_encode([
					'maxWidth' => '600px',
					'maxHeight' => '600px',
					'minHeight' => '300px',
				]),
			]);
		}
	}
	?>
</div>
<?php
echo elgg_view_input('submit', [
	'name' => 'submit_action',
	'value' => elgg_echo('save'),
	'class' => 'hidden',
]);

$params = $vars;
$params['input_name'] = 'guids';
$params['show_placeholder'] = true;
echo elgg_view('folders/resources', $params);

echo elgg_view_input('hidden', [
	'name' => 'main_folder_guid',
	'value' => $folder->guid,
]);

echo elgg_view_input('hidden', [
	'name' => 'resource_guid',
	'value' => $resource->guid,
]);
