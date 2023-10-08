<?php
namespace Imzers\Utils;
use \DateTime;
use \DateTimeZone;
class Datezone {
	protected $DateObject;
	
	function __construct($timezone) {
		// $timezone : Asia/Bangkok
		#$microtime = microtime(true);
		#$micro = round(floor($microtime) * 1000000);
		#$this->DateObject = new DateTime(date("Y-m-d H:i:s.{$micro}", $microtime));
		$this->DateObject = new DateTime(date("Y-m-d H:i:s"));
		$this->DateObject->setTimezone(new DateTimeZone($timezone));
	}
	public function get_dateobject_source() {
		return $this->DateObject;
	}
	
	function create_datetime_format($format) {
		// $format : YmdHisu
		return $this->DateObject->format($format);
	}
	function datetime_format_date($format, $date) {
		return $this->DateObject->createFromFormat($format, $date);
	}
	public function format($format) {
		return $this->DateObject->format($format);
	}
	public static function create_time_zone($timezone, $datetime = null) {
		if (!isset($datetime)) {
			$datetime = date('Y-m-d H:i:s');
		}
		$DateObject = new DateTime($datetime);
		$DateObject->setTimezone(new DateTimeZone($timezone));
		// TO using use @DateObject->format('Y') : Year
		return $DateObject;
	}
}
