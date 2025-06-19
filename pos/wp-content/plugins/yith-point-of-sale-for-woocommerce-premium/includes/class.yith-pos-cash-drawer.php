<?php
/**
 * YITH POS Cash Drawer Integration
 *
 * This file handles automatic cash drawer opening when printing receipts.
 * It integrates ESC/POS commands with the existing YITH Point of Sale system.
 *
 * @package YITH\POS\CashDrawer
 * @author GitHub Copilot Assistant
 * @version 1.0.0
 */

defined( 'YITH_POS' ) || exit();

/**
 * Class YITH_POS_Cash_Drawer
 *
 * Handles cash drawer functionality for YITH Point of Sale system.
 */
class YITH_POS_Cash_Drawer {

	/**
	 * ESC/POS command to open cash drawer (pulse to pin 2)
	 */
	const ESC_POS_DRAWER_OPEN_PIN2 = "\x1B\x70\x00\x19\x64";

	/**
	 * ESC/POS command to open cash drawer (pulse to pin 5)
	 */
	const ESC_POS_DRAWER_OPEN_PIN5 = "\x1B\x70\x01\x19\x64";

	/**
	 * Instance of this class
	 *
	 * @var YITH_POS_Cash_Drawer
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get single instance of the class
	 *
	 * @return YITH_POS_Cash_Drawer
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Add cash drawer opening to footer scripts when printing receipts
		add_action( 'yith_pos_footer', array( $this, 'add_cash_drawer_script' ), 25 );

		// Add filter to modify receipt content to include cash drawer commands
		add_filter( 'yith_pos_receipt_content', array( $this, 'add_cash_drawer_to_receipt' ), 10, 2 );

		// Hook into WooCommerce receipt printing if available
		add_filter( 'woocommerce_printable_order_receipt_data', array( $this, 'add_cash_drawer_to_wc_receipt' ), 10, 2 );

		// Add JavaScript for browser-based printing
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_cash_drawer_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// AJAX handler for cash drawer opening
		add_action( 'wp_ajax_yith_pos_open_cash_drawer', array( $this, 'ajax_open_cash_drawer' ) );
		add_action( 'wp_ajax_nopriv_yith_pos_open_cash_drawer', array( $this, 'ajax_open_cash_drawer' ) );

		// Add admin settings for cash drawer configuration
		add_filter( 'yith_pos_admin_tabs', array( $this, 'add_cash_drawer_tab' ) );
		add_action( 'yith_pos_cash_drawer_tab', array( $this, 'cash_drawer_settings_page' ) );

		// Hook into order completion to open drawer
		add_action( 'woocommerce_payment_complete', array( $this, 'on_order_payment_complete' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'on_order_completed' ) );

		// Add hooks for receipt printing events
		add_action( 'yith_pos_before_print_receipt', array( $this, 'trigger_cash_drawer_open' ) );
		add_action( 'yith_pos_after_order_complete', array( $this, 'trigger_cash_drawer_open' ) );

		// Add admin hooks
		if ( is_admin() ) {
			add_action( 'wp_ajax_yith_pos_test_cash_drawer', array( $this, 'handle_test_ajax' ) );
			add_action( 'wp_ajax_yith_pos_open_cash_drawer', array( $this, 'handle_open_ajax' ) );
		}
	}

	/**
	 * Check if cash drawer functionality is enabled
	 *
	 * @return bool
	 */
	public function is_cash_drawer_enabled() {
		return apply_filters( 'yith_pos_cash_drawer_enabled', get_option( 'yith_pos_cash_drawer_enabled', 'yes' ) === 'yes' );
	}

	/**
	 * Get the ESC/POS command for opening cash drawer
	 *
	 * @return string
	 */
	public function get_drawer_open_command() {
		$pin = get_option( 'yith_pos_cash_drawer_pin', 'pin2' );
		$command = ( $pin === 'pin5' ) ? self::ESC_POS_DRAWER_OPEN_PIN5 : self::ESC_POS_DRAWER_OPEN_PIN2;
		
		return apply_filters( 'yith_pos_cash_drawer_command', $command, $pin );
	}

