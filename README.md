[![Test](https://github.com/level-level/Clarkson-Core/actions/workflows/test.yml/badge.svg)](https://github.com/level-level/Clarkson-Core/actions/workflows/test.yml)

# Clarkson Core
A plugin to write Object-Oriented code in combination with the Twig templating engine while keeping the WordPress Way of working in mind.

## ⚠️ Maintenance Mode Only
This project is no longer receiving new features. It will only receive maintenance releases for PHP and WordPress compatibility updates as needed. If you're looking for active development, please consider alternatives (such as [Timber](https://github.com/timber/timber)) or feel free to fork this project.

## Documentation and getting started
Check out the [getting started with Clarkson Core guide](https://level-level.github.io/Clarkson-Core/phpdoc/guide/index.html) if you are just getting to know the project.

## Overview

### Installation
```
composer require level-level/clarkson-Core
```

or read the [installation guide](https://level-level.github.io/Clarkson-Core/phpdoc/guide/getting-started/installation.html).

### Template Hierachy
Uses the internal [Template Hierarchy](https://level-level.github.io/Clarkson-Core/phpdoc/guide/getting-started/templating.html) so you can replace `index.php` with `index.twig` or `archive-company.php` with `archive-company.twig` and still have all Posts or CPT's available in "The Loop".


```twig
{% extends "layouts/full-width.twig" %}

{% block content %}
    {% for object in objects %}
        {% include 'partials/teaser.twig' %}
    {% endfor %}
{% endblock %}
```

### Autoloading of WordPress-object classes per Custom Post Type.
It autoloads default objects that behave just like `WP_Post` and other native WordPress object but with some more handy stuff.
When you register a Custom Post Type `ll_company` your custom class `ll_company` gets loaded in the Twig context as `objects` variable within the `archive-company.twig`.  

Read up on [Clarkson objects and how they are initiated](https://level-level.github.io/Clarkson-Core/phpdoc/guide/getting-started/clarkson-objects.html).

## More documentation

- [Actions & filters](https://level-level.github.io/Clarkson-Core/hooks/)
- [Reference](https://level-level.github.io/Clarkson-Core/phpdoc/namespaces/clarkson-core.html)
- [Legacy documentation](https://github.com/level-level/Clarkson-Core/wiki)

## Tests
Currently 
1. Clone repository `git@github.com:level-level/Clarkson-Core.git clarkson-core`.
1. Run `composer install` in the new `clarkson-core` directory.
1. Run `composer run test`.
