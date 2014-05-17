<?php
/**
 * Database Model
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Adapter;

use stdClass;
use Exception;
use CommonApi\Database\DatabaseInterface;
use CommonApi\Exception\RuntimeException;
use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Query\QueryInterface;

/**
 * Database Model
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class DatabaseModel implements CaptureUntranslatedStringInterface
{
    /**
     * Application ID
     *
     * @var    int
     * @since  1.0.0
     */
    protected $application_id = 2;

    /**
     * Public View Group ID
     *
     * @var    int
     * @since  1.0.0
     */
    protected $public_view_group_id = 1;

    /**
     * Primary Category ID
     *
     * @var    int
     * @since  1.0.0
     */
    protected $primary_category_id = 12;

    /**
     * Applications Array
     *
     * @var    array
     * @since  1.0.0
     */
    protected $applications = array();

    /**
     * Database Instance
     *
     * @var    object   CommonApi\Database\DatabaseInterface
     * @since  1.0.0
     */
    protected $database = null;

    /**
     * Query Object
     *
     * @var    object   CommonApi\Query\QueryInterface
     * @since  1.0.0
     */
    protected $query = null;

    /**
     * Used in queries to determine date validity
     *
     * @var    string
     * @since  1.0.0
     */
    protected $null_date;

    /**
     * Today's CCYY-MM-DD 00:00:00 formatted for query
     *
     * @var    string
     * @since  1.0.0
     */
    protected $current_date;

    /**
     * Model Registry
     *
     * @var    array
     * @since  1.0.0
     */
    protected $model_registry = null;

    /**
     * Language List
     *
     * @var     array
     * @since   1.0.0
     */
    protected $installed_languages = array();

    /**
     * Language List
     *
     * @var     array
     * @since   1.0.0
     */
    protected $tag_array = array();

    /**
     * List of Properties
     *
     * @var    array
     * @since  1.0.0
     */
    protected $property_array = array(
        'application_id',
        'database',
        'query',
        'null_date',
        'current_date',
        'model_registry',
        'installed_languages',
        'tag_array'
    );

    /**
     * Construct
     *
     * @param  int                $application_id
     * @param  DatabaseInterface  $database
     * @param  QueryInterface     $query
     * @param  string             $null_date
     * @param  string             $current_date
     * @param                     $model_registry
     * @param  integer            $public_view_group_id
     * @param  integer            $primary_category_id
     *
     * @since  1.0.0
     */
    public function __construct(
        $application_id,
        DatabaseInterface $database,
        QueryInterface $query,
        $null_date,
        $current_date,
        $model_registry = null,
        $public_view_group_id = 1,
        $primary_category_id = 12
    ) {
        $this->application_id       = (int)$application_id;
        $this->database             = $database;
        $this->query                = $query;
        $this->null_date            = $null_date;
        $this->current_date         = $current_date;
        $this->model_registry       = $model_registry;
        $this->public_view_group_id = (int)$public_view_group_id;
        $this->primary_category_id  = (int)$primary_category_id;

        $this->setInstalledLanguages();
        $this->getApplications();
    }

    /**
     * Get the current value (or default) of the specified key
     *
     * @param   string $key
     * @param   null   $default
     *
     * @return  mixed
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function get($key, $default = null)
    {
        if (in_array($key, $this->property_array)) {
        } else {
            throw new RuntimeException('Language Database: Get Key not known: ' . $key);
        }

        if (isset($this->$key)) {
            return $this->$key;
        }

        $this->$key = $default;

        return $this->$key;
    }

    /**
     * Retrieve installed languages for this application
     *
     * @return  $this
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function setInstalledLanguages()
    {
        $data_parameters     = new stdClass();
        $registry_parameters = $this->model_registry['parameters'];

        try {
            $this->query->clearQuery();

            $this->query->select('*');
            $this->query->from('#__extension_instances');
            $this->query->where('column', 'catalog_type_id', '=', 'integer', (int)6000);
            $this->query->where('column', 'catalog_type_id', '<>', 'column', 'extension_id');

            $data = $this->database->loadObjectList($this->query->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel setInstalledLanguages Query Failed: ' . $e->getMessage()
            );
        }

        foreach ($data as $language) {

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

            foreach ($registry_parameters as $parameters) {

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
        }

        return $this;
    }

    /**
     * Get Primary Language Language Strings
     *
     * @param   string $language
     *
     * @return  array
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function getLanguageStrings($language = 'en-GB')
    {
        try {
            $this->query->clearQuery();

            $this->query->select('title');
            $this->query->select('content_text');
            $this->query->from('#__language_strings');
            $this->query->where('column', 'catalog_type_id', '=', 'integer', (int)6250);
            $this->query->where('column', 'extension_instance_id', '=', 'integer', (int)6250);
            $this->query->where('column', 'language', '=', 'string', $language);
            $this->query->orderBy('title', 'ASC');

            $data = $this->database->loadObjectList($this->query->getSQL());

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
     * Save untranslated strings for use by translators
     *
     * @param   string $string
     *
     * @return  $this
     * @since   1.0.0
     */
    public function setString($string)
    {
        $language  = 'string';
        $parent_id = $this->exists($string, $language);

        if ((int)$parent_id == 0) {
            $parent_id = $this->saveLanguageString($string, $language, 0);
        }

        foreach ($this->installed_languages as $key => $row) {
            $language = $row->tag;
            $exists   = $this->exists($string, $language);
            if ((int)$exists === 0) {
                $this->saveLanguageString($string, $language, $parent_id);
            }
        }

        return $this;
    }

    /**
     * Determine if the language string exists for the language
     *
     * @param   string $string
     * @param   string $language
     *
     * @return  int
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function exists($string, $language)
    {
        try {
            $this->query->clearQuery();

            $this->query->select('id');
            $this->query->from('#__language_strings');
            $this->query->where('column', 'language', '=', 'string', $language);
            $this->query->where('column', 'title', '=', 'string', $string);

            $result = $this->database->loadResult($this->query->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel exists query failed for Language/String: '
                . $language . '/' . $string . $e->getMessage()
            );
        }

        return (int)$result;
    }

    /**
     * Retrieve the key for the base language string
     *
     * @param   string $language_string
     * @param   string $language
     * @param   int    $parent_id
     *
     * @return  int
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function saveLanguageString($language_string, $language, $parent_id)
    {
        $path = 'languagestrings';
        if ($language == 'string') {
        } else {
            $path .= '/' . strtolower(trim($language));
        }

        try {
            $this->query->clearQuery();

            $this->query->setType('insert');
            $this->query->from('#__language_strings');

            $this->query->select('site_id', null, (int)0, 'integer');
            $this->query->select('extension_instance_id', null, (int)6250, 'integer');
            $this->query->select('catalog_type_id', null, (int)6250, 'integer');
            $this->query->select('title', null, $language_string, 'string');
            $this->query->select('subtitle', null, '', 'string');
            $this->query->select('path', null, $path, 'string');
            $this->query->select('alias', null, $language_string, 'alias');
            $this->query->select('content_text', null, $language_string, 'string');
            $this->query->select('protected', null, (int)0, 'integer');
            $this->query->select('featured', null, (int)0, 'integer');
            $this->query->select('stickied', null, (int)0, 'integer');
            $this->query->select('status', null, (int)1, 'integer');
            $this->query->select('start_publishing_datetime', null, $this->current_date, 'datetime');
            $this->query->select('stop_publishing_datetime', null, $this->null_date, 'datetime');
            $this->query->select('version', null, (int)1, 'integer');
            $this->query->select('version_of_id', null, (int)0, 'integer');
            $this->query->select('status_prior_to_version', null, (int)0, 'integer');
            $this->query->select('created_datetime', null, $this->current_date, 'datetime');
            $this->query->select('created_by', null, (int)1, 'integer');
            $this->query->select('modified_datetime', null, $this->current_date, 'datetime');
            $this->query->select('modified_by', null, (int)1, 'integer');
            $this->query->select('checked_out_datetime', null, $this->null_date, 'datetime');
            $this->query->select('checked_out_by', null, (int)0, 'integer');
            $this->query->select('root', null, (int)0, 'integer');
            $this->query->select('parent_id', null, (int)$parent_id, 'integer');
            $this->query->select('lft', null, (int)0, 'integer');
            $this->query->select('rgt', null, (int)0, 'integer');
            $this->query->select('lvl', null, (int)1, 'integer');
            $this->query->select('home', null, (int)0, 'integer');
            $this->query->select('customfields', null, '{}', 'string');
            $this->query->select('parameters', null, '{}', 'string');
            $this->query->select('metadata', null, '{}', 'string');
            $this->query->select('language', null, $language, 'string');
            $this->query->select('translation_of_id', null, (int)0, 'integer');
            $this->query->select('ordering', null, (int)0, 'integer');

            $this->database->execute($this->query->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel saveLanguageString exists query failed for Language/String: '
                . $language . '/' . $language_string . $e->getMessage()
            );
        }

        $language_id = (int)$this->database->getInsertId();

        $this->insertCatalogEntry($language_id);

        return $language_id;
    }

    /**
     * Insert into the Catalog Table
     *
     * @param   int $language_id
     *
     * @return  $this
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function insertCatalogEntry($language_id)
    {
        $sef_request = 'languagestrings/' . $this->getSEFRequest($language_id);

        try {

            foreach ($this->applications as $application_id) {

                $this->query->clearQuery();

                $this->query->setType('insert');
                $this->query->from('#__catalog');

                $this->query->select('application_id', null, (int)$application_id, 'integer');
                $this->query->select('catalog_type_id', null, (int)6250, 'integer');
                $this->query->select('source_id', null, (int)$language_id, 'integer');
                $this->query->select('enabled', null, (int)1, 'integer');
                $this->query->select('redirect_to_id', null, (int)0, 'integer');
                $this->query->select('sef_request', null, $sef_request, 'string');
                $this->query->select('page_type', null, 'item', 'string');
                $this->query->select('extension_instance_id', null, (int)6250, 'integer');
                $this->query->select('view_group_id', null, (int)$this->public_view_group_id, 'integer');
                $this->query->select('primary_category_id', null, (int)$this->primary_category_id, 'integer');

                $this->database->execute($this->query->getSQL());
            }

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel insertCatalogEntry query failed for Language/String: '
                . $language_id . '/' . $sef_request . $e->getMessage()
            );
        }
    }

    /**
     * Determine if the language string exists for the language
     *
     * @param   int $language_id
     *
     * @return  int
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getSEFRequest($language_id)
    {
        try {
            $this->query->clearQuery();

            $this->query->select('alias');
            $this->query->from('#__language_strings');
            $this->query->where('column', 'id', '=', 'integer', $language_id);

            return $this->database->loadResult($this->query->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel getSEFRequest Query Failed Language string Primary Key: '
                . $language_id . $e->getMessage()
            );
        }
    }

    /**
     * Determine if the language string exists for the language
     *
     * @return  $this
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    protected function getApplications()
    {
        try {
            $this->query->clearQuery();

            $this->query->select('id');
            $this->query->from('#__applications');

            $applications = $this->database->loadObjectList($this->query->getSQL());

        } catch (Exception $e) {
            throw new RuntimeException(
                'DatabaseModel getApplications Query Failed ' . $e->getMessage()
            );
        }

        foreach ($applications as $application) {
            $this->applications[] = $application->id;
        }

        return $this;
    }
}
