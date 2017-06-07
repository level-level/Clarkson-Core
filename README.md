<img src="https://travis-ci.org/level-level/Clarkson-Core.svg?branch=master">

# Clarkson Core
A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.

## Requirements
Composer, that's it.

## Whats does What?

Here is a brief explenation of the bas
### Template Hierachy
Uses the internal [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) so you can replace `index.php` with `index.twig` or `archive-company.php` with `archive-company.twig` and still have all Posts or CPT's available in "The Loop".


```
{% extends "layouts/full-width.twig" %}

{% block content %}
    {% for object in objects %}
        {% include 'partials/teaser.twig' %}
    {% endfor %}
{% endblock %}
```

### Autoloading of WordPress-object classes per Custom Post Type.
It autoloads a default [Clarkson Object](https://github.com/level-level/Clarkson-Core/blob/master/post-objects/Clarkson_Object.php) that is just like `WP_Post` but with some more handy stuff.
When you register a Custom Post Type `ll_company` your custom class `ll_company` gets loaded in the Twig context as `objects` variable within the `archive-company.twig`.  

[More info](http://wp-clarkson.com/core/docs/wordpress-objects.html) about WordPress objects.

## More documentation

- [Installation](http://wp-clarkson.com/core/docs/wordpress-objects.html)
- [WordPress Objects](http://wp-clarkson.com/core/docs/wordpress-objects.html)
- [Everything else](http://wp-clarkson.com/core/docs/)

[More info](http://wp-clarkson.com/core/docs/wordpress-objects.html) about WordPress objects.

## Code style check
Currently 
1. Clone repository `git@github.com:level-level/Clarkson-Core.git clarkson-core`.
1. Run `composer install` in the new `clarkson-core` directory.
1. Run `vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs/`.
1. Run `vendor/bin/phpcs --standard=ruleset.xml`.

## Theme Development

Should work out of the box with an empty PHP theme or use the starter theme [Clarkson Theme](https://github.com/level-level/Clarkson-Theme/) using Twig.
