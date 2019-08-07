<?php
namespace Imzers\Utils;
class Utils {
	private static $instance;
	function __construct() {
		$php_input_request = self::php_input_request();
		$this->set_php_input_request($php_input_request);
	}
	public static function get_instance() {
		if (!self::$instance) {
            self::$instance = new Utils();
        }
		//self::$instance->set_instance(self::$instance->table, self::$instance->transaction_data, self::$instance->merchant_system);
        return self::$instance;
	}
	// Set utils instance
	function set_php_input_request($php_input_request) {
		$this->php_input_request = $php_input_request;
		return $this;
	}
	function get_php_input_request() {
		return $this->php_input_request;
	}
	//--------------------------------------------------
	// Utils
	//--------------------------------------------------
	public static function parse_raw_http_request($content_type, array &$a_data) {
		$input = file_get_contents('php://input');
		preg_match('/boundary=(.*)$/', $content_type, $matches);
		$boundary = (isset($matches[1]) ? $matches[1] : '');
		$a_blocks = preg_split("/-+{$boundary}/", $input);
		array_pop($a_blocks);
		// loop data blocks
		foreach ($a_blocks as $id => $block) {
			if (empty($block)) {
				continue;
			}
			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== FALSE) {
				// match "name", then everything after "stream" (optional) except for prepending newlines 
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
			} else {
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
			}
			$a_data[$matches[1]] = $matches[2];
		}
	}
	public static function php_input_request() {
		###############################
		# Request Input by imzers:
		###############################
		$RequestInputParams = array();
		$RequestInput = array();
		$incomingHeaders = self::apache_headers();
		if (isset($incomingHeaders['Content-Type'])) {
			if ((!is_array($incomingHeaders['Content-Type'])) && (!is_object($incomingHeaders['Content-Type']))) {
				if (0 === strpos($incomingHeaders['Content-Type'], 'application/json')) {
					$RequestInput = file_get_contents("php://input");
					if (!$RequestInputJson = json_decode($RequestInput, true)) {
						parse_str($RequestInput, $RequestInputParams);
					} else {
						$RequestInputParams = $RequestInputJson;
					}
				} else if (0 === strpos($incomingHeaders['Content-Type'], 'application/x-www-form-urlencoded')) {
					$RequestInput = file_get_contents("php://input");
					parse_str($RequestInput, $RequestInputParams);
				} else if (0 === strpos($incomingHeaders['Content-Type'], 'application/xml')) {
					$RequestInput = file_get_contents("php://input");
					$RequestInputParams = $RequestInput;
				} else if(0 === strpos($incomingHeaders['Content-Type'], 'mutipart/formdata')) {
					$RequestInput = fread(STDIN, 4096000);
					$RequestInputParams = json_decode($RequestInput, true);
				} else {
					//self::parse_raw_http_request($incomingHeaders['Content-Type'], $RequestInput);
					$RequestInputParams = $RequestInput;
					if ($_SERVER['REQUEST_METHOD'] == 'POST') {
						if (isset($_POST) && (count($_POST) > 0)) {
							foreach ($_POST as $k => $v) {
								$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
							}
						}
						if (isset($_FILES) && (count($_FILES) > 0)) {
							foreach ($_FILES as $k => $v) {
								$RequestInputParams = array_merge(array($k => $v), $RequestInputParams);
							}
						}
					}
				}
			}
		} else {
			$RequestInput = file_get_contents("php://input");
			parse_str($RequestInput, $RequestInputParams);
		}
		$params['input'] = $RequestInput;
		$params['header'] = $incomingHeaders;
		$params['body'] = $RequestInputParams;
		return $params;
	}
	public static function php_input_querystring() {
		$__GET = (isset($_GET) ? $_GET : array());
		$request_uri = ((isset($_SERVER['REQUEST_URI']) && (!empty($_SERVER['REQUEST_URI']))) ? $_SERVER['REQUEST_URI'] : '');
		$query_string = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '');
		parse_str(parse_url(html_entity_decode($request_uri), PHP_URL_QUERY), $array);
		if (count($array) > 0) {
			foreach ($array as $key => $val) {
				$__GET[$key] = $val;
			}
		}
		return $__GET;
	}
	public static function _GET(){
		$__GET = (isset($_GET) ? $_GET : array());
		$request_uri = ((isset($_SERVER['REQUEST_URI']) && (!empty($_SERVER['REQUEST_URI']))) ? $_SERVER['REQUEST_URI'] : '');
		$_get_str = explode('?', $request_uri);
		if( !isset($_get_str[1]) ) return $__GET;
		$params = explode('&', $_get_str[1]);
		foreach ($params as $p) {
			$parts = explode('=', $p);
			$parts[0] = (is_string($parts[0]) ? strtolower($parts[0]) : $parts[0]);
			$__GET[$parts[0]] = isset($parts[1]) ? $parts[1] : '';
		}
		return $__GET;
	}
	public static function apache_headers() {
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			$out = array();
			foreach ($headers AS $key => $value) {
				$key = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
				$out[$key] = $value;
			}
			if	(isset($_SERVER['CONTENT_TYPE'])) {
				$out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
			}
			if (isset($_ENV['CONTENT_TYPE'])) {
				$out['Content-Type'] = $_ENV['CONTENT_TYPE'];
			}
		} else {
			$out = array();
			if	(isset($_SERVER['CONTENT_TYPE'])) {
				$out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
			}
			if (isset($_ENV['CONTENT_TYPE'])) {
				$out['Content-Type'] = $_ENV['CONTENT_TYPE'];
			}
			if (isset($_SERVER)) {
				if (count($_SERVER) > 0) {
					foreach ($_SERVER as $key => $value) {
						if (substr($key, 0, 5) == "HTTP_") {
							$key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
							$out[$key] = $value;
						}
					}
				}
			}
		}
		return $out;
	}
	
	//-------------------------
	function custom_file_exists($file_path='') {
		$file_exists=false;
		//clear cached results
		//clearstatcache();
		//trim path
		$file_dir=trim(dirname($file_path));
		//normalize path separator
		$file_dir=str_replace('/',DIRECTORY_SEPARATOR,$file_dir).DIRECTORY_SEPARATOR;
		//trim file name
		$file_name=trim(basename($file_path));
		//rebuild path
		$file_path=$file_dir."{$file_name}";
		//If you simply want to check that some file (not directory) exists, 
		//and concerned about performance, try is_file() instead.
		//It seems like is_file() is almost 2x faster when a file exists 
		//and about the same when it doesn't.
		$file_exists=is_file($file_path);
		//$file_exists=file_exists($file_path);
		return $file_exists;
	}
	
	
	
	//=====================================================
	public static function weblog_request($db_instance, $input_params = array()) {
		$instance = self::get_instance();
		$sql_params = array(
			'request_header'		=> (isset($input_params['header']) ? $input_params['header'] : array()),
			'request_input'			=> (isset($input_params['input']) ? $input_params['input'] : ''),
			'request_body'			=> (isset($input_params['body']) ? $input_params['body'] : array()),
		);
		$sql_params['request_header'] = (is_array($sql_params['request_header']) ? json_encode($sql_params['request_header'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : '');
		$sql_params['request_input'] = (is_array($sql_params['request_input']) ? json_encode($sql_params['request_input'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : $sql_params['request_input']);
		$sql_params['request_body'] = (is_array($sql_params['request_body']) ? json_encode($sql_params['request_body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) : '');
		$sql = sprintf("INSERT INTO weblog_request(request_header, request_input, request_body, request_datetime) VALUES('%s', '%s', '%s', NOW())",
			$db_instance->sql_addslashes($sql_params['request_header'], 'mysql'),
			$db_instance->sql_addslashes($sql_params['request_input'], 'mysql'),
			$db_instance->sql_addslashes($sql_params['request_body'], 'mysql')
		);
		try {
			$db_instance->db_query($sql);
		} catch (Exception $ex) {
			throw $ex;
			return false;
		}
		$new_insert_id = $db_instance->db_insert_id();
		return $new_insert_id;
	}
}