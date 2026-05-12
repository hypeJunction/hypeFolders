<?php

namespace hypeJunction\Folders\Tests\Integration;

use Elgg\IntegrationTestCase;
use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

class FolderTest extends IntegrationTestCase {

    public function testFolderSubtype() {
        $folder = new Folder();
        $this->assertEquals('resource_folder', $folder->getSubtype());
    }

    public function testFolderSubtypeConstant() {
        $this->assertEquals('resource_folder', Folder::SUBTYPE);
    }

    public function testMainFolderSubtype() {
        $main = new MainFolder();
        $this->assertEquals('main_resource_folder', $main->getSubtype());
    }
}
