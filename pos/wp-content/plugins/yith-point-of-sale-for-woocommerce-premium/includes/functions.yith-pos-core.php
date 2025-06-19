<?php
/**
 * Functions
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\POS\Functions
 */

defined( 'YITH_POS' ) || exit;

if ( ! function_exists( 'yith_pos_get_view' ) ) {
	/**
	 * Print a view.
	 *
	 * @param string $view View.
	 * @param array  $args Arguments.
	 */
	function yith_pos_get_view( $view, $args = array() ) {
		$view_path = trailingslashit( YITH_POS_VIEWS_PATH ) . $view;
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}
}

if ( ! function_exists( 'yith_pos_is_store_wizard' ) ) {
	/**
	 * Is this the Store Wizard?
	 *
	 * @return bool
	 */
	function yith_pos_is_store_wizard() {
		global $pagenow, $post, $post_type;

		return ! ! $pagenow && ! ! $post && ! ! $post_type && YITH_POS_Post_Types::STORE === $post_type && ( 'post-new.php' === $pagenow || 'draft' === $post->post_status );
	}
}

if ( ! function_exists( 'yith_pos_get_employee_name' ) ) {
	/**
	 * Get the employee name
	 *
	 * @param int   $user_id User ID.
	 * @param array $options Options.
	 *
	 * @return string
	 */
	function yith_pos_get_employee_name( $user_id, $options = array() ) {
		$defaults = array(
			'hide_nickname' => false,
		);
		$options  = wp_parse_args( $options, $defaults );

		$user_info = get_userdata( $user_id );
		if ( $user_info ) {
			if ( $user_info->first_name || $user_info->last_name ) {
				if ( $options['hide_nickname'] ) {
					// translators: 1. first name; 2. last name.
					$name = esc_html( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), ucfirst( $user_info->first_name ), ucfirst( $user_info->last_name ) ) );
				} else {
					// translators: 1. first name; 2. last name; 3. nick-name.
					$name = esc_html( sprintf( _x( '%1$s %2$s (%3$s)', 'full name', 'woocommerce' ), ucfirst( $user_info->first_name ), ucfirst( $user_info->last_name ), $user_info->nickname ) );
				}
			} else {
				$name = esc_html( ucfirst( $user_info->display_name ) );
			}
		} else {
			// translators: %s is the user ID.
			$name = esc_html( sprintf( __( 'User #%s', 'yith-point-of-sale-for-woocommerce' ), $user_id ) );
		}

		return apply_filters( 'yith_pos_get_employee_name', $name, $user_id, $user_info );
	}
}


if ( ! function_exists( 'yith_pos_get_employees' ) ) {
	/**
	 * Get the employee list
	 *
	 * @param string             $role  The role (allowed values: manager|cashier).
	 * @param int|YITH_POS_Store $store The store.
	 *
	 * @return int[]
	 */
	function yith_pos_get_employees( $role = 'manager', $store = null ) {
		$employees = array();
		if ( is_null( $store ) ) {
			$user_query = new WP_User_Query(
				array(
					'role'   => 'yith_pos_' . $role,
					'fields' => 'ID',
				)
			);
			$employees  = $user_query->get_results();
			$employees  = array_filter( array_map( 'absint', $employees ) );
		} else {
			$store_obj = yith_pos_get_store( $store );
			if ( $store_obj ) {
				$employees = 'manager' === $role ? $store_obj->get_managers() : $store_obj->get_cashiers();
			}
		}

		return apply_filters( 'yith_pos_get_employees', $employees, $role, $store );
	}
}

if ( ! function_exists( 'yith_pos_admin_screen_ids' ) ) {
	/**
	 * Return POS screen ids
	 *
	 * @return array
	 */
	function yith_pos_admin_screen_ids() {
		$screen_ids = array(
			'yith-plugins_page_yith_pos_panel',
		);
		$post_types = array(
			YITH_POS_Post_Types::STORE,
			YITH_POS_Post_Types::RECEIPT,
			YITH_POS_Post_Types::REGISTER,
		);
		foreach ( $post_types as $post_type ) {
			$screen_ids[] = $post_type;
			$screen_ids[] = 'edit-' . $post_type;
		}

		return apply_filters( 'yith_pos_admin_screen_ids', $screen_ids );
	}
}

if ( ! function_exists( 'yith_pos_compact_list' ) ) {
	/**
	 * Print a compact list
	 *
	 * @param array $items Items.
	 * @param array $args  Arguments.
	 */
	function yith_pos_compact_list( $items, $args = array() ) {
		$defaults          = array(
			'limit'             => 5,
			'class'             => '',
			// translators: %s is the number of other items.
			'show_more_message' => __( 'and other %s...', 'yith-point-of-sale-for-woocommerce' ),
			'hide_more_message' => __( 'hide', 'yith-point-of-sale-for-woocommerce' ),
		);
		$args              = wp_parse_args( $args, $defaults );
		$total             = count( $items );
		$limit             = absint( $args['limit'] );
		$hidden            = max( 0, $total - $limit );
		$class             = $args['class'];
		$show_more_message = sprintf( $args['show_more_message'], $hidden );
		$hide_more_message = $args['hide_more_message'];

		echo '<div class="yith-pos-compact-list ' . esc_attr( $class ) . '" data-total="' . esc_attr( $total ) . '" data-limit="' . esc_attr( $limit ) . '" data-show-more-message="' . esc_attr( $show_more_message ) . '" data-hide-more-message="' . esc_attr( $hide_more_message ) . '">';
		$index = 1;

		foreach ( $items as $item ) {
			$item_class = 'yith-pos-compact-list__item';
			if ( ( $limit + 1 ) === $index ) {
				echo "<div class='yith-pos-compact-list__hidden-items'>";
			}
			echo '<div class="' . esc_attr( $item_class ) . '" data-index="' . esc_attr( $index ) . '">' . esc_html( $item ) . '</div>';
			$index++;
		}
		if ( $hidden ) {
			echo '</div>';
			echo "<div class='clear'></div>";
			echo '<span class="yith-pos-compact-list__show-more">' . esc_html( $show_more_message ) . '<span class="yith-icon yith-icon-arrow_down"></span></span>';
			echo '<span class="yith-pos-compact-list__hide-more">' . esc_html( $hide_more_message ) . '<span class="yith-icon yith-icon-arrow_up"></span></span>';
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'yith_pos_get_current_post_type' ) ) {
	/**
	 * In admin return the current post type.
	 *
	 * @return mixed|void
	 */
	function yith_pos_get_current_post_type() {
		global $pagenow;
		$post_type = '';
		// phpcs:disable WordPress.Security.NonceVerification
		if ( isset( $_POST['post_type'] ) ) {
			$post_type = wc_clean( wp_unslash( $_POST['post_type'] ) );
		} elseif ( isset( $_GET['post'] ) ) {
			$post_type = get_post_type( wc_clean( wp_unslash( $_GET['post'] ) ) );
		} elseif ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) ) {
			$post_type = wc_clean( wp_unslash( $_GET['post_type'] ) );
		}

		// phpcs:enable

		return apply_filters( 'yith_pos_current_post_type', $post_type );
	}
}

