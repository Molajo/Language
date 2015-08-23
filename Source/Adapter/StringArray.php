<?php
/**
 * StringArray Adapter for Language
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Adapter;

use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Language\LanguageInterface;
use CommonApi\Language\TranslateInterface;
use CommonApi\Exception\RuntimeException;
use stdClass;

/**
 * StringArray Adapter for Language
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class StringArray extends AbstractAdapter implements LanguageInterface, TranslateInterface
{
    /**
     * Language
     *
     * @var    string
     * @since  1.0.0
     */
    protected $language;

    /**
     * Title
     *
     * @var    string
     * @since  1.0.0
     */
    protected $title;

    /**
     * Tag
     *
     * @var    string
     * @since  1.0.0
     */
    protected $tag;

    /**
     * Locale
     *
     * @var    string
     * @since  1.0.0
     */
    protected $locale;

    /**
     * Rtl
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $rtl;

    /**
     * Direction
     *
     * @var    string
     * @since  1.0.0
     */
    protected $direction;

    /**
     * First Day
     *
     * @var    int
     * @since  1.0.0
     */
    protected $first_day;

    /**
     * UTC Offset
     *
     * @var    string
     * @since  1.0.0
     */
    protected $language_utc_offset;

    /**
     * Language Strings for the language loaded in this instance
     *
     * @var    array
     * @since  1.0.0
     */
    protected $language_strings = array();

    /**
     * Primary Language
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $primary_language = true;

    /**
     * List of Properties
     *
     * @var    array
     * @since  1.0.0
     */
    protected $property_array
        = array(
            'language',
            'title',
            'tag',
            'locale',
            'rtl',
            'direction',
            'first_day',
            'language_utc_offset',
            'primary_language'
        );

    /**
     * Backup language instances
     *
     * @var    array
     * @since  1.0.0
     */
    protected $language_instances = array();

    /**
     * Untranslated Instance
     *
     * @var    object  CommonApi\Language\CaptureUntranslatedStringInterface
     * @since  1.0.0
     */
    protected $untranslated;

    /**
     * Constructor
     *
     * @param  array                              $options
     * @param  array                              $language_strings
     * @param  CaptureUntranslatedStringInterface $untranslated
     * @param  LanguageInterface                  $default_language
     * @param  LanguageInterface                  $en_gb_instance
     *
     * @since  1.0.0
     */
    public function __construct(
        array $options,
        array $language_strings,
        CaptureUntranslatedStringInterface $untranslated,
        LanguageInterface $default_language = null,
        LanguageInterface $en_gb_instance = null
    ) {
        $this->setLanguageMetadata($options);
        $this->language_strings = $language_strings;
        $this->untranslated     = $untranslated;
        $this->setBackupInstances($default_language, $en_gb_instance);
    }

    /**
     * Get Language Properties
     *
     * Specify null for key to have all language properties for current language returned as object
     *
     * @param   null|string $key
     *
     * @return  mixed
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function get($key = null)
    {
        if ($key === null) {
            return $this->getAll();
        }

        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new RuntimeException(
                'Language StringArray Adapter: Attempting to get value for unknown property: ' . $key
            );
        }

        return $this->$key;
    }

    /**
     * Translate String
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    public function translateString($string)
    {
        if (trim($string) === '') {
            return $string;
        }

        $key = strtolower($string);

        if (isset($this->language_strings[$key])) {
            return $this->language_strings[$key];
        }

        if ($this->primary_language === true) {
            return $this->translateStringNotFound($key, $string);
        }

        return $string;
    }

    /**
     * Search language for string
     *
     * @param   string $key
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    protected function translateStringNotFound($key, $string)
    {
        $results = $this->searchBackupLanguages($key);

        if ($results === $key) {
            return $this->setString($string);
        }

        return $results;
    }

    /**
     * Search other languages for string, for primary language only
     *
     * @param   string $key
     *
     * @return  string
     * @since   1.0.0
     */
    protected function searchBackupLanguages($key)
    {
        if (count($this->language_instances) === 0) {
            return $key;
        }

        foreach ($this->language_instances as $key => $language_instance) {

            $result = $language_instance->translateString($key);

            if ($result === $key) {
            } else {
                return $result;
            }
        }

        return $key;
    }

    /**
     * Save untranslated strings for localization, for primary language only
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    protected function setString($string)
    {
        return $this->untranslated->setString($string);
    }

    /**
     * Set Language Metadata
     *
     * @param   array $options
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setLanguageMetadata(array $options = array())
    {
        foreach ($this->property_array as $key) {
            if (isset($options[$key])) {
                $this->$key = $options[$key];
            }
        }

        return $this;
    }

    /**
     * Load Backup language instances
     *
     * @param  LanguageInterface $default_language
     * @param  LanguageInterface $en_gb_instance
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setBackupInstances($default_language = null, $en_gb_instance = null)
    {
        if ($default_language === null) {
        } else {
            $this->language_instances['default'] = $default_language;
        }

        if ($en_gb_instance === null) {
        } else {
            $this->language_instances['en-GB'] = $en_gb_instance;
        }

        return $this;
    }

    /**
     * Get All Language Properties
     *
     * @return  stdClass
     * @since   1.0.0
     */
    protected function getAll()
    {
        $temp = new stdClass();

        foreach ($this->property_array as $key) {
            $temp->$key = $this->$key;
        }

        return $temp;
    }
}
