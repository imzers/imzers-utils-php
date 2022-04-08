<?php
namespace Imzers\Utils;
class Client_ip {
	private static $instance;
	private static $ip_address;
	function __construct() {
		
	}
	private static function get_instance() {
		$instance = new Client_ip();
		return $instance;
	}
	public static function set_ip_address($ip_address) {
		self::$ip_address = $ip_address;
	}
	public function get_ip_address() {
		return $this->ip_address;
	}
	
	public static function set_client_ip($data = null) {
		self::set_ip_address($data);
		// Get user IP address
		$ip = self::$ip_address;
		$ip = (isset($ip) ? $ip : '0.0.0.0');
		if (strpos($ip, ',')) {
			$ip2 = explode(',', $ip);
			$ip = $ip2[0];
			if(strpos($ip, '192.168.') !== false && isset($ip2[1])) {
				$ip = $ip2[1];
			} elseif(strpos($ip, '10.') !== false && isset($ip2[1])) {
				$ip = $ip2[1];
			} elseif(strpos($ip, '172.16.') !== false && isset($ip2[1])) {
				$ip = $ip2[1];
			}
		}
		$ip = filter_var($ip, FILTER_VALIDATE_IP);
		$ip = ($ip === false) ? '0.0.0.0' : $ip;
		return $ip;
	}
	function check_ip_version($type, $ip) {
		switch($type) {
			case 'ipv6':
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
					return true;
				} else {
					return false;
				}
			break;
			case 'ipv4':
			default:
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
					return true;
				} else {
					return false;
				}
			break;
		}
	}
}

