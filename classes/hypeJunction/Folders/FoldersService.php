<?php

namespace hypeJunction\Folders;

use ElggBatch;
use stdClass;

/**
 * @access private
 */
class FoldersService {

	/**
	 * Returns a batch of entities or an array of guids
	 *
	 * @param array    $options  ege* options
	 * @param bool     $as_guids Only return guids
	 * @param callable $ege      ege* callable
	 * @return ElggBatch|array
	 */
	protected function getEntities(array $options = array(), $as_guids = false, callable $ege = null) {

		if (!$ege) {
			$ege = 'elgg_get_entities_from_attributes';
		}

		if (!is_callable($ege)) {
			return array();
		}

		if (!empty($options['count'])) {
			return call_user_func($ege, $options);
		}

		if ($as_guids) {
			$options['callback'] = array($this, 'rowToGUID');
		}

		return new ElggBatch($ege, $options);
	}

	/**
	 * Callback function for ege* to only return guids
	 *
	 * @param stdClass $row DB row
	 * @return int
	 */
	public static function rowToGUID($row) {
		return (int) $row->guid;
	}

	/**
	 * Get entity subtypes allowed to be added to folders
	 * @return array
	 */
	public function getContentTypes() {

		$allowed = get_registered_entity_types('object');
		$exceptions = ['messages', 'comment', 'discussion_reply'];

		$allowed = array_diff($allowed, $exceptions);

		return elgg_trigger_plugin_hook('content_types', 'folders', [], $allowed);
	}

}
