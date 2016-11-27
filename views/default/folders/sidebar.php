<?php

$folder = elgg_extract('folder', $vars);
if (!$folder instanceof \hypeJunction\Folders\MainFolder) {
	return;
}

$vars['sort_by'] = 'priority';
$vars['class'] = $folder->canEdit() ? 'elgg-state-sortable' : '';

$menu = elgg_view_menu('folders', $vars);

if ($menu) {
	echo elgg_view_module('info', '', $menu, array(
		'class' => 'folders-sidebar-nav ',
	));
}