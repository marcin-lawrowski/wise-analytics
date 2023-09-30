<?php
/*
	Plugin Name: Wise Analytics
	Version: 1.0.0
	Plugin URI: https://kainex.pl/projects/wp-plugins/wise-analytics
	Description: Manage your own stats!
	Author: Kainex
	Author URI: https://kainex.pl
	Text Domain: wise-analytics
*/

use Kainex\WiseAnalytics\Analytics;
use Kainex\WiseAnalytics\Container;
use Kainex\WiseAnalytics\Endpoints\FrontHandler;
use Kainex\WiseAnalytics\Endpoints\ReportsEndpoint;
use Kainex\WiseAnalytics\Installer;
use Kainex\WiseAnalytics\Loader;
use Kainex\WiseAnalytics\Reports\PagesSetup;
use Kainex\WiseAnalytics\Tracking\Core;

define('WISE_ANALYTICS_VERSION', '1.0.0');
define('WISE_ANALYTICS_ROOT', dirname(__FILE__));
define('WISE_ANALYTICS_NAME', 'Wise Analytics');

// class loader:
require_once(dirname(__FILE__).'/src/Loader.php');
Loader::install();

// DI container:
$container = Container::getInstance();

add_action('wp_enqueue_scripts', [$container->get(Analytics::class), 'enqueueResources']);

// report pages:
add_action('admin_menu', [$container->get(PagesSetup::class), 'install']);

// install tracking:
add_action('init', [$container->get(Core::class), 'install']);

// install endpoints:
add_action('init', [$container->get(FrontHandler::class), 'registerEndpoints']);
add_action('pre_get_posts', [$container->get(FrontHandler::class), 'registerHandlers']);
add_action('rest_api_init', [$container->get(ReportsEndpoint::class), 'registerEndpoints']);

if (is_admin()) {
	Installer::setup(__FILE__);
	add_action('admin_enqueue_scripts', [$container->get(Analytics::class), 'enqueueAdminResources']);
}