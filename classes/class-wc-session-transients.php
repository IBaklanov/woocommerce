<?php
/**
 * Handle data for the current customers session.
 * Implements the WC_Session abstract class
 *
 * @class 		WC_Session_Transients
 * @version		1.7
 * @package		WooCommerce/Classes
 * @author 		WooThemes
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Session_Transients extends WC_Session {	
	
	/** customer_id */
	private $_customer_id;
	
	/** cookie name */
	private $_cookie;
	
	/** cookie expiration time in seconds */
	private $_cookie_expires;
	
	/**
	 * Constructor for the session class.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		
		$this->_cookie			= 'wc_session_cookie_' . COOKIEHASH;
		$this->_cookie_expires	= get_transient( 'wc_cookie_expires' );
		
		if ( false === $this->_cookie_expires )
    		$this->_cookie_expires = 172800;
		
		$this->_customer_id 	= $this->get_customer_id();
		$this->_data 			= maybe_unserialize( get_transient( 'wc_session_' . $this->_customer_id ) );
    	
		if ( false === $this->_data )
    		$this->_data = array();
    }
	
	/**
	 * get_customer_id function.
	 * 
	 * @access private
	 * @return mixed
	 */
	private function get_customer_id() {
		if ( $customer_id = $this->get_session_cookie() ) {
			return $customer_id;
		} elseif ( is_user_logged_in() ) {
			return get_current_user_id();
		} else {
			return $this->create_customer_id();
		}
	}
	
	/**
	 * get_session_cookie function.
	 * 
	 * @access private
	 * @return mixed
	 */
	private function get_session_cookie() {
		if ( ! isset( $_COOKIE[ $this->_cookie ] ) ) 
			return false;
		
		list( $customer_id, $expires, $hash ) = explode( '|', $_COOKIE[ $this->_cookie ] );
		
		// Validate hash
		$data 	= $customer_id . $expires;
		$rehash = hash_hmac( 'md5', $data, wp_hash( $data ) );

		if ( $hash != $rehash )
			return false;
			
		return $customer_id;
	}
	
	/**
	 * Create a unqiue customer ID and store it in a cookie, along with its hashed value and expirey date. Stored for 48hours.
	 * 
	 * @access private
	 * @return void
	 */
	private function create_customer_id() {
		$customer_id 	= wp_generate_password( 32 ); // Ensure this and the transient is < 45 chars. wc_session_ leaves 34.
		$expires 		= time() + $this->_cookie_expires;
		$data 			= $customer_id . $expires;
		$hash 			= hash_hmac( 'md5', $data, wp_hash( $data ) );
		$value 			= $customer_id . '|' . $expires . '|' . $hash;
		
		setcookie( $this->_cookie, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, false, true );
		
		return $customer_id;
	}
    
    /**
     * save_data function.
     * 
     * @access public
     * @return void
     */
    public function save_data() {
	    // Set cart data for 48 hours
		set_transient( 'wc_session_' . $this->_customer_id, $this->_data, $this->_cookie_expires );
    }
	
	/**
     * Sets cookie expiration time in seconds.
     * 
     * @access public
     * @return void
     */
	public function set_cookie_expires( $seconds = 0, $auto_extend = false ) {
		$seconds = absint($seconds);
		if ( ( $seconds > 0 ) && ( $this->_cookie_expires != $seconds ) ) {
			$this->_cookie_expires = $seconds;
			set_transient( 'wc_cookie_expires', $this->_cookie_expires );
			$this->regenerate_cookie();
		} else if ( $auto_extend ) {
			$this->regenerate_cookie();
		}
	}
	
	/**
     * Returns cookie expiration time in seconds.
     * 
     * @access public
     * @return string
     */
	public function get_cookie_expires() {
		return $this->_cookie_expires;
	}
	
	/**
	 * Regenerate cookie to store new expiration date
	 * 
	 * @access private
	 * @return void
	 */
	private function regenerate_cookie() {
		$customer_id 	= $this->get_session_cookie();
		
		if ( false === $customer_id )
			$customer_id = wp_generate_password( 32 );
		
		$expires 		= time() + $this->_cookie_expires;
		$data 			= $customer_id . $expires;
		$hash 			= hash_hmac( 'md5', $data, wp_hash( $data ) );
		$value 			= $customer_id . '|' . $expires . '|' . $hash;
		
		setcookie( $this->_cookie, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, false, true );
	}
}