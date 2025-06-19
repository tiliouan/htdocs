<?php
/**
 * REST API payment methods report query
 * Class for parameter-based Payment Methods Report querying
 * Example usage:
 * ```$args = array(
 *          'before'       => '2018-07-19 00:00:00',
 *          'after'        => '2018-07-05 00:00:00',
 *          'page'         => 2,
 *          'order'        => 'desc',
 *          'orderby'      => 'items_sold',
 *         );
 * $report = new \YITH\POS\RestApi\Reports\PaymentMethods\Query( $args );
 * $mydata = $report->get_data();```
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\POS\RestApi\Reports\PaymentMethods
 */

namespace YITH\POS\RestApi\Reports\PaymentMethods;

defined( 'ABSPATH' ) || exit;

use \Automattic\WooCommerce\Admin\API\Reports\Query as ReportsQuery;

/**
 * Class Query
 *
 * @package YITH\POS\RestApi\Reports\PaymentMethods
 */
class Query extends ReportsQuery {

	const REPORT_NAME = 'yith-pos-report-payment-methods';

	/**
	 * Valid fields for PaymentMethods report.
	 *
	 * @return array
	 */
	protected function get_default_query_vars() {
		return array();
	}

	/**
	 * Get categories data based on the current query vars.
	 *
	 * @return array
	 * @throws \Exception The exception.
	 */
	public function get_data() {
		$args = apply_filters( 'yith_pos_reports_payment_methods_query_args', $this->get_query_vars() );
		/**
		 * The data store.
		 *
		 * @var DataStore $data_store
		 */
		$data_store = \WC_Data_Store::load( self::REPORT_NAME );
		$results    = $data_store->get_data( $args );

		return apply_filters( 'yith_pos_reports_payment_methods_select_query', $results, $args );
	}
}
