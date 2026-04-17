<a name="2.0.0"></a>
# 2.0.0 (2026-04-17)

### Breaking Changes

* **elgg:** raise minimum to Elgg 4.x (PHP 7.4+).

### Migration (3.x → 4.x)

* **plugin id:** lowercased plugin id from `hypeFolders` to `hypefolders` everywhere — plugin settings calls, composer name. Directory was already lowercase.
* **upgrade:** `Elgg\Upgrade\Batch` (`MigratePluginId`) copies all plugin settings (group_folders, user_folders, user_folders_restrict_by_owner) from the orphaned 3.x entity (`title='hypeFolders'`) to the 4.x entity (`title='hypefolders'`). Without this script, all admin-configured folder permission settings are lost on upgrade.
