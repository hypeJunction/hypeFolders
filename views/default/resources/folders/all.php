<?php

use hypeJunction\Folders\MainFolder;

elgg_push_breadcrumb(elgg_echo('folders'));

elgg_register_title_button('folders', 'add', 'object', MainFolder::SUBTYPE);

$title = elgg_echo('folders:all');

$content = elgg_view('folders/listing/all');

$layout = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter_context' => 'all',
		));

echo elgg_view_page($title, $layout);
