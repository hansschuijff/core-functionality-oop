<?php
/**
 * Administrative Dashboard Notices Service.
 *
 * @package DeWittePrins\CoreFunctionality\Services
 * @since   1.0.0
 */

namespace DeWittePrins\CoreFunctionality\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Notice
 *
 * Handles the registration, queuing, and HTML rendering of administrative dashboard notices.
 *
 * @since 1.0.0
 */
class Notice {

	/**
	 * Internal registry tracking queued notice messages.
	 *
	 * @var array<array{type: string, message: string, dismissible: bool}>
	 */
	private array $queued_notices = array();

	/**
	 * Notice Constructor.
	 *
	 * Hooks itself automatically into the native WordPress administrative notice lifecycle.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render_combined_queued_notices' ) );
	}

	/**
	 * Adds an administrative notice to the queue.
	 *
	 * @since  1.0.0
	 * @param  string $type        The WordPress notice class type (error, warning, success, info).
	 * @param  string $message     The unsanitized HTML/text message string to show.
	 * @param  bool   $dismissible Optional. True if the notice should be closeable. Default true.
	 * @return void
	 */
	public function add( string $type, string $message, bool $dismissible = true ): void {
		$this->queued_notices[] = array(
			'type'        => sanitize_key( $type ),
			'message'     => $message, // Wordt tijdens rendering gesaneerd via wp_kses.
			'dismissible' => $dismissible,
		);
	}

	/**
	 * Iterates over the registry to loop and output all queued notice items.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_queued_notices(): void {
		if ( empty( $this->queued_notices ) ) {
			return;
		}

		// Toegestane HTML tags voor de kses sanering (veilig voor links en dikgedrukte teksten).
		$allowed_tags = array(
			'strong' => array(),
			'br'     => array(),
			'code'   => array(),
			'a'      => array(
				'href'   => true,
				'target' => true,
				'style'  => true,
			),
		);

		foreach ( $this->queued_notices as $notice ) {
			$dismiss_class = $notice['dismissible'] ? 'is-dismissible' : '';
			$class_string  = sprintf( 'notice notice-%s %s', $notice['type'], $dismiss_class );
			?>
			<div class="<?php echo esc_attr( $class_string ); ?>">
				<p><?php echo wp_kses( $notice['message'], $allowed_tags ); ?></p>
			</div>
			<?php
		}

		// Maak de wachtrij leeg na rendering om duplicatie te voorkomen.
		$this->queued_notices = array();
	}

	/**
	 * Iterates over the registry to group and cluster notifications by type before rendering.
	 *
	 * Prevents dashboard pollution by merging multiple alerts into single, cohesive banners.
	 *
	 * Dit is een aangepaste versie  van render_queued_notices() die de boodschappen zamen in een notice toont, in plaats van een rij losse notices.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render_combined_queued_notices(): void {
		if ( empty( $this->queued_notices ) ) {
			return;
		}

		// 1. Sorteer en bundel de meldingen vloeibaar per type in het geheugen [INDEX].
		$grouped_notices = array();
		foreach ( $this->queued_notices as $notice ) {
			$grouped_notices[ $notice['type'] ][] = $notice;
		}

		// Toegestane HTML-tags voor de veilige kses-scrubbing.
		$allowed_tags = array(
			'strong' => array(),
			'br'     => array(),
			'code'   => array(),
			'li'     => array(),
			'ul'     => array(),
			'a'      => array(
				'href'   => true,
				'target' => true,
				'style'  => true,
			),
		);

		// 2. Render nu per type één enkele geclusterde banner [INDEX]!
		foreach ( $grouped_notices as $type => $notices ) {
			// Bepaal of de complete banner wegdrukbaar mag zijn (alleen als ALLE meldingen daarin dismissible zijn).
			$is_dismissible = true;
			foreach ( $notices as $n ) {
				if ( ! $n['dismissible'] ) {
					$is_dismissible = false;
					break;
				}
			}

			$dismiss_class = $is_dismissible ? 'is-dismissible' : '';
			$class_string  = sprintf( 'notice notice-%s %s', $type, $dismiss_class );
			?>
			<div class="<?php echo esc_attr( $class_string ); ?>" style="padding-top: 8px; padding-bottom: 8px;">
				<?php if ( count( $notices ) === 1 ) : ?>
					<!-- Enkelvoudige melding: Gewoon direct als paragraaf -->
					<p><?php echo wp_kses( $notices[0]['message'], $allowed_tags ); ?></p>
				<?php else : ?>
					<!-- Meervoudige melding (Sensei-stijl Clustering!): Gezellig in een nette lijst 🎯 -->
					<ul style="margin: 0 0 0 20px; list-style-type: disc; padding: 5px 0;">
						<?php foreach ( $notices as $notice ) : ?>
							<li style="margin-bottom: 4px;"><?php echo wp_kses( $notice['message'], $allowed_tags ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php
		}

		// Maak de wachtrij onherroepelijk leeg voor de volgende lifecycle.
		$this->queued_notices = array();
	}
}
