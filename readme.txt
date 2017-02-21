=== Metorik Helper ===
Contributors: bryceadams, metorik
Tags: woocommerce, metorik, woocommerce analytics, woocommerce metrics
Requires at least: 4.4.0
Tested up to: 4.7.2
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT

The Metorik Helper helps provide your WooCommerce store with powerful analytics, reports, and insights.

== Description ==
The Metorik Helper helps [Metorik](https://metorik.com) connect and work better with your site. Simply install, activate and Metorik will take care of the rest!

**Note:** This plugin is only really of use to you if you have a Metorik account/store. You can set one up for free and enjoy a 30 day trial, but keep in mind that it is a paid service.

---

**What is Metorik?** Metorik is a service that provides detailed and powerful analytics for WooCommerce stores, and includes features like:

* Growth rates
* Customer profiles
* Product insights
* Interactive charts
* Filtered exporting
* Automated reports sent to you
* Real-time analytics
* Live KPI calculations
* Blazing fast
* Unlimited team members

== Installation ==
Install, activate and leave it to do the rest.

Keep in mind that you do need a Metorik account for it to work with, so if you don't yet have a store set up in Metorik, head to [Metorik](https://metorik.com) and sign up now.

== Frequently Asked Questions ==
**Do I need a Metorik account to use this plugin?**

Yes, you do ([sign up here](https://metorik.com)). It will still work but will really not be of much use to you without one.

**Can I hide the Metorik links in my WordPress dashboard?**

If you truly want to (but why! They're so handy), you can. Simply add:

```
add_filter( 'metorik_show_ui', '__return_false' );
```

To your theme's `functions.php` or a custom plugin.

The other option is to simply 'dimiss' a Metorik notice and they will no longer appear.

To hide the links from individual orders/products, you can click the 'Screen Options' tab at the top of the page and uncheck the Metorik option.

**I accidentally hid the notices. How can I get them back?**

We all make mistakes. To get them back, go to http://yoursite.com/wp-admin?show-metorik-notices=yes while logged in as an administrator.

== Changelog ==
= 0.7.1 =
* WooCommerce 2.7 fixes

= 0.7.0 =
* Include order post meta data when pre WC 2.7
* Subscriptions endpoints
* Open Metorik links in new tabs
* Fix updated timezone issue

= 0.6.1 =
* Fix notices for unset http referer

= 0.6.0 =
* Track and store customer/order referer (source)
* Endpoint for possible order statuses
* Endpoint for possible customer (user) roles
* Ignore trashed orders/products in updated endpoints
* Allow dismissing/hiding of the Metorik notices
* PHP 5.2 compat fix

= 0.5.2 =
* Fix minor PHP notices in admin

= 0.5.1 =
* Fix undefined variable notice

= 0.5.0 =
* Remove custom customer index/single endpoints if 2.7
* Links from resource admin pages to Metorik

= 0.4.2 =
* Make activation method static

= 0.4.1 =
* Fix undefined variable in products updated endpoint

= 0.4.0 =
* Refund IDs endpoint

= 0.3.1 =
* Improve stability of customers updated endpoint

= 0.3.0 =
* New endpoints for orders updated
* New endpoints for customers updated
* New endpoints for products updated
* Fix customer IDs endpoint query for custom DB prefixes

= 0.2.3 =
* Show notice prompting users to go back to Metorik after installing to complete connection

= 0.2.2 =
* Fix customer IDs endpoint permissions

= 0.2.1 =
* Override WC single customer endpoint too to make faster during imports

= 0.2.0 =
* Override WC customers endpoint to make faster during imports

= 0.1.0 =
* Initial beta release.