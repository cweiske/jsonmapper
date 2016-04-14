********************************************************
JsonMapper - map nested JSON structures onto PHP classes
********************************************************

.. image:: https://api.travis-ci.org/cweiske/jsonmapper.png
   :target: https://travis-ci.org/cweiske/jsonmapper
   :align: right

Takes data retrieved from a JSON__ web service and converts them
into nested object and arrays - using your own model classes.

Starting from a base object, it maps JSON data on class properties,
converting them into the correct simple types or objects.

It's a bit like the native SOAP parameter mapping PHP's ``SoapClient``
gives you, but for JSON.
Note that it does not rely on any schema, only your class definitions.

Type detection works by parsing ``@var`` docblock annotations of
class properties, as well as type hints in setter methods.

You do not have to modify your model classes by adding JSON specific code;
it works automatically by parsing already-existing docblocks.

Keywords: deserialization, hydration

__ http://json.org/


.. contents::

============
Pro & contra
============

Benefits
========
- Autocompletion in IDEs
- It's easy to add comfort methods to data model classes
- Your JSON API may change, but your models can stay the same - not
  breaking applications that use the model classes.

Drawbacks
=========
- Model classes need to be written by hand

  Since JsonMapper does not rely on any schema information
  (e.g. from `json-schema`__), model classes cannot be generated
  automatically.

__ http://json-schema.org/


=====
Usage
=====

Basic usage
===========
#. Register an autoloader that can load `PSR-0`__ compatible classes.
#. Create a ``JsonMapper`` object instance
#. Call the ``map`` or ``mapArray`` method, depending on your data

Map a normal object:

.. code:: php

    <?php
    require 'autoload.php';
    $mapper = new JsonMapper();
    $contactObject = $mapper->map($jsonContact, new Contact());
    ?>

Map an array of objects:

.. code:: php

    <?php
    require 'autoload.php';
    $mapper = new JsonMapper();
    $contactsArray = $mapper->mapArray(
        $jsonContacts, array(), 'Contact'
    );
    ?>

Instead of ``array()`` you may also use ``ArrayObject`` and descending classes.

__ http://www.php-fig.org/psr/psr-0/


Example
=======
JSON from a address book web service:

.. code:: javascript

    {
        'name':'Sheldon Cooper',
        'address': {
            'street': '2311 N. Los Robles Avenue',
            'city': 'Pasadena'
        }
    }

Your local ``Contact`` class:

.. code:: php

    <?php
    class Contact
    {
        /**
         * Full name
         * @var string
         */
        public $name;

        /**
         * @var Address
         */
        public $address;
    }
    ?>

Your local ``Address`` class:

.. code:: php

    <?php
    class Address
    {
        public $street;
        public $city;

        public function getGeoCoords()
        {
            //do something with the $street and $city
        }
    }
    ?>

Your application code:

.. code:: php

    <?php
    $json = json_decode(file_get_contents('http://example.org/bigbang.json'));
    $mapper = new JsonMapper();
    $contact = $mapper->map($json, new Contact());

    echo "Geo coordinates for " . $contact->name . ": "
        . var_export($contact->address->getGeoCoords(), true);
    ?>


Property type documentation
===========================
``JsonMapper`` uses several sources to detect the correct type of
a property:

#. The setter method (``set`` + ``ucwords($propertyname)``) is inspected.

   Underscores make the next letter uppercase, which means that
   for a JSON property ``foo_bar_baz`` a setter method of
   ``setFooBarBaz`` is used.

   #. If it has a type hint in the method signature, this type used::

        public function setPerson(Contact $person) {...}

   #. The method's docblock is inspected for ``@param $type`` annotations::

        /**
         * @param Contact $person Main contact for this application
         */
        public function setPerson($person) {...}

   #. If no type could be detected, the plain JSON value is passed
      to the setter method.

#. ``@var $type`` docblock annotation of class properties::

    /**
     * @var \my\application\model\Contact
     */
    public $person;

   Note that the property has to be public to be used directly.

   If no type could be detected, the property gets the plain JSON value.

   If a property can not be found, JsonMapper tries to find the property
   in a case-insensitive manner.
   A JSON property ``isempty`` would then be mapped to a PHP property
   ``isEmpty``.

Supported type names:

- Simple types:

  - ``string``
  - ``bool``, ``boolean``
  - ``int``, ``integer``
  - ``float``
  - ``array``
  - ``object``
- Class names, with and without namespaces

  - ``Contact`` - will throw exception if JSON value is NULL
- Arrays of simple types and class names:

  - ``int[]``
  - ``Contact[]``
- ArrayObjects of simple types and class names:

  - ``ContactList[Contact]``
  - ``NumberList[int]``
- Nullable types:

  - ``int|null`` - will be ``null`` if the value in JSON is
    ``null``, otherwise it will be an integer
  - ``Contact|null`` - will be ``null`` if the value in JSON is
    ``null``, otherwise it will be an object of type ``Contact``

