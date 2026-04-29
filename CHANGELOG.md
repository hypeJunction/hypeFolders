<a name="3.0.0"></a>
# 3.0.0 (2026-04-28)

### Breaking Changes

* **elgg:** raise minimum to Elgg 5.x (PHP 8.2+).

### Migration (4.x → 5.x)

* **hooks→events:** All hook registrations in `elgg-plugin.php` merged into the `'events'` block. Handler type hints changed from `\Elgg\Hook` to `\Elgg\Event` in `Menus`, `Permissions`, and `Router`.
* **plugin hook trigger:** `elgg_trigger_plugin_hook('content_types', 'folders', ...)` → `elgg_trigger_event_results('content_types', 'folders', ...)` in `FoldersService`.
* **relationship API:** Removed `check_entity_relationship()`, `add_entity_relationship()`, `remove_entity_relationship()`. Now uses `_elgg_services()->relationshipsTable->check/remove()` and `$entity->addRelationship()`.
* **DB API:** Removed raw-SQL overloads of `insertData()`/`deleteData()`/`updateData()`. Now uses `Delete::fromTable()`, `Update::table()` QueryBuilder, and `getConnection('write')->executeStatement()` for the UPSERT.
* **route middleware:** Added `UserPageOwnerGatekeeper` / `GroupPageOwnerGatekeeper` to `owner`, `friends`, and `group` collection routes.
* **PHP 8.2 compatibility:** Null guards added for `get_entity()` call sites and optional `$entity`/`$container` in views.

<a name="2.0.0"></a>
# 2.0.0 (2026-04-17)

### Breaking Changes

* **elgg:** raise minimum to Elgg 4.x (PHP 7.4+).

### Migration (3.x → 4.x)

* **plugin id:** lowercased plugin id from `hypeFolders` to `hypefolders` everywhere — plugin settings calls, composer name. Directory was already lowercase.
* **upgrade:** `Elgg\Upgrade\Batch` (`MigratePluginId`) copies all plugin settings (group_folders, user_folders, user_folders_restrict_by_owner) from the orphaned 3.x entity (`title='hypeFolders'`) to the 4.x entity (`title='hypefolders'`). Without this script, all admin-configured folder permission settings are lost on upgrade.
