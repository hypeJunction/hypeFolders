<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

$subtypes = [
	MainFolder::SUBTYPE => MainFolder::class,
	Folder::SUBTYPE => Folder::class,
];

foreach ($subtypes as $subtype => $class) {
	if (!update_subtype('object', $subtype, $class)) {
		add_subtype('object', $subtype, $class);
	}
}

// Setup MySQL databases
run_sql_script(__DIR__ . '/install/mysql.sql');