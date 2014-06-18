<?php
/**
 * Language Factory Method
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Factories\Language;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\IoC\FactoryInterface;
use CommonApi\IoC\FactoryBatchInterface;
use Molajo\IoC\FactoryMethod\Base as FactoryMethodBase;
use Molajo\Language\Capture\Dummy;
use stdClass;

/**
 * Language Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
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

        $options                           = array();
        $this->dependencies                = array();
        $this->dependencies['Runtimedata'] = $options;
        $this->dependencies['Resource']    = $options;
        $this->dependencies['Database']    = $options;
        $this->dependencies['Query']      = $options;
        $this->dependencies['User']        = $options;

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
        $model                = new Dummy();
        $this->application_id = $this->dependencies['Runtimedata']->application->id;
        $this->getInstalledLanguages();
        $language         = $this->setLanguage();
        $language_strings = $this->getLanguageStrings($language);
        $adapter          = $this->instantiateStringArrayAdapter($language, $language_strings);

        try {
            $this->product_result = new $this->product_namespace($adapter, $language);

        } catch (Exception $e) {

            throw new RuntimeException
            (
                'IoC Factory Method Adapter Instance Failed for ' . $this->product_namespace
                . ' failed.' . $e->getMessage()
            );
        }

        return $this;
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
    protected function instantiateStringArrayAdapter($model, $language)
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
                $model,
                true,
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


    /**
     * Retrieve Language Strings for Current Language
     *
     * @return  array
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getLanguageStrings($language)
    {
        try {
            $this->dependencies['Query']->clearQuery();

            $this->dependencies['Query']->select('title');
            $this->dependencies['Query']->select('content_text');
            $this->dependencies['Query']->from('#__language_strings');
            $this->dependencies['Query']->where('column', 'catalog_type_id', '=', 'integer', (int)6250);
            $this->dependencies['Query']->where('column', 'extension_instance_id', '=', 'integer', (int)6250);
            $this->dependencies['Query']->where('column', 'language', '=', 'string', $language);
            $this->dependencies['Query']->orderBy('title', 'ASC');

            $data = $this->dependencies['Database']->loadObjectList($this->dependencies['Query']->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel getLanguageStrings Query Failed: ' . $e->getMessage()
            );
        }

        if (count($data) === 0) {
            throw new RuntimeException(
                'Language DatabaseModel getLanguageStrings: No Language strings for Language.'
            );
        }

        $strings = array();
        foreach ($data as $item) {
            $title           = strtolower($item->title);
            $strings[$title] = $item->content_text;
        }

        return $strings;
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
     * @param $language
     * @param $registry_parameters
     */
    protected function getInstalledLanguages()
    {
        //todo this is NOT done;

        $model_registry = $this->dependencies['Resource']->get(
            'xml:///Molajo//Model//Datasource//Languages.xml'
        );

        try {
            $this->dependencies['Query']->clearQuery();

            $this->dependencies['Query']->select('*');
            $this->dependencies['Query']->from('#__extension_instances');
            $this->dependencies['Query']->where('column', 'catalog_type_id', '=', 'integer', (int)6000);
            $this->dependencies['Query']->where('column', 'catalog_type_id', '<>', 'column', 'extension_id');

            $temp = $this->dependencies['Database']->loadObjectList($this->dependencies['Query']->getSQL());

            $language = $temp[0];

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel setInstalledLanguages Query Failed: ' . $e->getMessage()
            );
        }

        $data_parameters = new stdClass();

        $temp_row                        = new stdClass();
        $temp_row->extension_id          = (int)$language->extension_id;
        $temp_row->extension_instance_id = (int)$language->id;
        $temp_row->title                 = $language->subtitle;
        $temp_row->tag                   = $language->title;
        $temp_parameters                 = json_decode($language->parameters);

        if (count($temp_parameters) > 0
            && (int)$this->application_id > 0
        ) {
            foreach ($temp_parameters as $key => $value) {
                if ($key == (int)$this->application_id) {
                    $data_parameters = $value;
                    break;
                }
            }
        }

        foreach ($model_registry->parameters as $parameters) {

            $key = $parameters['name'];

            if (isset($parameters['default'])) {
                $default = $parameters['default'];
            } else {
                $default = false;
            }

            if (isset($data_parameters->$key)) {
                $value = $data_parameters->$key;
            } else {
                $value = null;
            }

            if ($value === null) {
                $value = $default;
            }

            $temp_row->$key = $value;
        }

        $temp_row->language_utc_offset = null;

        $this->installed_languages[$temp_row->tag] = $temp_row;
        $this->tag_array[]                         = $temp_row->tag;

        return $this;
    }
}
