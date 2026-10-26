<?php
/**
 * Defaults of the Reader Experience settings tab (module ReaderExperience, feature Configuration).
 *
 * The tab saves its choices as a whole; until then, these apply. Empty labels and an empty
 * container list mean: the Reader Experience plugin's own defaults.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'anchors_enabled'      => true,
	'anchors_post_types'   => array( 'post', 'page', 'tribe_events' ),
	'anchors_position'     => 'before',
	'anchors_containers'   => array(),
	'anchors_label_copy'   => '',
	'anchors_label_copied' => '',
	'anchors_load_css'     => true,
	'anchors_load_js'      => true,
	'anchors_reserved_ids' => array(),
	'reading_wpm'          => 225,
	'feedback_notify'      => true,
	'feedback_spam_check'  => true,
);
