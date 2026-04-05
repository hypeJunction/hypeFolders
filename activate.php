<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;
$subtypes = [MainFolder::SUBTYPE => MainFolder::class, Folder::SUBTYPE => Folder::class];
foreach ($subtypes as $subtype => $class) {
    if (!elgg_set_entity_class('object', $subtype, $class)) {
        elgg_set_entity_class('object', $subtype, $class);
    }
}
// Setup MySQL databases
run_sql_script(__DIR__ . '/install/mysql.sql');