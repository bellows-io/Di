<?php

namespace Di;

class Injector {

	protected $registry;
	protected $aliases = [];

	public function __construct(ServiceRegistry $registry) {
		$this->registry = $registry;
	}

	public function aliasClassDependencies($classPath, array $dependencies) {
		if (! isset($this->aliases[$classPath])) {
			$this->aliases[$classPath] = [];
		}

		foreach ($dependencies as $k => $v) {
			$this->aliases[$classPath][$k] = $v;
		}
	}

	protected function getKeyValues($classPath, array $keys, array $optionalArgs = array()) {

		if (isset($this->aliases[$classPath])) {
			foreach ($keys as $i => $key) {
				if (isset($this->aliases[$classPath][$key])) {
					$keys[$i] = $this->aliases[$classPath][$key];
				}
			}
		}

		$output = [];
		foreach ($keys as $key) {
			if (isset($optionalArgs[$key])) {
				$output[] = $optionalArgs[$key];
			} else {
				$output[] = $this->registry->get($key);
			}
		}
		return $output;
	}

	public function invokeConstructor($classPath) {
		$class = new \ReflectionClass($classPath);
		$constructor = $class->getConstructor();

		if (! $constructor) {
			$instance = $class->newInstance();
		} else {
			$parameters = $constructor->getParameters();

			$keys = array_map(function($p) {
				return $p->getName();
			}, $parameters);

			$arguments = $this->getKeyValues($classPath, $keys);
			$instance = $class->newInstanceArgs($arguments);
		}

		$methods = $class->getMethods();
		foreach ($methods as $method) {
			if (substr($method->getName(), 0, 5) == 'init_') {
				$this->invokeReflectionMethod($instance, $method);
			}
		}

		return $instance;
	}

	public function invokeMethod($object, $methodName, array $optionalArgs = array()) {
		$method = new \ReflectionMethod($object, $methodName);
		$class = new \ReflectionClass($object);

		if (! $method->isPublic()) {
			$className = get_class($object);
			throw new \Exception("$className::$methodName is not publicly accessible");
		}

		return $this->invokeReflectionMethod($object, $method, $optionalArgs);
	}

	protected function invokeReflectionMethod($object, \ReflectionMethod $method, array $optionalArgs = array()) {
		$className = get_class($object);
		$parameters = $method->getParameters();

		$keys = array_map(function($p) {
			return $p->getName();
		}, $parameters);

		$arguments = $this->getKeyValues($className, $keys, $optionalArgs);

		return $method->invokeArgs($object, $arguments);
	}
}