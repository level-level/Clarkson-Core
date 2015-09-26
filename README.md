# Clarkson Core
Making Twig available the WordPress way.

## Requirements
Composer, that's it.

## Whats does What?

### Template Hierachy
Uses the internal [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) so you can replace `index.php` with `index.twig` or `archive-company.php` with `archive-company.twig` and still have all   Posts or CPT's available in "The Loop".

### Autoloading of Object classes per Custom Post Type.
It autoloads a default [Object](https://github.com/level-level/Clarkson-Core/blob/master/post-objects/Clarkson_Object.php) just like `WP_Post` but with some more handy stuff.
When you register a Custom Post Type `Company` your custom class `Clarkson_Company` gets loaded in the `objects` variable within `archive-company.twig`


## Installation

1. Clone repository or download zip.
2. Run `composer install` in the `Core` directory.
3. Run `npm install` in the `development` directory.

## Theme Development

Should work out of the box with an empty theme or use the starter theme [Clarkson Theme](https://github.com/level-level/Clarkson-Theme/)
