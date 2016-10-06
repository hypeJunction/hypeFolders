<?php

if (elgg_is_sticky_form('folders/edit')) {
	$sticky = elgg_get_sticky_values('folders/edit');
	if (is_array($sticky)) {
		$vars = array_merge($vars, $sticky);
		elgg_clear_sticky_form('folders/edit');
	}
}

echo elgg_view_form('folders/edit', array(
	'enctype' => 'multipart/form-data',
		), $vars);
