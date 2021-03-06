=== Clarkson Core ===
Contributors: level level, jmslbam
Tags: twig, templating, template engine, templates, oop, wordpress objects
Requires at least: 4.7
Tested up to: 4.9.4
Stable tag: 1.1.0
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

= 1.1.1 - March 11, 2021 =

* Change the priority of the clarkson_core_template_context filter from 10 to 5 to make sure Clarkson Core fills the context first.

= 1.1.0 =

* Adds a new `clarkson_core_block_context_{$name}` hook to modify variables available in the twig render function of a block.

= 1.0.0 =

* Now uses the actual WordPress template hierarchy, solving a lot of edge cases around template loading.
* The twig environment is exposed through the new `clarkson_twig_environment` filter.
* Normalises the `Clarkson_User` and `Clarkson_Term` object creation interfaces.
* Minor docblock typehint improvements in `Clarkson_Object`.
* Extends parameters available in `Clarkson_Core_Gutenberg_Block_Type::clarkson_render_callback`
* `Clarkson_Object` has a new `get_object()` method.
* Adds new `clarkson_core_template_context` filter.
* Adds `object` as the first of `objects` to twig context.
* There is now an object hierarchy:
    * Objects: \Clarkson_Core\WordPress_Object\$post_type, \Clarkson_Core\WordPress_Object\base_object, \Clarkson_Core\WordPress_Object\Clarkson_Object
    * Terms: \Clarkson_Core\WordPress_Object\$taxonomy, \Clarkson_Core\WordPress_Object\base_term, \Clarkson_Core\WordPress_Object\Clarkson_Term
    * Users: \Clarkson_Core\WordPress_Object\user, \Clarkson_Core\WordPress_Object\Clarkson_User
    * Templates: \Clarkson_Core\WordPress_Object\$template, \Clarkson_Core\WordPress_Object\base_template, \Clarkson_Core\WordPress_Object\Clarkson_Template,   
    * Post Types: \Clarkson_Core\WordPress_Object\post_type_$post_type, \Clarkson_Core\WordPress_Object\base_post_type, \Clarkson_Core\WordPress_Object\Clarkson_Post_Type
    * Blocks: \Gutenberg\Blocks\$block_name, \Gutenberg\Blocks\base_block, Clarkson_Core\Gutenberg\Block_Type
* Adds `get_terms()` method to mimic `get_objects` and `get_users` on Object factory.
* Adds `clarkson_term_types` and `clarkson_user_type` filters to overwrite class lookup.
* Adds `Clarkson_Post_Type` object. `post_type` is also automatically added to the template context on archive pages.
* The twig environment is now only created once per request.
* `Clarkson_Object::get_many` can now set it a variable to it's \WP_Query.
* Adds a filter `clarkson_core_{$post_type}_templates` to manipulate templates per post type.
* Adds `::get_many`, `::get_one` and `::get` methods to `Clarkson_Term` and `Clarkson_User`.
* Adds `get_roles()` method (returns role name as string) to `Clarkson_User`.
* Adds `get_role_objects()` method (returns `Clarkson_Role`) to `Clarkson_User`.
* The `get_children()` and `get_attachments()` methods on the `Clarkson_Object` now accepts a Post arguments parameter.

Backward incompatible changes:
* Removes compatibility for WordPress < 4.7.
* Removes 'page-' template file name compatibility.
* Themes that relied on incorrect loading of template files may experience some incompatibility.
* Removes `Clarkson_Object::get_json`, which was deprecated in `0.2.0`.
* The `Clarkson_Term` and `Clarkson_User` interfaces have changed, and you might now get an Exception, instead of an invalid object.
* Removes deprecated features everywhere except for wordpress-objects/
* Removes loading of user roles instead of a user object.
* Removes deprecated construction of objects by id. Use ::get instead.
* Objects, term and user creation calls (such as ::get and ::get_one) now return null instead of throwing an error when no valid result is found.
* Updates twig from v1 to v3. See https://twig.symfony.com/doc/1.x/deprecated.html and https://twig.symfony.com/doc/2.x/deprecated.html
* Updates required PHP version to 7.2.
* Removes `get_role()` method from `Clarkson_User` because a user can have multiple roles. Replaced with `get_roles()` method, which returns an array of role names.
* The `get_children()` and `get_attachments()` methods on the `Clarkson_Object` now return `Clarkson_Object[]` instead of `WP_Post[]`.
* Template objects are now standalone objects (`Clarkson_Template`), instead of extending `Clarkson_Object`.

= 0.4.2 - August 19, 2019 =

* Fixes #165, incorrect null value when no terms are linked to a post.

= 0.4.1 - August 6, 2019 =

* Adds `clarkson_core_available_templates` filter.

= 0.4.0 - August 5, 2019 =

* Fixes typo for get_date function.
* Gutenberg implementation added.
* Object factory can now be overwritten with a filter.
* Some unit tests added.
* Some minor fixes.

= 0.2.2 - March 30, 2018 =

* Reintroduce get_post_type with fallback on Queried Object for Archives #98

= 0.2.1 - Jan 10, 2018 =

* Add support for the new Custom Post Types templates in WordPress 4.7
* Deprecate page-filename.twig and introduce template-filename.twig
* Fix Excerpt on archive pages
* Fix when an archive is empty, index.twig is used as a template instead of archive-{post_type}.twig

[https://github.com/level-level/Clarkson-Core/milestone/3](Check details here)

= 0.2.0 - June 7, 2017 =

This release breaks backwards compatibility, but we got your back by just adding 1 line. [More info](http://wp-clarkson.com/core/docs/upgrading.html)

<?php
// Paste this code in mu-plugins/clarkson-core-deprecated.php
add_filter('clarkson_core_autoload_theme_pre_020', '__return_true');

* Proper autoloading of wordpress-objects
* Autoloading Core and Theme `wordpress-objects`.
* Removal of loading theme specific directory via `glob`.
* Autoload CPT's when the filename is seperated by minus instead of underscore

If you used these then your code wouldn't have worked, so we removed them:
* Replace / remove hma_get_user_url & hma_get_avatar
* Remove get_avatar_img code that uses hma_get_avatar

Non breaking
* Remove `symfony/translations` dependency.
* Commit `composer.lock` file.
* Fix page_vars - Add `user` to context and move vars into one place
* Term object not correctly getting loaded
* "get_json" method missing on Clarkson_Object
* Replace hm_is_queried_object

[https://github.com/level-level/Clarkson-Core/milestone/2?closed=1](Check details here)

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
