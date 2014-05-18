<?php
/**
 * Database
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Capture;

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
class Database implements CaptureUntranslatedStringInterface
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
    protected $property_array
        = array(
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
        $this->installed_languages  = $installed_languages;

        $this->getApplications();
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
                'Database exists query failed for Language/String: '
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
                'Database saveLanguageString exists query failed for Language/String: '
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
                'Database insertCatalogEntry query failed for Language/String: '
                . $language_id . '/' . $sef_request . $e->getMessage()
            );
        }
    }

    /**
     * Retrieve the slug after saving
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
                'Database getSEFRequest Query Failed Language string Primary Key: '
                . $language_id . $e->getMessage()
            );
        }
    }

    /**
     * Retrieve a list of application_ids for this database
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
                'Database getApplications Query Failed ' . $e->getMessage()
            );
        }

        foreach ($applications as $application) {
            $this->applications[] = $application->id;
        }

        return $this;
    }
}
