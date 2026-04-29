# hypeFolders plugin architecture (Elgg 5.x)

Allows users to organize content into hierarchical folder structures. Users can create main
folders that act as containers for resources (files, pages, videos) and organize them in
tree hierarchies with drag-and-drop support.

## Layout

```
hypefolders/
├── composer.json             Plugin metadata and PHP dependencies
├── elgg-plugin.php           Elgg 5.x declarative configuration (routes, actions, events, entities)
├── autoloader.php            PSR-0 autoloader for classes/
├── classes/hypeJunction/Folders/
│   ├── Bootstrap.php         Plugin lifecycle: register(), activate() — creates custom DB table
│   ├── Folder.php            ElggObject subclass for resource_folder subtype
│   ├── MainFolder.php        ElggObject subclass for main_resource_folder — core folder logic
│   ├── FoldersService.php    Helper service for entity queries with batching
│   ├── Menus.php             Hook handlers for menu registration (folder tree, entity, owner block)
│   ├── Permissions.php       Hook handlers for container/folder write permissions
│   ├── Router.php            Hook handler for entity:url routing
│   └── Upgrades/
│       └── MigratePluginId.php   Batch upgrade from 3.x plugin ID (hypeFolders → hypefolders)
├── actions/folders/
│   ├── edit.php              Create/edit main folders
│   ├── reorder.php           Reorder folder items
│   ├── folder/
│   │   └── edit.php          Create/edit subfolders
│   └── resources/
│       ├── add.php           Add existing resources to folders
│       ├── move.php          Move resources between folders
│       └── remove.php        Remove resources from folders
├── views/default/
│   ├── bundles.css           Aggregated stylesheet imports
│   ├── folders/
│   │   ├── edit.php          Main folder edit view
│   │   ├── filter.php        Filter/search sidebar
│   │   ├── resource.php      Single resource in tree view
│   │   ├── resources.php     Resources list for folder
│   │   ├── search_results.php Search results display
│   │   ├── sidebar.php       Folder tree sidebar
│   │   ├── stylesheet.css    Main plugin styles
│   │   ├── listing/          Collection views (all/friends/group/owner)
│   │   └── resources/        Resource management views (add/edit/move/new)
│   ├── forms/folders/        Form rendering (edit, folder/edit, resources/add|move|search)
│   ├── navigation/menu/folders/  Folder tree menu views + JavaScript
│   ├── object/
│   │   ├── main_resource_folder.php   Main folder entity view
│   │   └── resource_folder.php        Subfolder entity view
│   ├── plugins/hypeFolders/
│   │   └── settings.php      Plugin admin settings UI
│   └── resources/folders/    Full-page resource responses (add/all/edit/friends/group/owner/search/view + resources/)
├── lib/
│   └── upgrades.php          Legacy upgrade hooks (3.x compatibility)
├── languages/
│   └── en.php                English translations
└── tests/
    ├── bootstrap.php
    ├── phpunit/integration/hypeJunction/Folders/
    │   ├── FolderTreeTest.php
    │   ├── FoldersServiceTest.php
    │   ├── MainFolderEntityTest.php
    │   ├── PermissionsTest.php
    │   ├── RouterTest.php
    │   └── ViewsTest.php
    └── phpunit/unit/hypeJunction/Folders/
        └── FolderClassConstantsTest.php
```

## Registered events (elgg-plugin.php)

All handlers registered under the `'events'` key (Elgg 5.x merged hooks and events).

| Event | Type | Handler | Notes |
|-------|------|---------|-------|
| create | object | MainFolder::addCreatedResource | Triggered when a folder is created |
| update | object | MainFolder::syncTitle | Syncs folder title to related resources |
| delete | object | MainFolder::removeDeletedItems (priority 999) | Cleans up when folder deleted |
| entity:url | object | Router::entityUrlHandler (priority 999) | Custom URLs for folder entities |
| container_permissions_check | object | Permissions::checkContainerPermissions | Folder creation permissions |
| container_permissions_check | all | Permissions::checkFolderPermissions | Content addition to folders |
| register | menu:folders | Menus::setupFolderMenu | Folder tree menu structure |
| register | menu:entity | Menus::setupFolderResourceMenu | Folder actions on entity menus |
| register | menu:owner_block | Menus::setupOwnerBlockMenu | Folder link in owner profile block |

View extensions declared:
- `elgg.css` ← `folders/stylesheet.css`
- `forms/file/upload` ← `folders/resources/new`
- `forms/pages/edit` ← `folders/resources/new`
- `forms/videolist/edit` ← `folders/resources/new`

## Routes

| Route name | Path | Middleware |
|------------|------|-----------|
| collection:object:main_resource_folder:all | /folders/all | — |
| collection:object:main_resource_folder:owner | /folders/owner/{username} | UserPageOwnerGatekeeper |
| collection:object:main_resource_folder:friends | /folders/friends/{username} | UserPageOwnerGatekeeper |
| collection:object:main_resource_folder:group | /folders/group/{guid} | GroupPageOwnerGatekeeper |
| view:object:main_resource_folder | /folders/view/{guid}/{resource_guid?} | — |
| add:object:main_resource_folder | /folders/add/{container_guid?} | Gatekeeper |
| edit:object:main_resource_folder | /folders/edit/{guid} | Gatekeeper |
| folders:resources:edit | /folders/resources/edit/{guid}/{resource_guid} | Gatekeeper |
| folders:resources:add | /folders/resources/add/{guid}/{resource_guid?} | Gatekeeper |
| folders:resources:new | /folders/resources/new/{guid}/{resource_guid}/{subtype} | Gatekeeper |
| folders:resources:move | /folders/resources/move/{guid}/{resource_guid} | Gatekeeper |
| folders:search | /folders/search | — |

