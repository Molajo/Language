<?php
/**
 * Database Adapter for Language
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Adapter;

use CommonApi\Language\CaptureUntranslatedStringInterface;
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
class Database extends AbstractAdapter implements LanguageInterface,
                                                  TranslateInterface,
                                                  CaptureUntranslatedStringInterface
{
    /**
     * Language
     *
     * @var    string
     * @since  1.0.0
     */
    protected $language;

    /**
     * Extension ID
     *
     * @var    int
     * @since  1.0.0
     */
    protected $extension_id;

    /**
     * Extension Instance ID
     *
     * @var    int
     * @since  1.0.0
     */
    protected $extension_instance_id;

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
     * List of Properties
     *
     * @var    array
     * @since  1.0.0
     */
    protected $property_array = array(
        'language',
        'extension_id',
        'extension_instance_id',
        'title',
        'tag',
        'locale',
        'rtl',
        'direction',
        'first_day',
        'language_utc_offset',
        'language_strings',
        'model',
        'default_language',
        'en_gb_instance'
    );

    /**
     * Constructor
     *
     * @param  string $language
     * @param  int    $extension_id
     * @param  int    $extension_instance_id
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
        $extension_id,
        $extension_instance_id,
        $title,
        $tag,
        $locale,
        $rtl,
        $direction,
        $first_day,
        $language_utc_offset,
        $model,
        $default_language = null,
        $en_gb_instance = null
    ) {
        $this->language              = $language;
        $this->extension_id          = $extension_id;
        $this->extension_instance_id = $extension_instance_id;
        $this->title                 = $title;
        $this->tag                   = $tag;
        $this->locale                = $locale;
        $this->rtl                   = $rtl;
        $this->direction             = $direction;
        $this->first_day             = $first_day;
        $this->language_utc_offset   = $language_utc_offset;
        $this->model                 = $model;
        $this->default_language      = $default_language;
        $this->en_gb_instance        = $en_gb_instance;

        $this->language_strings = $this->model->getLanguageStrings($this->language);
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
     * @since   1.0
     */
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            $temp                        = new stdClass();
            $temp->extension_id          = $this->extension_id;
            $temp->extension_instance_id = $this->extension_instance_id;
            $temp->title                 = $this->title;
            $temp->tag                   = $this->tag;
            $temp->locale                = $this->locale;
            $temp->rtl                   = $this->rtl;
            $temp->direction             = $this->direction;
            $temp->first_day             = $this->first_day;
            $temp->language_utc_offset   = $this->language_utc_offset;

            return $temp;
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
     * @param   string|array $string
     *
     * @return  string
     * @since   1.0
     */
    public function translateString($string)
    {
        if (is_array($string)) {

            $found = array();

            foreach ($string as $item) {
                $found[$item] = $this->search($string);
            }

            return $found;
        }

        return $this->search($string);
    }

    /**
     * Search sequence for translation:
     *
     *  - Current language
     *  - Default language
     *  - Final fallback en-GB
     *  - Store as untranslated string - send back, as is
     *
     * @param   string  $string
     *
     * @return  string
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function search($string)
    {
        $key = strtolower($string);

        if (isset($this->language_strings[$key])) {
            return $this->language_strings[$key];
        }

        if (is_object($this->default_language)) {
            $result = $this->default_language->translate($key);
            if ($result == $key) {
            } else {
                return $result;
            }
        }

        if (is_object($this->en_gb_instance)) {
            $result = $this->en_gb_instance->translate($key);
            if ($result == $key) {
            } else {
                return $result;
            }
        }

        $this->setString($key);

        return $string;
    }

    /**
     * Save untranslated strings for localization
     *
     * @param   string $string
     *
     * @return  bool
     * @since   1.0
     */
    public function setString($string)
    {
        $this->model->setString($string);

        return $this;
    }
}
