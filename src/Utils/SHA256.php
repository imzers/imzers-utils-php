<?php
namespace Imzers\Utils;
class SHA256 {
	public $uppercase = NULL;
	function __construct($uppercase = NULL) {
		if (!isset($uppercase)) {
			$this->uppercase = FALSE;
		} else {
			$this->uppercase = TRUE;
		}
	}
	function set_uppercase($uppercase) {
		$this->uppercase = $uppercase;
		return $this;
	}
	function set_data($data) {
		$this->data = $data;
		return $this;
	}
	function set_secret($secret) {
		$this->secret = $secret;
		return $this;
	}
	function create_sha256() {
		return hash_hmac('sha256', $this->data, $this->secret, $this->uppercase);
	}
}