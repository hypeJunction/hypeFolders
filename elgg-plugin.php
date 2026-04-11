<?php

use hypeJunction\Folders\Bootstrap;
use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

return [
	'bootstrap' => Bootstrap::class,

	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'main_resource_folder',
			'class' => MainFolder::class,
			'searchable' => true,
		],
		[
			'type' => 'object',
			'subtype' => 'resource_folder',
			'class' => Folder::class,
			'searchable' => true,
		],
	],
	'actions' => [
		'folders/edit' => [],
		'folders/reorder' => [],
		'folders/folder/edit' => [],
		'folders/resources/add' => [],
		'folders/resources/move' => [],
		'folders/resources/remove' => [],
	],
	'routes' => [
		'collection:object:main_resource_folder:all' => [
			'path' => '/folders/all',
			'resource' => 'folders/all',
		],
		'collection:object:main_resource_folder:owner' => [
			'path' => '/folders/owner/{username}',
			'resource' => 'folders/owner',
		],
		'collection:object:main_resource_folder:friends' => [
			'path' => '/folders/friends/{username}',
			'resource' => 'folders/friends',
		],
		'collection:object:main_resource_folder:group' => [
			'path' => '/folders/group/{guid}',
			'resource' => 'folders/group',
		],
		'view:object:main_resource_folder' => [
			'path' => '/folders/view/{guid}/{resource_guid?}',
			'resource' => 'folders/view',
		],
		'add:object:main_resource_folder' => [
			'path' => '/folders/add/{container_guid?}',
			'resource' => 'folders/add',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'edit:object:main_resource_folder' => [
			'path' => '/folders/edit/{guid}',
			'resource' => 'folders/edit',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'folders:resources:edit' => [
			'path' => '/folders/resources/edit/{guid}/{resource_guid}',
			'resource' => 'folders/resources/edit',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'folders:resources:add' => [
			'path' => '/folders/resources/add/{guid}/{resource_guid?}',
			'resource' => 'folders/resources/add',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'folders:resources:new' => [
			'path' => '/folders/resources/new/{guid}/{resource_guid}/{subtype}',
			'resource' => 'folders/resources/new',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'folders:resources:move' => [
			'path' => '/folders/resources/move/{guid}/{resource_guid}',
			'resource' => 'folders/resources/move',
			'middleware' => [
				\Elgg\Router\Middleware\Gatekeeper::class,
			],
		],
		'folders:search' => [
			'path' => '/folders/search',
			'resource' => 'folders/search',
		],
	],
	'view_extensions' => [
		'elgg.css' => [
			'folders/stylesheet.css' => [],
		],
		'forms/file/upload' => [
			'folders/resources/new' => [],
		],
		'forms/pages/edit' => [
			'folders/resources/new' => [],
		],
		'forms/videolist/edit' => [
			'folders/resources/new' => [],
		],
	],
];
