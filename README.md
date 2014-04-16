=======
Language Package
=======

[![Build Status](https://travis-ci.org/Molajo/Language.png?branch=master)](https://travis-ci.org/Molajo/Language)

Language Services supporting translations for User Interface language strings for PHP applications
Adapters for different implementation types (ex., Database, .PO, Joomla *.ini)

## System Requirements ##

* PHP 5.4, or above
* [PSR-0 compliant Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
* PHP Framework independent
* [optional] PHPUnit 3.5+ to execute the test suite (phpunit --version)

### How to specify Language ###

Language strings are loaded in startup for language determined in this order (and installed):

 1. Injected value during class instantiation
 2. Session
 3. User Data
 4. Client Language
 5. Application Configuration
 6. Defaults to en-GB

## Instantiation ##

To instantiate the Language Class:

 ```php
    $language = new Language($language);
 ```

Once instantiated, all calls to the class can be made using the $language instance.

## Language Properties ##

To retrieve the key value (ex. 'en-GB') for the language which is loaded:
 ```php
    $language->get('language');
 ```
### Retrieve Language Strings ###

To retrieve all language strings and translations for the loaded language:
 ```php
    $language->get('strings');
 ```
### Retrieve List of all installed Languages ###

To retrieve a list of all languages installed in this application:
 ```php
    $language->get('installed');
 ```
### Retrieve Language Attribute ###

To retrieve a registry attribute value (id, name, rtl, local, first_day) for the loaded language:
 ```php
    $language->get('name-of-attribute');
 ```
### Retrieve all Language Attributes ###

To retrieve all registry attribute values as an array for the loaded language:

 ```php
    $language->get('registry');
 ```
## Translate ##

To translate the string $xyz:
 ```php
    $language->translate($xyz);
 ```

To retrieve a list of language strings and translations matching a wildcard value:
 ```php
    $language->translate($xyz, 1);
 ```

## Identify Untranslated Strings ##

To insert strings found in code but are not already in database:

- If an administrator is logged on, the primary language services automatically insert untranslated strings
- To avoid doing so, override the `LanguagePlugin` and set `insert_missing_strings` to `0`
- For instances you define, set the `insert_missing_strings`, as needed.

To log strings found in code, but are not already in database:

- Set the Application configuration option `profile_missing_strings` to `1` and turn on `profiling`
