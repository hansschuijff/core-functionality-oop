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
3. `plugins_loaded`, admin only: `Plugin::setup()` builds `Notices`, the settings page and the
   `PluginIntegrityWatch`.
4. `init` priority 0 (filter `core-functionality/launch-timing`): `Kernel::boot()` does everything in one step. It lets
   every active environment guard register its toolbar and menu nodes, takes the launchable components
   from the `ComponentsRegister` (already filtered on activation, deployment tier and orientation), keeps
   those whose environment dependencies are ready, creates the modules and their features, drops features
   whose required features are missing (`uses_features()`), calls `launch()` on every instance, marks the
   plugin ready and fires `core-functionality/ready`. All plugins and the theme have loaded by then, so the guards see
   what really started; features can still hook into `init` itself.
5. Other code waits for that with `Plugin::call_when_ready()` or the `core-functionality/ready` action.

Until October 2026 the Kernel scheduled on `plugins_loaded` and launched on `init`, a leftover from when
components were scheduled from outside; the filter `dwp_cf_plugin_load_timing` went with it.

## Structure

```
core-functionality-oop/
├── core-functionality.php        bootstrap
├── src/
│   ├── Plugin.php                container: services, paths, option keys, ready state
│   ├── Kernel.php                schedules on plugins_loaded, launches on init
│   ├── Interfaces/               ModuleInterface, FeatureInterface, LoggerInterface
│   ├── Abstracts/                AbstractModule, AbstractFeature, DescribedByMeta (getters from get_meta())
│   ├── Enums/                    Orientation, Deployment, Register, RegisterSetting, ...
│   ├── DTO/                      AdminMenuBlock, ToolbarShortcut, ComponentMeta
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
the files are read and copied into it. The options are there for speed; the files are a fallback and are
only written when the `kernel/` folder is writable (on production it often is not, and the option then
keeps the change).

After a plugin update the components option would still describe the old version. The plugin version is
therefore stored with the registers (`components-register-version`). On a different version, before
anything reads the registers, the components option is removed so it is refilled from the new version's
file; components that are new get their enabled state and deployment tier from the files, while the
site's choices for existing components stay. An administrator gets a notice (on `admin_init`) listing
what was added. During development the version does not change, so a rescan on the settings page is
still how new code is picked up locally.

## Environment

A dependency is an id. `Environment` turns it into a guard class by convention: `woocommerce` →
`Environment\Plugins\Woocommerce`, `<id>-theme` → `Environment\Themes\…`, `<id>-environment` →
`Environment\Servers\…`, and `wordpress-core` → `Environment\Core\WordPress`. Ids are kebab-case
(Hans's preference); the key in `environment-index.php` is the id without the `-theme` suffix. Plugin guards extend
`AbstractPlugin` and prove the plugin is alive with a `ProofRequirement` (classes, functions, actions);
results are cached per request. `settings/environment-index.php` maps plugin ids to plugin basenames and
theme ids to theme name and author. `get_dependencies()` returns one id or an `and` / `or` array of ids.

The plugin and theme guards also supply admin toolbar and admin menu links for their plugin or theme
(`get_toolbar_nodes()`, `get_menu_nodes()`), collected in `AdminToolbarRegister` and `AdminMenuRegister`.
This is deliberate: a guard is the plugin's internal representative of something outside, so it is the
one that knows that plugin's screens. The links are building blocks ("lego bricks") from which Hans
composes his own quick menu in the admin toolbar, because the admin menu is long and needs scrolling.
Custom links can be added on the settings page as well. Blocks of a plugin that is not active must not
show in the menu. (The legacy plugin did it the other way round, from config, and had to test every
item's dependencies itself.)

Planned (not built): the settings screen needs the full catalogue of nodes, also of inactive plugins, to
compose the menu; at runtime the composed menu is matched against the guards that are ready, like the
components register does for loading. Guards therefore need a way to hand over their nodes outside
`is_ready()`. The admin menu feature has a similar, latent mechanism for menu items.

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
`core-functionality/register-settings-sections`. `PluginIntegrityWatch` reports structural problems such as duplicate
ids in the error log and as an admin notice.

## Naming

| What | Convention |
|---|---|
| PHP namespace | `DeWittePrins\CoreFunctionality\`, PSR-4 under `src/` |
| Text domain | `core-functionality` (final folder name) |
| Hooks | `core-functionality/<kebab-name>` |
| Options, nonces, cron events | `dwp_cf_…` |
| Settings page slug | `dwp-cf-settings` |
| Module | `src/Modules/<Module>/<Module>.php` |
| Feature | `src/Modules/<Module>/Features/<Feature>/<Feature>.php` |
| Environment ids | kebab-case of the guard's class name; `-theme` and `-environment` suffixes pick the guard folder |

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `core-functionality/ready` | action | Everything is launched |
| `core-functionality/register-settings-sections` | action | Add a section to the settings page (`AdminSettingsPage` argument) |
| `core-functionality/launch-timing` | filter | `array( hook, priority )` on which the Kernel boots (default `init`, 0) |
| `core-functionality/admin-menu-config-nodes` | filter | Admin menu nodes |
| `core-functionality/admin-menu-removals` | filter | Admin menu items to remove |

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
| One boot step on `init` 0 | Check, create and launch together | The register delivers a complete list; on `init` plugins and theme have loaded, so proofs can check what started |
| PHP 8.2+ | Enums, readonly properties, named arguments | Use current PHP features without going far back; production runs PHP 8.5 |
| Guards supply links | Each plugin/theme guard hands in its own toolbar and menu links | The guard knows the external plugin; the menu is composed from these blocks |
| Hook names | `core-functionality/<name>` (October 2026, before production) | "core" alone suggests WordPress core; replaces `dwp_cf_` and `dwp_core_` hooks |
| External contract | Only the ready moment (`core-functionality/ready`, `call_when_ready()`) is promised to other code | Internal timing may change |
| Metadata in one DTO | A static `get_meta()` returns `ComponentMeta`; abstract base classes answer the getters | A feature class holds only its functionality (Hans's idea, October 2026) |
| Version sync of the registers | Plugin version stored with the registers; on a new version the components option is rebuilt from the file and new components get file defaults | An update's new features appear without a manual rescan, and the site's choices stay |
| Register files optional | Written only when `kernel/` is writable | They are a fallback for the database; production folders are often read-only |

## Open points

Known issues:

- The public API is not worked out yet (parked): `CF::initialize()` is never called, so the `Api` facade
  and functions answer "not ready", and `src/Api/helpers.php` is a placeholder (not autoloaded; it calls a
  `Core` class that no longer exists).
- phpcs works again since October 2026 (it silently exited before, see `src/Api/functions.php`). Over the
  whole plugin: 2795 errors and 306 warnings, of which phpcbf can fix 2634 automatically (best in one
  separate commit). The rest is mostly missing docblocks (also in the guard classes) and
  `declare( strict_types=1 );` in about half of the files.
- Tested in a logged-in admin (October 2026): the "new components" notice after a simulated update, the
  integrity check and all settings tabs. The two issues found there (the toolbar Cleaner reading
  `$node->id` from an array, and feature names ending in a period) are fixed. The plugin is active again
  on the local site.
- `src/Modules/LegeModule/` (gitignored template) and `KeepLicenseAlive/leeg.php` still use the old style.
- Local databases may still hold the old option `wp_cf_options_token_translations` (now `dwp_cf_…`).

Environment proofs (reviewed October 2026):

- Most proofs were chosen when the Kernel still checked on `plugins_loaded` 10, so they use what a plugin's
  main file defines. Now that the Kernel boots on `init`, they could check a plugin's successful start
  instead (for example Event Tickets Plus, which starts on `plugins_loaded` 50). Optional tightening.
- Guards for plugins that are not installed locally (Carbon Fields, Carbon Fields Helper, SearchWP,
  ActiveWoo API Helper, Schema Glue, Sensei Paid Courses) only use the activation check until a proof is
  confirmed.
- The Tribe guards check the Tribe template functions, which catch a Tribe plugin that switched itself
  off after a version conflict. (A `not_actions` check on `tribe_extensions_failed_requirements` was
  removed: that action does not exist in the Tribe plugins.)
- Verified on the local site at Kernel timing: The Events Calendar (+ Pro), Event Tickets (+ Plus),
  WooCommerce, Loco Translate, SEO Redirection Premium, Debug Toolkit. Checked in the sources only: the
  other installed plugins, including Redis Cache and Sensei Pro (WC Paid Courses, folder
  `woothemes-sensei`, which bundles Sensei LMS; its guard extends SenseiLms for the course links).
  Festinger Vault: which folder production uses, left for later. `sensei-paid-courses` may be an old
  name of Sensei Pro (confirm).
- AutomateWoo replaced WooCommerce Follow-Up Emails; the guards for Follow-Up Emails, EU VAT Assistant
  (legacy) and WP phpMyAdmin (not needed) were removed. The AdminToolbar settings files
  (`settings/modules/admin-toolbar/`) are legacy, copied from the old plugin and no longer used; they are
  removed or replaced once the toolbar feature works.
- Site-specific URLs in guard code (`/evenementen/`, `/agenda/categorie/familieopstellingen/`,
  `/artikelen/`, the `course-payment-plans` product category) stay for now; they could also become custom
  links on the settings page.

Considered and set aside: keeping a feature's administration (its meta) on a settings screen; options do
not travel with an update to another site.

Missing:

- `languages/` with `.pot` and nl_NL `.po`/`.mo`, and loading of the text domain.
- `README.md` for developers (how to add a module or feature, hooks table).
- `.github/workflows/release.yml` that builds the release zip, as in Reader Experience.
- Rename of the folder and repository to `core-functionality` once the legacy plugin is retired.

## Testing

No automated tests. Checks: `php -l`, `vendor/bin/phpcs` (WordPress-Core and WordPress-Docs, PSR-4 file
names allowed, `-` and `/` allowed in hook names), and manual checks on the DDEV site
`https://speeltuin.ddev.site`. The plugin is not active there; Claude tests by loading it for a single
request from a temporary mu-plugin (removed afterwards), which shows whether the Kernel boots, which
components start and which guards are ready, without changing the activation state.
