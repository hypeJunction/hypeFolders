<?php

$vars['sort_by'] = 'priority';

$menu = elgg_view_menu('folders', $vars);

if ($menu) {
	echo elgg_view_module('info', '', $menu, array(
		'class' => 'folders-sidebar-nav',
	));
}