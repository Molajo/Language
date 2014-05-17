<?php
/**
 * Database Adapter for Language
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Adapter;

use CommonApi\Language\LanguageInterface;
use CommonApi\Language\TranslateInterface;
use CommonApi\Exception\RuntimeException;
use stdClass;

/**
 * Database Adapter for Language
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class Database extends AbstractAdapter implements LanguageInterface, TranslateInterface
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
     * Model Instance - save untranslated strings
     *
     * @var    object  CommonApi\Language\DatabaseModelInterface
     * @since  1.0.0
     */
    protected $model;

    /**
     * Default Language Instance
     *
     * @var    null|object  CommonApi\Language\LanguageInterface
     * @since  1.0.0
     */
    protected $default_language;

    /**
     * Final Fallback en-GB Language Instance
     *
     * @var    null|object  CommonApi\Language\LanguageInterface
     * @since  1.0.0
     */
    protected $en_gb_instance;

    /**
     * Primary Language
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $primary_language = true;

    /**
     * Backup language instances
     *
     * @var    array
     * @since  1.0.0
     */
    protected $language_instances = array('default_language', 'en_gb_instance');

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
            'language_strings',
            'model',
            'primary_language',
            'default_language',
            'en_gb_instance'
        );

    /**
     * Constructor
     *
     * @param  string $language
     * @param  string $title
     * @param  string $tag
     * @param  string $locale
     * @param  string $rtl
     * @param  string $direction
     * @param  int    $first_day
     * @param  int    $language_utc_offset
     * @param  object $model
     * @param  object $default_language
     * @param  object $en_gb_instance
     *
     * @since  1.0.0
     */
    public function __construct(
        $language,
        $title,
        $tag,
        $locale,
        $rtl,
        $direction,
        $first_day,
        $language_utc_offset,
        $model,
        $primary_language = true,
        $default_language = null,
        $en_gb_instance = null
    ) {
        $this->model = $model;

        $this->model->getLanguageStrings($language);

        $this->model->setLanguageMetadata(
            $language,
            $title,
            $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset
        );

        $this->primary_language = $primary_language;
        $this->default_language = $default_language;
        $this->en_gb_instance   = $en_gb_instance;
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
     * @return  mixed
     * @since   1.0
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->getAll();
        }

        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new RuntimeException(
                'Language Database Adapter: Attempting to get value for unknown property: ' . $key
            );
        }

        if ($this->$key === null) {
            $this->$key = $default;
        }

        return $this->$key;
    }

    /**
     * Translate String
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0
     */
    public function translateString($string)
    {
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
        $results = $this->getBackupLanguage($key);

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
    protected function getBackupLanguage($key)
    {
        foreach ($this->language_instances as $language) {

            $result = $this->translateStringLanguage($key, $language);

            if ($result === $key) {
            } else {
                return $result;
            }
        }

        return $key;
    }

    /**
     * Search another language for string, for primary language only
     *
     * @param   string $key
     * @param   string $language
     *
     * @return  string
     * @since   1.0.0
     */
    protected function translateStringLanguage($key, $language)
    {
        if (is_object($this->$language)) {
            return $this->$language->translateString($key);
        }

        return $key;
    }

    /**
     * Save untranslated strings for localization, for primary language only
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0
     */
    protected function setString($string)
    {
        $this->model->setString($string);

        return $string;
    }

    /**
     * Set Language Metadata
     *
     * @param   string $language
     * @param   string $title
     * @param   string $tag
     * @param   string $locale
     * @param   string $rtl
     * @param   string $direction
     * @param   int    $first_day
     * @param   int    $language_utc_offset
     *
     * @return  $this
     * @since   1.0
     */
    protected function setLanguageMetadata(
        $language,
        $title,
        $tag,
        $locale,
        $rtl,
        $direction,
        $first_day,
        $language_utc_offset
    ) {
        $this->language            = $language;
        $this->title               = $title;
        $this->tag                 = $tag;
        $this->locale              = $locale;
        $this->rtl                 = $rtl;
        $this->direction           = $direction;
        $this->first_day           = $first_day;
        $this->language_utc_offset = $language_utc_offset;

        return $this;
    }

    /**
     * Get All Language Properties
     *
     * @return  stdClass
     * @since   1.0
     */
    protected function getAll()
    {
        $temp = new stdClass();

        foreach ($this->property_array as $key) {
            if ($key === 'language_strings') {
            } else {
                $temp->$key = $key;
            }
        }

        return $temp;
    }
}
