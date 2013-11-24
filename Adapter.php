<?php
/**
 * Adapter for Language
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language;

use Exception\Language\AdapterException;
use CommonApi\Language\LanguageInterface;

/**
 * Adapter for Language
 *
 * @package    Molajo
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0
 */
class Adapter implements LanguageInterface
{
    /**
     * Language Adapter Handler
     *
     * @var     object CommonApi\Language\LanguageInterface
     * @since   1.0
     */
    protected $handler;

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
        LanguageInterface $handler,
        $language
    ) {
        $this->handler  = $handler;
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
     * @throws  \Exception\Language\AdapterException
     */
    public function get($key = null, $default = null)
    {
        return $this->handler->get($key, $default);
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
     * @throws  \Exception\Language\AdapterException
     */
    public function translate($string)
    {
        return $this->handler->translate($string);
    }

    /**
     * Store Untranslated Language Strings
     *
     * @param   $string
     *
     * @return  $this
     * @since   1.0
     * @throws  \Exception\Language\AdapterException
     */
    public function setUntranslatedString($string)
    {
        return $this->handler->setUntranslatedString($string);
    }
}
