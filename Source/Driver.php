<?php
/**
 * Adapter for Language
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language;

use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Language\LanguageInterface;

/**
 * Language Driver
 *
 * @package    Molajo
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @since      1.0.0
 */
class Driver implements LanguageInterface, TranslateInterface, CaptureUntranslatedStringInterface
{
    /**
     * Language Adapter
     *
     * @var     object CommonApi\Language\LanguageInterface
     * @since   1.0.0
     */
    protected $adapter;

    /**
     * Language
     *
     * @var    string
     * @since   1.0.0
     */
    protected $language;

    /**
     * Constructor
     *
     * @param   LanguageInterface $language
     *
     * @since   1.0.0
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
     * @return  int  $this
     * @since   1.0.0
     */
    public function get($key = null, $default = null)
    {
        return $this->adapter->get($key, $default);
    }

    /**
     * Translate String
     *
     * @param   $string
     *
     * @return  string
     * @since   1.0.0
     */
    public function translate($string)
    {
        return $this->adapter->translate($string);
    }

    /**
     * Save untranslated strings for localization
     *
     * @param   string $string
     *
     * @return  bool
     * @since   1.0.0
     */
    public function setString($string)
    {
        return $this->adapter->setString($string);
    }
}
