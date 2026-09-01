=== Remote Tickets Blocks ===
Contributors: Hans Schuijff @hansschuijff
Tags: the events calendar, event tickets, woocommerce, gutenberg, block, synchronization
Requires at least: 7.0
Tested up to: 7.0.4
Requires Plugins: remote-tickets-server-addons
Stable tag: 3.2.3
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
The Remote Tickets block is a high-performance Gutenberg blocks plugin that can be used to list events and sell tickets that are hosted on other websites running the events calendar, and the event tickets plus plugin and a WooCommerce store. It allows content creators to add tickets of external events, to a local copy of that exact event (matched by title and events date-time) and to add a list of events on local event pages and landingspages.

**CRITICAL DEPENDENCY WARNING:**
Version 2.0.0+ of this block architecture switches entirely to an optimized, consolidated atomic REST API protocol. This version is NOT backward compatible with vanilla implementations of The Events Calendar or legacy 1.x installations. It strictly requires the accompanying **Remote Tickets Server Add-ons (Version 1.2.0+)** plugin to be active on the target repository server.

== Features ==
* Atomic Consolidated Queries: Replaces legacy multi-stage nested loops with a single, high-performance request targeting a dedicated server-side database view.
* Source-Filtered Datastreams: Enforces strict server-side pre-filtering (`hide_unbookable=1`), guaranteeing that only events and tickets that are actually for sale cross the network layer to the client site.
* Smart Timing Check: If an event has already started or passed, the block instantly shows a helpful warning notice to the visitor while quietly double-checking in the background if there are still last-minute tickets available to buy.
* Lightweight Checkout Math: Currency formatting and price calculations are handled entirely by the remote server, allowing the visitor's browser to instantly update order totals when using the plus and minus buttons.
* Localized Shop Statuses: Automatically intercepts English shop statuses (like 'sold-out') and turns them into clear, reader-friendly Dutch words like 'Uitverkocht' (depending on language settings), complete with high-contrast colors.
* Centralized Configuration: Features a single, central settings backbone in the code. All remote website URLs, checkout redirect templates, and notification messages are managed from one central place instead of being scattered across different files.
* Fully Translatable: Built from the ground up to support internationalization. You can easily add your own language files using tools like Loco Translate (a full language template and a Dutch translation file are included).

== Installation ==
1. Upload the `remote-tickets-blocks` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the block inserter in the Gutenberg editor to add 'Remote Tickets' or 'Remote Events' to your pages.
4. Select your preferred source repository website and match criteria in the inspector controls panel on the right sidebar.

== Frequently Asked Questions ==

= Does this block require a custom server setup on the local website? =
No. The website running this Gutenberg block can be a completely vanilla WordPress installation. However, the *remote* repository website (the ticket provider) must have the Remote Tickets Server Add-ons plugin (v1.2.0+) active. Without it, the combined endpoint architecture will return a connection failure.

= Why does the editor only show active tickets now? =
To ensure perfect sandboxing and optimization across separate business boundaries. The client site strictly queries and receives active commercial records, meaning any expired or non-bookable tickets are discarded at the source database layer before crossing the network.

= How does the block prevent making unneeded network requests? =
Before initiating any external fetch, the block executes local guard clauses checking if the title or timestamps are empty. It also evaluates if the local end-datetime has already passed. If any condition triggers, the block stops immediately and displays an informative message. Also it makes use of a dedicated Rest Api dewitteprins/remote-tickets endpoint that returns all the needed informatie in a single request from the remote server.

= What happens if someone changes an event timestamp and breaks the match? =
The block handles mismatches gracefully. It will securely wipe any cached `eventId` attributes, clear old ticket states, and raise an informative charcoal-colored notice inside the table reading exactly what failed without triggering layout crashes.

= Why does the editor table show expired or sold-out tickets? =
To give the content manager absolute visual confirmation that the connection with the repository is healthy. Seeing a ticket marked as "Uitverkocht" or "Verlopen" proves the sync is functioning, whereas dropping them completely might falsely imply a network error.

== Changelog ==

= 3.2.3 =
* fix: make sure git updater only updates from release zip files.

= 3.2.2 =
* fix: some small typos.

= 3.2.1 =
* Updates the release workflow, to build remote-tickets-blocks ending with an s.

= 3.2.0 =
* Release Date: August 27, 2026
* Feature: Introduced the brand-new `remote-events` Overview List block mapping directly to custom responsive Flexbox layouts.
* Feature: Developed a dynamic REST-API search filtering subsystem allowing real-time workspace queries by title keyword, display limit, and date boundaries straight from the Gutenberg sidebar controls.
* Refactor: Relocated and centralized sandbox cache-bypassing and URL normalization rules into unified utility methods inside the global `PluginConfig` registry.
* Optimization: Implemented high-precision MD5 hash filter modifiers for transient cache keys to strictly isolate individual filter combination response packages inside the database object pool.
* Fix: Implemented native `wp_timezone()` context integration inside the date rendering engines to eliminate seasonal daylight savings time-shifts (+2 hours) on remote event schedules.
* Fix: Developed keyboard-rest state buffering and onBlur commit hooks inside Gutenberg text controls to dramatically optimize server traffic and restrict redundant REST API requests.
* Documentation: Fully finalized strict WordPress Javadoc-style docblock specifications across all newly implemented core, cache, and rendering components.

