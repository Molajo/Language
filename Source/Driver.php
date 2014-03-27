<?php
/**
 * Adapter for Language
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language;

use CommonApi\Language\LanguageInterface;

/**
 * Language Driver
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class Driver implements LanguageInterface
{
    /**
     * Language Adapter
     *
     * @var     object CommonApi\Language\LanguageInterface
     * @since   1.0
     */
    protected $adapter;

    /**
     * Language
     *
     * @var    string
     * @since  1.0
     */
    protected $language;

    /**
     * Constructor
     *
     * @param   LanguageInterface $language
     *
     * @since   1.0
     */
    public function __construct(
        LanguageInterface $adapter,
        $language
    ) {
        $this->adapter  = $adapter;
        $this->language = $language;
    }

    /**
     * Get Language Properties
     *
     * Specify null for key to have all language properties for current language
     * returned aas an object
     *
     * @param   null|string $key
     * @param   null|string $default
     *
     * @return  int
     * @since   1.0
     */
    public function get($key = null, $default = null)
    {
        return $this->adapter->get($key, $default);
    }

    /**
     * Translate String
     *
     *  - Current language
     *  - Default language
     *  - Final fallback en-GB
     *  - Store as untranslated string
     *
     * @param   $string
     *
     * @return  string
     * @since   1.0
     */
    public function translate($string)
    {
        return $this->adapter->translate($string);
    }

    /**
     * Store Untranslated Language Strings
     *
     * @param   $string
     *
     * @return  $this
     * @since   1.0
     */
    public function setUntranslatedString($string)
    {
        return $this->adapter->setUntranslatedString($string);
    }
}