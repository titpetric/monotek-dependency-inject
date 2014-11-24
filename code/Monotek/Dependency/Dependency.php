<?php

namespace Monotek\Dependency;

class Dependency
{
	const E_DEPENDENCY_NONE = "Can't inject '%s': dependency doesn't exist.";

	protected static $inject = array();

	public static function set($object, $callback)
	{
		self::$inject[strtolower($object)] = $callback;
	}

	public static function get($object, $resolve = false)
	{
		$object = strtolower($object);
		if (!isset(self::$inject[$object])) {
			throw new \Exception(sprintf(self::E_DEPENDENCY_NONE, $object));
		}
		if ($resolve && is_callable(self::$inject[$object])) {
			$func = self::$inject[$object];
			return $func();
		}
		return self::$inject[$object];
	}

	public static function getList()
	{
		return self::$inject;
	}

	public static function resolve($object)
	{
		return self::get($object, true);
	}

	public static function share($callable)
	{
		return function() use ($callable) {
			if (!is_callable($callable)) {
				return $callable;
			}
			$args = func_get_args();
			$key = empty($args) ? "default" : implode(':', $args);
			static $ret = array();
			if (isset($ret[$key])) {
				return $ret[$key];
			}
			$ret[$key] = call_user_func_array($callable, func_get_args());
			return $ret[$key];
		};
	}

	public static function singleton($callable)
	{
		return self::share($callable);
	}
}