if ( ! function_exists( 'yith_pos_get_stores' ) ) {
	/**
	 * Return the list of stores.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	function yith_pos_get_stores( $args = array() ) {

		$defaults = array(
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'include'        => '',
			'exclude'        => '',
			'meta_key'       => '', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '', // phpcs:ignore WordPress.DB.SlowDBQuery
			'post_type'      => YITH_POS_Post_Types::STORE,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$args = wp_parse_args( $args, $defaults );

		$return_stores = 'stores' === $args['fields'];

		if ( $return_stores ) {
			$args['fields'] = 'ids';
		}

		$stores = get_posts( $args );

		if ( $return_stores ) {
			$stores = array_filter( array_map( 'yith_pos_get_store', $stores ) );
		}

		return apply_filters( 'yith_pos_stores', $stores );
	}
}

if ( ! function_exists( 'yith_pos_get_registers' ) ) {
	/**
	 * Return the list of registers.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	function yith_pos_get_registers( $args = array() ) {
		$defaults = array(
			'posts_per_page' => 5,
			'offset'         => 0,
			'include'        => '',
			'exclude'        => '',
			'meta_key'       => '', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '', // phpcs:ignore WordPress.DB.SlowDBQuery
			'post_type'      => YITH_POS_Post_Types::REGISTER,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$args             = wp_parse_args( $args, $defaults );
		$return_registers = 'registers' === $args['fields'];

		if ( $return_registers ) {
			$args['fields'] = 'ids';
		}

		$registers = get_posts( $args );

		if ( $return_registers ) {
			$registers = array_filter( array_map( 'yith_pos_get_register', $registers ) );
		}

		return apply_filters( 'yith_pos_get_registers', $registers );
	}
}

if ( ! function_exists( 'yith_pos_get_registers_by_store' ) ) {
	/**
	 * Return the list of registers of a specific store
	 *
	 * @param int   $store_id Store ID.
	 * @param array $args     Arguments.
	 *
	 * @return array
	 */
	function yith_pos_get_registers_by_store( $store_id, $args = array() ) {
		$defaults = array(
			'order'          => 'ASC',
			'posts_per_page' => -1,
			'meta_key'       => '_store_id', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => absint( $store_id ), // phpcs:ignore WordPress.DB.SlowDBQuery
			'post_status'    => 'any',
			'fields'         => 'registers',
		);

		$args = wp_parse_args( $args, $defaults );

		return yith_pos_get_registers( $args );
	}
}

if ( ! function_exists( 'yith_post_rest_get_register_list' ) ) {
	/**
	 * Callback that return to the store rest api the list of registers
	 * as array ('id','name')
	 *
	 * @param array           $object     The object data.
	 * @param string          $field_name Field name.
	 * @param WP_REST_Request $request    The request.
	 *
	 * @return array
	 * @see \YITH_POS_Post_Types::add_registers_field_to_store
	 */
	function yith_post_rest_get_register_list( $object, $field_name, $request ) {
		$registers = yith_pos_get_registers_by_store( $object['id'] );

		$register_list = array();
		if ( $registers ) {
			foreach ( $registers as $register ) {
				array_push(
					$register_list,
					array(
						'id'   => $register->get_id(),
						'name' => $register->get_name(),
					)
				);
			}
		}

		return $register_list;
	}
}

