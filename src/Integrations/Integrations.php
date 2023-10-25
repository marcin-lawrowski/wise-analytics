<?php

namespace Kainex\WiseAnalytics\Integrations;

class Integrations {

	/** @var WordPressIntegrations */
	private $wordPressIntegrations;

	/**
	 * Integrations constructor.
	 * @param WordPressIntegrations $wordPressIntegrations
	 */
	public function __construct(WordPressIntegrations $wordPressIntegrations)
	{
		$this->wordPressIntegrations = $wordPressIntegrations;
	}

	public function install() {
		$this->wordPressIntegrations->install();
	}

}