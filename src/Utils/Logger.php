<?php

namespace Kainex\WiseAnalytics\Utils;

/**
 * TODO: rotate logs
 *
 * Class Logger
 * @package Kainex\WiseAnalytics\Utils
 */
class Logger {

	public function info(string $text) {
		$text = sprintf('[%s] [wa] [info] %s', (new \DateTime())->format('Y-m-d H:i:s'), $text);
		$this->publish($text);
	}

	public function error(string $text) {
		$text = sprintf('[%s] [wa] [error] %s', (new \DateTime())->format('Y-m-d H:i:s'), $text);
		$this->publish($text);
	}

	private function publish(string $text) {
		$this->publishFallback($text);
	}

	private function publishFallback(string $text) {
		error_log($text);
	}

}