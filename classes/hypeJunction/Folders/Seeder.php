<?php

namespace hypeJunction\Folders;

use Elgg\Database\Seeds\Seed;

/**
 * Seeds folder entities for development and testing.
 */
class Seeder extends Seed {

	/**
	 * {@inheritdoc}
	 */
	public function seed() {
		$this->advance($this->getCount());

		while ($this->seedsCount() < $this->getCount()) {
			$entity = new MainFolder();
			$entity->owner_guid = $this->getRandomUser()->guid;
			$entity->container_guid = $entity->owner_guid;
			$entity->title = $this->faker->sentence(3);
			$entity->description = $this->faker->paragraph();

			if (!$entity->save()) {
				continue;
			}

			$this->advance();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function unseed() {
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => MainFolder::SUBTYPE,
			'limit' => false,
			'batch' => true,
		]);

		foreach ($entities as $entity) {
			$entity->delete();
			$this->advance();
		}
	}

	/**
	 * Register this seeder with the seeds event.
	 *
	 * @param \Elgg\Event $event seeds,database event
	 * @return array
	 */
	public static function addSeed(\Elgg\Event $event) {
		$seeds = $event->getValue();
		$seeds[] = self::class;
		return $seeds;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getType(): string {
		return MainFolder::SUBTYPE;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCountOptions(): array {
		return [
			'type' => 'object',
			'subtype' => MainFolder::SUBTYPE,
		];
	}

}
