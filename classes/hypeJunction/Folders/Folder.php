<?php

namespace hypeJunction\Folders;

class Folder extends \ElggObject {

	const CLASSNAME = __CLASS__;
	const SUBTYPE = 'resource_folder';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * Sets folder reference
	 *
	 * @param MainFolder $folder
	 * @return void
	 */
	public function setMainFolder($folder) {
		$this->setVolatileData('select:folder_guid', $folder->guid);
	}

	/**
	 * Returns a folder reference
	 * @return MainFolder
	 */
	public function getMainFolder() {
		$folder_guid = $this->getVolatileData('select:folder_guid');
		$folder = get_entity($folder_guid);
		if (!$folder) {
			$folders = elgg_get_entities_from_relationship(array(
				'types' => 'object',
				'subtypes' => MainFolder::SUBTYPE,
				'relationship' => 'resource',
				'relationship_guid' => $this->guid,
				'inverse_relationship' => false,
				'limit' => 1,
			));
			$folder = ($folders) ? $folders[0] : new MainFolder;
		}
		return $folder;
	}
}
