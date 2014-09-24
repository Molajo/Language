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
use CommonApi\Language\TranslateInterface;

/**
 * Abstract Language Adapter
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class AbstractAdapter implements LanguageInterface, TranslateInterface
{
    /**
     * Get Language Properties
     *
     * Specify null for key to have all language properties for current language returned as object
     *
     * @param   null|string $key
     *
     * @return  int  $this
     * @since   1.0
     */
    abstract public function get($key = null);

    /**
     * Translate String
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    abstract public function translateString($string);
}
