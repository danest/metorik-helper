=== Metorik Helper ===
Contributors: bryceadams, metorik
Tags: woocommerce, woocommerce analytics, woocommerce reports, woocommerce filtering, woocommerce google analytics, woocommerce zendesk, woocommerce help scout, woocommerce freshdesk, woocommerce support, subscription reports, woo reports
Requires at least: 4.4.0
Tested up to: 4.9.0
Stable tag: trunk
License: MIT
License URI: https://opensource.org/licenses/MIT

The Metorik Helper helps provide your WooCommerce store with powerful analytics, reports, and tools.

== Description ==

> **Note:** This plugin is only really of use to you if you have a Metorik account/store. You can set one up for free and enjoy a 30 day trial, but keep in mind that it is a paid service. [**Try Metorik for free!**](https://metorik.com?ref=wporg)

In just a few clicks, Metorik gives your store a powerful real-time dashboard, unifying your store's orders, customers, and products, helping you understand your data and make more informed decisions every day.

= Blazing fast =

Tired of spending half your day waiting for WooCommerce reports to load? Metorik spins up detailed reports and charts faster than any other tool available. It also reduces the load on your site's admin dashboard since you can do everything from Metorik, as such making your site faster.

= Every KPI you could ask for =

What's your average customer LTV (lifetime value)? What's your average numbers of items per order? How many of product A or variation B did you sell last month? If these are questions you've always wanted answers for, Metorik will be a lifesaver.

= Filter everything by anything =

Metorik offers a robust & powerful filter system. It allows you segment your data by anything and everything (seriously), export that segmented data to a CSV (automatically, if that's your thing). You can even save the filters you used for next time, or share them with your team.

Want all customers who have an LTV over $100 and own a certain product? **Easy.**

Orders that were made last month where 2 items were purchased and the customer was from the UK? **Easy.**

Subscriptions that were set to be canceled this week? **Easy.**

Customers who haven't ordered in 4 months and live in California? **Easy.**

Read more about the filter system [here](https://metorik.com/blog/improved-segmenting-filtering-in-metorik?ref=wporg).

= Customer service integrations =

Metorik integrates with your existing support system to show customer data right alongside support tickets. Data like their contact information, lifetime value, order history, products purchased and more, instantly at you and your customer service teams' fingertips. Additionally, you'll find data from your support systems shown on order pages and customer profiles in Metorik.

Integrations are currently available for [Zendesk](https://metorik.com/blog/connecting-zendesk-and-woocommerce?ref=wporg), Help Scout, Freshdesk, and Intercom, with more to come.

= Google Analytics integration =

Connect your Google Analytics account to Metorik and get access to stats like conversion rates instantly. Better yet, you can get **historical conversion rates!** [Read more about it here](https://metorik.com/blog/conversation-rates-for-woocommerce-with-google-analytics?ref=wporg).

= Email + Slack reports =

Automatically receive reports summarising your store's activity as often as you'd like. They can be sent by both Email & Slack, and include your KPIs, charts, best sellers, and more.

= One-off and automated exports =

Any data can be exported from Metorik at any time in minutes. You can even schedule exports to happen automatically as often as you'd like.

*Bonus:* These exports have zero-impact on your site whatsoever. No more server downtime!

= WooCommerce Subscriptions support =

Metorik integrates seamlessly with [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions?ref=66), offering subscription filtering & exporting, along with reports like MRR, Churn, Retention, Forecasting, and more. You can even have an automated subscriptions report sent to you every day summarising everything subscriptions-related.

= Live chat support =

Support is available through live chat to every Metorik user. Metorik's founder - [Bryce](https://twitter.com/bryceadams) - will personally work with you to ensure you and your team get the most out of Metorik.

= Bring your team =

Whether you're running a store solo or bringing your team, Metorik has your back through its team system. Each store can have **unlimited team members** at no extra cost, each with their own role & permissions. No more sharing sales reports with your support reps and no more analytics modifying orders by accident.

= More? =

Oh, there's so much more. Seriously. Just have a look around the [Metorik website](https://metorik.com?ref=wporg) to get an idea of how valuable Metorik will be for your store.

---

The Metorik Helper helps [Metorik](https://metorik.com?ref=wporg) connect and work better with your site. Simply install, activate and Metorik will take care of the rest!

== Installation ==
Install, activate and leave it to do the rest.

Keep in mind that you do need a Metorik account for it to work with, so if you don't yet have a store set up in Metorik, head to [Metorik](https://metorik.com?ref=wporg) and sign up now.

== Frequently Asked Questions ==
**Do I need a Metorik account to use this plugin?**

Yes, you do ([sign up here](https://metorik.com?ref=wporg)). It will still work but will really not be of much use to you without one.

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
= 0.12.0 =
* Multisite support for customers/updated endpoint.
* Added WooCommerce 3.2 required/tested plugin headers.
* Improve Woo customers API performance.

= 0.11.0 =
* Change method for stopping customer spend calculations in API so it just does it for Metorik API requests instead of on a time-basis by option.

= 0.10.0 =
* Track 'Engage' data
* Improve UTM tracking
* Set tracking data in user meta during checkout
* Added an 'hours' arg to updated endpoints
* Added pagination to updated endpoints
* Don't include draft orders in updated endpoints

= 0.9.0 =
* Coupon endpoints

= 0.8.1 =
* Extend source & UTM cookie storing time to 6 months

= 0.8.0 =
* Track UTM tags in order/customer meta
* Filter for referer

= 0.7.1 =
* Further updated timezone fixes

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