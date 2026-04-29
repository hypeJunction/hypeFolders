<?php

namespace hypeJunction\Folders;

use Elgg\IntegrationTestCase;

class FoldersServiceTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testGetContentTypesReturnsArray(): void {
		$svc = new FoldersService();
		$types = $svc->getContentTypes();
		$this->assertIsArray($types);
	}

	public function testGetContentTypesExcludesComments(): void {
		$svc = new FoldersService();
		$types = $svc->getContentTypes();
		$this->assertNotContains('comment', $types);
		$this->assertNotContains('discussion_reply', $types);
		$this->assertNotContains('messages', $types);
	}

	public function testContentTypesHookCanFilterList(): void {
		$handler = function (\Elgg\Event $event) {
			return ['custom_type'];
		};
		\elgg_register_event_handler('content_types', 'folders', $handler);
		try {
			$svc = new FoldersService();
			$types = $svc->getContentTypes();
			$this->assertEquals(['custom_type'], $types);
		} finally {
			\elgg_unregister_event_handler('content_types', 'folders', $handler);
		}
	}
}
