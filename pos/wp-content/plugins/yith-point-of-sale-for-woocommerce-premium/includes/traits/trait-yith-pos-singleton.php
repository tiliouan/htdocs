<?php
/**
 * Singleton class trait.
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\POS\Traits
 */

/**
 * Singleton trait.
 */
trait YITH_POS_Singleton_Trait {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {
	}

	/**
	 * Get class instance.
	 *
	 * @return self
	 */
	final public static function get_instance() {
		return ! is_null( static::$instance ) ? static::$instance : static::$instance = new static();
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {
	}

	/**
	 * Prevent un-serializing.
	 */
	public function __wakeup() {
		yith_pos_doing_it_wrong( get_called_class() . '::' . __FUNCTION__, 'Unserializing instances of this class is forbidden.', '1.0.0' );
	}
}