if ( ! function_exists( 'yith_pos_get_receipts_options' ) ) {
	/**
	 * Return the list of receipts.
	 */
	function yith_pos_get_receipts_options() {
		$args = array(
			'posts_per_page' => -1,
			'post_type'      => YITH_POS_Post_Types::RECEIPT,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$receipts = array( '' => __( 'No receipt', 'yith-point-of-sale-for-woocommerce' ) );

		$receipts_posts = get_posts( $args );

		if ( $receipts_posts ) {
			foreach ( $receipts_posts as $receipt ) {
				$receipts[ $receipt->ID ] = $receipt->post_title;
			}
		}

		return $receipts;
	}
}

if ( ! function_exists( 'yith_pos_create_user_form' ) ) {
	/**
	 * Print a form for creating a new user
	 *
	 * @param array $args Arguments.
	 * @param bool  $echo Set true to print directly; false to return the content.
	 *
	 * @return false|string|void
	 */
	function yith_pos_register_user_form( $args = array(), $echo = true ) {
		static $form_id = 0;
		$form_id++;

		$defaults        = array(
			'title'               => __( 'Create new user', 'yith-point-of-sale-for-woocommerce' ),
			'button_text'         => __( 'Create new user', 'yith-point-of-sale-for-woocommerce' ),
			'button_close_text'   => __( 'Close new user creation', 'yith-point-of-sale-for-woocommerce' ),
			'save_text'           => __( 'Create user', 'yith-point-of-sale-for-woocommerce' ),
			'user_type'           => '',
			'select2_to_populate' => '',
		);
		$args            = wp_parse_args( $args, $defaults );
		$html            = '';
		$args['form_id'] = $form_id;

		if ( current_user_can( 'create_users' ) ) {
			ob_start();
			yith_pos_get_view( 'fields/create-user.php', $args );
			$html = ob_get_clean();
		}

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		return $html;
	}
}

if ( ! function_exists( 'yith_pos_get_required_field_message' ) ) {
	/**
	 * Get the required message for fields.
	 *
	 * @return string
	 */
	function yith_pos_get_required_field_message() {
		$message = '<span class="yith-pos-required-field-message">' . esc_html__( 'This field is required.', 'yith-point-of-sale-for-woocommerce' ) . '</span>';

		return apply_filters( 'yith_pos_get_required_message', $message );
	}
}

if ( ! function_exists( 'yith_pos_svg' ) ) {
	/**
	 * Print or return an SVG.
	 *
	 * @param string $svg  SVG name.
	 * @param bool   $echo Set true to print directly; false to return the content.
	 *
	 * @return false|string|void
	 */
	function yith_pos_svg( $svg, $echo = true ) {
		$path = trailingslashit( YITH_POS_ASSETS_PATH ) . 'svg/' . $svg . '.svg';
		$html = '';

		if ( file_exists( $path ) ) {
			ob_start();
			include $path;
			$html = ob_get_clean();
		}

		if ( $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		return $html;
	}
}

if ( ! function_exists( 'yith_pos_get_post_edit_link_html' ) ) {
	/**
	 * Return the Post Edit link html
	 *
	 * @param int  $post_id The post ID.
	 * @param bool $echo    Echo flag.
	 *
	 * @return string
	 */
	function yith_pos_get_post_edit_link_html( $post_id, $echo = false ) {
		if ( $post_id ) {
			ob_start();

			echo sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_post_link( $post_id ) ),
				esc_html( get_the_title( $post_id ) )
			);

			$the_link = ob_get_clean();

			if ( $echo ) {
				echo $the_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $the_link;
		}

		return '';
	}
}

if ( ! function_exists( 'yith_pos_get_register_options' ) ) {
	/**
	 * Return the array of the options of the register
	 *
	 * @param YITH_POS_Register|int $register The register.
	 *
	 * @return array
	 */
	function yith_pos_get_register_options( $register ) {
		$register = yith_pos_get_register( $register );

		return include YITH_POS_DIR . '/plugin-options/metabox/register-options.php';
	}
}

if ( ! function_exists( 'yith_pos_register_statuses' ) ) {
	/**
	 * Return the statuses for registers
	 *
	 * @return array
	 */
	function yith_pos_register_statuses() {
		$statuses = array(
			'opened' => __( 'Opened', 'yith-point-of-sale-for-woocommerce' ),
			'closed' => __( 'Closed', 'yith-point-of-sale-for-woocommerce' ),
		);

		return apply_filters( 'yith_pos_register_statuses', $statuses );
	}
}

if ( ! function_exists( 'yith_pos_get_register_status_name' ) ) {
	/**
	 * Return the name of the status
	 *
	 * @param string $status The status.
	 *
	 * @return string
	 */
	function yith_pos_get_register_status_name( $status ) {
		$statuses = yith_pos_register_statuses();

		return array_key_exists( $status, $statuses ) ? $statuses[ $status ] : '';
	}
}

if ( ! function_exists( 'yith_pos_get_register_full_name' ) ) {
	/**
	 * Return the name of the register including the name of the store
	 *
	 * @param int $register_id Register ID.
	 *
	 * @return string
	 */
	function yith_pos_get_register_full_name( $register_id ) {
		$register_name = yith_pos_get_register_name( $register_id );
		$store_id      = absint( get_post_meta( $register_id, '_store_id', true ) );
		$store_name    = $store_id ? yith_pos_get_store_name( $store_id ) : '';

		if ( $store_name ) {
			$full_name = sprintf( '%s (%s)', $register_name, $store_name );
		} else {
			$full_name = $register_name;
		}

		return apply_filters( 'yith_pos_get_register_full_name', $full_name, $register_id );
	}
}


if ( ! function_exists( 'yith_pos_get_pos_page_url' ) ) {
	/**
	 * Return the URL of YITH Pos page
	 *
	 * @return string
	 */
	function yith_pos_get_pos_page_url() {
		$option_value = get_option( 'settings_pos_page' );

		if ( function_exists( 'wpml_object_id_filter' ) ) {
			global $sitepress;

			if ( ! is_null( $sitepress ) && is_callable( array( $sitepress, 'get_current_language' ) ) ) {
				$option_value = wpml_object_id_filter( $option_value, 'post', true, $sitepress->get_current_language() );
			}
		}

		$base_url = get_the_permalink( $option_value );

		return apply_filters( 'yith_pos_page_url', $base_url );
	}
}

if ( ! function_exists( 'yith_pos_get_pos_page_id' ) ) {
	/**
	 * Return the id of YITH Pos Page
	 *
	 * @return int
	 */
	function yith_pos_get_pos_page_id() {
		$page_id = get_option( 'settings_pos_page' );

		if ( function_exists( 'wpml_object_id_filter' ) ) {
			global $sitepress;

			if ( ! is_null( $sitepress ) && is_callable( array( $sitepress, 'get_current_language' ) ) ) {
				$page_id = wpml_object_id_filter( $page_id, 'post', true, $sitepress->get_current_language() );
			}
		}

		return absint( apply_filters( 'yith_pos_page_id', $page_id ) );
	}
}

if ( ! function_exists( 'is_yith_pos' ) ) {

	/**
	 * Is_yith_pos?
	 * Returns true when viewing the YITH Pos page.
	 *
	 * @return bool
	 */
	function is_yith_pos() {
		$page_id = yith_pos_get_pos_page_id();

		return ( $page_id && is_page( $page_id ) );
	}
}

if ( ! function_exists( 'yith_pos_maybe_add_user_role' ) ) {

	/**
	 * Add a specific role to an user if he/she doesn't have it
	 *
	 * @param int|WP_User $user The user.
	 * @param string      $role The role.
	 */
	function yith_pos_maybe_add_user_role( $user, $role ) {
		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}
		if ( $user && $user->exists() && ! in_array( $role, $user->roles, true ) ) {
			$user->add_role( $role );
		}
	}
}