= 3.1.1 =
* Bugfix in RenderController.

= 3.1.0 =
* Release Date: August 26, 2026
* Upgrade: Architectural migration to a high-cohesion, decoupled PSR-4 layered structure.
* Refactor: Replaced the legacy manual require cascades with standard Composer autoloading mechanisms.
* Enhancement: Eliminated the dynamic function redeclaration overhead inside block templates by moving runtime parsing logic directly into dedicated standalone Controllers and Handlers.
* Feature: Introduced a robust `ApiResponse` Data Transfer Object (DTO) layout to allow granular tracking and semantic localization of remote HTTP REST status code exceptions (e.g., 404, 409, 500).
* Localization: Harmonized the chronological execution matrix inside the `init` action hook to guarantee all translation domains are mounted safely before configurations are included.
* Optimization: Relocated general numerical price and currency formatting tools into a globally accessible utility namespace (`Inc\Utils\Formatter`) to prevent code replication.

= 3.0.2 =
* Refactored the plugin architecture to completely clean up the main plugin file; it now exclusively boots the core engine via Plugin::launch().
* Introduced a centralized Plugin class to decouple lifecycle management from procedural loops.
* Migrated the block execution layer to native core attributes mapping utilizing src/render.php.
* Moved all frontend data fetching and rendering operations to src/render.php to improve performance.
* Upgraded the site architecture to a protocol-agnostic "Domain-as-Key" model, preventing block breakage during remote HTTPS transitions.
* Optimized network footprints by sourcing cart-slugs directly from local manifests instead of heavy remote REST calls.
* Updated documentation.

= 3.0.1 =
* Build forgiveness in get_remote_event, by falling back to old exact_date_match endpoint when no remote_event_id is passed in the attriibutes.

= 3.0.0 =
* Redesign and refactor the internal working of the plugin to prepare for future enhancements.
* connect to new and changes rest api endpoint of the remote tickets server add-ons plugin.
* updated language files
* base remote server id on domain rather than url to prevent problems when protocol changes (http/https).
* build enhanced and OO Config functionality with settings in config-manifest.
* remove rendering remote tickets block from frontend.js.
* bail out early in frontend.js when there are no tickets.
* render frontend tickets block in dedicated php class.
* remove duplicate remote fetch via de rest api.

= 2.0.1 =
* Renames the language files to the changed textdomain: remote-tickets-block-dwp.

= 2.0.0 =
* Major Architecture Upgrade: Completely removed legacy nested fetch-chains and replaced them with a single, consolidated atomic endpoint query (`/v1/event`).
* Feature: Enforced strict server-side filtering (`hide_unbookable=1`) across all lifecycle requests to keep the client interface clean of dead inventory ballast.
* Refactor: Renamed main orchestration architecture, main entrypoint file to `remote-tickets-block.php`, and adjusted project slug namespaces for pristine isolation.
* Refactor: Standardized all back-end frameworks with full English inline documentation and strict functional typehints.
* Optimization: Restored high-precision e-commerce totalizers and native HTML5 step controls into the front-end layout stream.
* Deployment: Overhauled the automated build pipelines to output properly nested, production-ready WordPress plugin archives.

= 1.0.0 =
* Production Release: Officially closed the pre-release phase and established the first stable production build (the finalized dual-fetch architecture).
* Feature: Implemented local client-side timeline guards to abort external requests instantly for concluded or active events.
* Optimization: Hardened API requests with `per_page=2` parameter structures to intercept and flag overlapping server duplicates.
* Refactor: Enhanced WCAG color contrasts (`#374151`) and removed alarming, non-actionable error terminology from placeholder rows.

= 0.1.4 =
* Feature: Completed the block functionality in the editor, making use of extra event and ticket data offered by Remote Events Server Add-on.
* Feature: Added support for new REST API parameters `hide_unbookable` and `exact_date_match` to force a single bookable event response.
* Restriction: Allowed only one remote tickets block per event page.
* Refactor: Complete refactoring of the PHP code infrastructure.
* Optimization: Added efficiency in the editor by making more use of locally available event data.
* Localization: Updated translations, corrected docblocks, and streamlined i18n workflows.

= 0.1.3 =
* Feature: Started using event status (postmeta `_tribe_event_status`) to recognize and handle canceled and postponed remote events.
* Refactor: Made English the default language of the plugin.
* Localization: Added limited translatability and Dutch translation source files.

= 0.1.1 =
* Fix: Changed the multi-product add-to-cart URL generated by the remote-tickets buy button to match the syntax offered by the Remote Tickets Server Add-on plugin.

= 0.1.0 =
* Initial Release: Established the initial block editor client release with basic chained fetch protocols.
