<?php
/**
 * Software Baseline Configuration for Environment Abstraction Mappings.
 *
 * Returns a static dictionary mapping abstract dependency identification keys
 * to their physical active WordPress plugin directory and file basenames.
 *
 * @package DeWittePrins\CoreFunctionality
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	// Core & Common Utilities.
	'yoast_seo'                       => 'wordpress-seo/wp-seo.php',
	'jetpack'                         => 'jetpack/jetpack.php',
	'gravity_forms'                   => 'gravityforms/gravityforms.php',
	'carbon_fields'                   => 'carbon-fields/carbon-fields.php',
	'carbon_fields_helper'            => 'carbon-fields-helper/carbon-fields-helper.php',
	'searchwp'                        => 'searchwp/searchwp.php',
	'debug_toolkit'                   => '_debug-toolkit/debug-toolkit.php',

	// WooCommerce Core & Extensions.
	'woocommerce'                     => 'woocommerce/woocommerce.php',
	'activewoo_api_helper'            => 'activewoo-api-helper/activewoo-api-helper.php',
	'woocommerce_activecampaign'      => 'woocommerce-activecampaign/woocommerce-activecampaign.php',
	'mollie_payments_for_woocommerce' => 'mollie-payments-for-woocommerce/mollie-payments-for-woocommerce.php',
	'woocommerce_pdf_invoices'        => 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips.php',
	'woocommerce_subscriptions'       => 'woocommerce-subscriptions/woocommerce-subscriptions.php',

	// The Events Calendar Stack.
	'the_events_calendar'             => 'the-events-calendar/the-events-calendar.php',
	'event_tickets_plus'              => 'event-tickets-plus/event-tickets-plus.php',
	'schema_glue_yoast_tec'           => 'schema-glue-for-yoast-the-events-calendar/schema-glue.php',

	// Sensei LMS Stack.
	'sensei_lms'                      => 'sensei-lms/sensei-lms.php',
	'sensei_content_drip'             => 'sensei-content-drip/sensei-content-drip.php',
	'sensei_paid_courses'             => 'sensei-paid-courses/sensei-paid-courses.php',

	// Social & Feeds.
	'scriptless_social_sharing'       => 'scriptless-social-sharing/index.php',
	'shared_counts'                   => 'shared-counts/shared-counts.php',
	'send_images_to_rss'              => 'send-images-to-rss/send-images-to-rss.php',

	// Genesis Utility Plugins.
	'display_featured_image_genesis'  => 'display-featured-image-genesis/display-featured-image-genesis.php',
	'genesis_simple_faq'              => 'genesis-simple-faq/genesis-simple-faq.php',
	'be_like_content'                 => 'be-like-content/be-like-content.php',
);