if ( ! function_exists( 'yith_pos_maybe_remove_user_role' ) ) {

	/**
	 * Remove a specific role to an user if he/she doesn't have it
	 *
	 * @param int|WP_User $user             The user.
	 * @param string      $role             The role.
	 * @param int         $current_store_id Current store ID.
	 */
	function yith_pos_maybe_remove_user_role( $user, $role, $current_store_id ) {
		if ( ! is_object( $user ) ) {
			$user = get_userdata( $user );
		}

		if ( $user && $user->exists() && in_array( 'yith_pos_' . $role, $user->roles, true ) ) {
			$stores      = yith_pos_get_stores( array( 'post_status' => array( 'publish', 'draft' ) ) );
			$remove_role = true;
			if ( $stores ) {
				foreach ( $stores as $store_id ) {
					if ( absint( $store_id ) !== absint( $current_store_id ) ) {
						$employees = yith_pos_get_employees( $role, $store_id );

						if ( in_array( $user->ID, $employees, true ) ) {
							$remove_role = false;
							break;
						}
					}
				}
			}
			if ( $remove_role ) {
				$user->remove_role( 'yith_pos_' . $role );

				if ( ! count( $user->roles ) ) {
					$user->add_role( 'customer' );
				}
			}
		}
	}
}

if ( ! function_exists( 'yith_pos_get_format_address' ) ) {
	/**
	 * Get address format..
	 *
	 * @param string $country The country.
	 *
	 * @return mixed|string
	 */
	function yith_pos_get_format_address( $country ) {
		$format          = '';
		$address_formats = WC()->countries->get_address_formats();
		if ( isset( $address_formats[ $country ] ) ) {
			$format = $address_formats[ $country ];
		} elseif ( isset( $address_formats['default'] ) ) {
			$format = $address_formats['default'];
		}

		return $format;
	}
}

if ( ! function_exists( 'yith_pos_get_required_gateways' ) ) {
	/**
	 * Get the list of required gateways
	 *
	 * @return array
	 */
	function yith_pos_get_required_gateways() {
		return apply_filters(
			'yith_pos_required_gateways',
			array(
				'yith_pos_cash_gateway',
				'yith_pos_chip_pin_gateway',
			)
		);
	}
}

if ( ! function_exists( 'yith_pos_get_enabled_gateways_option' ) ) {
	/**
	 * Get the list of gateways enabled
	 *
	 * @return array
	 */
	function yith_pos_get_enabled_gateways_option() {

		$pos_gateways = get_option( 'yith_pos_general_gateway_enabled' );

		if ( ! $pos_gateways ) {
			$pos_gateways = yith_pos_get_required_gateways();
			add_option( 'yith_pos_general_gateway_enabled', $pos_gateways );

		}

		return $pos_gateways;
	}
}

if ( ! function_exists( 'yith_pos_get_indexed_payment_methods' ) ) {
	/**
	 * Get the list of gateways indexed for plugin options.
	 *
	 * @param bool $all If true all WC Gateways will be retrieved.
	 *
	 * @return array
	 */
	function yith_pos_get_indexed_payment_methods( $all = false ) {
		/**
		 * @var WC_Payment_Gateway[] $payment_methods
		 */
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$indexed_payment = array();

		if ( $all ) {
			foreach ( $payment_methods as $key => $gateway ) {
				$method_title = implode( ' - ', array_filter( array_unique( array($gateway->get_method_title(), $gateway->get_title() ) ) ));
				$indexed_payment[ $key ] = $method_title;
			}
		} else {
			$pos_gateways = yith_pos_get_enabled_gateways_option();
			foreach ( $payment_methods as $key => $gateway ) {
				if ( in_array( $key, $pos_gateways, true ) ) {
					$method_title = implode( ' - ', array_filter( array_unique( array($gateway->get_method_title(), $gateway->get_title() ) ) ));
					$indexed_payment[ $key ] = $method_title;
				}
			}
		}

		return $indexed_payment;
	}
}

if ( ! function_exists( 'yith_pos_get_active_payment_methods' ) ) {
	/**
	 * Get the list of gateways active for YITH POS
	 *
	 * @return WC_Payment_Gateway[]
	 */
	function yith_pos_get_active_payment_methods() {
		$pos_gateways    = yith_pos_get_enabled_gateways_option();
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$active_payments = array();

		foreach ( $payment_methods as $key => $gateway ) {
			if ( in_array( $key, $pos_gateways, true ) ) {
				$active_payments[ $key ] = $gateway;
			}
		}

		return $active_payments;
	}
}

if ( ! function_exists( 'yith_pos_get_order_payment_methods' ) ) {
	/**
	 * Get the payment methods as array of object.
	 *
	 * @param WC_Order|int $order The order.
	 *
	 * @return mixed|void
	 */
	function yith_pos_get_order_payment_methods( $order ) {
		$payment_methods = array();
		$order_meta      = array();

		if ( is_numeric( $order ) ) {
			$order_meta = get_post_meta( $order );
		} elseif ( $order instanceof WC_Order ) {
			$order_meta = $order->get_meta_data();
		}

		if ( $order_meta ) {
			$payment_methods = array_filter(
				$order_meta,
				function ( $value, $key ) {
					if ( is_object( $value ) ) {
						return strpos( $value->key, '_yith_pos_gateway_' ) === 0;
					} else {
						return strpos( $key, '_yith_pos_gateway_' ) === 0;
					}
				},
				ARRAY_FILTER_USE_BOTH
			);

			$payment_methods = array_map(
				function ( $key, $value ) {
					if ( is_object( $value ) ) {
						return array(
							'paymentMethod' => str_replace( '_yith_pos_gateway_', '', $value->key ),
							'amount'        => $value->value,
						);
					} else {
						return array(
							'paymentMethod' => str_replace( '_yith_pos_gateway_', '', $key ),
							'amount'        => $value[0],
						);
					}
				},
				array_keys( $payment_methods ),
				array_values( $payment_methods )
			);
		}

		return apply_filters( 'yith_pos_get_order_payment_methods', $payment_methods, $order );
	}
}

