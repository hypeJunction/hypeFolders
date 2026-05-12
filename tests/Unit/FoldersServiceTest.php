<?php

namespace hypeJunction\Folders\Tests\Unit;

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\FoldersService;
use PHPUnit\Framework\TestCase;

/**
 * Basic smoke tests for FoldersService autoloading and class constants.
 *
 * @group unit
 */
class FoldersServiceTest extends TestCase {

    public function testFolderSubtypeConstant() {
        $this->assertEquals('resource_folder', Folder::SUBTYPE);
    }

    public function testFoldersServiceClassExists() {
        $this->assertTrue(class_exists(FoldersService::class));
    }
}
