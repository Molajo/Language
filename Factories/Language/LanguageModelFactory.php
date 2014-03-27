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
use Molajo\IoC\FactoryMethodBase;

/**
 * Language Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class LanguageFactoryMethod extends FactoryMethodBase implements FactoryInterface, FactoryBatchInterface
{
    /**
     * Language List
     *
     * @var     array
     * @since   1.0
     */
    protected $installed_languages = array();

    /**
     * Language List
     *
     * @var     array
     * @since   1.0
     */
    protected $tag_array = array();

    /**
     * Constructor
     *
     * @param  $options
     *
     * @since  1.0
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
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function setDependencies(array $reflection = null)
    {
        $this->reflection = array();

        $options                           = array();
        $this->dependencies                = array();
        $this->dependencies['Runtimedata'] = $options;
        $this->dependencies['Resource']    = $options;
        $this->dependencies['Database']    = $options;
        $this->dependencies['Query']       = $options;
        $this->dependencies['User']        = $options;

        return $this->dependencies;
    }

    /**
     * Instantiate Class
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    public function instantiateClass()
    {
        $database = $this->instantiateDatabaseModel();
        $language = $this->setLanguage();
        $adapter  = $this->instantiateDatabaseAdapter($database, $language);

        try {
            $this->product_result = new $this->product_namespace ($adapter, $language);

        } catch (Exception $e) {

            throw new RuntimeException
            ('IoC Factory Method Adapter Instance Failed for ' . $this->product_namespace
            . ' failed.' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Instantiate Database Adapter for Language
     *
     * @param   string $model
     * @param   string $language
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    protected function instantiateDatabaseAdapter($model, $language)
    {
        $default_language      = null;
        $en_gb_instance        = null;
        $extension_id          = $this->installed_languages['en-GB']->extension_id;
        $extension_instance_id = $this->installed_languages['en-GB']->extension_instance_id;
        $title                 = $this->installed_languages['en-GB']->title;
        $tag                   = $this->installed_languages['en-GB']->tag;
        $locale                = $this->installed_languages['en-GB']->locale;
        $rtl                   = $this->installed_languages['en-GB']->rtl;
        $direction             = $this->installed_languages['en-GB']->rtl;
        $first_day             = $this->installed_languages['en-GB']->first_day;
        $language_utc_offset   = $this->installed_languages['en-GB']->language_utc_offset;

        try {
            $class = 'Molajo\\Language\\Adapter\\Database';

            return new $class(
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
                $default_language,
                $en_gb_instance
            );
        } catch (Exception $e) {

            throw new RuntimeException
            ('IoC Factory Method Adapter Instance Failed for ' . $this->product_namespace
            . ' failed.' . $e->getMessage());
        }
    }

    /**
     * Instantiate Language Model for capturing missing translations
     *
     * @param   $adapter
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    protected function instantiateDatabaseModel()
    {
        $public_view_group_id = 1;
        $database             = $this->dependencies['Database'];
        $null_date            = $this->dependencies['Query']->getNullDate();
        $current_date         = $this->dependencies['Query']->getDate();
        $model_registry       = $this->dependencies['Resource']->get(
            'xml:///Molajo//Model//Datasource//Languages.xml'
        );

        $class = 'Molajo\\Language\\Adapter\\DatabaseModel';

        try {
            $databasemodel = new $class(
                $public_view_group_id,
                $database,
                $this->dependencies['Query'],
                $null_date,
                $current_date,
                $model_registry
            );
        } catch (Exception $e) {

            throw new RuntimeException
            ('Language Model instantiateDatabaseModel method Failed for '
            . $class . ' in LanguageFactoryMethod ' . $e->getMessage());
        }

        $this->installed_languages = $databasemodel->get('installed_languages');
        $this->tag_array           = $databasemodel->get('tag_array');

        return $databasemodel;
    }

    /**
     * Sets language based on specific order of checking values
     *
     * @return  string
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException;
     */
    protected function setLanguage()
    {
        if (count($this->tag_array) == 1) {
            $languages = $this->tag_array;
            $language  = $languages[0];
            return $language;
        }

        $language = $this->dependencies['User']->getUserData()->language;

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

        throw new RuntimeException
        ('Language Factory Method: No Language Defined.');
    }
}
