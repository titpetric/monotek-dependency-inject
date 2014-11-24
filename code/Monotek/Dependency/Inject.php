<?php

namespace Monotek\Dependency;

class Inject
{
	const E_INJECT_NONE = "Unknown method call: '%s'";
	const E_INJECT_UNKNOWN = "Unknown method call prefix: '%s/%s'";

	public $inject = array();
	protected $inject_store = array();

	public function addInject($inject)
	{
		$this->inject[] = strtolower($inject);
	}

	public function __call($method, $args)
	{
		$method = strtolower($method);
		$prefix = substr($method, 0, 3);
		$object = substr($method, 3);
		if (!in_array($object, $this->inject)) {
			throw new \Exception(sprintf(self::E_INJECT_NONE, $method), 501);
		}
		if ($prefix === "set") {
			$this->inject_store[$object] = $args[0];
			return;
		}
		if ($prefix === "get") {
			if (!isset($this->inject_store[$object])) {
				$this->inject_store[$object] = Dependency::get($object);
			}
			if (is_callable($this->inject_store[$object])) {
				return call_user_func_array($this->inject_store[$object], $args);
			}
			return $this->inject_store[$object];
		}
		throw new \Exception(sprintf(self::E_INJECT_UNKNOWN, $prefix, $method), 501);
	}
}