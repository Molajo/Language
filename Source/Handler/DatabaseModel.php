<?php
/**
 * Database Model
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Handler;

use stdClass;
use Exception;
use CommonApi\Database\DatabaseInterface;
use CommonApi\Language\DatabaseModelInterface;
use CommonApi\Model\FieldhandlerInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Database Model
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class DatabaseModel implements DatabaseModelInterface
{
    /**
     * Application ID
     *
     * @var    int
     * @since  1.0
     */
    protected $application_id = null;

    /**
     * Database Instance
     *
     * @var    object   CommonApi\Database\DatabaseInterface
     * @since  1.0
     */
    protected $database = null;

    /**
     * Query Object
     *
     * @var    object   CommonApi\Database\QueryObjectInterface
     * @since  1.0
     */
    protected $query = null;

    /**
     * Used in queries to determine date validity
     *
     * @var    string
     * @since  1.0
     */
    protected $null_date;

    /**
     * Today's CCYY-MM-DD 00:00:00 formatted for query
     *
     * @var    string
     * @since  1.0
     */
    protected $current_date;

    /**
     * Model Registry
     *
     * @var    array
     * @since  1.0
     */
    protected $model_registry = null;

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
     * List of Properties
     *
     * @var    object
     * @since  1.0
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
     * @param  int                   $application_id
     * @param  DatabaseInterface     $database
     * @param                        $query
     * @param  string                $null_date
     * @param  string                $current_date
     * @param  FieldhandlerInterface $fieldhandler
     * @param                        $model_registry
     *
     * @since  1.0
     */
    public function __construct(
        $application_id,
        DatabaseInterface $database,
        $query,
        $null_date,
        $current_date,
        FieldhandlerInterface $fieldhandler,
        $model_registry = null
    ) {
        $this->application_id = $application_id;
        $this->database       = $database;
        $this->query          = $query;
        $this->null_date      = $null_date;
        $this->current_date   = $current_date;
        $this->fieldhandler   = $fieldhandler;
        $this->model_registry = $model_registry;

        $this->setInstalledLanguages();
    }

    /**
     * Get the current value (or default) of the specified key
     *
     * @param   string $key
     * @param   null   $default
     *
     * @return  mixed
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function get($key, $default = null)
    {
        if (in_array($key, $this->property_array)) {
        } else {
            throw new RuntimeException
            ('Language Database: Get Key not known: ' . $key);
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
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setInstalledLanguages()
    {
        $registry_parameters = $this->model_registry['parameters'];

        $query = $this->database->getQueryObject();

        $query->select('*');
        $query->from($this->database->qn('#__extension_instances'));
        $query->where(
            $this->database->qn('catalog_type_id')
            . ' = ' . $this->database->q(6000)
        );
        $query->where(
            $this->database->qn('catalog_type_id')
            . ' <> ' . $this->database->qn('extension_id')
        );

        $data = $this->database->loadObjectList();

        foreach ($data as $language) {

            $temp_row                        = new stdClass();
            $temp_row->extension_id          = $language->extension_id;
            $temp_row->extension_instance_id = $language->id;
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

                $type = $parameters['type'];

                $temp_row->$key = $this->filter($key, $value, $type);
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
     * @since   1.0
     */
    public function getLanguageStrings($language = 'en-GB')
    {
        $query = $this->database->getQueryObject();

        $query->select('title');
        $query->select('content_text');
        $query->from($this->database->qn('#__language_strings'));
        $query->where(
            $this->database->qn('catalog_type_id')
            . ' = ' . $this->database->q(6250)
        );
        $query->where(
            $this->database->qn('extension_instance_id')
            . ' = ' . $this->database->q(6250)
        );
        $query->where(
            $this->database->qn('language')
            . ' = ' . $this->database->q('en-GB')
        );
        $query->limit(0, 99999);
        $query->order('title');

        $data = $this->database->loadObjectList();

        if (count($data) === 0) {
            throw new RuntimeException
            ('Language Database: No Language strings for Language.');
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
     * @since   1.0
     */
    public function setUntranslatedString($string)
    {
        $language      = 'string';
        $parent_id     = $this->exists($string, $language);
        $now_datetime  = $this->database->getDate();
        $null_datetime = $this->database->getNullDate();

        if ((int)$parent_id == 0) {
            $parent_id = $this->saveLanguageString($string, $language, 0, $now_datetime, $null_datetime);
        }

        foreach ($this->installed_languages as $key => $row) {
            $language = $row->tag;
            $exists   = $this->exists($string, $language);
            if ((int)$exists === 0) {
                $this->saveLanguageString($string, $language, $parent_id, $now_datetime, $null_datetime);
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
     * @since   1.0
     */
    public function exists($string, $language)
    {
        $query = $this->database->getQueryObject();

        $query->select($this->database->qn('id'));
        $query->from($this->database->qn('#__language_strings'));
        $query->where($this->database->qn('language') . ' = ' . $this->database->q($language));
        $query->where($this->database->qn('title') . ' = ' . $this->database->q($string));

        try {
            $result = $this->database->loadResult();

            return (int)$result;

        } catch (Exception $e) {
            throw new RuntimeException
            ('Language DatabaseModel: exists query failed for Key: ' . $string . $e->getMessage());
        }
    }

    /**
     * Retrieve the key for the base language string
     *
     * @param   string $language_string
     * @param   string $language
     * @param   int    $parent_id
     * @param   string $now_datetime
     * @param   string $null_datetime
     *
     * @return  int
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function saveLanguageString($language_string, $language, $parent_id, $now_datetime, $null_datetime)
    {
        $alias = $this->filter('alias', $language_string, 'alias');

        $path = 'languagestrings';
        if ($language === 'string') {
        } else {
            $path .= '/' . strtolower($language);
        }

        $site_id                   = 0;
        $extension_instance_id     = 6250;
        $catalog_type_id           = 6250;
        $title                     = $this->database->q($language_string);
        $subtitle                  = $this->database->q('');
        $path                      = $this->database->q($path);
        $alias                     = $this->database->q($alias);
        $content_text              = $this->database->q($language_string);
        $protected                 = 0;
        $featured                  = 0;
        $stickied                  = 0;
        $status                    = 1;
        $start_publishing_datetime = $this->database->q($now_datetime);
        $stop_publishing_datetime  = $this->database->q($null_datetime);
        $version                   = 1;
        $version_of_id             = $this->database->q('null');
        $status_prior_to_version   = $this->database->q('null');
        $created_datetime          = $this->database->q($now_datetime);
        $created_by                = 1;
        $modified_datetime         = $this->database->q($null_datetime);
        $modified_by               = $this->database->q($null_datetime);
        $checked_out_datetime      = $this->database->q($null_datetime);
        $checked_out_by            = $this->database->q('null');
        $root                      = 0;
        $parent_id                 = (int)$parent_id;
        $lft                       = 0;
        $rgt                       = 0;
        $lvl                       = 1;
        $home                      = 0;
        $customfields              = $this->database->q('{}');
        $parameters                = $this->database->q('{}');
        $metadata                  = $this->database->q('{}');
        $language                  = $this->database->q($language);
        $translation_of_id         = 0;
        $ordering                  = 0;

        $sql = 'INSERT INTO `#__language_strings`

                (`site_id`, `extension_instance_id`, `catalog_type_id`,
                    `title`, `subtitle`, `path`, `alias`, `content_text`,
                    `protected`, `featured`, `stickied`, `status`,
                    `start_publishing_datetime`, `stop_publishing_datetime`, `version`,
                    `version_of_id`, `status_prior_to_version`, `created_datetime`,
                    `created_by`, `modified_datetime`, `modified_by`,
                    `checked_out_datetime`, `checked_out_by`, `root`, `parent_id`,
                    `lft`, `rgt`, `lvl`, `home`,
                    `customfields`, `parameters`, `metadata`, `language`,
                    `translation_of_id`, `ordering`)

                VALUES (' . $site_id . ', ' . $extension_instance_id . ', ' . $catalog_type_id
            . ', ' . $title . ', ' . $subtitle . ', ' . $path . ', ' . $alias . ', ' . $content_text
            . ', ' . $protected . ', ' . $featured . ', ' . $stickied . ', ' . $status
            . ', ' . $start_publishing_datetime . ', ' . $stop_publishing_datetime . ', ' . $version
            . ', ' . $version_of_id . ', ' . $status_prior_to_version . ', ' . $created_datetime
            . ', ' . $created_by . ', ' . $modified_datetime . ', ' . $modified_by
            . ', ' . $checked_out_datetime . ', ' . $checked_out_by . ', ' . $root . ', ' . $parent_id
            . ', ' . $lft . ', ' . $rgt . ', ' . $lvl . ', ' . $home
            . ', ' . $customfields . ', ' . $parameters . ', ' . $metadata . ', ' . $language
            . ', ' . $translation_of_id . ', ' . $ordering . ')';

        try {

            $this->database->execute($sql);

            $language_id = (int)$this->database->getInsertId();

            $this->insertCatalogEntry($language_id);

            return $language_id;

        } catch (Exception $e) {
            throw new RuntimeException
            ('Language DatabaseModel: insert query failed for Language: ' . $language
            . 'Language String: ' . $language_string . ' ' . $e->getMessage());
        }
    }

    /**
     * Retrieve the key for the base language string
     *
     * @param   int $language_id
     *
     * @return  $this
     * @since   1.0
     */
    public function insertCatalogEntry($language_id)
    {
        $sql = "
            INSERT INTO `molajo_catalog`(`application_id`, `catalog_type_id`, `source_id`,
                `enabled`, `redirect_to_id`, `sef_request`, `page_type`, `extension_instance_id`,
                `view_group_id`, `primary_category_id`)

                SELECT `b`.`id`, `a`.`catalog_type_id`,
                    `a`.`id`, 1, 0, CONCAT(`a`.`path`, '/', `a`.`alias`),
                    'item', `a`.`extension_instance_id`, 1, 12

                FROM `molajo_language_strings` as `a`,
                    `molajo_applications` as `b`

                WHERE a.id = " . (int)$language_id;

        try {
            $this->database->execute($sql);

        } catch (Exception $e) {
            throw new RuntimeException
            ('Language DatabaseModel: insertCatalogEntry query failed for Language Key: '
            . $language_id . $e->getMessage());
        }

        return $this;
    }

    /**
     * Filter Input
     *
     * @param  string      $key
     * @param  null|string $value
     * @param  string      $type
     * @param  array       $filter_options
     *
     * @return  $this
     * @since   1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    protected function filter($key, $value = null, $type, $filter_options = array())
    {
        if ($type == 'text') {
            $filter = 'Html';
        } elseif ($type == 'char') {
            $filter = 'string';
        } elseif (substr($type, strlen($type) - 3, 3) == '_id'
            || $key == 'id'
            || $type == 'integer'
            || $key == 'status'
        ) {
            $filter = 'Int';
        } elseif ($type == 'char') {
            $filter = 'String';
        } else {
            $filter = $type;
        }

        try {
            $value = $this->fieldhandler->filter($key, $value, $filter, $filter_options);
        } catch (Exception $e) {
            throw new RuntimeException
            ('Request: Filter class Failed for Key: ' . $key . ' Filter: ' . $filter . ' ' . $e->getMessage());
        }

        return $value;
    }
}
