<?php
/**
 * Database Handler for Language
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language;

defined('MOLAJO') or die;

use Molajo\Language\Api\LanguageInterface;
use Molajo\Language\Exception\LanguageException;

/**
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class Database extends AbstractHandler implements LanguageInterface
{
    /**
     * Language used for this instance
     *
     * @var    string
     * @since  1.0
     */
    protected $language;

    /**
     * Controller Class
     *
     * @var    string
     * @since  1.0
     */
    protected $controller_class_namespace;

    /**
     * Default Language Instance if loaded language is missing translation and is not the default language
     *
     * @var    string
     * @since  1.0
     */
    protected $default_language_instance;

    /**
     * en-GB Language Instance if loaded language is missing translation and is not the en-GB language
     *
     * @var    string
     * @since  1.0
     */
    protected $en_gb_instance;

    /**
     * Language Registry for the language loaded in this instance
     *
     * @var    array
     * @since  1.0
     */
    protected $registry = array();

    /**
     * Installed Languages includes tags, like en-GB, for all installed languages
     *
     * @var    array
     * @since  1.0
     */
    protected $installed = array();

    /**
     * Language Strings for the language loaded in this instance
     *
     * @var    array
     * @since  1.0
     */
    protected $strings = array();

    /**
     * Indicator of whether or not missing language strings should be profiled
     *
     * @var    bool
     * @since  1.0
     */
    protected $profile_missing_strings;

    /**
     * Indicator of whether or not missing language strings should be inserted into the database
     *
     * @var    bool
     * @since  1.0
     */
    protected $insert_missing_strings;

    /**
     * List of Properties
     *
     * @var    object
     * @since  1.0
     */
    protected $property_array = array(
        'registry',
        'installed',
        'language',
        'strings',
        'profile_missing_strings',
        'insert_missing_strings'
    );

    /**
     * class constructor
     *
     * @param string $language
     *
     * @since   1.0
     */
    public function __construct($language = '')
    {
        $this->language = $language;

        return;
    }

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
    public function get($key, $default = '')
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {

            if (isset($this->$key)) {
            } else {
                $this->$key = $default;
            }

            return $this->$key;
        }

        if (isset($this->registry->$key)) {
            return $this->registry->$key;
        }

        throw new LanguageException
            ('Language Service: attempting to get value for unknown property: ' . $key);
    }

    /**
     * Set the value of the specified key
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function set($key, $value = null)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {

            $this->$key = $value;

            return $this->$key;
        }

        if (isset($this->registry->$key)) {
            $this->registry->$key = $value;

            return $this->registry->$key;
        }

        throw new LanguageException
            ('Language Service: attempting to get value for unknown property: ' . $key);
    }

    /**
     * Translate String in loaded language or create new instance to translate using fall back language
     *
     * @param   string $string
     * @param   int    $list
     *
     * @return  array|string
     * @since   1.0
     */
    public function translate($string, $list = 0)
    {
        $string = strtolower(trim($string));

        if ((int) $list == 1) {
            $found = array();

            $keys = array_keys($this->strings);
            foreach ($keys as $key) {
                if (strpos(strtolower($key), $string) === false) {
                } else {
                    $found[$key] = $this->strings[$key];
                }
            }

            return $found;
        }

        if (isset($this->strings->$string)) {
            return $this->strings->$string;
        }

        $this->logUntranslatedString($string);

        if ($this->language == $this->get('default_language')) {
            if ($this->language == 'en-GB') {
                return $string;

            } else {
                $this->en_gb_instance = new Language($this->get('en-GB'));
                $translated_string    = $this->en_gb_instance->translate($string);
            }

        } else {
            $this->default_language_instance = new Language($this->get('default_language'));
            $translated_string               = $this->default_language_instance->translate($string);
        }

        if ($translated_string == false) {
        } else {
            return $translated_string;
        }

        return $string;
    }

    /**
     * Language strings found within the code but not translated can be saved to the database
     *
     * @param   $string
     *
     * @return  void
     * @since   1.0
     */
    protected function insertUntranslatedString($string)
    {
        if ((int) $this->get('insert_missing_strings', 0) === 0) {
            return;
        }

        $controller_class_namespace = $this->controller_class_namespace;
        $controller      = new $controller_class_namespace();
        $controller->getModelRegistry('System', 'Languagestrings', 1);

        $controller->set('check_view_level_access', 0, 'model_registry');
        $controller->model->insertLanguageString($string);

        return;
    }

    /**
     * Language strings found within the code but not translated can be logged
     *
     * @param   $string
     *
     * @return  void
     * @since   1.0
     */
    protected function logUntranslatedString($string)
    {
        if ((int) $this->get('profile_missing_strings', 0) === 0) {
            return;
        }

        if (defined('PROFILER_ON') && PROFILER_ON === true) {

            $this->profiler->set(
                'Language Services: ' . $this->get('current_language', 'en-GB')
                    . ' Language is missing translation for string: ' . $string,
                'Application'
            );
        }

        return;
    }
}
