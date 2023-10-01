<?php

namespace Kainex\WiseAnalytics;

class Container {

	/** @var Container */
	private static $instance;

	/** @var array */
	private $cache;

	/**
	 * Container constructor.
	 */
	private function __construct() {
		$this->cache = [];
	}

	/**
	 * @return Container
	 */
	public static function getInstance(): Container {
		if (!self::$instance) {
			self::$instance = new Container();
		}

		return self::$instance;
	}

	/**
	 * @param string $className
	 * @return object
	 * @throws \Exception
	 */
	public function get(string $className) {
		if (isset($this->cache[$className])) {
			return $this->cache[$className];
		}

		$reflect  = new \ReflectionClass($className);
		$dependencies = [];
		if ($reflect->getConstructor()) {
			foreach ($reflect->getConstructor()->getParameters() as $param) {
				$dependencies[] = $this->get($param->getType());
			}
		}

		$instance = $reflect->newInstanceArgs($dependencies);
		$this->cache[$className] = $instance;

		return $instance;
	}

}
