<?php
/**
 * Tabbed Core Administration Dashboard and Option Page Registry.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   4.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class SettingsRegistry
 *
 * Provides a decentralized option configuration interface layout inspired by WooCommerce tabs.
 * Allows modules to dynamically attach field parameters without injecting raw HTML into core trees.
 *
 * @since 4.0.0
 */
class SettingsRegistry {

	/**
	 * Storage dictionary array capturing active setting tab profiles and layout callbacks.
	 *
	 * @since 4.0.0
	 * @var array
	 */
	private array $tabs = array();

	/**
	 * SettingsRegistry constructor.
	 *
	 * Declares baseline system layout administration panels automatically.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->register_tab( 'general', __( 'General', 'core-functionality-dwp' ), array( $this, 'render_general_tab' ) );
		$this->register_tab( 'debug', __( 'Debug Cockpit', 'core-functionality-dwp' ), array( $this, 'render_debug_tab' ) );
	}

	/**
	 * Allocates a newly discovered option layout navigation pane into the collection.
	 *
	 * @since 4.0.0
	 * @param string   $id       Unique key identity code identifier string for the tab interface.
	 * @param string   $title    Human-readable localization string used on menu tabs.
	 * @param callable $callback Structural rendering callback parameter handling field generation.
	 * @return void
	 */
	public function register_tab( string $id, string $title, callable $callback ): void {
		$this->tabs[ $id ] = array(
			'title'    => $title,
			'callback' => $callback,
		);
	}

	/**
	 * Compiles and echoes the final integrated administration configuration form.
	 *
	 * @since 4.0.0
	 * @return void
	 * @see esc_html(), call_user_func()
	 */
	public function render_admin_page(): void {
		$active_tab = $_GET['tab'] ?? 'general';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( __( 'De Witte Prins Core Configuration', 'core-functionality-dwp' ) ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<?php foreach ( $this->tabs as $id => $tab ) : ?>
					<a href="?page=dwp-core-settings&tab=<?php echo $id; ?>" class="nav-tab <?php echo $active_tab === $id ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab['title'] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<div class="tab-content" style="padding: 20px 0;">
				<?php
				if ( isset( $this->tabs[ $active_tab ] ) ) {
					call_user_func( $this->tabs[ $active_tab ]['callback'] );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Default execution fallback pane placeholder rendering standard metrics.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function render_general_tab(): void {
		echo '<p>' . esc_html( __( 'Manage standalone engine properties and toggles.', 'core-functionality-dwp' ) ) . '</p>';
	}

	/**
	 * Default execution fallback pane placeholder rendering debug settings.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function render_debug_tab(): void {
		echo '<p>' . esc_html( __( 'Configure active logging routing targets and diagnostic tools.', 'core-functionality-dwp' ) ) . '</p>';
	}
}
