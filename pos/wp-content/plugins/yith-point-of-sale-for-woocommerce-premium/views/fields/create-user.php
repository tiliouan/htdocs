<?php
/**
 * Create user field.
 *
 * @var string $button_text         The text of the button.
 * @var string $button_close_text   The close text of the button.
 * @var string $save_text           The text of the save button.
 * @var string $title               The title of the form.
 * @var string $user_type           The type of the user.
 * @var string $select2_to_populate The ID of the select2 to populate after creation.
 * @var int    $form_id             The unique ID of the form.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\POS\Views\Fields
 */

defined( 'YITH_POS' ) || exit();

$required_message = yith_pos_get_required_field_message();

$fields = array(
	array(
		'id'       => "yith-pos-create-user-form-{$form_id}__user_login",
		'type'     => 'text',
		'label'    => __( 'Username', 'yith-point-of-sale-for-woocommerce' ),
		'required' => true,
		'class'    => 'yith-pos-create-user-form__field yith-pos-create-user-form__field--to-reset yith-pos-required-field validate-user',
		'desc'     => __( 'Enter a username to identify this user.', 'yith-point-of-sale-for-woocommerce' ) . $required_message,
		'data'     => array(
			'message' => __( 'The username is required and must be unique.', 'yith-point-of-sale-for-woocommerce' ),
			'name'    => 'user_login',
		),
	),
	array(
		'id'       => "yith-pos-create-user-form-{$form_id}__user_pass",
		'type'     => 'password',
		'label'    => __( 'Password', 'yith-point-of-sale-for-woocommerce' ),
		'required' => true,
		'class'    => 'yith-pos-create-user-form__field yith-pos-create-user-form__field--to-reset yith-pos-required-field',
		'desc'     => __( 'Enter a password for this user.', 'yith-point-of-sale-for-woocommerce' ) . $required_message,
		'data'     => array(
			'message' => __( 'You need to set a password to the user.', 'yith-point-of-sale-for-woocommerce' ),
			'name'    => 'user_pass',
		),
	),
	array(
		'id'       => "yith-pos-create-user-form-{$form_id}__user_email",
		'type'     => 'text',
		'label'    => __( 'User Email', 'yith-point-of-sale-for-woocommerce' ),
		'required' => true,
		'class'    => 'yith-pos-create-user-form__field yith-pos-create-user-form__field--to-reset yith-pos-required-field validate-user validate-email',
		'desc'     => __( 'Enter the e-mail address of this user.', 'yith-point-of-sale-for-woocommerce' ) . $required_message,
		'data'     => array(
			'message' => __( 'Enter a valid email. Must be unique for each user.', 'yith-point-of-sale-for-woocommerce' ),
			'name'    => 'user_email',
		),
	),
	array(
		'id'       => "yith-pos-create-user-form-{$form_id}__first_name",
		'type'     => 'text',
		'label'    => __( 'First Name', 'yith-point-of-sale-for-woocommerce' ),
		'required' => true,
		'class'    => 'yith-pos-create-user-form__field yith-pos-create-user-form__field--to-reset yith-pos-required-field',
		'desc'     => __( 'Enter the first name of this user.', 'yith-point-of-sale-for-woocommerce' ) . $required_message,
		'data'     => array(
			'message' => __( 'You need to enter a name for this user.', 'yith-point-of-sale-for-woocommerce' ),
			'name'    => 'first_name',
		),
	),
	array(
		'id'       => "yith-pos-create-user-form-{$form_id}__last_name",
		'type'     => 'text',
		'label'    => __( 'Last Name', 'yith-point-of-sale-for-woocommerce' ),
		'required' => true,
		'class'    => 'yith-pos-create-user-form__field yith-pos-create-user-form__field--to-reset yith-pos-required-field',
		'desc'     => __( 'Enter the last name of this user.', 'yith-point-of-sale-for-woocommerce' ) . $required_message,
		'data'     => array(
			'message' => __( 'You need to enter a last name for this user.', 'yith-point-of-sale-for-woocommerce' ),
			'name'    => 'last_name',
		),
	),
);
?>
<div class="yith-pos-create-user-form__container" data-select2-to-populate="<?php echo esc_attr( $select2_to_populate ); ?>">
	<span class="yith-pos-create-user-form__add yith-add-button"
			data-text="<?php echo esc_attr( $button_text ); ?>"
			data-close-text="<?php echo esc_attr( $button_close_text ); ?>"
	><?php echo esc_html( $button_text ); ?></span>

	<div class="yith-pos-create-user-form">

		<?php if ( $title ) : ?>
			<h3><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php
		foreach ( $fields as $field ) :
			$extra_class = ! empty( $field['required'] ) ? 'yith-plugin-fw--required' : '';
			?>
			<div class="yith-pos-create-user-form__row <?php echo esc_attr( $extra_class ); ?>">
				<label class="yith-pos-create-user-form__label"><?php echo esc_html( $field['label'] ); ?></label>
				<div class="yith-pos-create-user-form__field-container">
					<?php yith_plugin_fw_get_field( $field, true ); ?>
					<span class="description"><?php echo ! empty( $field['desc'] ) ? wp_kses_post( $field['desc'] ) : ''; ?></span>
				</div>
			</div>
		<?php endforeach; ?>

		<input type="hidden" class="yith-pos-create-user-form__field" data-name="user_type" value="<?php echo esc_attr( $user_type ); ?>">
		<input type="hidden" class="yith-pos-create-user-form__field" data-name="security" value="<?php echo esc_attr( wp_create_nonce( 'yith-pos-create-user' ) ); ?>">
		<input type="hidden" class="yith-pos-create-user-form__field" data-name="action" value="yith_pos_create_user">

		<div class="yith-pos-create-user-form__message"></div>
		<div class="yith-pos-create-user-form__actions">
			<span class="yith-pos-create-user-form__save yith-save-button"><?php echo esc_html( $save_text ); ?></span>
		</div>
	</div>
</div>
