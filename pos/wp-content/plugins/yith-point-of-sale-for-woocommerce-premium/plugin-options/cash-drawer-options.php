<?php
/**
 * Cash Drawer options file
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\POS\Plugin Options
 */

defined( 'YITH_POS' ) || exit;

$cash_drawer = array(
	'cash-drawer' => array(
		'section_cash_drawer_title' => array(
			'name' => __( 'Cash Drawer Settings', 'yith-point-of-sale-for-woocommerce' ),
			'type' => 'title',
			'id'   => 'yith_pos_cash_drawer_settings_title',
		),
		
		'cash_drawer_enabled' => array(
			'name'    => __( 'Enable Cash Drawer', 'yith-point-of-sale-for-woocommerce' ),
			'desc'    => __( 'Enable automatic cash drawer opening when printing receipts', 'yith-point-of-sale-for-woocommerce' ),
			'id'      => 'yith_pos_cash_drawer_enabled',
			'type'    => 'yith-field',
			'yith-type' => 'checkbox',
			'default' => 'no',
		),
		
		'cash_drawer_command_type' => array(
			'name'    => __( 'Cash Drawer Command', 'yith-point-of-sale-for-woocommerce' ),
			'desc'    => __( 'Select the ESC/POS command type for your cash drawer', 'yith-point-of-sale-for-woocommerce' ),
			'id'      => 'yith_pos_cash_drawer_command_type',
			'type'    => 'yith-field',
			'yith-type' => 'select',
			'options' => array(
				'pin2' => __( 'Pin 2 (Standard)', 'yith-point-of-sale-for-woocommerce' ),
				'pin5' => __( 'Pin 5 (Alternative)', 'yith-point-of-sale-for-woocommerce' ),
			),
			'default' => 'pin2',
		),
		
		'cash_drawer_pulse_duration' => array(
			'name'    => __( 'Pulse Duration', 'yith-point-of-sale-for-woocommerce' ),
			'desc'    => __( 'Duration of the cash drawer opening pulse (1-255)', 'yith-point-of-sale-for-woocommerce' ),
			'id'      => 'yith_pos_cash_drawer_pulse_duration',
			'type'    => 'yith-field',
			'yith-type' => 'number',
			'default' => 120,
			'custom_attributes' => array(
				'min' => 1,
				'max' => 255,
			),
		),
		
		'cash_drawer_auto_open' => array(
			'name'    => __( 'Auto-open on Receipt Print', 'yith-point-of-sale-for-woocommerce' ),
			'desc'    => __( 'Automatically open cash drawer when a receipt is printed', 'yith-point-of-sale-for-woocommerce' ),
			'id'      => 'yith_pos_cash_drawer_auto_open',
			'type'    => 'yith-field',
			'yith-type' => 'checkbox',
			'default' => 'yes',
		),
		
		'cash_drawer_manual_button' => array(
			'name'    => __( 'Show Manual Open Button', 'yith-point-of-sale-for-woocommerce' ),
			'desc'    => __( 'Display a manual cash drawer open button in the POS interface', 'yith-point-of-sale-for-woocommerce' ),
			'id'      => 'yith_pos_cash_drawer_manual_button',
			'type'    => 'yith-field',
			'yith-type' => 'checkbox',
			'default' => 'yes',
		),
		
		'section_cash_drawer_test' => array(
			'name' => __( 'Cash Drawer Testing', 'yith-point-of-sale-for-woocommerce' ),
			'type' => 'title',
			'id'   => 'yith_pos_cash_drawer_test_title',
		),
		
		'cash_drawer_test_button' => array(
			'name' => __( 'Test Cash Drawer', 'yith-point-of-sale-for-woocommerce' ),
			'desc' => __( 'Click to test the cash drawer opening functionality', 'yith-point-of-sale-for-woocommerce' ),
			'id'   => 'yith_pos_cash_drawer_test',
			'type' => 'yith-field',
			'yith-type' => 'html',
			'html' => '<button type="button" id="yith-pos-cash-drawer-test" class="button button-secondary">' . __( 'Test Cash Drawer', 'yith-point-of-sale-for-woocommerce' ) . '</button>
			<div id="yith-pos-cash-drawer-test-result" style="margin-top: 10px;"></div>
			<script>
			jQuery(document).ready(function($) {
				$("#yith-pos-cash-drawer-test").on("click", function() {
					var button = $(this);
					var result = $("#yith-pos-cash-drawer-test-result");
					
					button.prop("disabled", true).text("' . __( 'Testing...', 'yith-point-of-sale-for-woocommerce' ) . '");
					result.html("");
					
					$.ajax({
						url: ajaxurl,
						type: "POST",
						data: {
							action: "yith_pos_test_cash_drawer",
							nonce: "' . wp_create_nonce( 'yith_pos_cash_drawer_test' ) . '"
						},
						success: function(response) {
							if (response.success) {
								result.html("<span style=\"color: green;\">" + response.data + "</span>");
							} else {
								result.html("<span style=\"color: red;\">" + response.data + "</span>");
							}
						},
						error: function() {
							result.html("<span style=\"color: red;\">' . __( 'Test failed - please check your settings and try again.', 'yith-point-of-sale-for-woocommerce' ) . '</span>");
						},
						complete: function() {
							button.prop("disabled", false).text("' . __( 'Test Cash Drawer', 'yith-point-of-sale-for-woocommerce' ) . '");
						}
					});
				});
			});
			</script>',
		),
		
		'section_cash_drawer_info' => array(
			'name' => __( 'Setup Information', 'yith-point-of-sale-for-woocommerce' ),
			'type' => 'title',
			'id'   => 'yith_pos_cash_drawer_info_title',
		),
		
		'cash_drawer_setup_info' => array(
			'name' => __( 'Setup Instructions', 'yith-point-of-sale-for-woocommerce' ),
			'type' => 'yith-field',
			'yith-type' => 'html',
			'html' => '
			<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #00a0d2; margin: 10px 0;">
				<h4>' . __( 'Cash Drawer Setup Guide', 'yith-point-of-sale-for-woocommerce' ) . '</h4>
				<ol>
					<li>' . __( 'Connect your cash drawer to your receipt printer using the RJ11/RJ12 cable', 'yith-point-of-sale-for-woocommerce' ) . '</li>
					<li>' . __( 'Ensure your browser supports the Web Serial API (Chrome 89+ recommended)', 'yith-point-of-sale-for-woocommerce' ) . '</li>
					<li>' . __( 'Configure your receipt printer settings in the Receipts tab', 'yith-point-of-sale-for-woocommerce' ) . '</li>
					<li>' . __( 'Test the cash drawer using the button above', 'yith-point-of-sale-for-woocommerce' ) . '</li>
				</ol>
				<p><strong>' . __( 'Troubleshooting:', 'yith-point-of-sale-for-woocommerce' ) . '</strong></p>
				<ul>
					<li>' . __( 'If the drawer doesn\'t open, try changing the command type to Pin 5', 'yith-point-of-sale-for-woocommerce' ) . '</li>
					<li>' . __( 'Check that the cash drawer cable is properly connected', 'yith-point-of-sale-for-woocommerce' ) . '</li>
					<li>' . __( 'Ensure your printer firmware supports cash drawer commands', 'yith-point-of-sale-for-woocommerce' ) . '</li>
				</ul>
			</div>',
		),
		
		'section_cash_drawer_end' => array(
			'type' => 'sectionend',
			'id'   => 'yith_pos_cash_drawer_settings_end',
		),
	),
);

return apply_filters( 'yith_pos_panel_cash_drawer_tab', $cash_drawer );
