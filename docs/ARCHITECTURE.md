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
   every active environment guard register its admin menu nodes, takes the launchable components
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
│   ├── Services/                 ComponentsRegister (+ ComponentsRegisterFiles), Settings (+ SettingsFiles,
│   │                             Options), InterDependenciesGuard, PluginIntegrityWatch, ClassParser,
│   │                             FileSystem/ (FileService, the scanners)
│   ├── Environment/              Environment, EnvironmentLookup, Core/WordPress,
│   │                             Plugins/, Themes/ (one guard class per id), Registers/
│   ├── Admin/Services/Settings/  settings page (Settings → Core Functionality) with sections (tabs)
│   ├── Notifiers/                admin notices; push notices with Pushover and Telegram drivers
│   ├── Diagnostics/Logger.php
│   ├── Api/                      CF facade and functions for other plugins
│   └── Modules/
│       ├── AdminToolbar/  MenuBuilder, Cleaner, StylesFixer, Visualizer
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
the files are read and copied into it (through `ComponentsRegisterFiles`, which knows which register lives
in which file). The options are there for speed; the files are a fallback and are
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
`Environment\Plugins\Woocommerce`, `<id>-theme` → `Environment\Themes\…`, and `wordpress-core` →
`Environment\Core\WordPress`. Ids are kebab-case
(Hans's preference); the key in `environment-index.php` is the id without the `-theme` suffix. Plugin guards extend
`AbstractPlugin` and prove the plugin is alive with a `ProofRequirement` (classes, functions, actions);
results are cached per request. `settings/environment-index.php` maps plugin ids to plugin basenames and
theme ids to theme name and author. `get_dependencies()` returns one id or an `and` / `or` array of ids.

`Environment` creates each guard once per request: `get_plugin( $id )` and `get_theme( $id )` keep the
instances per kind (plugins and themes are registered separately), `get_wordpress()` the core guard.
There are no server guards: the server tier is handled by the deployment tiers of the components, and
switching plugins per server by a separate mu-plugin (the `-environment` guards were removed in October
2026).

The plugin and theme guards also supply admin toolbar and admin menu links for their plugin or theme
(`get_toolbar_nodes()`, `get_menu_nodes()`), collected in `AdminToolbarRegister` and `AdminMenuRegister`.
This is deliberate: a guard is the plugin's internal representative of something outside, so it is the
one that knows that plugin's screens. The links are building blocks ("lego bricks") from which Hans
composes his own quick menu in the admin toolbar, because the admin menu is long and needs scrolling.
Custom links can be added on the settings page as well. Blocks of a plugin that is not active must not
show in the menu. (The legacy plugin did it the other way round, from config, and had to test every
item's dependencies itself.)

**Toolbar catalogue.** `AdminToolbarRegister` holds the toolbar nodes of *every known* plugin and theme
(every guard class in `Plugins/` and `Themes/`) plus the WordPress core, each provider with a
`ProviderStatus`. This plugin has a guard of its own there (`Plugins\CoreFunctionality`, e.g. the link to
its settings page): always ready, no index entry, and should anything name it as a dependency it answers
true. The statuses:
active (`is_ready()`), inactive (installed) or not installed. `get_all()` serves the settings page, so a
menu can be set up once, before a plugin is installed or activated; `get_active()` serves the toolbar, so
activating or deactivating a plugin switches its nodes on and off. The register is filled in memory on
first use after `init` (`Environment::load_toolbar_catalogue()`, set as its loader); it is not cached in
the database, because hrefs can be closures and titles are translated. Because nodes of inactive plugins
are built too, `get_toolbar_nodes()` may only call WordPress functions directly; anything that needs the
plugin itself goes in a closure as href (`ToolbarShortcut::get_href()` resolves it). The admin menu nodes
still go to `AdminMenuRegister` from `is_ready()`.

`ToolbarShortcut::$orientation` is the kind of link (to an admin page or to the site), shown as a badge in
the menu builder. Where a node is shown is not decided by the guard but per node in the menu builder.

**Menu builder.** The saved tree (settings key `menu-builder-hierarchical-tree`) holds per node: `id`,
`type` (`shortcut` or `submenu`), `title` (empty: the shortcut's own, translated title), `href` (submenus
only; a shortcut's URL always comes from the guard), `visibility` (`admin`, `frontend` or `both`, the
default) and `children`, at most four levels (`MenuBuilderSettings::MAX_DEPTH`; the runtime has no limit of its own). At runtime a shortcut whose provider is not active is left
out together with its children, and a submenu without a link of its own is left out when none of its
children is shown. The settings tab renders the catalogue and the tree as JSON; `menu-builder-settings.js`
builds the pool and the tree from it (one place for the markup). A new node takes the depth of the node it
is inserted before (at the end: of the last node), so it never takes over a submenu; moving a node moves
its branch. With one node selected, "Add" (the checked pool items) inserts below it (inside it when it is a submenu
without a link). The admin menu feature has a similar, latent mechanism for menu items.

Visibility is shown as a one-click switch per node. A node is shown only where its parents are shown
too (the runtime checks every node), but a child keeps its own setting under a restricted parent: its
setting usually follows from where its link goes, and changing it automatically would lose that when the
parent is changed back. The editor shows the parent's restriction and marks a conflicting child (never
shown) without changing it. Nodes can be selected (shift-click for a range) and changed in bulk:
visibility, reset titles, remove, and dragging or keyboard moves of the whole selection, each node with
its branch. The pool is grouped per source with search and an "only active" filter; tree branches can be
collapsed (remembered in the browser). The tree is an ARIA tree with keyboard control (arrows, Alt+arrows
to move and indent, Space to select, Ctrl+Space or Ctrl-click for a node with its branch, Enter, Delete,
Esc); "Keyboard shortcuts" (or ?) shows the overview. When the columns fit side by side, the builder fills
the visible part of the settings page, so only the lists scroll. Branches and the explanations above the
pool and the menu start collapsed (one "Explanation" button above either column opens both). The pool's
controls are one row: search, a filter switch for "only active" and Add; selecting a whole source goes
with the checkbox in its heading. A collapsed branch that receives a node is opened, and depths at the end of the
menu follow the last visible node, so nothing disappears into a closed branch. Every change can be undone
and redone (Ctrl+Z / Ctrl+Y, up to 50 steps; serialize() records the state before each change).

Dragging: the drop depth follows the pointer sideways (one indent from where the drag started, or entered
the tree, is one level), between the depth of the node below and one deeper than the node above; an
indented line shows where it lands, so a node dragged sideways in place is indented or outdented.
Dragging a checked pool item takes all checked items along; dragging a source's heading drops a submenu
named after the source with its shortcuts (at the deepest level only the shortcuts). The pool shows each
shortcut's URL and the search covers it. "Preview" shows the menu as the toolbar would, for admin pages or
the site, with all plugins or only those active here; submenus start closed and open with a click.
(Hans had a toolbar-like flyout on hover in mind; the list is fine for now.)

The menu can travel with the plugin: "Save as default" (on a site where the settings folder is writable,
in practice development; deliberately a separate button, in case a production site can write there too)
writes it to `settings/modules/admin-toolbar/menu-builder.php` and lets the site follow it; committed, it
goes along with the release. A site without a menu of its own uses that default (`Settings::get_whole()`);
saving a menu equal to the default drops the site's own copy, so it follows the default again. A status
line above the tree says which applies. "Import / export" opens a dialog: export downloads the menu in the
editor as JSON; import takes the default or a JSON file into the editor (undoable, saved with "Save
menu"), replacing the menu or merging. Merging adds the items the menu lacks, recognised by instance id so
nothing is added twice: an item goes after its predecessor from the source if the menu has it, else first
under its parent, else at the end of the top level. The menu's own items, titles, visibility and
arrangement stay; removed items come back, which is what merging means (quicker to remove again than to
rebuild a structure). This is a deliberate action, unlike `Settings::get()`'s merge on every read, which
would make removals impossible. Instance ids differ per site, so merging a file from another site adds
all of its items.

The settings page frame is fixed beside the admin menu (from 783px wide), so a long admin menu scrolls on
its own and the buttons stay in view. The WordPress footer is hidden on this page: with the frame fixed,
the window itself still scrolls, and the footer would slide over the buttons.

Titles: a menu item has a full title (clear on its own: "Venue Categories", "Add Plugin") and may have a
short one ("Categories", "New") with a group (what it is about: plugins, posts, venues; default its source).
Without a title of its own, a menu item under a parent of the same group shows the short title, elsewhere
the full one; a custom submenu counts as the group all its menu items share. The pool shows full titles.
The toolbar (MenuBuilder.php) and the editor (serialize()) apply the same rule, so nothing is stored.

Menu item ids read `cf-<source>-<page>`: `cf-woocommerce-orders`, `cf-events-calendar-settings-seo`; the
source alone is its menu head (`cf-automatewoo`), and `view-` marks a page on the site
(`cf-woocommerce-view-my-account`). Saved menus store these ids, so renaming one means converting the
saved menus too (done once, in October 2026, while the plugin ran on the development site only). Each
guard names its source (`get_name()`), shown in the pool.

Cleanup point (minor): the catalogue holds some duplicate links (the same URL from two sources, such as
Sensei Pro repeating Sensei LMS, a menu head equal to its first item, or two WooCommerce links with `#`).
Harmless, at most a little inefficient.

## Settings and options

- **Settings files** (`settings/`) hold defaults and site data as PHP files returning arrays. General files
  sit in `settings/` (`environment-index.php`, `push-notice-drivers.php`); each module has its own folder
  under `settings/modules/<module-folder>/`. `settings/setup/modules-settings-path.php` maps a module id to
  its folder, and `settings/setup/key-slugs-translation.php` maps a settings key to a file in that folder.
  That is why `Settings::get( $key, $module_id )` takes the module id as second argument. A settings file
  is read as part of `Settings`, so it can compose its value with `$this->get()`.
- **Database overrides**: `Settings::get()` merges the file with the stored option
  (`array_replace_recursive`); `$reset_to_defaults` returns the file only. For lists and trees, where a
  removed item must stay removed, `Settings::get_whole()` takes the stored value as a whole, or else the
  file.
- **Writable defaults**: `Settings::save_default()` writes a value into its settings file, where the folder
  is writable (development); the file then goes along with git and the release. `can_save_default()`,
  `get_default()` and `has_saved()` support a "save as default / follow the default" flow (used by the menu
  builder); following the default again is `Settings::reset()`.
- `Settings` is the holder of all settings, with two helpers: **`SettingsFiles`** for the files (mapping,
  reading, writing) and the **Options vault** (`Options`) for the database: it maps setting tokens to real
  option names, either standalone or grouped in one option, and keeps unknown tokens in a quarantine pool
  until they are assigned. All option access belongs behind `Settings`.
- **Files**: `FileService` is the general reader and writer of data files (PHP files returning an array,
  or JSON, by extension; it creates missing folders). The layers on top say what the files are:
  `SettingsFiles` for the settings defaults, `ComponentsRegisterFiles` for the registers in `kernel/`.

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
| Environment ids | kebab-case of the guard's class name; the `-theme` suffix picks the guard folder |

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
| Toolbar catalogue of all known plugins | The register holds the nodes of every guard with a provider status; the toolbar shows only active ones | A menu is set up once; installing or activating a plugin switches its links on and off |
| Catalogue in memory only | Built per request on first use, not stored in the database | Hrefs can be closures and titles are translated; the cost is small |
| Visibility per menu node | Admin, front end or both, set in the menu builder; the guard's orientation only says what kind of link it is | Where a link is useful depends on the menu, not on the plugin |

## Open points

Known issues:

- The public API is not worked out yet (parked): `CF::initialize()` is never called, so the `Api` facade
  and functions answer "not ready", and `src/Api/helpers.php` is a placeholder (not autoloaded; it calls a
  `Core` class that no longer exists).
- phpcs works again since October 2026 (it silently exited before, see `src/Api/functions.php`) and the
  plugin is clean. Fixing the findings also fixed three bugs: the
  Transient Optimizer compared UTC expiry times with local time (transients removed up to two hours
  early), the WooCommerce category link always used an example slug, and two buttons were never
  translated.
- Tested in a logged-in admin (October 2026): the "new components" notice after a simulated update, the
  integrity check and all settings tabs. The two issues found there (the toolbar Cleaner reading
  `$node->id` from an array, and feature names ending in a period) are fixed. The plugin is active again
  on the local site.
- All option access goes through `Settings` (October 2026); options of WordPress or another plugin use
  `get_unmanaged()` / `save_unmanaged()`. Transients (as in `KeepLicenseAlive`) still use the WordPress
  functions directly.
- Menu builder (October 2026): titles saved before this version are stored as overrides, also where
  they equal the English title; "Reset titles" on a selection makes nodes follow the translation again.
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
  (legacy) and WP phpMyAdmin (not needed) were removed. The legacy AdminToolbar settings files from the
  old plugin went with the LegacyMenuBuilder feature (October 2026); `settings/modules/admin-toolbar/`
  now only holds the menu builder's default (`menu-builder.php`) once one is saved.
- Site-specific URLs in guard code (`/evenementen/`, `/agenda/categorie/familieopstellingen/`,
  `/artikelen/`, the `course-payment-plans` product category) stay for now; they could also become custom
  links on the settings page.

Considered and set aside: keeping a feature's administration (its meta) on a settings screen; options do
not travel with an update to another site.

Later:

- Rename of the folder and repository to `core-functionality` once the legacy plugin is retired (also
  `PLUGIN_SLUG` in the release workflow).

## Release and translations

`README.md` describes both. A pushed `vX.Y.Z` tag runs `.github/workflows/release.yml`: Composer installs
the autoloader without development packages, the PHP syntax and the header version are checked, and a
zip of the `core-functionality-oop` folder goes to the GitHub release, where Git Updater picks it up.
`languages/` holds the `.pot` and the nl_NL `.po`/`.mo`; the text domain is loaded on `init`.

## Testing

No automated tests. Checks: `composer lint`, `composer phpcs` (WordPress-Core and WordPress-Docs, PHP files
only, PSR-4 file names allowed, `-` and `/` allowed in hook names), and manual checks on the DDEV site
`https://speeltuin.ddev.site`, where the plugin is active. The site's debug log only records fatal errors,
so tests use a temporary mu-plugin (removed afterwards) that logs warnings, notices, `_doing_it_wrong()`
and fatal errors from this plugin. For a plugin that is not active, such a mu-plugin can load it for a
single request.
