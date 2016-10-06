<?php

use hypeJunction\Folders\FoldersService;

$folder = elgg_extract('folder', $vars);
$resource = elgg_extract('resource', $vars);
?>
<div class="folders-editor-component">
	<p class="elgg-text-help">
		<?= elgg_echo('folders:add:content:instructions'); ?>
	</p>
	<div class="folders-content-editor elgg-content">
		<div class="folders-search-area">
			<?php
			echo elgg_view_form('folders/resources/search', [
				'action' => "folders/search",
				'method' => 'GET',
				'disable_security' => true,
			], $vars);
			?>
			<div class="folders-search-results">
				<?php
				$params = $vars;
				$params['input_name'] = 'guids';
				echo elgg_view('folders/search_results', $params);
				?>
			</div>
		</div>
		<div class="folders-content-area">
			<?= elgg_view_form('folders/resources/add', [], $vars) ?>
		</div>
	</div>
</div>
<script>
	require(['folders/resources/add']);
</script>