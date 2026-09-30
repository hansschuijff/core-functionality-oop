<?php
/**
 * Administrative Dashboard Notices Collection Service.
 *
 * @package    DeWittePrins\CoreFunctionality
 * @subpackage Notifiers
 * @author     Hans Schuijff <@hansschuijff>
 * @license    GPL-2.0
 * @since      1.0.0
 */

declare( strict_types=1 );

namespace DeWittePrins\CoreFunctionality\Notifiers;

use DeWittePrins\CoreFunctionality\Notifiers\DTO\Notice;

use function add_action;
use function delete_transient;
use function esc_attr;
use function get_current_user_id;
use function get_transient;
use function is_array;
use function sanitize_key;
use function set_transient;
use function sprintf;
use function wp_kses;
use function count;
use function in_array;
use function trim;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Notices
 *
 * Handles the registration, transient persistence, and unified HTML rendering of administrative dashboard notices.
 *
 * @since 1.0.0
 */
class Notices {

	/**
	 * The database transient key used to persist the notice queue across requests.
	 *
	 * @var string
	 */
	private string $transient_key = '';

	/**
	 * A boolean indicating if Notices should be rendered separate from each other
	 * or rendered in grouped Notices per type (takes less space).
	 *
	 * @var bool
	 */
	private bool $should_group = true;

	/**
	 * Notices Constructor.
	 *
	 * Hooks directly into the native WordPress lifestyle to output notices at the correct moment.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->transient_key = 'dwp_notice_queue_' . get_current_user_id();

		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Adds an administrative notice directly into the persistent transient queue using the Notice DTO.
	 *
	 * @since  1.0.0
	 * @param  string $type        The notice context type (e.g., 'success', 'error', 'warning', 'info').
	 * @param  string $message     The translatable text string or HTML snippet payload.
	 * @param  bool   $dismissible Optional. Whether the interface renders a manual closure cross button. Default true.
	 * @return void
	 */
	public function add( string $type, string $message, bool $dismissible = true ): void {
		$notice = new Notice(
			sanitize_key( $type ),
			$message,
			$dismissible
		);

		$this->add_notice( $notice );
	}

	/**
	 * Rendert alle verzamelde notices uit de transient pool en wist deze daarna direct.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function render(): void {
		$notices = $this->get_notices();
		if ( empty( $notices ) ) {
			return;
		}
		$this->delete_notices();

		$notices = $this->validate_notices( $notices );

		// Render notices grouped by type in a collection notice, or render each notice separately.
		if ( $this->should_group ) {
			$this->render_grouped( $notices );
		} else {
			$this->render_separate( $notices );
		}
	}

	/**
	 * Validates and filters the raw transient input into a sanitized Notice DTO array.
	 *
	 * @since  1.0.0
	 * @param  array $notices Raw array from transient storage.
	 * @return Notice[] Cleaned Notice DTO collection.
	 */
	private function validate_notices( array $notices ): array {
		$dto_notices = array();
		foreach ( $notices as $notice ) {
			if ( $notice instanceof Notice ) {
				$dto_notices[] = $notice;
			}
		}
		return $dto_notices;
	}

	/**
	 * Renders notices grouped by type and dismissibility.
	 *
	 * @since  1.0.0
	 * @param  Notice[] $notices Notice DTO objects to render.
	 * @return void
	 */
	private function render_grouped( array $notices ): void {
		$grouped_notices = array();
		foreach ( $notices as $notice ) {
			$dismissable = $notice->dismissible ? '1' : '0';
			$type        = trim( $notice->type );
			if ( ! in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ) {
				$type = 'info';
			}
			$grouped_notices[ $type ][ $dismissable ][] = $notice->message;
		}

		$allowed_tags = $this->get_allowed_tags();

		foreach ( $grouped_notices as $type => $dismiss_groups ) {
			foreach ( $dismiss_groups as $is_dismissible => $messages ) {
				$dismiss_class = $is_dismissible ? 'is-dismissible' : '';
				$class_string  = sprintf( 'notice notice-%s %s', $type, $dismiss_class );
				?>
				<div class="<?php echo esc_attr( $class_string ); ?>" style="padding-top: 8px; padding-bottom: 8px;">
					<?php if ( count( $messages ) === 1 ) : ?>
						<p><?php echo wp_kses( $messages[0], $allowed_tags ); ?></p>
					<?php else : ?>
						<ul style="margin: 0 0 0 20px; list-style-type: disc; padding: 5px 0;">
							<?php foreach ( $messages as $message ) : ?>
								<li style="margin-bottom: 4px;"><?php echo wp_kses( $message, $allowed_tags ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Renders notices one by one (not grouped).
	 *
	 * @since  1.0.0
	 * @param  Notice[] $notices Notice DTO objects to render.
	 * @return void
	 */
	private function render_separate( array $notices ): void {
		$allowed_tags = $this->get_allowed_tags();

		foreach ( $notices as $notice ) {
			$dismiss_class = $notice->dismissible ? 'is-dismissible' : '';
			$class_string  = sprintf( 'notice notice-%s %s', $notice->type, $dismiss_class );
			?>
			<div class="<?php echo esc_attr( $class_string ); ?>" style="padding-top: 8px; padding-bottom: 8px;">
				<p><?php echo wp_kses( $notice->message, $allowed_tags ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Sets a runtime setting for grouped notice rendering to false,
	 * so notices will be rendered individually.
	 *
	 * @return void
	 */
	public function set_render_separate(): void {
		$this->should_group = false;
	}

	/**
	 * Sets a runtime setting for grouped notice rendering to true,
	 * so notices will be rendered in groups per type.
	 *
	 * @return void
	 */
	public function set_render_grouped(): void {
		$this->should_group = true;
	}

	/**
	 * Returns an array of allowed tags for wp_kses().
	 *
	 * @since  1.0.0
	 * @return array An array with allowed tags to be used for wp_kses().
	 */
	private function get_allowed_tags(): array {
		return array(
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
	}

	/**
	 * Adds a new notice to the existing notices pool in transient storage.
	 *
	 * @since  1.0.0
	 * @param  Notice $notice Notice DTO object for a single notice.
	 * @return bool           True on successful commitment, false otherwise.
	 */
	private function add_notice( Notice $notice ): bool {
		$notices   = $this->get_notices();
		$notices[] = $notice;

		return $this->save_notices( $notices );
	}

	/**
	 * Get the existing Notice DTO collection from transient storage.
	 *
	 * @since  1.0.0
	 * @return Notice[] Array containing stored Notice DTO instances.
	 */
	private function get_notices(): array {
		$notices = get_transient( $this->transient_key );
		return is_array( $notices ) ? $notices : array();
	}

	/**
	 * Save Notice DTO collection to transient storage.
	 *
	 * @since  1.0.0
	 * @param  Notice[] $notices Complete collection of active framework Notice DTO objects.
	 * @return bool              True on successful commitment, false otherwise.
	 * @return bool              True on successful commitment, false otherwise.
	 */
	private function save_notices( array $notices ): bool {
		return set_transient( $this->transient_key, $notices, 45 );
	}

	/**
	 * Clears the notices pool in transient storage.
	 *
	 * @since  1.0.0
	 * @return bool True on successful commitment, false otherwise.
	 */
	private function delete_notices(): bool {
		return delete_transient( $this->transient_key );
	}
}
