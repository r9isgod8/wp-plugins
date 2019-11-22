=== Flying Pages ===

Contributors: gijo
Donate link: https://www.buymeacoffee.com/gijovarghese
Tags: performance, speed, fast, prefetch, seo, http2, preconnect, optimization
Requires at least: 4.5
Tested up to: 5.3
Requires PHP: 5.6
Stable tag: 2.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Load inner pages instantly, intelligently!

== Description ==

Flying Pages preload pages before the user click on it, making them load instantly

## Quick Links

- Demo: Open [https://wpspeedmatters.com](https://wpspeedmatters.com) and click on any post
- [GitHub repo](https://github.com/gijo-varghese/flying-pages)
- [Quicklink vs Instant page vs Flying Pages](https://wpspeedmatters.com/quicklink-vs-instant-page-vs-flying-pages/)
- Join our [Facebook Group](https://www.facebook.com/groups/wpspeedmatters/), a community of WordPress speed enthusiasts
- [Buy me a coffee](https://www.buymeacoffee.com/gijovarghese)

## How it Works?

Flying Pages injects a tiny JavaScript code (1KB gzipped), waits until the browser becomes idle. Then it detects pages in the viewport and on mouse hover and preloads them.

Flying Pages is intelligent to make sure preloading doesn't crash your server or make it slow.

- **Preload pages in the viewport** - Detect links within the viewport (current viewing area) using 'Intersection Observer' and tells the browser to preload them using 'prefetch', switch to xhr if not available (similar to [Quicklink](https://github.com/GoogleChromeLabs/quicklink)).

- **Preload pages on mouse hover** - On hovering links, if it's not preloaded yet using above 'viewport', then Flying Pages will prefetch them instantly (similar to [Instant.page](https://instant.page/)).

- **Limits the number of preloads per second** - If your page has too many links, prefetching all at the same time will cause the server to crash or slow down the website to visitors. Flying Pages limits the number of preloads per second (3 req/sec by default) using an in-built queue. For example, if you've 10 links in the viewport, preloading all these are span into 4 seconds.

- **Stops preloading if the server is busy** - In case the server starts to respond slowly or return errors, preloading will be stopped to reduce the server load.

- **Understands user's connection and preferences** - Checks if the user is on a slow connection like 2G or has enabled data-saver. Flying Pages won't preload anything in this case.

== Installation ==

#### From within WordPress

1. Visit 'Plugins > Add New'
1. Search for 'Flying Pages'
1. Activate Flying Pages for WordPress from your Plugins page.
1. Visit 'Settings -> Flying Pages' to configure

#### Manually

1. Upload the `flying-pages` folder to the `/wp-content/plugins/` directory
1. Activate the Flying Pages plugin through the 'Plugins' menu in WordPress
1. Visit 'Settings -> Flying Pages' to configure

== Screenshots ==

1. Flying Pages Settings

== Frequently Asked Questions ==

= Recommended hosting provider & cache plugin? =
 
Flying Pages works with every hosting provider, without any cache plugins. However, to get the best results, consitent performance and no downtime, our recommended hosting providers are [Cloudways](https://wpspeedmatters.com/get/cloudways/) and [Kinsta](https://wpspeedmatters.com/get/kinsta/). For cache plugin, I always recommend [WP Rocket](https://wpspeedmatters.com/get/wp-rocket/)

 
= Does Flying Pages affect Google Analytics or similar tracking scripts? =
 
The prefetch only downloads the resources, but does not execute the code (even inline JavaScript). So it will not affect Google Analytics or similar scripts

= Can I preload only on mouse hover? =
 
Yes, you can disable preloading links in the viewport and only on mouse hover. Set the delay in settings to "Don't preload links in viewport".

= I'm not seeing any improvements in GTmetrix/Pingdom =
 
Flying Pages preload links after the website is loaded and only improves the speed on clicking links. It doesn't help you to speed up the initial load.

= Does Flying Pages increase server load? =
 
In short, yes. But Flying Pages is intelligent to stop preloading if the server is busy. You can also configure Flying Pages to limit the number of preloads per second or preload only on mouse hover
 
= My 'Fully Loaded Time' in GTmetrix/Pingdom increased after installing Flying Pages =
 
Flying Pages starts preloading when all other resources have completed downloading and browser has become idle. It will not affect the TTFB or First Contentful Paint or Time to Interactive. If you're worried about the fully loaded time in GTmetrix/Pingdom, set the delay to a higher number in settings.

= Is it compatible with WooCommerce? =
 
Yes. Based on the number of links in the page and your hosting providers capacity, adjust the maximum requests per second to reduce server load. You can also configure it to preload only on mouse hover

== Changelog ==

= 2.1.2 =

- Removed XMLHttpRequest and improved minification of JS file (reduce size by ~300bytes)
- Added option '2 secs' to delay to start preloading

= 2.1.1 =

- Changing JavaScript scope to prevent conflicts with other plugins using same variables

= 2.1.0 =

- Option to disable preloading when logged in as administrator

= 2.0.9 =

- Disable on Internet Explorer
- Renamed observer object to prevent conflicts with other plugins

= 2.0.8 =

- Ignore query strings by default

= 2.0.7 =

- Allow 1 second in delay

= 2.0.6 =

- Better compatibility for WooCommerce
- Prefetching on mobile using 'touchstart'

= 2.0.5 =

- Bug fix - Start queue only after calling main function, causing errors in browser console

= 2.0.4 =

- Bug fix - Issues with Swift Performance cache plugin when 'Merge Scripts' is enabled

= 2.0.3 =

- Bug fix - Prevent max rps from resetting to default value on plugin update

= 2.0.2 =

- Support WordPress 4.5+
- Copy fixes

= 2.0.1 =

- Bug fix - Set default config on plugin update/activation

= 2.0.0 =

MAJOR UPDATE!
* Configure ignore keywords
* Configure delay for prefetching from viewport
* Configure to prefetch on mouse hover only
* Configure maximum requests per second
* Configure mouse hover delay

= 1.0.5 =

- Bug fix - Prevent wp-admin links from preloading

= 1.0.4 =

- Bug fix for Safari/iOS Safari

= 1.0.3 =

- Prevent logout links from preloading

= 1.0.2 =

- Prevent external links from preloading on hover

= 1.0.1 =

- Support for Microsoft Edge browser
- Prevents current page from preloading

= 1.0.0 =

- Initial release