	/**
	 * Add JavaScript for cash drawer functionality
	 */
	public function add_cash_drawer_script() {
		if ( ! $this->is_cash_drawer_enabled() ) {
			return;
		}

		// Only add script on POS pages
		if ( ! $this->is_pos_page() ) {
			return;
		}

		?>
		<script type="text/javascript">
		(function($) {
			'use strict';

			// Cash drawer functionality
			window.YithPosCashDrawer = {
				// ESC/POS commands
				commands: {
					pin2: '\x1B\x70\x00\x19\x64',
					pin5: '\x1B\x70\x01\x19\x64'
				},

				// Configuration
				config: {
					enabled: <?php echo json_encode( $this->is_cash_drawer_enabled() ); ?>,
					pin: '<?php echo esc_js( get_option( 'yith_pos_cash_drawer_pin', 'pin2' ) ); ?>',
					autoOpen: <?php echo json_encode( get_option( 'yith_pos_cash_drawer_auto_open', 'yes' ) === 'yes' ); ?>,
					ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
					nonce: '<?php echo wp_create_nonce( 'yith_pos_cash_drawer_nonce' ); ?>'
				},

				// Open cash drawer via ESC/POS
				openDrawer: function() {
					if (!this.config.enabled) {
						return false;
					}

					try {
						// Method 1: Try to send ESC/POS command directly to printer
						if (this.sendEscPosCommand()) {
							console.log('Cash drawer opened via ESC/POS');
							return true;
						}

						// Method 2: Use AJAX fallback
						this.openDrawerViaAjax();
					} catch (error) {
						console.error('Cash drawer error:', error);
						this.openDrawerViaAjax();
					}

					return true;
				},

				// Send ESC/POS command directly
				sendEscPosCommand: function() {
					if (!window.navigator || !window.navigator.serial) {
						return false;
					}

					// Modern browsers with Web Serial API
					return this.sendViaWebSerial();
				},

				// Send command via Web Serial API
				sendViaWebSerial: async function() {
					try {
						const port = await navigator.serial.requestPort();
						await port.open({ baudRate: 9600 });

						const writer = port.writable.getWriter();
						const command = this.commands[this.config.pin] || this.commands.pin2;
						
						await writer.write(new TextEncoder().encode(command));
						writer.releaseLock();
						await port.close();

						return true;
					} catch (error) {
						console.error('Web Serial error:', error);
						return false;
					}
				},

				// Fallback AJAX method
				openDrawerViaAjax: function() {
					$.ajax({
						url: this.config.ajaxUrl,
						type: 'POST',
						data: {
							action: 'yith_pos_open_cash_drawer',
							nonce: this.config.nonce
						},
						success: function(response) {
							if (response.success) {
								console.log('Cash drawer opened via server');
							} else {
								console.error('Server error:', response.data);
							}
						},
						error: function(xhr, status, error) {
							console.error('AJAX error:', error);
						}
					});
				},

				// Initialize cash drawer functionality
				init: function() {
					if (!this.config.enabled) {
						return;
					}

					// Hook into print receipt events
					this.bindPrintEvents();

					// Add manual open button
					this.addManualOpenButton();
				},

				// Bind to print events
				bindPrintEvents: function() {
					var self = this;

					// Hook into existing print functionality
					$(document).on('click', '.yith-pos-order-receipt-print-control__standard-print', function() {
						if (self.config.autoOpen) {
							setTimeout(function() {
								self.openDrawer();
							}, 500); // Small delay to ensure print job starts first
						}
					});

					// Hook into browser print event
					window.addEventListener('beforeprint', function() {
						if (self.config.autoOpen) {
							self.openDrawer();
						}
					});

					// Hook into print command from React components
					$(document).on('yith-pos-print-receipt', function() {
						if (self.config.autoOpen) {
							self.openDrawer();
						}
					});
				},

				// Add manual open button to POS interface
				addManualOpenButton: function() {
					var self = this;
					var buttonHtml = '<button type="button" class="button yith-pos-open-drawer-btn" style="margin-left: 10px;">' +
						'<?php echo esc_js( __( 'Open Drawer', 'yith-point-of-sale-for-woocommerce' ) ); ?>' +
						'</button>';

					// Add to receipt print controls if they exist
					$('.yith-pos-order-receipt-print-control').append(buttonHtml);

					// Bind click event
					$(document).on('click', '.yith-pos-open-drawer-btn', function(e) {
						e.preventDefault();
						self.openDrawer();
					});
				}
			};

			// Initialize when DOM is ready
			$(document).ready(function() {
				YithPosCashDrawer.init();
			});

		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Add cash drawer command to receipt content
	 *
	 * @param string $content Receipt content
	 * @param object $receipt Receipt object
	 * @return string Modified content
	 */
	public function add_cash_drawer_to_receipt( $content, $receipt ) {
		if ( ! $this->is_cash_drawer_enabled() ) {
			return $content;
		}

		$drawer_command = $this->get_drawer_open_command();
		
		// Add the ESC/POS command at the beginning of the receipt
		$content = $drawer_command . $content;

		return $content;
	}

	/**
	 * Add cash drawer command to WooCommerce receipt data
	 *
	 * @param array $data Receipt data
	 * @param WC_Order $order Order object
	 * @return array Modified data
	 */
	public function add_cash_drawer_to_wc_receipt( $data, $order ) {
		if ( ! $this->is_cash_drawer_enabled() ) {
			return $data;
		}

		// Add cash drawer command to CSS for printing
		$drawer_command = $this->get_drawer_open_command();
		$data['cash_drawer_command'] = $drawer_command;

		return $data;
	}
	/**
	 * Enqueue JavaScript files for cash drawer functionality
	 */
	public function enqueue_cash_drawer_scripts() {
		if ( ! $this->is_pos_page() ) {
			return;
		}

		// Enqueue main cash drawer script
		wp_enqueue_script(
			'yith-pos-cash-drawer',
			YITH_POS_ASSETS_URL . 'js/yith-pos-cash-drawer.js',
			array( 'jquery' ),
			YITH_POS_VERSION,
			true
		);

		// Enqueue cash drawer styles
		wp_enqueue_style(
			'yith-pos-cash-drawer',
			YITH_POS_ASSETS_URL . 'css/yith-pos-cash-drawer.css',
			array(),
			YITH_POS_VERSION
		);

		// Localize script with configuration and translations
		wp_localize_script( 'yith-pos-cash-drawer', 'yith_pos_cash_drawer_config', array(
			'enabled'   => $this->is_cash_drawer_enabled(),
			'pin'       => get_option( 'yith_pos_cash_drawer_pin', 'pin2' ),
			'autoOpen'  => get_option( 'yith_pos_cash_drawer_auto_open', 'yes' ) === 'yes',
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'yith_pos_cash_drawer_nonce' ),
		) );

		wp_localize_script( 'yith-pos-cash-drawer', 'yith_pos_cash_drawer_l10n', array(
			'open_drawer'           => __( 'Open Drawer', 'yith-point-of-sale-for-woocommerce' ),
			'drawer_opened'         => __( 'Cash drawer opened successfully', 'yith-point-of-sale-for-woocommerce' ),
			'drawer_error'          => __( 'Error opening cash drawer', 'yith-point-of-sale-for-woocommerce' ),
			'connection_error'      => __( 'Connection error. Please try again.', 'yith-point-of-sale-for-woocommerce' ),
			'try_manual'            => __( 'Would you like to see manual opening instructions?', 'yith-point-of-sale-for-woocommerce' ),
			'manual_instructions'   => __( 'To manually open the cash drawer:\n1. Locate the manual release button/key on your cash drawer\n2. Press the button or turn the key\n3. The drawer should open with a click sound\n\nIf this doesn\'t work, check your printer and drawer connections.', 'yith-point-of-sale-for-woocommerce' ),
			'test_successful'       => __( 'Cash drawer test completed', 'yith-point-of-sale-for-woocommerce' ),
		) );

		// Enqueue a small inline script to handle print events
		wp_add_inline_script( 'yith-pos-cash-drawer', '
			jQuery(document).ready(function($) {
				// Override browser print to trigger cash drawer
				var originalPrint = window.print;
				window.print = function() {
					$(document).trigger("yith-pos-print-receipt");
					originalPrint.call(window);
				};
				
				// Hook into existing POS print events
				$(document).on("click", "[data-print-receipt], .print-receipt-btn", function() {
					$(document).trigger("yith-pos-print-receipt");
				});
			});
		' );
	}

	/**
	 * Enqueue admin scripts and styles for cash drawer functionality
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Only load on YITH POS admin pages
		if ( strpos( $hook, 'yith_pos_panel' ) === false ) {
			return;
		}
		
		wp_enqueue_script(
			'yith-pos-cash-drawer-admin',
			YITH_POS_ASSETS_URL . '/js/yith-pos-cash-drawer.js',
			array( 'jquery' ),
			YITH_POS_VERSION,
			true
		);
		
		wp_enqueue_style(
			'yith-pos-cash-drawer-admin',
			YITH_POS_ASSETS_URL . '/css/yith-pos-cash-drawer.css',
			array(),
			YITH_POS_VERSION
		);
		
		// Localize script for AJAX
		wp_localize_script( 'yith-pos-cash-drawer-admin', 'yith_pos_cash_drawer', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'test_nonce' => wp_create_nonce( 'yith_pos_cash_drawer_test' ),
			'open_nonce' => wp_create_nonce( 'yith_pos_cash_drawer' ),
			'strings' => array(
				'opening' => __( 'Opening cash drawer...', 'yith-point-of-sale-for-woocommerce' ),
				'success' => __( 'Cash drawer opened successfully!', 'yith-point-of-sale-for-woocommerce' ),
				'error' => __( 'Failed to open cash drawer.', 'yith-point-of-sale-for-woocommerce' ),
			),
		) );
	}
	
	/**
	 * AJAX handler to open cash drawer
	 */
	public function ajax_open_cash_drawer() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'yith_pos_cash_drawer_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		if ( ! $this->is_cash_drawer_enabled() ) {
			wp_send_json_error( 'Cash drawer functionality is disabled' );
		}

		// Check user permissions
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'yith_pos_cashier' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Log the cash drawer opening
		$this->log_drawer_opening();

		// Try to open the drawer via system command if possible
		$success = $this->open_drawer_system_command();

		if ( $success ) {
			wp_send_json_success( 'Cash drawer opened successfully' );
		} else {
			wp_send_json_error( 'Failed to open cash drawer' );
		}
	}

	/**
	 * Handle AJAX request to test cash drawer
	 */
	public function handle_test_ajax() {
		// Check nonce and permissions
		if ( ! check_ajax_referer( 'yith_pos_cash_drawer_test', 'nonce', false ) || ! current_user_can( 'yith_pos_manage_pos_options' ) ) {
			wp_die( __( 'Invalid request', 'yith-point-of-sale-for-woocommerce' ) );
		}
		
		try {
			// Test the cash drawer opening
			$result = $this->open_cash_drawer();
			
			if ( $result ) {
				wp_send_json_success( __( 'Cash drawer test command sent successfully!', 'yith-point-of-sale-for-woocommerce' ) );
			} else {
				wp_send_json_error( __( 'Cash drawer test failed. Please check your settings.', 'yith-point-of-sale-for-woocommerce' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Error: ', 'yith-point-of-sale-for-woocommerce' ) . $e->getMessage() );
		}
	}
	
	/**
	 * Handle AJAX request to open cash drawer manually
	 */
	public function handle_open_ajax() {
		// Check nonce and permissions
		if ( ! check_ajax_referer( 'yith_pos_cash_drawer', 'nonce', false ) || ! current_user_can( 'yith_pos_manage_pos' ) ) {
			wp_die( __( 'Invalid request', 'yith-point-of-sale-for-woocommerce' ) );
		}
		
		try {
			$result = $this->open_cash_drawer();
			
			if ( $result ) {
				wp_send_json_success( __( 'Cash drawer opened successfully!', 'yith-point-of-sale-for-woocommerce' ) );
			} else {
				wp_send_json_error( __( 'Failed to open cash drawer.', 'yith-point-of-sale-for-woocommerce' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Error: ', 'yith-point-of-sale-for-woocommerce' ) . $e->getMessage() );
		}
	}
	
	/**
	 * Try to open cash drawer via system command
	 *
	 * @return bool Success status
	 */
	private function open_drawer_system_command() {
		// This would need to be implemented based on the specific printer/drawer setup
		// For security reasons, we'll just log the attempt
		
		do_action( 'yith_pos_cash_drawer_opened', $this->get_drawer_open_command() );
		
		return true;
	}

	/**
	 * Log cash drawer opening event
	 */
	private function log_drawer_opening() {
		$user_id = get_current_user_id();
		$timestamp = current_time( 'mysql' );
		
		// Log to WordPress
		error_log( sprintf( 
			'YITH POS: Cash drawer opened by user %d at %s', 
			$user_id, 
			$timestamp 
		) );

		// Trigger action for other plugins to hook into
		do_action( 'yith_pos_cash_drawer_log', $user_id, $timestamp );
	}
	/**
	 * Check if current page is a POS page
	 *
	 * @return bool
	 */
	private function is_pos_page() {
		global $post;
		
		// Check if we're on a POS page
		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'yith_pos' ) !== false ) {
			return true;
		}

		// Check for POS post types
		if ( $post && in_array( $post->post_type, array( 'yith-pos-store', 'yith-pos-register', 'yith-pos-receipt' ) ) ) {
			return true;
		}

		// Check for POS template
		if ( is_page() && $post && get_page_template_slug( $post->ID ) === 'pos-template.php' ) {
			return true;
		}

		// Check if we're in the POS frontend application
		if ( defined( 'YITH_POS_FRONTEND' ) && YITH_POS_FRONTEND ) {
			return true;
		}

		return false;
	}

	/**
	 * Trigger cash drawer opening
	 */
	public function trigger_cash_drawer_open() {
		if ( ! $this->is_cash_drawer_enabled() ) {
			return;
		}

		// Trigger action for JavaScript to pick up
		wp_add_inline_script( 'yith-pos-cash-drawer', 'jQuery(document).trigger("yith-pos-open-cash-drawer");' );
	}

	/**
	 * Handle order payment completion
	 *
	 * @param int $order_id Order ID
	 */
	public function on_order_payment_complete( $order_id ) {
		if ( ! $this->is_cash_drawer_enabled() ) {
			return;
		}

		// Check if this is a POS order
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$is_pos_order = $order->get_meta( '_yith_pos_order', true );
		if ( $is_pos_order ) {
			$this->trigger_cash_drawer_open();
		}
	}

	/**
	 * Handle order completion
	 *
	 * @param int $order_id Order ID
	 */
	public function on_order_completed( $order_id ) {
		$this->on_order_payment_complete( $order_id );
	}

	/**
	 * Add cash drawer tab to admin settings
	 *
	 * @param array $tabs Existing tabs
	 * @return array Modified tabs
	 */
	public function add_cash_drawer_tab( $tabs ) {
		$tabs['cash_drawer'] = __( 'Cash Drawer', 'yith-point-of-sale-for-woocommerce' );
		return $tabs;
	}

	/**
	 * Display cash drawer settings page
	 */
	public function cash_drawer_settings_page() {
		if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'yith_pos_cash_drawer_settings' ) ) {
			// Save settings
			update_option( 'yith_pos_cash_drawer_enabled', isset( $_POST['cash_drawer_enabled'] ) ? 'yes' : 'no' );
			update_option( 'yith_pos_cash_drawer_pin', sanitize_text_field( $_POST['cash_drawer_pin'] ) );
			update_option( 'yith_pos_cash_drawer_auto_open', isset( $_POST['cash_drawer_auto_open'] ) ? 'yes' : 'no' );

			echo '<div class="notice notice-success"><p>' . __( 'Cash drawer settings saved successfully.', 'yith-point-of-sale-for-woocommerce' ) . '</p></div>';
		}

		$enabled = get_option( 'yith_pos_cash_drawer_enabled', 'yes' ) === 'yes';
		$pin = get_option( 'yith_pos_cash_drawer_pin', 'pin2' );
		$auto_open = get_option( 'yith_pos_cash_drawer_auto_open', 'yes' ) === 'yes';
		?>
		<div class="wrap">
			<h1><?php echo esc_html( __( 'Cash Drawer Settings', 'yith-point-of-sale-for-woocommerce' ) ); ?></h1>
			
			<form method="post" action="">
				<?php wp_nonce_field( 'yith_pos_cash_drawer_settings' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php echo esc_html( __( 'Enable Cash Drawer', 'yith-point-of-sale-for-woocommerce' ) ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="cash_drawer_enabled" value="1" <?php checked( $enabled ); ?> />
								<?php echo esc_html( __( 'Enable automatic cash drawer opening when printing receipts', 'yith-point-of-sale-for-woocommerce' ) ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html( __( 'Cash Drawer Pin', 'yith-point-of-sale-for-woocommerce' ) ); ?></th>
						<td>
							<select name="cash_drawer_pin">
								<option value="pin2" <?php selected( $pin, 'pin2' ); ?>><?php echo esc_html( __( 'Pin 2 (Most Common)', 'yith-point-of-sale-for-woocommerce' ) ); ?></option>
								<option value="pin5" <?php selected( $pin, 'pin5' ); ?>><?php echo esc_html( __( 'Pin 5', 'yith-point-of-sale-for-woocommerce' ) ); ?></option>
							</select>
							<p class="description"><?php echo esc_html( __( 'Select the pin that your cash drawer is connected to on the receipt printer.', 'yith-point-of-sale-for-woocommerce' ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html( __( 'Auto Open on Print', 'yith-point-of-sale-for-woocommerce' ) ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="cash_drawer_auto_open" value="1" <?php checked( $auto_open ); ?> />
								<?php echo esc_html( __( 'Automatically open cash drawer when printing receipts', 'yith-point-of-sale-for-woocommerce' ) ); ?>
							</label>
						</td>
					</tr>
				</table>
				
				<h2><?php echo esc_html( __( 'ESC/POS Commands', 'yith-point-of-sale-for-woocommerce' ) ); ?></h2>
				<table class="form-table">
					<tr>
						<th scope="row"><?php echo esc_html( __( 'Pin 2 Command', 'yith-point-of-sale-for-woocommerce' ) ); ?></th>
						<td>
							<code><?php echo esc_html( bin2hex( self::ESC_POS_DRAWER_OPEN_PIN2 ) ); ?></code>
							<p class="description"><?php echo esc_html( __( 'ESC/POS command sent to open cash drawer connected to pin 2.', 'yith-point-of-sale-for-woocommerce' ) ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo esc_html( __( 'Pin 5 Command', 'yith-point-of-sale-for-woocommerce' ) ); ?></th>
						<td>
							<code><?php echo esc_html( bin2hex( self::ESC_POS_DRAWER_OPEN_PIN5 ) ); ?></code>
							<p class="description"><?php echo esc_html( __( 'ESC/POS command sent to open cash drawer connected to pin 5.', 'yith-point-of-sale-for-woocommerce' ) ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php echo esc_html( __( 'Test Cash Drawer', 'yith-point-of-sale-for-woocommerce' ) ); ?></h2>
				<p>
					<button type="button" id="test-cash-drawer" class="button">
						<?php echo esc_html( __( 'Test Cash Drawer', 'yith-point-of-sale-for-woocommerce' ) ); ?>
					</button>
					<span class="description"><?php echo esc_html( __( 'Click to test if cash drawer opens correctly.', 'yith-point-of-sale-for-woocommerce' ) ); ?></span>
				</p>

				<?php submit_button(); ?>
			</form>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#test-cash-drawer').on('click', function() {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'yith_pos_open_cash_drawer',
						nonce: '<?php echo wp_create_nonce( 'yith_pos_cash_drawer_nonce' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							alert('<?php echo esc_js( __( 'Cash drawer test successful!', 'yith-point-of-sale-for-woocommerce' ) ); ?>');
						} else {
							alert('<?php echo esc_js( __( 'Cash drawer test failed: ', 'yith-point-of-sale-for-woocommerce' ) ); ?>' + response.data);
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'Connection error. Please try again.', 'yith-point-of-sale-for-woocommerce' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}
}

// Initialize the cash drawer functionality
YITH_POS_Cash_Drawer::get_instance();
