<?php
/* 
        Faye PHP Library
    /////////////////////////////////
	PHP library for Faye.

	See the README for usage information: https://github.com/harishanchu/faye-php-server

	Copyright 2013, Harish A <harishanchu@gmail.com>. Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
*/
//todo:user authentication
//todo: encrypt messages

class FayeException extends Exception
{
}
class FayeInstance 
{	
	private static $instance = null;
	private static $host = null;
	private static $mountPoint = null;

	private function __construct() { }
	private function __clone() { }
	
	public static function get_faye()
	{
		if (self::$instance !== null) return self::$instance;

		self::$instance = new Faye(
			self::$host,
	            	self::$mountPoint
	            	);

		return self::$instance;
	}
}
class Faye
{
	public static $VERSION = '1.0.0';
	private $settings = array ();
	private $logger = null;
	
	public function __construct($host, $mountPoint, $port = '8000', $timeout = 30, $debug = false)
	{
		// Check compatibility, disable for speed improvement
		$this->check_compatibility();
		
		// Setup defaults
		$this->settings['server'] = $host;
		$this->settings['port']		= $port;
        	$this->settings['url']		= $mountPoint;
		$this->settings['debug']	= $debug;
		$this->settings['timeout']	= $timeout;
	}
	
	/**
	 * Set a logger to be informed of interal log messages.
	 */
	public function set_logger( $logger ) {
		$this->logger = $logger;
	}

	/**
	 *
	 */
	private function log( $msg ) {
		if( is_null( $this->logger ) == false ) {
			$this->logger->log( 'Faye: ' . $msg );
		}
	}
	
	/**
	* Check if the current PHP setup is sufficient to run this class
	*/
	private function check_compatibility()
	{
		if ( ! extension_loaded( 'curl' ) || ! extension_loaded( 'json' ) )
		{
			throw new FayeException('There is missing dependant extensions - please ensure both cURL and JSON modules are installed');
		}
	}
	
	/**
	 * Utility function used to create the curl object with common settings
	 */
	private function create_curl($s_url, $request_method = 'GET', $query_params = array() )
	{

		$full_url = $this->settings['server'] . ':' . $this->settings['port'] . $s_url;

		$this->log( 'curl_init( ' . $full_url . ' )' );
		
		# Set cURL opts and execute request
		$ch = curl_init();
		if ( $ch === false )
		{
			throw new FayeException('Could not initialise cURL!');
		}
		curl_setopt( $ch, CURLOPT_URL, $full_url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array ( "Content-Type: application/json" ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->settings['timeout'] );
		
		return $ch;
	}
	
	/**
	 * Utility function to execute curl and create capture response information.
	 */
	private function exec_curl( $ch ) {
		$response = array();

		$response[ 'body' ] = curl_exec( $ch );
		$response[ 'status' ] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$this->log( 'exec_curl response: ' . print_r( $response, true ) );

		curl_close( $ch );

		return $response;
	}
	
	public function trigger( $channel, $event, $data, $debug = false)
	{
		$s_url = $this->settings['url'];
		$post_params = array();
		$post_params['data']['event' ] = $event;
		$post_params['data']['message'] = $data;
		$post_params['channel' ] = '/'.$channel;
		
		$post_value = json_encode( $post_params );

		$ch = $this->create_curl( $s_url, 'POST', $post_value );

		$this->log( 'trigger POST: ' . $post_value );

		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_value );

		$response = $this->exec_curl( $ch );

		if ( $response[ 'status' ] == 200 && $debug == false )
		{
			return true;
		}
		elseif ( $debug == true || $this->settings['debug'] == true )
		{
			return $response;
		}
		else
		{
			return false;
		}		
	}
}
?>