## Actions

| Action | Handler | Purpose |
|--------|---------|---------|
| folders/edit | actions/folders/edit.php | Create/update main folder |
| folders/reorder | actions/folders/reorder.php | Reorder items within folder |
| folders/folder/edit | actions/folders/folder/edit.php | Create/update subfolder |
| folders/resources/add | actions/folders/resources/add.php | Add existing resource to folder |
| folders/resources/move | actions/folders/resources/move.php | Move resource to different folder/parent |
| folders/resources/remove | actions/folders/resources/remove.php | Remove resource from folder |

## Entities

| Type | Subtype | Class | Purpose |
|------|---------|-------|---------|
| object | main_resource_folder | MainFolder | Main folder container for organizing resources into hierarchies |
| object | resource_folder | Folder | Subfolder within a main folder |

Key `MainFolder` methods: `addResource($resource_guid, $parent_guid, $weight)`,
`isResource($guid)`, `getResources()`, `getChildren($guid)`, `getParent($guid)`,
`getAncestors($guid)`, `getPriority($guid)`.

Key `Folder` methods: `setMainFolder(MainFolder)`, `getMainFolder()`.

## Database schema (custom)

Created on activation in `Bootstrap::activate()`:

```sql
CREATE TABLE {dbprefix}folders (
  id             int(11) NOT NULL AUTO_INCREMENT,
  relationship_id int(11) NOT NULL,
  folder_guid    bigint(20) unsigned NOT NULL,
  parent_guid    bigint(20) unsigned NOT NULL,
  resource_guid  bigint(20) unsigned NOT NULL,
  weight         int(11) NOT NULL DEFAULT '0',
  title          text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY relationship_id (relationship_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

Tracks folder hierarchy relationships, parent-child links, and item ordering.

## External dependencies

Only `elgg/elgg ^5.0` and `composer/installers ^2.0` — no third-party PHP libraries.

Optional Elgg plugin dep: `elgg_tokeninput` (for resource selection autocomplete).

## Migration notes (4.x → 5.x)

1. **Hooks merged into events** — the `'hooks'` key in `elgg-plugin.php` is removed in Elgg 5.x.
   All formerly-hook handlers (`entity:url`, `container_permissions_check`, `register:menu:*`)
   moved into the `'events'` block. Handler type hints changed from `\Elgg\Hook` to `\Elgg\Event`.

2. **`elgg_trigger_plugin_hook()` removed** — replaced with `elgg_trigger_event_results()` in
   `FoldersService::getContentTypes()`.

3. **Relationship helpers removed** — `check_entity_relationship()`, `add_entity_relationship()`,
   and `remove_entity_relationship()` are gone. Replaced with:
   - `_elgg_services()->relationshipsTable->check()`
   - `$entity->addRelationship()`
   - `_elgg_services()->relationshipsTable->remove()`

4. **Raw SQL DB methods removed** — `elgg()->db->insertData()`, `deleteData()`, and `updateData()`
   no longer accept raw SQL strings. All now require Doctrine DBAL QueryBuilder instances
   (`Delete::fromTable()`, `Update::table()`). The MySQL UPSERT in `addResource()` uses
   `elgg()->db->getConnection('write')->executeStatement()` as the QueryBuilder lacks `ON DUPLICATE KEY UPDATE`.

5. **Bootstrap::activate() raw SQL** — CREATE TABLE call updated to use
   `elgg()->db->getConnection('write')->executeStatement()` instead of raw `updateData()`.

6. **PHP 8.2 strict typing** — `get_entity()` requires `int`, not nullable. All call sites guard
   against `null`/`0` before calling. Null guards added throughout views for optional `$entity`/`$container`.

7. **Route middleware** — added `UserPageOwnerGatekeeper` and `GroupPageOwnerGatekeeper` to the
   `owner`, `friends`, and `group` collection routes (new requirement in Elgg 5.x).

## Migration notes (3.x → 4.x)

1. **Plugin ID** — `hypeFolders` → `hypefolders`. `MigratePluginId` batch upgrade transfers
   settings on first activation.

2. **Declarative config** — hooks, events, actions, routes, view extensions, entities all
   moved from PHP registration to `elgg-plugin.php`.

3. **Bootstrap class** — extends `DefaultPluginBootstrap`; entity class registration in
   `register()`, table creation in `activate()`.

4. **Hook API** — handlers are static class methods receiving `\Elgg\Hook`/`\Elgg\Event`
   objects (getter methods) instead of positional parameters.

5. **Entity classes** — `MainFolder` and `Folder` explicitly extend `ElggObject` with subtype
   constants, registered via Bootstrap.

6. **Permissions** — split into two handlers: `checkContainerPermissions` (folder creation)
   and `checkFolderPermissions` (content addition to folders).
