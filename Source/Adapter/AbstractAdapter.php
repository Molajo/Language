<?php
/**
 * Abstract Language Adapter
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Adapter;

use CommonApi\Language\LanguageInterface;

/**
 * Abstract Language Adapter
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class AbstractAdapter implements LanguageInterface
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
