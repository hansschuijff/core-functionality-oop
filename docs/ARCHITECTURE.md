# Core Functionality (OOP): architecture and decisions

Status: October 2026, version 1.0.0 in development. Not in production; runs on the local DDEV site next to
the legacy `core-functionality` plugin, whose features move over step by step. Written by Claude from the
code and Hans's explanations; points marked "(confirm)" are Claude's reading and still need Hans's check.

## Goal

One plugin that holds all site-specific functionality of Hans's WordPress sites, independent of the theme.
It is a small microkernel: it finds its own modules and features, and starts only those that fit the current
site (active plugins and themes, server tier, admin or front end, and what the site owner switched on).
New customizations are added as new features without touching the kernel.

## Principles

- **Modules are containers, features do the work.** A module groups features and carries shared metadata;
  all behaviour lives in features.
- **Convention over registration.** A feature is found by its place on disk
  (`src/Modules/<Module>/Features/<Feature>/<Feature>.php`); there is no list to edit by hand.
- **Check before creating.** Metadata is static, so orientation, environment and interdependencies are
  checked before any object exists.
- **Environment aware.** A component only starts when the plugins, themes and server tier it needs are
  really there, proven at runtime (class, function or action present), not only "activated".
- **Fail closed.** If the settings files are missing or corrupt, the plugin stops completely and shows one
  admin notice; the rest of the site keeps working. An unknown server tier counts as production.
- **Code and site data apart.** Code is generic; site-specific data (toolbar menus, links, licence data)
  comes from settings files with database overrides.

## Boot sequence

1. `core-functionality.php` loads the Composer autoloader and creates `Plugin`.
2. `Plugin::__construct()` builds `Settings` (stops here on failure, see Principles), then
   `InterDependenciesGuard`, `EnvironmentLookup` (server tier), `Environment`, `FileService`,
   `ComponentsRegister` and the `Kernel`.
3. `plugins_loaded` (filter `dwp_cf_plugin_load_timing`): `Kernel::schedule_launches()` lets every active
   environment guard register its data, takes the launchable components from the `ComponentsRegister`,
   checks orientation and dependencies, and creates the module and feature instances.
   In admin, `Plugin::setup()` also builds `Notices`, the settings page and the `PluginIntegrityWatch`.
4. `init` (filter `dwp_cf_launch_timing`): `Kernel::launch()` drops instances whose required features are
   missing (`uses_features()`), calls `launch()` on every instance, marks the plugin ready and fires
   `dwp_cf_ready`.
5. Other code waits for that with `Plugin::call_when_ready()` or the `dwp_cf_ready` action.

## Structure

```
core-functionality-oop/
├── core-functionality.php        bootstrap
├── src/
│   ├── Plugin.php                container: services, paths, option keys, ready state
│   ├── Kernel.php                schedules on plugins_loaded, launches on init
│   ├── Interfaces/               ModuleInterface, FeatureInterface, LoggerInterface
│   ├── Enums/                    Orientation, Deployment, Register, RegisterSetting, ...
│   ├── DTO/                      AdminMenuBlock, ToolbarShortcut
│   ├── ValueObjects/             module, feature and environment validators
│   ├── Services/                 ComponentsRegister, Settings, Options, InterDependenciesGuard,
│   │                             PluginIntegrityWatch, ClassParser, FileSystem/ (scan and file writes)
│   ├── Environment/              Environment, EnvironmentLookup, Core/WordPress,
│   │                             Plugins/, Themes/, Servers/ (one guard class per id), Registers/
│   ├── Admin/Services/Settings/  settings page (Settings → Core Functionality) with sections (tabs)
│   ├── Notifiers/                admin notices; push notices with Pushover and Telegram drivers
│   ├── Diagnostics/Logger.php
│   ├── Api/                      CF facade and functions for other plugins
│   └── Modules/
│       ├── AdminToolbar/  MenuBuilder, LegacyMenuBuilder, Cleaner, StylesFixer, Visualizer
│       ├── AdminMenu/     AdminMenuArchitect
│       ├── ActiveWoo/     KeepLicenseAlive
│       └── Cleaner/       TransientOptimizer
├── settings/                     default settings files (site data), see Settings
└── kernel/                       generated components registers
```

