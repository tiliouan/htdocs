<?php
/**
 * YITH POS Receipt Template Integration for Cash Drawer
 *
 * This file provides functions to integrate cash drawer ESC/POS commands
 * directly into receipt templates.
 *
 * @package YITH\POS\CashDrawer
 * @author GitHub Copilot Assistant
 * @version 1.0.0
 */

defined( 'YITH_POS' ) || exit();

/**
 * Add ESC/POS cash drawer command to receipt template output
 *
 * This function adds the ESC/POS command directly to the beginning of
 * receipt content so that when printed to an ESC/POS compatible printer,
 * the cash drawer will open automatically.
 *
 * @param string $receipt_content The receipt content
 * @param int $order_id The order ID
 * @return string Modified receipt content with cash drawer command
 */
function yith_pos_add_cash_drawer_to_receipt( $receipt_content, $order_id = 0 ) {
	// Check if cash drawer is enabled
	$cash_drawer = YITH_POS_Cash_Drawer::get_instance();
	if ( ! $cash_drawer->is_cash_drawer_enabled() ) {
		return $receipt_content;
	}

	// Get the cash drawer command
	$drawer_command = $cash_drawer->get_drawer_open_command();

	// Add command at the beginning of receipt
	$receipt_content = $drawer_command . $receipt_content;

	return $receipt_content;
}

/**
 * Generate ESC/POS receipt with cash drawer command
 *
 * This function creates a complete ESC/POS formatted receipt
 * including the cash drawer opening command.
 *
 * @param WC_Order $order The order object
 * @param array $receipt_config Receipt configuration
 * @return string ESC/POS formatted receipt
 */
function yith_pos_generate_escpos_receipt( $order, $receipt_config = array() ) {
	$cash_drawer = YITH_POS_Cash_Drawer::get_instance();
	
	// Start with cash drawer command if enabled
	$escpos_output = '';
	if ( $cash_drawer->is_cash_drawer_enabled() ) {
		$escpos_output .= $cash_drawer->get_drawer_open_command();
	}

	// ESC/POS initialization
	$escpos_output .= "\x1B\x40"; // Initialize printer

	// Receipt header
	$escpos_output .= "\x1B\x61\x01"; // Center align
	$store_name = get_bloginfo( 'name' );
	if ( $store_name ) {
		$escpos_output .= "\x1B\x21\x30" . $store_name . "\n"; // Double width/height
	}

	// Store address if available
	$store_address = get_option( 'woocommerce_store_address', '' );
	$store_city = get_option( 'woocommerce_store_city', '' );
	if ( $store_address || $store_city ) {
		$escpos_output .= "\x1B\x21\x00"; // Normal text
		if ( $store_address ) {
			$escpos_output .= $store_address . "\n";
		}
		if ( $store_city ) {
			$escpos_output .= $store_city . "\n";
		}
	}

	$escpos_output .= "\n";

	// Order information
	$escpos_output .= "\x1B\x61\x00"; // Left align
	$escpos_output .= str_repeat( '-', 32 ) . "\n";
	$escpos_output .= sprintf( "Order #%s\n", $order->get_order_number() );
	$escpos_output .= sprintf( "Date: %s\n", $order->get_date_created()->format( 'Y-m-d H:i:s' ) );
	
	if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
		$escpos_output .= sprintf( "Customer: %s %s\n", 
			$order->get_billing_first_name(), 
			$order->get_billing_last_name() 
		);
	}

	$escpos_output .= str_repeat( '-', 32 ) . "\n";

	// Order items
	foreach ( $order->get_items() as $item_id => $item ) {
		$product_name = $item->get_name();
		$quantity = $item->get_quantity();
		$total = $order->get_formatted_line_subtotal( $item );

		// Truncate product name if too long
		if ( strlen( $product_name ) > 20 ) {
			$product_name = substr( $product_name, 0, 17 ) . '...';
		}

		$escpos_output .= sprintf( "%-20s %2dx %8s\n", $product_name, $quantity, $total );
	}

	$escpos_output .= str_repeat( '-', 32 ) . "\n";

	// Order totals
	$escpos_output .= sprintf( "%-20s %11s\n", "Subtotal:", $order->get_formatted_order_total() );

	// Tax if applicable
	if ( $order->get_total_tax() > 0 ) {
		$escpos_output .= sprintf( "%-20s %11s\n", "Tax:", wc_price( $order->get_total_tax() ) );
	}

	// Shipping if applicable
	if ( $order->get_shipping_total() > 0 ) {
		$escpos_output .= sprintf( "%-20s %11s\n", "Shipping:", wc_price( $order->get_shipping_total() ) );
	}

	$escpos_output .= str_repeat( '-', 32 ) . "\n";
	$escpos_output .= "\x1B\x21\x30"; // Double width/height for total
	$escpos_output .= sprintf( "TOTAL: %s\n", $order->get_formatted_order_total() );
	$escpos_output .= "\x1B\x21\x00"; // Normal text

	// Payment method
	$payment_method = $order->get_payment_method_title();
	if ( $payment_method ) {
		$escpos_output .= sprintf( "Payment: %s\n", $payment_method );
	}

	$escpos_output .= "\n";

	// Footer
	$escpos_output .= "\x1B\x61\x01"; // Center align
	$escpos_output .= "Thank you for your purchase!\n";
	$escpos_output .= "\n\n\n";

	// Cut paper
	$escpos_output .= "\x1D\x56\x41\x10"; // Partial cut

	return $escpos_output;
}