if ( ! function_exists( 'yith_pos_get_order_payment_details' ) ) {
	/**
	 * Get the payment methods as array of object.
	 *
	 * @param WC_Order $order The order.
	 *
	 * @return array
	 * @since 1.0.14
	 */
	function yith_pos_get_order_payment_details( $order ) {

		$payment_details = array();

		if ( $order instanceof WC_Order ) {
			$order_meta      = $order->get_meta_data();
			$payment_methods = WC()->payment_gateways()->payment_gateways();
			$payment_methods = wp_list_pluck( $payment_methods, 'title', 'id' );
			$change          = (float) $order->get_meta( '_yith_pos_change' );

			if ( $order_meta ) {
				foreach ( $order_meta as $meta ) {
					if ( strpos( $meta->key, '_yith_pos_gateway_' ) !== false ) {
						$payment_method = str_replace( '_yith_pos_gateway_', '', $meta->key );
						$name           = $payment_methods[ $payment_method ] ?? str_replace( '_', ' ', $payment_method );
						$amount         = (float) $meta->value;

						if ( $change && 'yith_pos_cash_gateway' === $payment_method ) {
							$amount += $change;
						}

						$payment_details[] = array(
							'key'            => 'payment-method-' . $payment_method,
							'type'           => 'payment-method',
							'payment-method' => $payment_method,
							'name'           => $name,
							'amount'         => $amount,
						);
					}
				}
			}

			if ( $change ) {
				$payment_details[] = array(
					'key'    => 'change',
					'type'   => 'change',
					'name'   => __( 'Change', 'yith-point-of-sale-for-woocommerce' ),
					'amount' => $change,
				);
			}
		}

		return apply_filters( 'yith_pos_get_order_payment_details', $payment_details, $order );
	}
}

if ( ! function_exists( 'yith_pos_validate_hex' ) ) {
	/**
	 * Validates hex color code and returns proper value
	 *
	 * @see https://github.com/mpbzh/PHP-RGB-HSL-Converter
	 *
	 * @param string $hex Hex color (Format #ffffff, #fff, ffffff or fff).
	 *
	 * @return string | bool
	 */
	function yith_pos_validate_hex( $hex ) {
		// Complete patterns like #ffffff or #fff.
		if ( preg_match( '/^#([0-9a-fA-F]{6})$/', $hex ) || preg_match( '/^#([0-9a-fA-F]{3})$/', $hex ) ) {
			// Remove #.
			$hex = substr( $hex, 1 );
		}

		// Complete patterns without # like ffffff or 000000.
		if ( preg_match( '/^([0-9a-fA-F]{6})$/', $hex ) ) {
			return $hex;
		}

		// Short patterns without # like fff or 000.
		if ( preg_match( '/^([0-9a-f]{3})$/', $hex ) ) {
			// Spread to 6 digits.
			return substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
		}

		return false;
	}
}

if ( ! function_exists( 'yith_pos_hex2hsl' ) ) {
	/**
	 * Converts hex color code to RGB color.
	 *
	 * @param string $hex Hex color.
	 *
	 * @return array
	 */
	function yith_pos_hex2hsl( $hex ) {
		// Validate Hex Input.
		$hex = yith_pos_validate_hex( $hex );

		// Split input by color.
		$hex = str_split( $hex, 2 );
		// Convert color values to value between 0 and 1.
		$r = ( hexdec( $hex[0] ) ) / 255;
		$g = ( hexdec( $hex[1] ) ) / 255;
		$b = ( hexdec( $hex[2] ) ) / 255;

		return yith_pos_rgb2hsl( array( $r, $g, $b ) );
	}
}

if ( ! function_exists( 'yith_pos_rgb2hsl' ) ) {
	/**
	 * Converts RGB color to HSL color
	 *
	 * @param array $rgb RGB color.
	 *
	 * @return array
	 */
	function yith_pos_rgb2hsl( $rgb ) {
		list( $r, $g, $b ) = $rgb;

		$r /= 255;
		$g /= 255;
		$b /= 255;

		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$h   = 0;
		$s   = 0;
		$l   = ( $max + $min ) / 2;
		$d   = $max - $min;
		if ( ! $d ) {
			$h = 0;
			$s = 0;
		} else {
			$s = $d / ( 1 - abs( 2 * $l - 1 ) );
			switch ( $max ) {
				case $r:
					$h = 60 * fmod( ( ( $g - $b ) / $d ), 6 );
					if ( $b > $g ) {
						$h += 360;
					}
					break;
				case $g:
					$h = 60 * ( ( $b - $r ) / $d + 2 );
					break;
				case $b:
					$h = 60 * ( ( $r - $g ) / $d + 4 );
					break;
			}
		}

		return array( round( $h, 2 ), round( $s, 2 ), round( $l, 2 ) );
	}
}

if ( ! function_exists( 'yith_pos_hsl2rgb' ) ) {
	/**
	 * Converts HSL color to RGB color
	 *
	 * @param array $hsl HSL color.
	 *
	 * @return array
	 */
	function yith_pos_hsl2rgb( $hsl ) {
		// Fill variables $h, $s, $l by array given.
		list( $h, $s, $l ) = $hsl;

		$h /= 360;

		$r = $l;
		$g = $l;
		$b = $l;
		$v = ( $l <= 0.5 ) ? ( $l * ( 1.0 + $s ) ) : ( $l + $s - $l * $s );
		if ( $v > 0 ) {

			$m  = $l + $l - $v;
			$sv = ( $v - $m ) / $v;

			$h *= 6.0;

			$sextant = floor( $h );
			$fract   = $h - $sextant;
			$vsf     = $v * $sv * $fract;
			$mid1    = $m + $vsf;
			$mid2    = $v - $vsf;

			switch ( $sextant ) {
				case 0:
					$r = $v;
					$g = $mid1;
					$b = $m;
					break;
				case 1:
					$r = $mid2;
					$g = $v;
					$b = $m;
					break;
				case 2:
					$r = $m;
					$g = $v;
					$b = $mid1;
					break;
				case 3:
					$r = $m;
					$g = $mid2;
					$b = $v;
					break;
				case 4:
					$r = $mid1;
					$g = $m;
					$b = $v;
					break;
				case 5:
					$r = $v;
					$g = $m;
					$b = $mid2;
					break;
			}
		}
		$r = round( $r * 255, 0 );
		$g = round( $g * 255, 0 );
		$b = round( $b * 255, 0 );

		return array( $r, $g, $b );
	}
}

