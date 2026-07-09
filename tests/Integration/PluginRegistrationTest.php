<?php

namespace hypeJunction\Folders\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

/**
 * Asserts that the hypefolders elgg-plugin.php registrations actually land on a
 * booted Elgg 7 runtime: entity classes, routes and actions from the manifest.
 */
class PluginRegistrationTest extends IntegrationTestCase {

	/**
	 * 6466037: the entities block maps the literal subtypes to the folder
	 * classes. Prove the class resolution works at runtime (the whole point of
	 * keeping literal subtype strings so the classmap is wired in time).
	 */
	public function testEntityClassesRegistered(): void {
		$this->assertSame(MainFolder::class, elgg_get_entity_class('object', 'main_resource_folder'));
		$this->assertSame(Folder::class, elgg_get_entity_class('object', 'resource_folder'));
	}

	/**
	 * A saved main folder actually instantiates as MainFolder (registration +
	 * literal-subtype resolution end to end).
	 */
	public function testSavedMainFolderResolvesToMainFolderClass(): void {
		$user = $this->createUser();

		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			$folder = new MainFolder();
			$folder->owner_guid = $user->guid;
			$folder->container_guid = $user->guid;
			$folder->title = 'Reg test';
			$this->assertTrue($folder->save());

			$loaded = get_entity($folder->guid);
			$this->assertInstanceOf(MainFolder::class, $loaded);
			$this->assertSame('main_resource_folder', $loaded->getSubtype());
		});
	}

	/**
	 * Every route declared in elgg-plugin.php is registered by name.
	 */
	public function testRoutesRegistered(): void {
		$routes = _elgg_services()->routes;
		$names = [
			'collection:object:main_resource_folder:all',
			'view:object:main_resource_folder',
			'add:object:main_resource_folder',
			'folders:resources:move',
			'folders:search',
		];
		foreach ($names as $name) {
			$this->assertNotNull($routes->get($name), "route '{$name}' must be registered");
		}
	}

	/**
	 * Every action declared in elgg-plugin.php is registered.
	 */
	public function testActionsRegistered(): void {
		$actions = _elgg_services()->actions;
		$names = [
			'folders/edit',
			'folders/reorder',
			'folders/folder/edit',
			'folders/resources/add',
			'folders/resources/move',
			'folders/resources/remove',
		];
		foreach ($names as $name) {
			$this->assertTrue($actions->exists($name), "action '{$name}' must be registered");
		}
	}
}
