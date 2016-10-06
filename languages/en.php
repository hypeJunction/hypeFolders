<?php

return [

	'folders:settings:user_folders' => 'Enable user folders',
	'folders:settings:user_folders:help' => 'Allow users to create their own folders',

	'folders:settings:user_folders_restrict_by_owner' => 'Restrict user folders',
	'folders:settings:user_folders_restrict_by_owner:help' => 'Restrict user folder content to items that have been created by the folder owner (for non admins)',

	'folders:settings:group_folders' => 'Enable group folders',
	'folders:settings:group_folders:help' => 'Allow groups to create their own folders',

	'folders:settings:group_folders_restrict_by_container' => 'Restrict group folders',
	'folders:settings:group_folders_restrict_by_container:help' => 'Restrict group folder content to items that have been created within a group (for non admins)',

	'item:object:main_resource_folder' => 'Root Folders',
	'item:object:resource_folder' => 'Folders',

	'folders:group' => 'Group folders',
	'folders:group_tool:folders' => 'Enable group folders',
	'folders:group_tool:admin_only' => 'Retrict new folder creation to group admins',
	'folders:group_tool:add_to_folders' => 'Allow all group members to add and remove content from group folders',

	'folder:add' => 'New folder',
	'folders' => 'Folders',
	'folders:all' => 'All folders',
	'folders:mine' => 'My folders',
	'folders:owner' => '%s\'s folders',
	'folders:friends:mine' => 'Friends\' folders',
	'folders:friends' => '%s\'s friends\' folders',
	'folders:edit' => 'Edit folder',
	'folders:add' => 'Create a folder',
	'folders:none' => 'There are no folders to display',
	'folders:empty' => 'There are no resources in this folder',
	'folders:resources' => 'Resources',
	'folders:resources:add' => 'Add resources',
	'folders:resources:remove' => 'Remove',
	'folders:folder:title' => 'Title',
	'folders:folder:description' => 'Description',
	'folders:folder:icon' => 'Icon',
	'folders:folder:tags' => 'Tags',
	'folders:folder:category' => 'Category',
	'folders:folder:access_id' => 'Visibility',
	'folders:input:error:required' => 'Required field %s is missing',
	'folders:get:error:entity' => 'Object does not exist',
	'folders:edit:error:entity' => 'You are not allowed to edit this item',
	'folders:write:error:container' => 'You are not allowed to add content here',
	'folders:save:success' => 'Folder was successfully saved',
	'folders:save:error:generic' => 'Folder could not be saved',
	'folders:ajax:error' => 'Requested resource could not be loaded',
	'folders:folder:error:no_entity' => 'Folder could not be found',
	'folders:resources:add:error:empty_selection' => 'You need to select at least one item to add to folder',
	'folders:resources:add:success' => '%s of %s items were added to folder',
	'folders:folder' => 'Folder',
	'folders:folder:title' => 'Title',
	'folders:folder:description' => 'Description',
	'folders:folder:icon' => 'Icon',
	'folders:folder:tags' => 'Tags',
	'folders:folder:category' => 'Category',
	'folders:folder:access_id' => 'Visibility',
	'folders:content' => 'Content items',
	'folders:content:subtypes:all' => 'All types',
	'folders:content:search:query' => 'Search keyword',
	'folders:content:search:subtype' => 'Content type',
	'folders:search' => 'Search',
	'folders:search:no_results' => 'No items matching your query',
	'folders:resources:no_results' => 'No items in this folder yet',
	'folders:no_results' => 'There are no folders yet',
	
	'folders:search:in' => ' in %s',

	'folders:query:placeholder' => 'Enter search keyword',

	'folders:add:content:instructions' => '
		You can find existing content items using the search form.
		You can drag and drop items from the search results into the designated area on the right in order to add them to the folder.
		You can use reorder items in the folder by dragging and dropping them.
	',

	'folders:new:file' => 'File',
	'folders:new:videolist_item' => 'Video',
	'folders:new:resource_folder' => 'Subfolder',
	'folders:new:page_top' => 'Page',

];
