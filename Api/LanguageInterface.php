<?php
/**
 * Language Interface
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Api;

defined('MOLAJO') or die;

use Molajo\Language\Exception\LanguageException;

/**
 * Language Services
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
interface LanguageInterface
{
    /**
     * Get language property
     *
     * @param   string  $key
     * @param   string  $default
     *
     * @return  array|mixed|string
     * @throws  LanguageException
     * @since   1.0
     */
    public function get($key, $default = '');

    /**
     * Set the value of the specified key
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  $this
     * @since   1.0
     * @throws  LanguageException
     */
    public function set($key, $value = null);

    /**
     * Translate String in loaded language or create new instance to translate using fall back language
     *
     * @param   string  $string
     * @param   int     $list
     *
     * @return  array|string
     * @since   1.0
     */
    public function translate($string, $list = 0);

    /**
     * Language strings found within the code but not translated can be logged
     *
     * @param   $string
     *
     * @return  $this
     * @since   1.0
     */
    public function logUntranslatedString($string);
}
