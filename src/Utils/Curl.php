<?php
namespace Imzers\Utils;
Class Curl {
	public $instance = NULL;
	public $endpoint;
	public $error = FALSE, $error_msg = array();
	public $UA = "Api.Context/UA (By imzers[at]gmail.com)";
	protected $curl_collect = array(
		'http_body'					=> FALSE
	);
	function __construct() {
		# Set Endpoint
		$this->set_endpoint('');
		
		# Headers
		$this->set_headers();
		$this->add_headers('Content-type', 'application/json;charset=utf-8');
	}
	public static function get_instance($instance = null) {
		if (!isset($instance)) {
			$instance = new Curl();
		}
		return $instance;
	}
	
	
	
	function set_endpoint($endpoint) {
		$this->endpoint = $endpoint;
		return $this;
	}
	//=======================================================================================================================
	function create_curl_request($action, $url, $UA, $headers = null, $params = array(), $timeout = 30) {
		$this->curl_collect['error_code'] = 0;
		$this->curl_collect['error_msg'] = '';
		
		
		$cookie_file = (dirname(__FILE__).'/cookies.txt');
		$url = (is_string($url) ? $url : '');
		if (strlen($url) > 0) {
			$url = str_replace( "&amp;", "&", urldecode(trim($url)) );
		} else {
			return FALSE;
		}
		$ch = curl_init();
		switch (strtolower($action)) {
			case 'get':
				if ((is_array($params) || is_object($params)) && (count($params) > 0)) {
					$params = (array)$params;
					$Querystring = http_build_query($params);
					$url .= "?";
					$url .= $Querystring;
				}
			break;
			case 'post':
			default:
				$url .= "";
			break;
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		if ($headers != null) {
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		} else {
			curl_setopt($ch, CURLOPT_HEADER, false);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $UA);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_COOKIE, $cookie_file);
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		$post_fields = '';
		switch (strtolower($action)) {
			case 'get':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			break;
			case 'put':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			break;
			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
			case 'post':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			break;
		}
		switch (strtolower($action)) {
			case 'get':
			default:
				curl_setopt($ch, CURLOPT_POST, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, null);
			break;
			case 'put':
			case 'post':
				if ((is_array($headers) && count($headers) > 0)) {
					$curl_content_type = array(
						'name'				=> '',
						'value'				=> '',
					);
					foreach ($headers as $heval) {
						$getContentType = explode(":", $heval);
						if (isset($getContentType[0]) && isset($getContentType[1])) {
							$curl_content_type['name']		= strtolower(trim($getContentType[0]));
							$curl_content_type['value']		= sprintf("%s", $getContentType[1]);
							$curl_content_type['value']		= strtolower(trim($curl_content_type['value']));
							if (strtolower($curl_content_type['name']) !== 'content-type') {
								continue;
							} else {
								if (strpos($curl_content_type['value'], 'application/xml') !== FALSE) {
									if (is_string($params) || is_numeric($params)) {
										$post_fields = sprintf("%s", $params);
									} else if (is_array($params) || is_object($params)) {
										$post_fields = '';
									} else {
										$post_fields = '';
									}
								} else if (strpos($curl_content_type['value'], 'application/json') !== FALSE) {
									if (is_array($params) || is_object($params)) {
										$post_fields = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
									} else if (is_string($params) || is_numeric($params)) {
										$post_fields = sprintf("%s", $params);
									} else {
										$post_fields = '';
									}
								} else if (strpos($curl_content_type['value'], 'multipart/form-data') !== FALSE) {
									$params = (is_object($params) ? (array)$params : $params);
									if (is_array($params)) {
										$post_fields = http_build_query($params);
									} else if (is_string($params) || is_numeric($params)) {
										$post_fields = sprintf("%s", $params);
									} else {
										$post_fields = '';
									}
								} else if (strpos($curl_content_type['value'], 'application/x-www-form-urlencoded') !== FALSE) {
									if (is_array($params)) {
										$post_fields = http_build_query($params);
									} else if (is_string($params) || is_numeric($params)) {
										$post_fields = sprintf("%s", $params);
									} else {
										$post_fields = '';
									}
								} else {
									if (is_array($params)) {
										$post_fields = http_build_query($params);
									} else if (is_string($params) || is_numeric($params)) {
										$post_fields = sprintf("%s", $params);
									} else {
										$post_fields = '';
									}
								}
							}
						}
					}
				} else if ((!empty($params)) || ($params != '')) {
					$post_fields = $params;
				}
				
				
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
			break;
		}
		
		// Get Response
		try {
			$this->curl_collect['http_body'] = curl_exec($ch);
		} catch (Exception $ex) {
			throw $ex;
			$this->curl_collect['http_body'] = FALSE;
		}
		$this->curl_collect['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_error($ch)) {
			$this->curl_collect['error_code'] = curl_errno($ch);
			$this->curl_collect['error_msg'] = curl_error($ch);
		}
		$this->curl_collect['mixed_info'] = curl_getinfo($ch);
		$this->curl_collect['header_size'] = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->curl_collect['header_string'] = substr($this->curl_collect['http_body'], 0, $this->curl_collect['header_size']);
		$this->curl_collect['header_content'] = $this->get_headers_from_curl_response($this->curl_collect['header_string']);
		$header_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (count($this->curl_collect['header_content']) > 1) {
			$this->curl_collect['header_content'] = end($this->curl_collect['header_content']);
		}
		$this->curl_collect['body'] = substr($this->curl_collect['http_body'], $this->curl_collect['header_size']);
		curl_close ($ch);
		$return = array(
			'request'		=> array(
				'method'			=> $action,
				'host'				=> $url,
				'header'			=> $headers,
				'body'				=> $post_fields,
			),
			'response'		=> array(),
		);
		if (!empty($this->curl_collect['http_body']) || ($this->curl_collect['http_body'] != '')) {
			$return['response']['code'] = (int)$header_code;
			$return['response']['header'] = array(
				'size' 			=> $this->curl_collect['header_size'], 
				'string' 		=> $this->curl_collect['header_string'],
				'content' 		=> $this->curl_collect['header_content'],
			);
			$return['response']['body'] = $this->curl_collect['body'];
			return $return;
		}
		return false;
	}
	private static function get_headers_from_curl_response($headerContent) {
		$headers = array();
		// Split the string on every "double" new line.
		$arrRequests = explode("\r\n\r\n", $headerContent);
		// Loop of response headers. The "count($arrRequests) - 1" is to 
		// avoid an empty row for the extra line break before the body of the response.
		for ($index = 0; $index < (count($arrRequests) - 1); $index++) {
			foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
				if ($i === 0) {
					$headers[$index]['http_code'] = $line;
				} else {
					list ($key, $value) = explode(': ', $line);
					$headers[$index][$key] = $value;
				}
			}
		}
		return $headers;
	}
	public function generate_curl_headers($headers = null) {
		if (!isset($headers)) {
			$headers = $this->headers;
		}
		return $this->create_curl_headers($headers);
	}
	public function create_curl_headers($headers = array()) {
		$curlheaders = array();
		foreach ($headers as $ke => $val) {
			$curlheaders[] = "{$ke}: {$val}";
		}
		return $curlheaders;
	}
	//------- utils
	function sanitize_file_name( $filename ) {
		$filename_raw = $filename;
		$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
		foreach ($special_chars as $chr) {
			$filename = str_replace($chr, '', $filename);
		}
		$filename = preg_replace('/[\s-]+/', '-', $filename);
		$filename = trim($filename, '.-_');
		$filename;
	}
	function sanitize_url_parameter($params_input = array()) {
		$sanitized = [];
		if (count($params_input) > 0) {
			foreach ($params_input as $key => $keval) {
				if (!is_array($keval) || (!is_object($keval))) {
					//$keval = filter_var($keval, FILTER_SANITIZE_STRING);
					$keval = filter_var($keval, FILTER_SANITIZE_URL);
				}
				$sanitized[$key] = $keval;
			}
		}
		return $sanitized;
	}
	//------
	function set_headers($headers = array()) {
		$this->headers = $headers;
		return $this;
	}
	function reset_headers() {
		$this->headers = null;
		return $this;
	}
	function add_headers($key, $val) {
		if (!isset($this->headers)) {
			$this->headers = $this->get_this_headers();
		}
		$add_header = array($key => $val);
		$this->headers = array_merge($add_header, $this->headers);
	}
	function get_this_headers() {
		return $this->headers;
	}
	// Utils
	function create_permalink($url) {
		$url = strtolower($url);
		$url = preg_replace('/&.+?;/', '', $url);
		$url = preg_replace('/\s+/', '_', $url);
		$url = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '_', $url);
		$url = preg_replace('|%|', '_', $url);
		$url = preg_replace('/&#?[a-z0-9]+;/i', '', $url);
		$url = preg_replace('/[^%A-Za-z0-9 \_\-]/', '_', $url);
		$url = preg_replace('|_+|', '-', $url);
		$url = preg_replace('|-+|', '-', $url);
		$url = trim($url, '-');
		$url = (strlen($url) > 128) ? substr($url, 0, 128) : $url;
		return $url;
	}
	
	
	
}













