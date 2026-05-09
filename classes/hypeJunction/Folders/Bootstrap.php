<?php

namespace hypeJunction\Folders;

use Elgg\DefaultPluginBootstrap;

/**
 * Bootstrap for hypeFolders plugin.
 */
class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		elgg_set_entity_class('object', MainFolder::SUBTYPE, MainFolder::class);
		elgg_set_entity_class('object', Folder::SUBTYPE, Folder::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_register_event_handler('seeds', 'database', [Seeder::class, 'addSeed']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {
		$dbprefix = \elgg_get_config('dbprefix');
		$sql = "CREATE TABLE IF NOT EXISTS `{$dbprefix}folders` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`relationship_id` int(11) NOT NULL,
			`folder_guid` bigint(20) unsigned NOT NULL,
			`parent_guid` bigint(20) unsigned NOT NULL,
			`resource_guid` bigint(20) unsigned NOT NULL,
			`weight` int(11) NOT NULL DEFAULT '0',
			`title` text NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `relationship_id` (`relationship_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		try {
			\elgg()->db->getConnection('write')->executeStatement($sql);
		} catch (\Throwable $e) {
			// Table may already exist
		}
	}
}
