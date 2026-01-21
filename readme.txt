=== Wise Analytics ===
Contributors: marcin.lawrowski
Donate link: https://kainex.pl/projects/wp-plugins/wise-analytics/wise-analytics-donate?utm_source=wiseanalytics-page&utm_medium=lead&utm_campaign=readme
Tags: stats, analytics, statistics, tracking, traffic
Requires at least: 6.2.0
Requires PHP: 7.4.0
Tested up to: 6.7
Stable tag: 1.1.20
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Your stats are private thing. No need to store them outside your server or give it for free! Wise Analytics is an advanced web traffic tracking and analytics plugin for WordPress sites.

== Description ==

**Wise Analytics** is a fully fledged site statistics plugin that helps to track the behavior of visitors, measure the traffic sources and actions taken on your website. The plugin provides a platform to analyze the traffic visualized as tables and charts. It is easy to set up and configure. Our development team is constantly working on new features and supporting the existing ones.

We believe that the statistics should be neither collected nor processed outside your server. With Wise Analytics you can have such level of privacy. Then you may learn your visitors and improve your site or business to earn more.

**[Read More](https://kainex.pl/projects/wp-plugins/wise-analytics?utm_source=wiseanalytics-page&utm_medium=lead&utm_campaign=readme)**

**[Source code on GitHub](https://github.com/marcin-lawrowski/wise-analytics)**

= List of features: =

* Tracking visitors, visits, the behaviour, page views and other actions
* Integration with Contact Form 7 (a visitor sends a form -> Wise Analytics recognizes all future actions of the visitor -> all actions are then assigned to e-mail address of the visitor)
* Tracking traffic sources (Referral, Social Networks, Search Engines, Direct, Paid Traffic)
* Detecting devices, languages, screens
* Visitors behaviour: pages stats, entry pages, exit pages
* Hourly visits in visitors' local time
* Tracking WordPress log-in event
* Visitor profile page (name, e-mail, language, screen size, device, last visit, hourly activity, full actions log, etc.)
* Top visited pages
* Full visitors table with average visit time
* Filtering by dates range

== Installation ==

= Requirements: =
* PHP >= 7.4.0

= Plugin installation: =
1. Upload the entire wise-analytics folder to the plugins directory (usually `/wp-content/plugins/`) of WordPress instance.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Traffic tracking will start just after the activation
1. Check the statistics on Dashboard -> Analytics page

= Post Installation Notices: =
* After installation go to Settings -> Wise Analytics Settings page for detailed configuration and configure visitors mapping

== Frequently Asked Questions ==

= How to add more details to visitors (e.g. e-mail address, name, city, etc.) ? =

By default, visitors in Wise Analytics are described by ID number and a list of actions (called events) they performed on your site only.
If you want to add more details to the current visitor execute the following PHP code somewhere in your logic:

`
if (class_exists('\Kainex\WiseAnalytics\Container', false)) {
   $visitorsService = \Kainex\WiseAnalytics\Container::getInstance()->get(\Kainex\WiseAnalytics\Services\Users\VisitorsService::class);
   $visitor = $visitorsService->getOrCreate();
   $visitor->setFirstName("Jerry");
   $visitor->setLastName("Smith");
   $visitor->setEmail("jerry@example.pl");
   $visitorsService->save($visitor);
}
`
Next time you check Wise Analytics stats browser this visitor will be displayed as Jerry rather than Visitor #12345

= How to register a conversion? =

When a visitor does something significant on your site you may register a conversion event in your code.
Then the conversion is visible in the stats browser.

`
if (class_exists("\Kainex\WiseAnalytics\Container", false)) {
  $visitors = \Kainex\WiseAnalytics\Container::getInstance()->get(\Kainex\WiseAnalytics\Services\Users\VisitorsService::class);
  $events = \Kainex\WiseAnalytics\Container::getInstance()->get(\Kainex\WiseAnalytics\Services\Events\EventsService::class);

  $visitor = $visitors->getOrCreate();
  $visitor->setFirstName("John");
  $visitor->setEmail("john@myshop.com");
  $visitors->save($visitor);

  $events->createEvent(
    $visitor,
     "conversion", [
     "uri" => \Kainex\WiseAnalytics\Utils\URLUtils::getCurrentURL(),
     "ip" => \Kainex\WiseAnalytics\Utils\IPUtils::getIpAddress(),
     "order.id" => 123456,
     "order.id.public" => "order_123456",
     "order.amount" => 19900
    ]
  );
}
`

= How to tell Wise Analytics to add more details to visitors after they put more details (e.g. e-mail address) in other plugins? =

Imagine you have a contact form on your site. A visitor fills in the form and sends a message.
Together with the message they usually provide more details like a name, e-mail address, phone number, company name, etc.
Those details may be intercepted by Wise Analytics and then presented on a visitor's profile page in the stats browser.
This way you will learn more about your visitors: how often they come back, what exactly they do on your site, etc.

Go to Settings -> Wise Analytics -> Visitors and map all detected contact forms. Currently, we support Contact Form 7 plugin only. Once a visitor submits a form it is the recognized in Wise Analytics by name or e-mail (depending on mapping).

== Screenshots ==

1. Main Page
2. Top Pages, Recent Visitors, Last events
3. Visitors table
4. Visitor detailed page
5. Traffic sources overview
6. Organic visits
7. Referral visits
8. Channels
9. Devices
10. Hourly stats
11. Pages report

== Changelog ==

= 1.1.20 =
* Added source column to visitors table
* Fixed: security issue

= 1.1.9 =
* Added engagements stats

= 1.1.8 =
* Events page
* Added period switching (daily, weekly, monthly) in lead line chart

= 1.1.7 =
* Lead line chart comparison option
* Conversion event type

= 1.1.6 =
* External links tracking

= 1.1.5 =
* Entry pages report
* Exit pages report

= 1.1.4 =
* Added pages views report
* Many minor adjustments

= 1.1.3 =
* Added hourly stats
* Improved backend sessions processing

= 1.1.2 =
* Added average time line chart
* Added screens table

= 1.1.1 =
* Recognizing paid traffic
* Traffic source daily chart with comparison to other metrics
* SocialNetworks table
* Channels table

= 1.1 =
* Added: social networks pie chart
* Added: organic search pie chart
* Added: referrals table

= 1.0 =

Initial version

== Upgrade Notice ==

None