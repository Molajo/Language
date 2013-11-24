<?php
/**
 * Abstract Language Handler
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Handler;

use CommonApi\Language\LanguageInterface;
use Exception\Language\LanguageException;

/**
 * Abstract Language Handler
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
abstract class AbstractHandler implements LanguageInterface
{
    /**
     * Get Language Properties
     *
     * Specify null for key to have all language properties for current language
     * returned aas an object
     *
     * @param   null|string $key
     * @param   null|string $default
     *
     * @return  int  $this
     * @since   1.0
     * @throws  \Exception\Language\LanguageException;
     */
    public function get($key = null, $default = null)
    {
        return $this;
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
     * @throws  \Exception\Language\LanguageException
     */
    public function translate($string)
    {
        return $string;
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
        return $string;
    }
}
