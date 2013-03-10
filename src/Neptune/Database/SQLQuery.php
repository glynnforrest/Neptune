<?php

namespace Neptune\Database;

use Neptune\Database\DatabaseFactory;
use Neptune\Database\Drivers\DatabaseDriver;

/**
 * SQLQuery
 * @author Glynn Forrest <me@glynnforrest.com>
 */
abstract class SQLQuery {

	protected $query = array();
	protected $type;
	protected $select_verbs = array('DISTINCT', 'FIELDS', 'FROM',
		'JOIN', 'WHERE', 'ORDER BY', 'LIMIT', 'OFFSET');
	protected $insert_verbs = array('INTO', 'FIELDS');
	protected $update_verbs = array('TABLES', 'FIELDS', 'WHERE');
	protected $delete_verbs = array('FROM', 'WHERE');
	protected $db;

	protected function __construct($type, DatabaseDriver $db) {
		$this->type = $type;
		$this->db = $db;
	}

	protected function formatQueryString() {
		switch ($this->type) {
			case 'SELECT':
				return $this->formatSelectString();
			case 'INSERT':
				return $this->formatInsertString();
			case 'UPDATE':
				return $this->formatUpdateString();
			case 'DELETE':
				return $this->formatDeleteString();
		}
	}

	public static function select($db = null) {
		$db = DatabaseFactory::getDriver($db);
		$class = $db->getBuilderName();
		return new $class('SELECT', $db);
	}

	public static function insert($db = null) {
		$db = DatabaseFactory::getDriver($db);
		$class = $db->getBuilderName();
		return new $class('INSERT', $db);
	}

	public static function update($db = null) {
		$db = DatabaseFactory::getDriver($db);
		$class = $db->getBuilderName();
		return new $class('UPDATE', $db);
	}

	public static function delete($db = null) {
		$db = DatabaseFactory::getDriver($db);
		$class = $db->getBuilderName();
		return new $class('DELETE', $db);
	}

	protected abstract function formatDeleteString();

	protected abstract function formatSelectString();

	protected abstract function formatInsertString();

	protected abstract function formatUpdateString();

	public function fields($fields) {
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		if (isset($this->query['FIELDS'])) {
			$this->query['FIELDS'] = array_merge($this->query['FIELDS'], $fields);
		} else {
			$this->query['FIELDS'] = $fields;
		}
		return $this;
	}

	public function from($tables) {
		if (!is_array($tables)) {
			$tables = array($tables);
		}
		if (isset($this->query['FROM'])) {
			$this->query['FROM'] = array_merge($this->query['FROM'], $tables);
		} else {
			$this->query['FROM'] = $tables;
		}
		return $this;
	}

	public function into($table) {
		$this->query['INTO'] = $table;
		return $this;
	}

	public function tables($tables) {
		if (!is_array($tables)) {
			$tables = array($tables);
		}
		if (isset($this->query['TABLES'])) {
			$this->query['TABLES'] = array_merge($this->query['TABLES'], $tables);
		} else {
			$this->query['TABLES'] = $tables;
		}
		return $this;
	}

	public function where($expression, $value = null, $logic='AND') {
		if (isset($value)) {
			if (empty($value) && $value !== 0) {
				return $this;
			}
			if ($value !== '?') {
				$value = $this->db->quote($value);
			}
		}
		$logic = strtoupper($logic);
		if (!isset($this->query['WHERE'])) {
			$this->query['WHERE'] = array();
		}
		$this->query['WHERE'][] = array($expression, $value, $logic);
		return $this;
	}

	public function andWhere($comparison, $value) {
		return $this->where($comparison, $value, 'AND');
	}

	public function orWhere($comparison, $value) {
		return $this->where($comparison, $value, 'OR');
	}

	public function whereIn($column, $values, $logic='AND') {
		$values = (array) $values;
		foreach($values as $v) {
			$v = $this->db->quote($v);
		}
		$string = $column . ' IN (' . implode(',', $values) . ')';
		return $this->where($string, null, $logic);
	}

	public function andWhereIn($column, $values) {
		return $this->whereIn($column, $values, 'AND');
	}

	public function orWhereIn($column, $values) {
		return $this->whereIn($column, $values, 'OR');
	}

	public function orderBy($expression, $sort = 'ASC') {
		$sort = strtoupper($sort);
		if ($sort !== 'DESC') {
			$sort = 'ASC';
		}
		if (!isset($this->query['ORDER BY'])) {
			$this->query['ORDER BY'] = array();
		}
		$this->query['ORDER BY'][] = array($expression, $sort);
		return $this;
	}

	//todo - maybe add second argument that calls offset
	public function limit($int) {
		$this->query['LIMIT'] = $int;
		return $this;
	}

	public function offset($int) {
		$this->query['OFFSET'] = $int;
		return $this;
	}

	public function distinct() {
		$this->query['DISTINCT'] = true;
		return $this;
	}

	public function join($table, $type = null) {
		$this->query['JOIN'] = $table;
		return $this;
	}

	public function getTables() {
		switch ($this->type) {
			case 'SELECT':
				return isset($this->query['FROM']) ? $this->query['FROM'] : null;
			case 'INSERT':
				return isset($this->query['INTO']) ? array($this->query['INTO']) : null;
			case 'UPDATE':
				return isset($this->query['TABLES']) ? $this->query['TABLES'] : null;
			case 'DELETE':
				return isset($this->query['FROM']) ? $this->query['FROM'] : null;
			default:
				return null;
		}
	}

	public function getFields() {
		if (isset($this->query['FIELDS'])) {
			return $this->query['FIELDS'];
		}
		return false;
	}

	public function setDatabase($db) {
		$this->db = $db;
	}

	public function getDatabase() {
		return $this->db;
	}

	public function prepare($override=false) {
		if ($override) {
			$query = $override;
		} else {
			$query = $this->formatQueryString();
		}
		return $this->db->prepare($query);
	}

	public function __toString() {
		return $this->formatQueryString();
	}

}

?>