## Components register

`ComponentsRegister` keeps track of which modules and features exist and which may load in the current
site. It combines:

- **what exists**: found by scanning `src/Modules/` (`rebuild()`), stored as module class → feature classes;
- **what is switched on**: the Feature Activation tab of the settings page;
- **server tier**: each component has a deployment level (development, staging, production) set on the
  Deployment tab. A development server loads everything, staging loads staging and production
  components, production loads only production components;
- **orientation**: admin, front end or both (`get_orientation()`).

The kernel then checks the environment dependencies of whatever passes. The registers are kept in the
database through the options vault and also written to `kernel/components-register.php`,
`components-enabled-register.php` and `components-deployment-register.php`; when the database has no value
the files are read and copied into it.

## Environment

A dependency is an id. `Environment` turns it into a guard class by convention: `woocommerce` →
`Environment\Plugins\Woocommerce`, `<id>_theme` → `Environment\Themes\…`, `<id>_environment` →
`Environment\Servers\…`, and `wordpress_core` → `Environment\Core\WordPress`. Plugin guards extend
`AbstractPlugin` and prove the plugin is alive with a `ProofRequirement` (classes, functions, actions);
results are cached per request. `settings/environment-index.php` maps plugin ids to plugin basenames and
theme ids to theme name and author. `get_dependencies()` returns one id or an `and` / `or` array of ids.

The plugin and theme guards also supply admin toolbar and admin menu links for their plugin or theme
(`get_toolbar_nodes()`, `get_menu_nodes()`), collected in `AdminToolbarRegister` and `AdminMenuRegister`.

## Settings and options

- **Settings files** (`settings/`) hold defaults and site data as PHP files returning arrays. General files
  sit in `settings/` (`environment-index.php`, `push-notice-drivers.php`); each module has its own folder
  under `settings/modules/<module-folder>/`. `settings/setup/modules-settings-path.php` maps a module id to
  its folder, and `settings/setup/key-slugs-translation.php` maps a settings key to a file in that folder.
  That is why `Settings::get( $key, $module_id )` takes the module id as second argument.
- **Database overrides**: `Settings::get()` merges the file with the stored option
  (`array_replace_recursive`); `$reset_to_defaults` returns the file only.
- **Options vault** (`Options`): maps setting tokens to real option names, either standalone or grouped in
  one option, and keeps unknown tokens in a quarantine pool until they are assigned.

## Admin

Settings → Core Functionality (`dwp-cf-settings`): one page with a sidebar of sections. Core sections:
Feature Activation, Push Notices, Deployment, Options Management. Features add their own section on
`dwp_cf_register_settings_sections`. `PluginIntegrityWatch` reports structural problems such as duplicate
ids in the error log and as an admin notice.

## Naming

