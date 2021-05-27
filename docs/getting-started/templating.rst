Templating
===

Clarkon Core allows you to write templates using `Twig <https://twig.symfony.com/>`_.

Structure
---

Clarkson Core looks into the ``templates/`` folder of the active theme, and a the parent theme if applicable to find files to load. 

Clarkson Core follows the same `template hierarchy <https://developer.wordpress.org/themes/basics/template-hierarchy/>`_ as WordPress does. 

The first file that exists will be displayed. An example for a ``404`` page the order would be:

1. ``wp-content/themes/child-theme/templates/404.twig``
2. ``wp-content/themes/parent-theme/templates/404.twig``
3. ``wp-content/themes/child-theme/404.php``
4. ``wp-content/themes/parent-theme/404.php``
5. ``wp-content/themes/child-theme/templates/index.twig``
6. ``wp-content/themes/parent-theme/templates/index.twig``
7. ``wp-content/themes/child-theme/index.php``
8. ``wp-content/themes/parent-theme/index.php``

An example theme structure
---

Imagine a custom WordPress theme with:

1. A custom post type ``event``.
2. A custom taxonomy ``venue``.
3. A custom ``contact`` page template.

You would structure the theme something like the following:

.. code-block::
    - templates/
      - 404.twig
      - archive.twig
      - archive-event.twig
      - embed-event.twig
      - single-event.twig
      - singular.twig
      - search.twig
      - taxonomy-venue.twig
      - template-contact.twig
    - embed.php
    - functions.php
    - home.php

Regular posts and pages would load ``templates/archive.twig`` and ``templates/singular.twig``.

The custom ``event`` post and the custom ``venue`` taxonomy would load ``templates/single-event.twig``, ``templates/archive-event.twig`` and ``templates/taxonomy-venue.twig``.

The user could select a custom template ``template-contact.twig`` for a page.

Embeds for the ``event`` post type would be handled by ``templates/embed-event.twig``, while all other embeds would be handled by ``embed.php``.

Search is handled by ``templates/search.twig``.

The 404 page is handled by ``templates/404.twig``.

The home page (blog) is handled by ``home.php``.

Overwriting default loading behaviour
---

You can use filters to determine how and where Clarkson Core will look for your templates. The following filters are available:

- `clarkson_twig_template_dirs <https://level-level.github.io/Clarkson-Core/hooks/clarkson_twig_template_dirs.html>`_
- `clarkson_twig_stylesheet_dir <https://level-level.github.io/Clarkson-Core/hooks/clarkson_twig_stylesheet_dir.html>`_
- `clarkson_twig_template_dir <https://level-level.github.io/Clarkson-Core/hooks/clarkson_twig_template_dir.html>`_
- `clarkson_core_template_paths <https://level-level.github.io/Clarkson-Core/hooks/clarkson_core_template_paths.html>`_
- `clarkson_twig_template_dirs <https://level-level.github.io/Clarkson-Core/hooks/clarkson_twig_template_dirs.html>`_

Adding (custom) page templates
---

WordPress allows developers to create custom templates for all post types, which can then be selected in a dropdown when editing the post in the WordPress editor.

Files in the ``templates/`` directory that match '``template-*.twig``' are automatically added to the template dropdown for pages.

If you want to hide a template from the page template dropdown, or you want to show your template in the dropdown for other posttypes, you can use on of the following filters:

- `clarkson_core_templates_types_for_{$name} <https://level-level.github.io/Clarkson-Core/hooks/clarkson_core_templates_types_for_%257B$name%257D.html>`_
- `clarkson_core_{$post_type}_templates <https://level-level.github.io/Clarkson-Core/hooks/clarkson_core_%257B$post_type%257D_templates.html>`_

Using PHP functions in your twig template
---

You can use functions in your twig template using the regular function calls in Twig.

.. code-block:: twig
    {{ get_stylesheet_directory_uri() }}

Clarkson Core registers a lot of functions that can be used in Twig by default.

.. warning:: 

    If you are trying to use a function that is not (yet) available in Twig, you can use the `clarkson_twig_function <https://level-level.github.io/Clarkson-Core/hooks/clarkson_twig_functions.html>`_ filter to add inyour custom functions.

Clarkson Objects in twig
---

Clarkson Core will have some :doc:`clarkson-objects` available in a template by default.

.. note::

    When ``WP_DEBUG`` is enabled, you can use the dump command (eg. ``{{ dump(object) }}``) to figure out which object you are working with.

.. code-block:: php

    // Page details (single.twig, single-*.twig, singular.twig, etc)
    [
        'object' => "Main item the single is about",
        'objects' => "(deprecated) Same as 'objects'",
    ]
    
.. code-block:: php

    // Archives (archive.twig, archive-*.twig, etc)
    [
        'object' => "First result of the query",
        'objects' => "All results of the query, maybe limited by posts_per_page.",
        'post_type' => "An object representing the post type this archive is for"
    ]

.. code-block:: php

    // Term archives (category.twig, taxonomy-*.twig, etc)
    [
        'object' => "First result of the query",
        'objects' => "All results of the query, maybe limited by posts_per_page.",
        'term' => "An object representing the term this archive is for"
    ]

.. code-block:: php

    // Authors (author.twig, etc)
    [
        'object' => "First result of the query",
        'objects' => "All results of the query, maybe limited by posts_per_page.",
        'user' => "(deprecated) An object representing the author"
        'author' => "An object representing the author"
    ]

.. code-block:: php

    // search (search.twig)
    [
        'object' => "First result of the query",
        'objects' => "All results of the query, maybe limited by posts_per_page.",
        'found_posts' => "Number of posts matching the query"
    ]


.. note::

    You can make custom objects available on your templates by using the `clarkson_core_template_context <https://level-level.github.io/Clarkson-Core/hooks/clarkson_core_template_context.html>`_ filter.

Rendering other templates
---

Sometimes you might want to render twig files outside of the normal WordPress template hierarchy.

Some usecases for this might admin panels, widget render callbacks or rendering e-mail contect.

In these cases you can use Clarkson Core to select the twig file to render and provide the appropriate environment.

.. code-block:: php
    :linenos:
    :emphasize-lines: 3,5
    
    <?php

    class EventWidget extends \WP_Widget {
        public function __construct( string $widget_title = '' ) {
            // Instantiate the widget.
        }

        public function widget( $args, $instance ) {
            $twig = \Clarkson_Core\Templates::get_instance();

            $twig->echo_twig(
                'partials/widgets/event.twig', array(
                    'args' => $args,
                ), true
            );
        }
    }

Whats next
---

- If you learn about :doc:`clarkson-objects`, you will be able to do much more with your templates.
- You can also read the `Twig for template designers documentation <https://twig.symfony.com/doc/3.x/templates.html>`_ to understand the capabilities and structure of Twig.
- Use tools like `TwigCS <https://github.com/friendsoftwig/twigcs>`_ to enforce best practices.
