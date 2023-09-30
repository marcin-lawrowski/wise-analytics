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

}