| What | Convention |
|---|---|
| PHP namespace | `DeWittePrins\CoreFunctionality\`, PSR-4 under `src/` |
| Text domain | `core-functionality` (final folder name) |
| Hooks, options, nonces, cron | `dwp_cf_…` (two filters still use `dwp_core_…`) |
| Settings page slug | `dwp-cf-settings` |
| Module | `src/Modules/<Module>/<Module>.php` |
| Feature | `src/Modules/<Module>/Features/<Feature>/<Feature>.php` |
| Environment ids | snake_case; `_theme` and `_environment` suffixes pick the guard folder |

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `dwp_cf_ready` | action | Everything is launched |
| `dwp_cf_register_settings_sections` | action | Add a section to the settings page (`AdminSettingsPage` argument) |
| `dwp_cf_plugin_load_timing` | filter | `array( hook, priority )` for scheduling (default `plugins_loaded`, 10) |
| `dwp_cf_launch_timing` | filter | `array( hook, priority )` for launching (default `init`, 10) |
| `dwp_core_admin_menu_config_nodes` | filter | Admin menu nodes |
| `dwp_core_admin_menu_removals` | filter | Admin menu items to remove |

## Decisions

| Topic | Decision | Reason |
|---|---|---|
| Successor, not refactor | New plugin next to the legacy one; features move one by one | The site keeps working while the new structure grows |
| Modules only contain features | No behaviour in module classes | One place for behaviour; switching a feature off really switches it off |
| Discovery by folder | Scan `src/Modules/`, strict folder and namespace rules | Adding a feature is adding a folder |
| Static metadata | Checks run before instantiation | Nothing loads that cannot run |
| Runtime proof | Guard classes check classes, functions or actions | "Active" in the plugin list is not proof that a plugin loaded |
| Deployment tiers | Per component, set in admin | Experimental features only on development or staging |
| Settings as files plus overrides | Files are the defaults and live in git, the database holds changes | Site data is versioned and can be reset to defaults |
| Fail closed | Stop the whole plugin on corrupt settings | Half a kernel is worse than none |
| PHP 8.2+ | Enums, readonly properties, named arguments | Production runs PHP 8.5 |

## Open points

Known errors (to fix first):

- Guard ids do not all follow the snake_case convention: `FestingerVault::get_id()` returns
  `festinger-vault`, and `GitRemoteUpdater` returns `git-updater`, the same id as `GitUpdater`
  (probably copied; `git_remote_updater` expected) (confirm).
- `CF::initialize()` is never called, so the `Api` facade and functions always answer "not ready".
  `src/Api/helpers.php` is not autoloaded and calls a `Core` class that no longer exists.
- `Plugin` imports `Services\Loaders\ModuleLoader`, which does not exist.
- `Kernel` contains unused code: the location and class guards and the `components_register_cache`.
- The option `wp_cf_options_token_translations` uses `wp_cf_` instead of `dwp_cf_`.
- `settings/modules.php` lists old feature namespaces and is not read anywhere (the `modules` key is mapped
  but never requested) (confirm: remove?).
- About half of the PHP files lack `declare( strict_types=1 );`; phpcs has not been run over everything.

Structure proposal (Claude, October 2026; not decided):

1. **Less boilerplate per component.** `AbstractModule` and `AbstractFeature` with defaults (orientation
   both, no dependencies, no `uses_features`, the constructor and `get_module()`), so a new feature only
   writes its id, name, description and `launch()`. Interfaces stay as the contract.
2. **Code and site state apart.** What exists follows from the code: rebuild the components register
   automatically when the plugin version changes. Enabled state and deployment level are site choices and
   belong only in the database; the `kernel/` files become defaults for a fresh install and are no longer
   written at runtime. Today the database copy wins, so a feature added in an update probably stays
   invisible until someone rescans, and on production the plugin folder is often not writable and is
   replaced on every update (confirm).
3. **Environment only answers "is it there".** Move toolbar and menu links out of the guard classes into
   the AdminToolbar and AdminMenu modules (a small provider file per integration). Now every new guard has
   to know about the toolbar, and shop links exist twice (`Plugins/Woocommerce.php` and
   `settings/modules/admin-toolbar/add-shop/`).
4. **Hook names.** Decide whether new hooks follow the brain's `core_functionality/<name>` format; existing
   `dwp_cf_` hooks stay. Align the two `dwp_core_` filters either way.
5. **Interfaces** need no `interface_exists()` wrapper with Composer autoloading.

Missing:

- `languages/` with `.pot` and nl_NL `.po`/`.mo`, and loading of the text domain.
- `README.md` for developers (how to add a module or feature, hooks table).
- `.github/workflows/release.yml` that builds the release zip, as in Reader Experience.
- Rename of the folder and repository to `core-functionality` once the legacy plugin is retired.

## Testing

No automated tests. Checks: `php -l`, `vendor/bin/phpcs` (WordPress-Core and WordPress-Docs, PSR-4 file
names allowed), and manual checks on the DDEV site `https://speeltuin.ddev.site`.
