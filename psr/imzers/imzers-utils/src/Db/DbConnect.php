<?php
namespace Imzers\Db;
use \mysqli;
Class DbConnect {
	public static $instance;
	protected $db_resource;
	public $db_result;
	public $config = array();
	public $queries = array();
	function __construct($config = array()) {
		$this->config = $config;
	}
	/***********************************************************************
	* Init Instance
	***********************************************************************/
	public static function init($configs) {
        if (!self::$instance) {
            self::$instance = new DbConnect($configs);
        }
        return self::$instance;
    }
	/***********************************************************************
	* Databases
	************************************************************************/
	function db_connect($database, $type = 'mysql') {
		$database = array(
			'db_host'				=> (isset($database['db_host']) ? $database['db_host'] : ''),
			'db_port'				=> (isset($database['db_port']) ? $database['db_port'] : ''),
			'db_user'				=> (isset($database['db_user']) ? $database['db_user'] : ''),
			'db_pass'				=> (isset($database['db_pass']) ? $database['db_pass'] : ''),
			'db_name'				=> (isset($database['db_name']) ? $database['db_name'] : ''),
		);
		//ini_set('display_errors', false);
		if ($type != 'mysql') {
			try {
				if (!$this->db_resource = odbc_connect($database['db_host'], $database['db_user'], $database['db_pass'])) {
					$this->add_error('Could not connect to Microsoft SQL Server.');
					return false;
				}
			} catch (Exception $e) {
				$this->add_error($e);
				$this->error("{$e}");
			}
		} else {
			try {
				$this->db_resource = new mysqli($database['db_host'], $database['db_user'], $database['db_pass'], $database['db_name']);
				if ($this->db_resource->connect_error) {
					$this->add_error('Could not connect to database server.');
					return false;
				}
				if (!$this->db_query("SET NAMES utf8")) { $this->add_error('Cannot set collation name as UTF-8.'); }
			} catch (Exception $e) {
				$this->add_error($e);
				$this->error("{$e}");
			}
		}
		return true;
	}
	function sql_addslashes($sql, $type = 'mysql') {
		switch ($type) {
			case 'mysql':
			default:
				if (!isset($result)) {
					$result = $this->db_resource;
				}
				$return = $result->real_escape_string($sql);
			break;
			case 'mssql':
				$return = str_replace("'", "", $sql);
				/*$return = $this->checkApostrophes($return);*/
			break;
		}
		return $return;
	}
	function db_free($type = 'mysql', $result = null) {
		if (!isset($result)) {
			$result = $this->db_result;
		}
		if ($type != 'mysql') {
			return odbc_free_result($result);
		}
	}
	function db_close($type = 'mysql', $resource = null) {
		if (!isset($resource)) {
			$resource = $this->db_resource;
		}
		if ($type != 'mysql') {
			return odbc_close($resource);
		} else {
			return $resource->close();
		}
	}
	function db_insert_id($type = 'mysql', $resource = null) {
		if (!isset($resource)) {
			$resource = $this->db_resource;
		}
		switch ($type) {
			case 'mysql': 
			default:
				return $resource->insert_id; 
			break;
			case 'mssql':
				return $this->db_query("SELECT @"."@IDENTITY AS Ident", 'mssql');
			break;
		}
	}
	function db_prepare($sql, $type = 'mysql', $resources = null) {
		if (!isset($resources)) {
			$resources = $this->db_resource;
		}
		$stmt = null;
		switch ($type) {
			case 'mysql':
			default:
			$stmt = $resources->prepare($sql);
			break;
			case 'mssql':
			$stmt = odbc_prepare($resources, $sql);
			break;
		}
		return $stmt;
	}
	function db_execute($type, $stmt, $arrayVal = array(), $resources = null) {
		if (!isset($resources)) {
			$resources = $this->db_resource;
		}
		$return = false;
		switch ($type) {
			case 'mysql':
			default:
			$return = $resources->execute();
			break;
			case 'mssql':
			$return = odbc_execute($stmt, $arrayVal);
			break;
		}
		return $return;
	}
	function db_query($sql, $type = 'mysql', $resources = null) {
		if (!isset($resources)) {
			$resources = $this->db_resource;
		}
		array_push($this->queries, $sql);
		switch ($type) {
			case 'mssql':
			if ($this->db_result = odbc_exec($resources, $sql)) {
				return $this->db_result;
			}
			break;
			case 'mysql':
			default:
			if ($this->db_result = $resources->query($sql)) {
				return $this->db_result;
			}
			break;
		}
		return false;
	}
	function db_fetch($type = 'mysql', $result = null) {
		if (!isset($result)) {
			$result = $this->db_result;
		}
		switch ($type) {
			case 'mysql':
			default:
				return $result->fetch_assoc();
			break;
			case 'mssql':
				return odbc_fetch_array($result);
			break;
		}
	}
	function db_num_rows($type = 'mysql', $result = null) {
		if (!isset($result)) {
			$result = $this->db_result;
		}
		switch ($type) {
			case 'mysql':
			default:
			return $result->num_rows;
			break;
			case 'mssql':
			return odbc_num_rows($result);
			break;
		}
	}
	function db_error($type = 'mysql', $resources = null) {
		if (!isset($resources)) {
			$resources = $this->db_resource;
		}
		switch ($type) {
			case 'mysql':
			default:
				return $resources->error;
			break;
			case 'mssql':
				return "ERROR!";
			break;
		}
		return true;
	}
	
	
}