/**
 * Send ESC/POS receipt to printer
 *
 * This function attempts to send the ESC/POS formatted receipt
 * directly to a printer using various methods.
 *
 * @param string $escpos_content ESC/POS formatted content
 * @param string $printer_name Optional printer name
 * @return bool Success status
 */
function yith_pos_send_to_printer( $escpos_content, $printer_name = null ) {
	$success = false;

	// Method 1: Try to use Windows print command
	if ( function_exists( 'shell_exec' ) && PHP_OS === 'WINNT' ) {
		$temp_file = tempnam( sys_get_temp_dir(), 'pos_receipt_' );
		file_put_contents( $temp_file, $escpos_content );
		
		if ( $printer_name ) {
			$command = sprintf( 'print /D:"%s" "%s"', $printer_name, $temp_file );
		} else {
			$command = sprintf( 'print "%s"', $temp_file );
		}
		
		$result = shell_exec( $command );
		unlink( $temp_file );
		
		if ( $result !== null ) {
			$success = true;
		}
	}

	// Method 2: Try to use Linux/Unix lp command
	if ( ! $success && function_exists( 'shell_exec' ) && PHP_OS !== 'WINNT' ) {
		$temp_file = tempnam( sys_get_temp_dir(), 'pos_receipt_' );
		file_put_contents( $temp_file, $escpos_content );
		
		if ( $printer_name ) {
			$command = sprintf( 'lp -d "%s" "%s"', $printer_name, $temp_file );
		} else {
			$command = sprintf( 'lp "%s"', $temp_file );
		}
		
		$result = shell_exec( $command );
		unlink( $temp_file );
		
		if ( $result !== null ) {
			$success = true;
		}
	}

	// Trigger action for other printing methods
	do_action( 'yith_pos_send_escpos_to_printer', $escpos_content, $printer_name, $success );

	return $success;
}

/**
 * Get available ESC/POS printers
 *
 * This function attempts to detect available ESC/POS compatible printers
 * on the system.
 *
 * @return array Array of available printers
 */
function yith_pos_get_available_printers() {
	$printers = array();

	// Windows printer detection
	if ( function_exists( 'shell_exec' ) && PHP_OS === 'WINNT' ) {
		$output = shell_exec( 'wmic printer get name' );
		if ( $output ) {
			$lines = explode( "\n", $output );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( $line && $line !== 'Name' ) {
					$printers[] = $line;
				}
			}
		}
	}

	// Linux/Unix printer detection
	if ( function_exists( 'shell_exec' ) && PHP_OS !== 'WINNT' ) {
		$output = shell_exec( 'lpstat -p 2>/dev/null | cut -d" " -f2' );
		if ( $output ) {
			$lines = explode( "\n", trim( $output ) );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( $line ) {
					$printers[] = $line;
				}
			}
		}
	}

	return apply_filters( 'yith_pos_available_printers', $printers );
}

/**
 * Test cash drawer functionality
 *
 * This function sends a test command to open the cash drawer.
 *
 * @return bool Success status
 */
function yith_pos_test_cash_drawer() {
	$cash_drawer = YITH_POS_Cash_Drawer::get_instance();
	
	if ( ! $cash_drawer->is_cash_drawer_enabled() ) {
		return false;
	}

	$command = $cash_drawer->get_drawer_open_command();
	
	// Try to send command to default printer
	$printers = yith_pos_get_available_printers();
	$success = false;
	
	if ( ! empty( $printers ) ) {
		$success = yith_pos_send_to_printer( $command, $printers[0] );
	}

	// Log the test
	if ( $success ) {
		error_log( 'YITH POS: Cash drawer test successful' );
	} else {
		error_log( 'YITH POS: Cash drawer test failed' );
	}

	do_action( 'yith_pos_cash_drawer_test', $success );

	return $success;
}

// Hook the receipt modification function
add_filter( 'yith_pos_receipt_content', 'yith_pos_add_cash_drawer_to_receipt', 5, 2 );

// Add hooks for generating ESC/POS receipts
add_action( 'yith_pos_print_receipt', function( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order ) {
		$escpos_content = yith_pos_generate_escpos_receipt( $order );
		yith_pos_send_to_printer( $escpos_content );
	}
}, 10, 1 );

// Add AJAX handler for printer testing
add_action( 'wp_ajax_yith_pos_test_printer', function() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'yith_pos_cash_drawer_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}

	$success = yith_pos_test_cash_drawer();
	
	if ( $success ) {
		wp_send_json_success( 'Printer test completed successfully' );
	} else {
		wp_send_json_error( 'Printer test failed' );
	}
} );
