<?php

namespace Di;
use \Exception;

class ServiceRegistry {

	private $registryStoredData = array();
	private $registryLazyData = array();

	public function setMany(array $data) {
		foreach ($data as $k => $v) {
			$this->set($k, $v);
		}
		return $this;
	}

	public function set($key, $value) {
		if ($this->registryKeyInUse($key)) {
			throw new Exception("Key `$key` already in use");
		}
		$this->registryStoredData[$key] = $value;
		return $this;
	}

	private function registryKeyInUse($key) {
		if (array_key_exists($key, $this->registryLazyData)) {
			return true;
		}
		if (array_key_exists($key, $this->registryStoredData)) {
			return true;
		}
		return false;
	}

	public function lazySet($key, callable $value) {
		if ($this->registryKeyInUse($key)) {
			throw new Exception("Key `$key` already in use");
		}
		$this->registryLazyData[$key] = $value;
		return $this;
	}

	public function getMany(array $keys) {
		$out = [];
		foreach ($keys as $key) {
			$out[] = $this->get($key);
		}
		return $out;
	}

	public function get($key) {
		if (isset($this->registryLazyData[$key])) {
			$this->registryStoredData[$key] = call_user_func($this->registryLazyData[$key]);
			unset ($this->registryLazyData[$key]);
		}
		if (! isset($this->registryStoredData[$key])) {
			throw new Exception("Invalid key: `$key`");
		}
		return $this->registryStoredData[$key];
	}

}
