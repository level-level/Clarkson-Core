<img src="https://travis-ci.org/level-level/Clarkson-Core.svg?branch=master">

# Clarkson Core
A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.

## Requirements
Composer, that's it.

## Whats does What?

### Template Hierachy
Uses the internal [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) so you can replace `index.php` with `index.twig` or `archive-company.php` with `archive-company.twig` and still have all   Posts or CPT's available in "The Loop".

### Autoloading of Object classes per Custom Post Type.
It autoloads a default [Object](https://github.com/level-level/Clarkson-Core/blob/master/post-objects/Clarkson_Object.php) just like `WP_Post` but with some more handy stuff.
When you register a Custom Post Type `ll_company` your custom class `ll_company` gets loaded in the `objects` variable within `archive-company.twig`.

#### Adding your own objects
[Loading](https://github.com/level-level/Clarkson-Core/blob/master/lib/clarkson-core-objects.php#L67) your own objects can be done in two ways:

1. Per complete directory by adding it via the filter `clarkson_available_objects_paths`.
2. Per single `object` / `class` file via `clarkson_available_objects`.

Our advice is you extend your new class with `Clarkson_Object`.

## Installation

1. Clone repository or download zip.
2. Run `composer install` in the `Core` directory.

## Code style check
1. Run `vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs/`.
2. Run `vendor/bin/phpcs --standard=ruleset.xml`.

## Theme Development

Should work out of the box with an empty theme or use the starter theme [Clarkson Theme](https://github.com/level-level/Clarkson-Theme/)