if ( ! function_exists( 'yith_pos_rgb2hex' ) ) {
	/**
	 * Converts RGB color to hex code
	 *
	 * @param array $rgb RGB Color.
	 *
	 * @return string
	 */
	function yith_pos_rgb2hex( $rgb ) {
		list( $r, $g, $b ) = $rgb;

		$r = round( 255 * $r );
		$g = round( 255 * $g );
		$b = round( 255 * $b );

		return '#' . sprintf( '%02X', $r ) . sprintf( '%02X', $g ) . sprintf( '%02X', $b );
	}
}

if ( ! function_exists( 'yith_pos_hsl2hex' ) ) {
	/**
	 * Converts HSL color to RGB hex code
	 *
	 * @param array $hsl HSL Color.
	 *
	 * @return string
	 */
	function yith_pos_hsl2hex( $hsl ) {
		$rgb = yith_pos_hsl2rgb( $hsl );

		return yith_pos_rgb2hex( $rgb );
	}
}

if ( ! function_exists( 'yith_pos_is_wc_admin_enabled' ) ) {
	/**
	 * Is WC Admin plugin enabled?
	 *
	 * @return bool
	 */
	function yith_pos_is_wc_admin_enabled() {
		return class_exists( 'Automattic\WooCommerce\Admin\Loader' ) && yith_pos_check_wc_admin_min_version();
	}
}

if ( ! function_exists( 'yith_pos_check_wc_admin_min_version' ) ) {
	/**
	 * Check min version for WC Admin.
	 *
	 * @return bool
	 */
	function yith_pos_check_wc_admin_min_version() {
		return defined( 'WC_ADMIN_VERSION_NUMBER' ) && version_compare( WC_ADMIN_VERSION_NUMBER, '0.24.0', '>=' );
	}
}

if ( ! function_exists( 'yith_pos_is_pos_order' ) ) {
	/**
	 * Is an order created through POS?
	 *
	 * @param int|WC_Order $order The order.
	 *
	 * @return bool
	 */
	function yith_pos_is_pos_order( $order ) {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order );

		return $order && ! ! absint( $order->get_meta( '_yith_pos_order' ) );
	}
}


if ( ! function_exists( 'yith_pos_get_cpt_object_name' ) ) {
	/**
	 * Get CPT object name.
	 *
	 * @param int    $id   The object ID.
	 * @param string $type Type.
	 *
	 * @return string
	 */
	function yith_pos_get_cpt_object_name( $id, $type ) {
		$hook = 'yith_pos_get_' . $type . '_name';
		$meta = '_name';

		$value = metadata_exists( 'post', $id, $meta ) ? get_post_meta( $id, $meta, true ) : '';

		return apply_filters( $hook, $value, $id );
	}
}

if ( ! function_exists( 'yith_pos_get_register_name' ) ) {
	/**
	 * Get register name.
	 *
	 * @param int $id The ID.
	 *
	 * @return string
	 */
	function yith_pos_get_register_name( $id ) {
		return yith_pos_get_cpt_object_name( $id, 'register' );
	}
}

if ( ! function_exists( 'yith_pos_get_store_name' ) ) {
	/**
	 * Get store name.
	 *
	 * @param int $id The ID.
	 *
	 * @return string
	 */
	function yith_pos_get_store_name( $id ) {
		return yith_pos_get_cpt_object_name( $id, 'store' );
	}
}

if ( ! function_exists( 'yith_pos_rest_product_thumbnail_size' ) ) {
	/**
	 * Get the thumbnail size.
	 *
	 * @return string
	 */
	function yith_pos_rest_product_thumbnail_size() {
		return apply_filters( 'yith_pos_rest_product_thumbnail_size', 'medium' );
	}
}

