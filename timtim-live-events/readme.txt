=== TimTim.Live Events ===
Contributors: timtimlive
Tags: events, concerts, tickets, festivals, music
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show live events from TimTim.Live on your site. Every "Get Tickets" button is your own TimTim.Live link.

== Description ==

TimTim.Live Events puts concerts, festivals and cultural events from TimTim.Live on your WordPress site — as a block or a shortcode — using the TimTim.Live Partner API.

* Pick a city, country, category and how many events.
* Every "Get Tickets" button is your tracked TimTim.Live link, so the people you send are counted for you.
* TimTim.Live sells the tickets and looks after the buyers. You do not need to become a ticketing company.
* Your key stays on your server. Visitors never see it.
* Answers are cached for ten minutes; if TimTim.Live is briefly unreachable, the last good list is shown.

This plugin is published by TimTim.Live and updates only from https://timtim.live. It contacts only https://timtim.live.

== Installation ==

1. Download the plugin from https://timtim.live/partners/docs#wordpress and upload it in Plugins → Add New → Upload Plugin.
2. Activate it.
3. Get a key on https://timtim.live/partners/dashboard — a website key (tt_pk_live_…), or a test key (tt_test_…) to try sample events.
4. Paste it in Settings → TimTim.Live Events and press Test Connection.
5. Add the "TimTim.Live Events" block to a page, or paste [timtim_events city="Washington" category="music" limit="6"].

== Frequently Asked Questions ==

= Which key should I use? =

A website key (tt_pk_live_…). It can read events and nothing else. Use a test key (tt_test_…) while you try it: you will see sample events marked TEST EVENT — NO REAL MONEY.

= Do I earn money? =

Only where your TimTim.Live agreement includes a reward. Ticket links that can earn carry rel="sponsored".

= What does the plugin store? =

Your settings (including the key) and cached event lists, in your own database. Deleting the plugin removes all of it.

== Changelog ==

= 1.0.0 =
* First release: block, shortcode, settings with connection test, ten-minute cache with fallback, updates from timtim.live.
