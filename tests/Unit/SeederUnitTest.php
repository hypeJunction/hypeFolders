<?php

namespace hypeJunction\Folders\Tests\Unit;

use hypeJunction\Folders\MainFolder;
use hypeJunction\Folders\Seeder;
use PHPUnit\Framework\TestCase;

/**
 * Pure-logic unit tests for the Seeder contract (no DB / no page owner).
 */
class SeederUnitTest extends TestCase {

	/**
	 * 5a1ced4: Seeder implements the Elgg 6.1 Seed contract. getType() is the
	 * subtype the seeder owns and unseeds, and must be the MainFolder subtype.
	 */
	public function testGetTypeReturnsMainFolderSubtype(): void {
		$this->assertSame('main_resource_folder', Seeder::getType());
		$this->assertSame(MainFolder::SUBTYPE, Seeder::getType());
	}

	/**
	 * seeds,database -> Seeder::addSeed appends the seeder class to the event's
	 * value array and returns the augmented array (registration contract).
	 */
	public function testAddSeedAppendsSeederClassToEventValue(): void {
		if (!class_exists(\Elgg\Event::class)) {
			$this->markTestSkipped('\Elgg\Event not autoloadable outside a booted Elgg');
		}

		$event = $this->createMock(\Elgg\Event::class);
		$event->method('getValue')->willReturn(['Some\Other\Seed']);

		$result = Seeder::addSeed($event);

		$this->assertIsArray($result);
		$this->assertContains(Seeder::class, $result);
		$this->assertContains('Some\Other\Seed', $result, 'existing seeds must be preserved');
	}
}
