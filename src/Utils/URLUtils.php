<?php

namespace Kainex\WiseAnalytics\Utils;

use Kainex\WiseAnalytics\Options;

class URLUtils {

	/** @var Options */
	private $options;

	/**
	 * URLUtils constructor.
	 * @param Options $options
	 */
	public function __construct(Options $options)
	{
		$this->options = $options;
	}

	public function toPathAndQuery(string $url): string {
		$url = str_replace($this->options->getBaseURL(), '', $url);
		if (!$url) {
			$url = '/';
		}

		return $url;
	}

	public static function getCurrentURL(): string {
		if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_SERVER['HTTP_REFERER'])) {
			return $_SERVER['HTTP_REFERER'];
		}


	    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
	        $protocol = 'https://';
	    } else {
	        $protocol = 'http://';
	    }

	    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

}