ArrayObjects and extending classes are treated as arrays.

Variables without a type or with type ``mixed`` will get the
JSON value set directly without any conversion.

See `phpdoc's type documentation`__ for more information.

__ http://phpdoc.org/docs/latest/references/phpdoc/types.html


Simple type mapping
-------------------
When an object shall be created but the JSON contains a simple type
only (e.g. string, float, boolean), this value is passed to
the classes' constructor. Example:

PHP code:

.. code:: php

    /**
     * @var DateTime
     */
    public $date;

JSON:

.. code:: js

    {"date":"2014-05-15"}

This will result in ``new DateTime('2014-05-15')`` being called.


Class map
---------
When variables as defined as objects ofabstract classes or interfaces,
JsonMapper would normally try to instantiate those directly and crash.

Using JsonMapper's ``$classMap`` property, you can specify which classes
shall get instantiated instead:

.. code:: php

    $jm = new JsonMapper();
    $jm->classMap['Foo'] = 'Bar';
    $jm->map(...);

This would create objects of type ``Bar`` when a variable is defined to be
of type ``Foo``.


Logging
=======
JsonMapper's ``setLogger()`` method supports all PSR-3__ compatible
logger instances.

Events that get logged:

- JSON data contain a key, but the class does not have a property
  or setter method for it.
- Neither setter nor property can be set from outside because they
  are protected or private

__ http://www.php-fig.org/psr/psr-3/


Handling invalid or missing data
================================
During development, APIs often change.
To get notified about such changes, JsonMapper may throw exceptions
in case of either missing or yet unknown data.


Unknown properties
------------------
When JsonMapper sees properties in the JSON data that are
not defined in the PHP class, you can let it throw an exception
by setting ``$bExceptionOnUndefinedProperty``:

.. code:: php

    $jm = new JsonMapper();
    $jm->bExceptionOnUndefinedProperty = true;
    $jm->map(...);

You may also choose to handle those properties yourself by setting
a callable__ to ``$undefinedPropertyHandler``:

__ http://php.net/manual/en/language.types.callable.php

.. code:: php

    /**
     * Handle undefined properties during JsonMapper::map()
     *
     * @param object $object    Object that is being filled
     * @param string $propName  Name of the unknown JSON property
     * @param mixed  $jsonValue JSON value of the property
     *
     * @return void
     */
    function setUndefinedProperty($object, $propName, $jsonValue)
    {
        $object->{'UNDEF' . $propName} = $jsonValue;
    }

    $jm = new JsonMapper();
    $jm->undefinedPropertyHandler = 'setUndefinedProperty';
    $jm->map(...);


Missing properties
------------------
Properties in your PHP classes can be marked as "required" by
putting ``@required`` in their docblock:

.. code:: php

    /**
     * @var string
     * @required
     */
    public $someDatum;

When the JSON data do not contain this property, JsonMapper will throw
an exception when ``$bExceptionOnMissingData`` is activated:

.. code:: php

    $jm = new JsonMapper();
    $jm->bExceptionOnMissingData = true;
    $jm->map(...);


Simple types instead of objects
-------------------------------
When a variable's type is a class and JSON data is a simple type
like ``string``, JsonMapper passes this value to the class' constructor.

If you do not want this, set ``$bStrictObjectTypeChecking`` to ``true``:

.. code:: php

    $jm = new JsonMapper();
    $jm->bStrictObjectTypeChecking = true;
    $jm->map(...);

An exception is thrown then in such cases.


Passing arrays to ``map()``
---------------------------
You may wish to pass array data into ``map()`` that you got by calling

.. code:: php

    json_decode($jsonString, true)

By default, JsonMapper will throw an exception because ``map()`` requires
an object as first parameter.
You can circumvent that by setting ``$bEnforceMapType`` to ``false``:

.. code:: php

    $jm = new JsonMapper();
    $jm->bEnforceMapType = false;
    $jm->map(...);


============
Installation
============

via Composer
============
From Packagist__::

    $ composer require netresearch/jsonmapper

__ https://packagist.org/packages/netresearch/jsonmapper


via PEAR
========
.. warning::
   Version 0.10.0 was the last version released on this PEAR channel.

From our `PEAR channel`__::

    $ pear channel-discover pear.nrdev.de
    $ pear install nr/jsonmapper-alpha

__ http://pear.nrdev.de/




================
Related software
================
- `Jackson's data binding`__ for Java
- `Johannes Schmitt Serializer`__ for PHP

__ http://wiki.fasterxml.com/JacksonDataBinding
__ http://jmsyst.com/libs/serializer


================
About JsonMapper
================

License
=======
JsonMapper is licensed under the `OSL 3.0`__.

__ http://opensource.org/licenses/osl-3.0


Coding style
============
JsonMapper follows the `PEAR Coding Standards`__.

__ http://pear.php.net/manual/en/standards.php


Author
======
`Christian Weiske`__, `cweiske.de`__

__ mailto:cweiske@cweiske.de
__ http://cweiske.de/
