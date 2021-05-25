Clarkson objects
===

Clarkson Core provides a way for theme developers to work with objects. 

These objects represent different entities in your WordPress site, such as posts, users and terms.

With these objects, you can create custom features, reuse logic and extends the way WordPress works.

How objects are created
---

Objects are created mainly through the ``\Clarkson_Core\Objects`` class. 

This class takes a resource and automatically tries to determine the best fitting object for it.

When you do not extend any objects in your theme, Clarkson Core will fall back to default objects, such as ``\Clarkson_Core\WordPress_Object\Clarkson_Object``.

If you do require custom features on your objects, you can extend the default object with your own.

Understanding the object hierarchy, loading custom objects
---

Much like the way the WordPress template hierarchy tries to determine the most applicable template for a page, Clarkson Core tries to load the most applicable PHP object.

.. warning::

    It is possible to register post types and taxonomies with names that can not lead to valid PHP class names. 

    Clarkson Core always normalizes an object name by making it lowercase, and replacing all non-alphanumeric characters with an ``_``.

    Eg: ``Example-PostType`` becomes ``example_posttype``.

You can use `Composer PSR-4 autoloading <https://getcomposer.org/doc/04-schema.md#psr-4>`_ to define where Clarkson Core should look for these objects. You can define the ``\Clarkson_Core\WordPress_Object`` namespace.

.. code-block:: json

    "autoload": {
        "psr-4": {
            "Clarkson_Core\\WordPress_Object\\": "themes/child-theme/app/WordPress_Objects/"
        }
    }

Objects (Posts, Pages, custom post types)
***

1. \Clarkson_Core\WordPress_Object\$post_type
2. \Clarkson_Core\WordPress_Object\base_object
3. \Clarkson_Core\WordPress_Object\Clarkson_Object (default)

Terms
***

1. \Clarkson_Core\WordPress_Object\$taxonomy
2. \Clarkson_Core\WordPress_Object\base_term
3. \Clarkson_Core\WordPress_Object\Clarkson_Term (default)

Users
***

1. \Clarkson_Core\WordPress_Object\user
2. \Clarkson_Core\WordPress_Object\Clarkson_User (default)

Templates
***

1. \Clarkson_Core\WordPress_Object\$template
2. \Clarkson_Core\WordPress_Object\base_template
3. \Clarkson_Core\WordPress_Object\Clarkson_Template (default)

Post types
***

1. \Clarkson_Core\WordPress_Object\post_type_$post_type
2. \Clarkson_Core\WordPress_Object\base_post_type
3. \Clarkson_Core\WordPress_Object\Clarkson_Post_Type (default)

Blocks (Gutenberg)
***
1. \Gutenberg\Blocks\$block_name
2. \Gutenberg\Blocks\base_block
3. Clarkson_Core\Gutenberg\Block_Type (default)

Extending objects
---

You can use the hierarchy in WordPress objects to define custom behaviour.

As an example:

.. code-block:: php

    <?php
    // wp-content/themes/child-theme/app/WordPress_Objects/event.php

    namespace Clarkson_Core\WordPress_Object;

    class event extends base_object{
        public static $type = 'event';

        public function get_event_date(): \DateTime{
            // This method is only available on the `event` object.
        }
    }

As you can see in the example above, you can create an object that specifically handles event functions, for the event posttype. 

In the example, this ``event`` objects extends from ``base_object``. You can use ``base_object`` as a way to create methods that are available on **all** objects within your theme.

.. code-block:: php
    
    <?php
    // wp-content/themes/child-theme/app/WordPress_Objects/base_object.php

    namespace Clarkson_Core\WordPress_Object;

    class base_object extends Clarkson_Object{
        public function has_teaser_video(): string{
            // This method is available on all objects Clarkson Core loads.
        }
    }

The created ``base_object`` extends from the default ``Clarkson_Object``, that comes with Clarson Core.

The original ``Clarkson_Object`` provides a lot of utility functions, which you can find in the API reference.

Retrieving resources from the database
---

When retrieving data from the database, you directly want to have the correct object to work with. Here we list some methods of getting objects and converting standard WordPress objects.

Get multiple resources at once
***
Every type of object has a ``get_many`` function. This allows you to perform a database query for the resource type and the result will be automatically converted into Clarkson Objects.

.. code-block:: php

    // Get 20 event objects.
    $events = \Clarkson_Core\WordPress_Object\event::get_many( array(
        'posts_per_page' => '20'
    ) );

    // Get users with a 'subscriber' role.
    $users = \Clarkson_Core\WordPress_Object\Clarkson_User::get_many( array(
        'role' => 'subscriber'
    ) );

    // Get 5 terms of the `venue` type.
    $venues = \Clarkson_Core\WordPress_Object\venue::get_many( array( 
        'number' => 5 
    ) );

Get a single Clarkson_Object by ID
***

Every relevant type of object has a ``get`` function, in which you can specify an object to retrieve.

.. code-block:: php

    // Get an event with ID 5
    $event = \Clarkson_Core\WordPress_Object\event::get( 5 );

.. note::

    If the type of the resource you request is not the same as the object you are requesting it on, Clarkson_Core will automatically pick the correct object type for you.

.. code-block:: php

    // Post ID 6 is of type 'page'
    $event = \Clarkson_Core\WordPress_Object\event::get( 6 ); // Notice we are requesting it on the `event` object.
    var_dump( get_class( $event ) ); // `Clarkson_Core\WordPress_Object\page`, `Clarkson_Core\WordPress_Object\base_object`, or `Clarkson_Core\WordPress_Object\Clarkson_Object`.

Converting a WP_Post to a Clarkson Object
***

Sometimes you'll have a basic WordPress model available that you want to convert to a Clarkson Object.

To do this, you ca use the utility class ``\Clarkson_Core\Objects`` to convert to the correct type.

.. code-block:: php

    $event_wp_post = get_post( 5 ); // a WP_Post object.
    $event = \Clarkson_Core\Objects::get_instance()->get_object( $event_wp_post ); // A \Clarkson_Core\WordPress_Object\event object.

This conversion type of method is available for all types. See  the ``\Clarkson_Core\Objects`` reference for these methods.

Overwriting class loading behaviour.
---

In some cases you might want more flexibility in determining which object is retrieved.

The following filters are available to manipulate the Clarkson Core object creation process:

- `clarkson_core_create_object_callback <https://level-level.github.io/Clarkson-Core/hooks/clarkson_core_create_object_callback.html>`_
- `clarkson_object_type <https://level-level.github.io/Clarkson-Core/hooks/clarkson_object_type.html>`_
- `clarkson_post_type_class <https://level-level.github.io/Clarkson-Core/hooks/clarkson_post_type_class.html>`_
- `clarkson_role_class <https://level-level.github.io/Clarkson-Core/hooks/clarkson_role_class.html>`_
- `clarkson_template_class <https://level-level.github.io/Clarkson-Core/hooks/clarkson_template_class.html>`_
- `clarkson_term_types <https://level-level.github.io/Clarkson-Core/hooks/clarkson_term_types.html>`_
- `clarkson_user_type <https://level-level.github.io/Clarkson-Core/hooks/clarkson_user_type.html>`_