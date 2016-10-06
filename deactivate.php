<?php

use hypeJunction\Folders\Folder;
use hypeJunction\Folders\MainFolder;

$subtypes = [
	MainFolder::SUBTYPE,
	Folder::SUBTYPE
];

foreach ($subtypes as $subtype) {
	update_subtype('object', $subtype);
}