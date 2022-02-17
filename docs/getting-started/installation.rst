Installation
===

Requirements
---

- PHP 7.4 or higher
- WordPress 4.7 or higher

Using composer
---

To install you simply use `composer <https://getcomposer.org/>`_ to add Clarkson Core as a dependency of your project.

.. code-block:: shell

    composer require level-level/clarkson-Core

Autoloading
---

After you install Clarkson Core, don't forget to load the composer autoload file somewhere in your project.

You can use the `Level Level Plugin Autoloader <https://github.com/level-level/ll-plugin-autoloader>`_ to load your composer dependencies automatically.

Another way to load the autoload file is to adjust your themes ``functions.php`` file.

.. code-block:: php
    <?php
    require_once 'vendor/autoload.php'

