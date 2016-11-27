<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggMenuItem) {
	return;
}

$guid = $item->getData('guid');
$collapse = (bool) $item->getData('collapse');

$children = $item->getChildren();

$item_class = array($item->getItemClass());

$submenu = '';
if ($children) {
	$item_class[] = "elgg-menu-parent";
	$item_class[] = ($collapse) ? 'elgg-menu-closed' : 'elgg-menu-open';

	$toggle = '<span class="elgg-child-menu-toggle"><span class="collapse ">&#9698;</span><span class="expand">&#9654;</span></span>';

	if (!empty($children)) {
		$submenu = elgg_view('navigation/menu/folders/section', array(
			'items' => $children,
			'class' => 'elgg-menu elgg-child-menu',
			'collapse' => true
		));
	}
} else {
	$item_class[] = "elgg-menu-nochildren";
	$toggle = '<span class="elgg-child-menu-indicator">&#9675;</span>';
}

if ($item->getSelected()) {
	$item_class[] = "elgg-state-selected";
}

if (isset($vars['item_class'])) {
	$item_class[] = $vars['item_class'];
}

echo elgg_format_element('li', array(
	'class' => $item_class,
	'data-guid' => $guid,
	'data-parent-guid' => $item->getData('parent-guid'),
	'data-folder-guid' => $item->getData('folder-guid'),
		), $toggle . elgg_view_menu_item($item) . $submenu);

