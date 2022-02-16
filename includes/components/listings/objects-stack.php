<?php
/**
 * Stack holder for current objects call hierarchy tree
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Define Jet_Engine_Objects_Stack class
 */
class Jet_Engine_Objects_Stack {

	/**
	 * Holds root object for the currently rendered page
	 * @var null
	 */
	private $root = null;

	/**
	 * Holds tree of called objects at the current moment.
	 *
	 * @var array
	 */
	private $stack = array();

	private $in_stack = false;

	public function __construct() {
		add_action( 'wp', array( $this, 'save_root' ) );
		add_action( 'jet-engine/listings/data/set-current-object', array( $this, 'ensure_root' ) );
		add_action( 'jet-engine/listings/frontend/setup-data', array( $this, 'increase_stack' ) );
		add_action( 'jet-engine/listings/frontend/object-done', array( $this, 'decrease_stack' ) );
	}

	/**
	 * Ensure we currently set current root
	 *
	 * @return [type] [description]
	 */
	public function ensure_root( $object ) {

		// if empty stack and empty root - we'll use current object as root
		if ( empty( $this->stack ) && ! $this->root ) {
			$this->root = $object;
		// if we on zero level of the stack and have current object - use this bject as start of stack
		} elseif ( empty( $this->stack ) ) {
			$this->stack[] = $object;
		// if we have zero level of the stack and current object is called outside of stack - we reset stack to current object
		} elseif ( ! $this->is_in_stack() ) {
			$this->stack = array( $object );
		}

	}

	/**
	 * Save root object
	 *
	 * @return [type] [description]
	 */
	public function save_root() {
		$this->root = get_queried_object();
	}

	/**
	 * Check if we curretnly processing stack item
	 *
	 * @return boolean [description]
	 */
	public function is_in_stack() {
		return $this->in_stack;
	}

	/**
	 * Add object to the stack
	 *
	 * @param  [type] $object [description]
	 * @return [type]         [description]
	 */
	public function increase_stack( $object ) {
		$this->stack[]  = $object;
		$this->in_stack = true;
	}

	/**
	 * Returns current stack
	 *
	 * @return [type] [description]
	 */
	public function get_stack() {
		return $this->stack;
	}

	/**
	 * Remove objects tree from the stack
	 *
	 * @return [type] [description]
	 */
	public function decrease_stack( $object ) {

		$reset = false;

		for ( $i = 0; $i < count( $this->stack ); $i++ ) {

			if ( isset( $this->stack[ $i ] ) && $this->stack[ $i ] === $object ) {
				$reset = true;
			}

			if ( $reset ) {
				unset( $this->stack[ $i ] );
			}

		}

		$this->stack = array_merge( array(), $this->stack );

		$this->in_stack = false;
	}

	/**
	 * returns current stack merged with root object
	 *
	 * @return [type] [description]
	 */
	public function get_full_stack() {

		$initial = array();

		if ( is_user_logged_in() ) {
			$initial[] = wp_get_current_user();
		}

		if ( $this->root ) {
			$initial[] = $this->root;
		}

		return array_merge( $initial, $this->stack );

	}

}
