<?php
/**
 * Language Factory Method
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Factories\Language;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\IoC\FactoryInterface;
use CommonApi\IoC\FactoryBatchInterface;
use Molajo\IoC\FactoryMethod\Base as FactoryMethodBase;

/**
 * Language Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class LanguageFactoryMethod extends FactoryMethodBase implements FactoryInterface, FactoryBatchInterface
{
    /**
     * Language List
     *
     * @var     array
     * @since   1.0.0
     */
    protected $installed_languages = array();

    /**
     * Application ID
     *
     * @var     integer
     * @since   1.0.0
     */
    protected $application_id = null;

    /**
     * Language List
     *
     * @var     array
     * @since   1.0.0
     */
    protected $tag_array = array();

    /**
     * Installed Languages
     *
     * @var     array
     * @since   1.0.0
     */
    protected $installed_language = array();

    /**
     * Constructor
     *
     * @param  $options
     *
     * @since  1.0.0
     */
    public function __construct(array $options = array())
    {
        $options['product_name']             = basename(__DIR__);
        $options['store_instance_indicator'] = true;
        $options['product_namespace']        = 'Molajo\\Language\\Driver';

        parent::__construct($options);
    }

    /**
     * Instantiate a new adapter and inject it into the Adapter for the FactoryInterface
     *
     * @return  array
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setDependencies(array $reflection = array())
    {
        $this->reflection = array();

        $options                            = array();
        $this->dependencies                 = array();
        $this->dependencies['Fieldhandler'] = $options;
        $this->dependencies['Resource']     = $options;
        $this->dependencies['Runtimedata']  = $options;
        $this->dependencies['User']         = $options;

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  object
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function instantiateClass()
    {
        $this->application_id = $this->dependencies['Runtimedata']->application->id;

        $this->getInstalledLanguages();

        $language = $this->setLanguage();

        $language_strings = $this->getLanguageStrings($language);
        $untranslated     = $this->getUntranslatedInstance();
        $adapter          = $this->instantiateStringArrayAdapter($untranslated, $language, $language_strings);

        try {
            $this->product_result = new $this->product_namespace($adapter, $language);

        } catch (Exception $e) {

            throw new RuntimeException (
                'IoC Factory Method Adapter Instance Failed for ' . $this->product_namespace
                . ' failed.' . $e->getMessage()
            );
        }

        return $this;
    }

    /**
     * Get Installed Languages
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function getInstalledLanguages()
    {
        $this->setQueryUsageTraitProperties();

        $this->setQueryController('Molajo//Model//Datasource//Languages.xml', 'Read');

        $this->setQueryControllerDefaults(
            $process_events = 0,
            $query_object = 'item',
            $get_customfields = 0,
            $use_special_joins = 1,
            $use_pagination = 0,
            $check_view_level_access = 0,
            $get_item_children = 0
        );

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');

        $this->query->where('column', $prefix . '.catalog_type_id', '=', 'integer', (int)6000);
        $this->query->where('column', $prefix . '.catalog_type_id', '<>', 'column', 'extension_id');

        $data = $this->runQuery();

        $this->setModelRegistry();

        $language = $this->setStandardFields($data, $this->model_registry);
        $language = $this->setCustomFields($language, $data, $this->model_registry);

        $language->language_utc_offset = null;

        $this->installed_languages[$language->parameters->tag] = $language;
        $this->tag_array[]                                     = $language->parameters->tag;

        return $this;
    }

    /**
     * Sets language based on specific order of checking values
     *
     * @return  string
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function setLanguage()
    {
        if (count($this->tag_array) == 1) {
            $languages = $this->tag_array;
            $language  = $languages[0];

            return $language;
        }

        $language = $this->dependencies['User']->getUserdata()->language;

        if (in_array($language, $this->tag_array)) {
            return $language;
        }

        $language = $this->dependencies['Runtimedata']->application->parameters->language;
        if (in_array($language, $this->tag_array)) {
            return $language;
        }

        //todo: needs work (and likely not worth it)
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (count($browserLanguages) > 0) {
                foreach ($browserLanguages as $language) {
                    if (in_array($language, $this->tag_array)) {
                        return $language;
                    }
                }
            }
        }

        $language = 'en-GB';
        if (in_array($language, $this->tag_array)) {
            return $language;
        }

        throw new RuntimeException(
            'Language Factory Method: No Language Defined.'
        );
    }

    /**
     * Retrieve Language Strings for Current Language
     *
     * @return  array
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getLanguageStrings($language)
    {
        $this->setQueryUsageTraitProperties();

        $this->setQueryController('Molajo//Model//Datasource//Languagestrings.xml', 'Read');

        $this->setQueryControllerDefaults(
            $process_events = 0,
            $query_object = 'list',
            $get_customfields = 0,
            $use_special_joins = 1,
            $use_pagination = 0,
            $check_view_level_access = 1
        );

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');

        $this->query->select($prefix . '.title');
        $this->query->select($prefix . '.content_text');
        $this->query->where('column', $prefix . '.language', '=', 'string', $language);
        $this->query->orderBy($prefix . '.title', 'ASC');

        $data = $this->runQuery();

        $strings = array();
        foreach ($data as $item) {
            $title           = strtolower($item->title);
            $strings[$title] = $item->content_text;
        }

        return $strings;
    }

    /**
     * Instantiate Untranslated Instance
     *
     * @return  object
     * @since   1.0.0
     */
    protected function getUntranslatedInstance()
    {
        $class = 'Molajo\\Language\\Capture\\Database';

        return new $class(
            $this->dependencies['Resource'],
            $this->dependencies['Fieldhandler'],
            $this->dependencies['Runtimedata'],
            $this->installed_languages
        );
    }

    /**
     * Instantiate StringArray Adapter for Language
     *
     * @param   string $model
     * @param   string $language
     *
     * @return  object
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function instantiateStringArrayAdapter($untranslated, $language, $language_strings)
    {
        $options = array();

        $options['language']            = $language;
        $options['title']               = $this->installed_languages['en-GB']->title;
        $options['tag']                 = $this->installed_languages['en-GB']->tag;
        $options['locale']              = $this->installed_languages['en-GB']->locale;
        $options['rtl']                 = (boolean)$this->installed_languages['en-GB']->rtl;
        $options['direction']           = $this->installed_languages['en-GB']->rtl;
        $options['first_day']           = $this->installed_languages['en-GB']->first_day;
        $options['language_utc_offset'] = $this->installed_languages['en-GB']->language_utc_offset;
        $options['primary_language']    = true;

        $default_language = null;
        $en_gb_instance   = null;

        try {
            $class = 'Molajo\\Language\\Adapter\\StringArray';

            return new $class(
                $options,
                $language_strings,
                $untranslated,
                $default_language,
                $en_gb_instance
            );
        } catch (Exception $e) {

            throw new RuntimeException(
                'IoC Factory Method Adapter Instance Failed for ' . $this->product_namespace
                . ' failed.' . $e->getMessage()
            );
        }
    }
}
