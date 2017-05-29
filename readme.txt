=== Clarkson Core ===
Contributors: level level, jmslbam
Tags: twig, templating, template engine, templates, oop, wordpress objects
Requires at least: 4.0
Tested up to: 4.7.2
Stable tag: 0.2.0
License: GPL v2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable Twig and WordPress Objects to write object orientated code for your theme.

== Description ==
A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.

== Installation ==
[View the docs on the Clarkson website ](http://wp-clarkson.com/core/docs/index.html#installation)

== Frequently Asked Questions ==
= Is it used in production? =
Yes, at Level Level we use it for all of our new projects. It's already running on some large and high traffic websites.


== Changelog ==

= 0.2.0 - Mar 24, 2017 =

* Removal of loading theme specific directory via `glob`.
* Autoloading Core and Theme `wordpress-objects`.
* Remove `symfony/translations` dependency.
* Commit `composer.json` file.


= 0.1.10 - Feb 27, 2017 =

* Search for custom page templates in both child and parent theme when child theme is active #72
* Fill page template drop down in WP 4.7 and up while using a regular PHP theme #72
* Added readme.txt

= 0.1.9 - Feb 1, 2017 =

* Add support for localized date translation. #68
* Downgraded twig version to 1.2 due to version 7.0 requirement of twig 2.0. #69
* Removed versioning from composer json + bump to 0.1.19 #70

= Version 0.1.8 - Jan 9, 2017 =

* Add support for custom template directory and so child-themes #64

= Version 0.1.7: Dec 16, 2016

* Fixed the issue where the dropdown with page templates would not show up on pages in WordPress 4.7.

= Version 0.1.6: Oct 14, 2016 =

* Deprecated yall_twig_functions for clarkson_twig_function #55
* Revised naming for objects folder to not only allow posts. Renamed post-objects to wordpress-objects #54
* Update some project information

= Version 0.1.5: Oct 6, 2016 =

* Prevent double loading of object in get_parent

= Version 0.1.4: Sep 28, 2016 =

* Remove redundant template loading warning

= Version 0.1.3: Sep 23, 2016 =

- Restored backward compatibility to object constructor #52

= Version 0.1.2: Sep 12, 2016 =

Please use 0.1.3 because of little backwards compatible issue that came with #48

- Now compatible with older php versions #50
- Allows __construct to be called with WP_Post objects #48
- Adds a notice for multiple twig renders #49

= Version 0.1.1: Sep 12, 2016 =

- availeble_objects > available_objects
- Add filter for object manipulation: clarkson_object_type

= Version 0.1.0: Jul 19, 2016 =
- Initial release