if ( ! function_exists( 'yith_pos_rest_get_product_thumbnail' ) ) {
	/**
	 * Get REST product thumbnail.
	 *
	 * @param int       $product_id   The product ID.
	 * @param int|false $variation_id The variation ID.
	 *
	 * @return mixed|void
	 */
	function yith_pos_rest_get_product_thumbnail( $product_id, $variation_id = false ) {
		$size = yith_pos_rest_product_thumbnail_size();

		$attachment_id = false;
		$image         = false;

		if ( $variation_id ) {
			$attachment_id = get_post_thumbnail_id( $variation_id );
		}

		if ( ! $attachment_id ) {
			$attachment_id = get_post_thumbnail_id( $product_id );
		}

		if ( $attachment_id ) {
			$attachment      = wp_get_attachment_image_src( $attachment_id, $size );
			$attachment_post = get_post( $attachment_id );

			if ( is_array( $attachment ) ) {
				$image = array(
					'id'                => (int) $attachment_id,
					'date_created'      => wc_rest_prepare_date_response( $attachment_post->post_date, false ),
					'date_created_gmt'  => wc_rest_prepare_date_response( strtotime( $attachment_post->post_date_gmt ) ),
					'date_modified'     => wc_rest_prepare_date_response( $attachment_post->post_modified, false ),
					'date_modified_gmt' => wc_rest_prepare_date_response( strtotime( $attachment_post->post_modified_gmt ) ),
					'src'               => current( $attachment ),
					'name'              => get_the_title( $attachment_id ),
					'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				);
			}
		}

		return apply_filters( 'yith_pos_rest_get_product_thumbnail', $image, $product_id, $variation_id );
	}
}

if ( ! function_exists( 'yith_pos_get_barcode_meta' ) ) {
	/**
	 * Return the barcode beta
	 *
	 * @return string
	 */
	function yith_pos_get_barcode_meta() {
		$meta = defined( 'YITH_YWBC_SLUG' ) ? '_ywbc_barcode_display_value' : '_sku';

		return apply_filters( 'yith_pos_barcode_custom_field', $meta );
	}
}

if ( ! function_exists( 'yith_pos_get_countries' ) ) {
	/**
	 * Get the list of countries.
	 *
	 * @return array
	 * @since 1.6.0
	 */
	function yith_pos_get_countries() {
		$wc_countries = WC()->countries->get_countries();
		$states       = WC()->countries->get_states();
		$countries    = array();

		foreach ( $wc_countries as $code => $name ) {
			$country = array(
				'code'   => $code,
				'name'   => $name,
				'states' => array(),
			);

			if ( isset( $states[ $code ] ) ) {
				foreach ( $states[ $code ] as $state_code => $state_name ) {
					$country['states'][] = array(
						'code' => $state_code,
						'name' => $state_name,
					);
				}
			}

			$countries[] = $country;
		}

		return apply_filters( 'yith_pos_get_countries', $countries );
	}
}

if ( ! function_exists( 'yith_pos_is_discount_coupon_code' ) ) {
	/**
	 * Return true if it's a POS discount coupon code.
	 *
	 * @param string $code The coupon code.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	function yith_pos_is_discount_coupon_code( $code ) {
		return strpos( $code, '_yith_pos_discount_' ) > -1;
	}
}

if ( ! function_exists( 'yith_pos_parse_product_stock' ) ) {
	/**
	 * Parse the product stock.
	 *
	 * @param WC_Product $product  The product.
	 * @param int        $store_id The store ID.
	 *
	 * @return WC_Product
	 * @since 2.0.0
	 */
	function yith_pos_parse_product_stock( $product, $store_id ) {
		$is_pos_multi_stock_enabled = 'yes' === get_option( 'yith_pos_multistock_enabled', 'no' );
		$multi_stock_condition      = get_option( 'yith_pos_multistock_condition', 'allowed' );
		$wc_notify_no_stock_amount  = wc_stock_amount( get_option( 'woocommerce_notify_no_stock_amount', 0 ) );

		if ( $is_pos_multi_stock_enabled && $product->managing_stock() ) {
			if ( $product->is_type( 'variation' ) && 'parent' === $product->get_manage_stock() ) {
				$parent = wc_get_product( $product->get_parent_id() );
				if ( $parent ) {
					$parent = yith_pos_parse_product_stock( $parent, $store_id );
					$product->set_stock_status( $parent->get_stock_status() );
					$product->set_stock_quantity( $parent->get_stock_quantity() );
				}
			} else {
				$is_multi_stock_enabled = 'yes' === $product->get_meta( '_yith_pos_multistock_enabled', true );
				if ( $is_multi_stock_enabled ) {
					$multi_stock = $product->get_meta( '_yith_pos_multistock' );
					$multi_stock = ! ! $multi_stock ? $multi_stock : array();
					if ( isset( $multi_stock[ $store_id ] ) ) {
						$product->set_stock_quantity( $multi_stock[ $store_id ] );

						if ( $product->get_stock_quantity() <= $wc_notify_no_stock_amount ) {
							$product->set_stock_status( $product->backorders_allowed() ? 'onbackorder' : 'outofstock' );
						} else {
							$product->set_stock_status( 'instock' );
						}
					} else {
						if ( 'allowed' === $multi_stock_condition ) {
							$product->set_stock_status( 'instock' );
							$product->set_manage_stock( false );
						} elseif ( 'not_allowed' === $multi_stock_condition ) {
							$product->set_stock_status( 'outofstock' );
						}
					}
				}
			}
		}

		return $product;
	}
}

if ( ! function_exists( 'yith_pos_do_rest_request' ) ) {
	/**
	 * Perform a REST request.
	 *
	 * @param string $method       Request Method.
	 * @param string $path         REST path.
	 * @param array  $query_params Query params.
	 *
	 * @return array|WP_Error
	 */
	function yith_pos_do_rest_request( $method, $path, $query_params = array() ) {
		$path         = '/' . ltrim( $path, '/' );
		$rest_request = new WP_REST_Request( $method, $path );

		if ( $query_params ) {
			$rest_request->set_query_params( $query_params );
		}

		$response = rest_do_request( $rest_request );
		$server   = rest_get_server();

		return is_wp_error( $response ) ? $response : $server->response_to_data( $response, false );
	}
}

if ( ! function_exists( 'yith_pos_is_admin_page' ) ) {
	/**
	 * Return true if it's an admin page.
	 *
	 * @param array|string $page    The page.
	 * @param string       $tab     The tab.
	 * @param string       $sub_tab The sub-tab.
	 *
	 * @return bool
	 */
	function yith_pos_is_admin_page( $page, $tab = '', $sub_tab = '' ) {
		if ( true === $page ) {
			$is_admin_page = true;
		} else {
			$screen       = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$panel        = 'yith_pos_panel';
			$panel_screen = 'yith-plugins_page_' . $panel;
			if ( ! $screen ) {
				return false;
			}
			$screen_id = $screen->id;

			if ( 'panel' === $page ) {
				$page = $panel_screen;
			} elseif ( 'any' === $page ) {
				$page = yith_pos_admin_screen_ids();
			}

			$page = (array) $page;

			$placeholders = array(
				'register'      => YITH_POS_Post_Types::REGISTER,
				'edit-register' => 'edit-' . YITH_POS_Post_Types::REGISTER,
				'store'         => YITH_POS_Post_Types::STORE,
				'edit-store'    => 'edit-' . YITH_POS_Post_Types::STORE,
				'receipt'       => YITH_POS_Post_Types::RECEIPT,
				'edit-receipt'  => 'edit-' . YITH_POS_Post_Types::RECEIPT,
			);
			$page         = array_map(
				function ( $p ) use ( $placeholders ) {
					return $placeholders[ $p ] ?? $p;
				},
				$page
			);

			$is_admin_page = ! $page || in_array( $screen_id, $page, true );
		}

		if ( $tab ) {
			if ( 'dashboard' === $tab ) {
				$is_admin_page = $is_admin_page && ( ! isset( $_GET['tab'] ) || $tab === $_GET['tab'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			} else {
				$is_admin_page = $is_admin_page && isset( $_GET['tab'] ) && $tab === $_GET['tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}

		if ( $sub_tab ) {
			$is_admin_page = $is_admin_page && isset( $_GET['sub_tab'] ) && $sub_tab === $_GET['sub_tab']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $is_admin_page;
	}
}

if ( ! function_exists( 'yith_pos_timestamp_to_datetime' ) ) {
	/**
	 * Retrieve a date object from timestamp.
	 *
	 * @param int|bool $timestamp A timestamp.
	 *
	 * @return false|WC_DateTime
	 */
	function yith_pos_timestamp_to_datetime( $timestamp ) {
		try {
			$date = ! ! $timestamp ? new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) ) : false;
			if ( $date ) {
				if ( get_option( 'timezone_string' ) ) {
					$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
				} else {
					$date->set_utc_offset( wc_timezone_offset() );
				}
			}
		} catch ( Exception $e ) {
			$date = false;
		}

		return $date;
	}
}

if ( ! function_exists( 'yith_pos_admin_date_format' ) ) {
	/**
	 * Return the admin date format.
	 *
	 * @param string $type The format type.
	 *
	 * @return string
	 */
	function yith_pos_admin_date_format( string $type = 'datetime' ): string {
		$formats = array(
			'date' => 'j M Y',
			'time' => 'H:i',
		);

		$formats['datetime'] = $formats['date'] . ' ' . $formats['time'];

		return $formats[ $type ] ?? $formats['datetime'];
	}
}

if ( ! function_exists( 'yith_pos_get_store_section_title_html' ) ) {
	/**
	 * Return the title of section
	 *
	 * @param int  $step    Step number.
	 * @param bool $publish True if published.
	 *
	 * @return string
	 */
	function yith_pos_get_store_section_title_html( $step = 1, $publish = true ) {
		$titles = array(
			'1' => array(
				'title' => $publish ? __( 'Info', 'yith-point-of-sale-for-woocommerce' ) : __( 'Step 1: Store Info', 'yith-point-of-sale-for-woocommerce' ),
				'desc'  => $publish ? __( 'General store info', 'yith-point-of-sale-for-woocommerce' ) : __( 'Enter the store details including the address, contact information and social accounts', 'yith-point-of-sale-for-woocommerce' ),
			),
			'2' => array(
				'title' => $publish ? __( 'Employees', 'yith-point-of-sale-for-woocommerce' ) : __( 'Step 2: Employees', 'yith-point-of-sale-for-woocommerce' ),
				'desc'  => $publish ? __( 'Set the employees (managers and cashiers) for this store<br/>You can choose your cashiers from the users already registered in this site or create new cashiers', 'yith-point-of-sale-for-woocommerce' ) : __( 'Assign a manager to manage all registers and add cashiers to each of them', 'yith-point-of-sale-for-woocommerce' ),
			),
			'3' => array(
				'title' => $publish ? __( 'Registers', 'yith-point-of-sale-for-woocommerce' ) : __( 'Step 3: Registers', 'yith-point-of-sale-for-woocommerce' ),
				'desc'  => $publish ? __( 'General store info', 'yith-point-of-sale-for-woocommerce' ) : __( 'Create at least one register for this store.<br/>You can set the visibility of each register to hide/show to some users', 'yith-point-of-sale-for-woocommerce' ),
			),
			'4' => array(
				'title' => __( 'Resume', 'yith-point-of-sale-for-woocommerce' ),
				'desc'  => __( 'Just one more step! Click the "Save Store" button to save your store. To make changes you can go back now or edit these from "Stores" tab.', 'yith-point-of-sale-for-woocommerce' ),
			),
		);

		$allowed_tags = array(
			'br'     => array(),
			'strong' => array(),
		);

		$title = isset( $titles[ $step ] ) ? sprintf( '<div class="yith-pos-store-metabox-title">%s</div><div class="yith-pos-store-metabox-subtitle">%s</div>', wp_kses( $titles[ $step ]['title'], $allowed_tags ), wp_kses( $titles[ $step ]['desc'], $allowed_tags ) ) : '';

		return apply_filters( 'yith_pos_store_section_title', $title, $step, $publish );
	}
}

if ( ! function_exists( 'yith_pos_get_tax_name_by_id' ) ) {
	/**
	 * Return the tax rate name.
	 *
	 * @param int $tax_id The tax rate ID.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_pos_get_tax_name_by_id( $tax_id ) {
		global $wpdb;

		$name = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate_name FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %d", $tax_id ) );

		return ! ! $name ? $name : __( 'VAT', 'yith-point-of-sale-for-woocommerce' );
	}
}

if ( ! function_exists( 'yith_pos_format_price' ) ) {
	/**
	 * Format a price with decimals.
	 *
	 * @param int|float|string $price The price.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_pos_format_price( $price ) {
		return number_format( $price, wc_get_price_decimals(), '.', '' );
	}
}

if ( ! function_exists( 'yith_pos_get_vat_field_label' ) ) {
	/**
	 * Get the label for the VAT field.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_pos_get_vat_field_label() {
		$label = get_option( 'yith_pos_vat_field_label', '' );
		$label = ! ! $label ? call_user_func( '__', $label, 'yith-point-of-sale-for-woocommerce' ) : '';

		return ! ! $label ? $label : __( 'VAT number', 'yith-point-of-sale-for-woocommerce' );
	}
}

if ( ! function_exists( 'yith_pos_is_wc_feature_enabled' ) ) {
	/**
	 * Return `true` if the feature is enabled in WooCommerce.
	 *
	 * @param string $feature The feature.
	 *
	 * @return bool
	 * @since 2.7.0
	 */
	function yith_pos_is_wc_feature_enabled( $feature ) {
		return class_exists( 'Automattic\WooCommerce\Admin\Features\Features' ) && Automattic\WooCommerce\Admin\Features\Features::is_enabled( $feature );
	}
}


if ( ! function_exists( 'yith_pos_get_current_screen_id' ) ) {
	/**
	 * Retrieve the current screen ID.
	 *
	 * @return string|false
	 * @since 2.11.0
	 */
	function yith_pos_get_current_screen_id() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

		return ! ! $screen && is_a( $screen, 'WP_Screen' ) ? $screen->id : false;
	}
}

if ( ! function_exists( 'yith_pos_translate' ) ) {
	/**
	 * Translate a label.
	 *
	 * @return string
	 */
	function yith_pos_translate( $label ) {
		return ! ! $label ? call_user_func( '__', $label, 'yith-point-of-sale-for-woocommerce' ) : '';
	}
}