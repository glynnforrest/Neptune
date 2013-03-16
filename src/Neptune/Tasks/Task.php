<?php

namespace Neptune\Tasks;

use \ReflectionMethod;
use Neptune\Console\Console;

/**
 * Task
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
abstract class Task {

	protected $console;

	public function __construct() {
		$this->console = Console::getInstance();
	}

	public function run($args = array()) {
		//if no args are supplied, offer methods available for the
		//current task.
		if($empty($args)) {
		}
	}

	protected function getTaskMethods() {
		$methods = get_class_methods($this);
		foreach ($methods as $k => $method) {
			$r = new ReflectionMethod($this, $method);
			if(!$r->isPublic()) {
				unset($methods[$k]);
			}
		}
		sort($methods);
		return $methods;
	}

	public function help() {
		//print out all methods and their docblocks
	}

}