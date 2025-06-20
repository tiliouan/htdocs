<?php
/**
 * Licence Banner template
 *
 * @var string $mode
 * @var string $slug
 * @var string $plugin_name
 * @var string $activation_url
 * @var string $landing_url
 * @package YITH/PluginUpgrade
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

$classes = implode( ' ', array( 'yith-plugin-upgrade-licence-banner', "yith-plugin-upgrade-licence-banner--$mode", 'yith-plugin-ui' ) );
?>
	<div class="<?php echo esc_attr( $classes ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>" data-security="<?php echo esc_attr( wp_create_nonce( $slug ) ); ?>">
		<div class="yith-plugin-upgrade-licence-banner__icon">
			<svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path clip-rule="evenodd" fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"></path>
			</svg>
		</div>		<div class="yith-plugin-upgrade-licence-banner__content">
			<div class="yith-plugin-upgrade-licence-banner__content__head">
				<!-- License messages removed -->
			</div>
			<div class="yith-plugin-upgrade-licence-banner__content__message">
				<!-- License reminder messages removed -->
			</div>
		</div>
		<div class="yith-plugin-upgrade-licence-banner__actions">
			<a class="yith-plugin-fw__button yith-plugin-fw__button--primary" href="<?php echo esc_attr( $activation_url ); ?>"><?php echo esc_html__( 'Enter your license key', 'yith-plugin-upgrade-fw' ); ?></a>
			<a class="yith-plugin-fw__button yith-plugin-fw__button--secondary" href="<?php echo esc_attr( $landing_url ); ?>" target="_blank"><?php echo esc_html__( 'Buy a license', 'yith-plugin-upgrade-fw' ); ?></a>
		</div>
	</div>
<?php

