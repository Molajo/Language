<?php
/**
 * Database Capture
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Capture;

use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Fieldhandler\FieldhandlerInterface;
use CommonApi\Query\QueryInterface;

/**
 * Database Capture
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class Database implements CaptureUntranslatedStringInterface
{
    /**
     * Fieldhandler Usage Trait
     *
     * @var     object  CommonApi\Fieldhandler\FieldhandlerUsageTrait
     * @since   1.0.0
     */
    use \CommonApi\Fieldhandler\FieldhandlerUsageTrait;

    /**
     * Query Usage Trait
     *
     * @var     object  CommonApi\Query\QueryUsageTrait
     * @since   1.0.0
     */
    use \CommonApi\Query\QueryUsageTrait;

    /**
     * Installed Languages
     *
     * @var    array
     * @since  1.0
     */
    protected $installed_languages = array();

    /**
     * Catalog Type ID
     *
     * @var    string
     * @since  1.0
     */
    protected $language_catalog_type_id = 6500;

    /**
     * Parent ID
     *
     * @var    integer
     * @since  1.0
     */
    protected $parent_id;

    /**
     * Constructor
     *
     * @param QueryInterface        $resource
     * @param FieldhandlerInterface $fieldhandler
     * @param object                $runtime_data
     * @param array                 $installed_languages
     *
     * @since  1.0
     */
    public function __construct(
        QueryInterface $resource,
        FieldhandlerInterface $fieldhandler,
        $runtime_data,
        array $installed_languages
    ) {
        $this->resource     = $resource;
        $this->fieldhandler = $fieldhandler;
        $this->runtime_data = $runtime_data;

        $this->setLanguages($installed_languages);
    }

    /**
     * Set installed languages array
     *
     * @param   array $installed_languages
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setLanguages(array $installed_languages = array())
    {
        $this->installed_languages = array();

        $this->installed_languages[] = 'string';

        foreach ($installed_languages as $key => $value) {
            $this->installed_languages[] = $key;
        }

        return $this;
    }

    /**
     * Save untranslated strings for use by translators (first to base, then to each installed language)
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    public function setString($string)
    {
        if (trim($string) === '') {
            return $string;
        }

        $this->parent_id = 0;

        foreach ($this->installed_languages as $key) {
            if ($this->getString($string, $key) === false) {
                $this->insertString($string, $key);
            }
        }

        return $string;
    }

    /**
     * Does String Exist?
     *
     * @param   string $string
     * @param   string $language
     *
     * @return  boolean
     * @since   1.0.0
     */
    protected function getString($string, $language = 'string')
    {
        $this->setQueryController('Molajo//Model//Datasource//LanguageStrings.xml', 'Read');

        $this->setQueryControllerDefaults(
            $process_events = 0,
            $query_object = 'result',
            $get_customfields = 0,
            $use_special_joins = 0,
            $use_pagination = 0,
            $check_view_level_access = 0,
            $get_item_children = 0
        );

        $this->query->select('a.id');
        $this->query->from('#__language_strings', 'a');
        $this->query->where('column', 'a.title', '=', 'string', strtolower(trim($string)));
        $this->query->where('column', 'a.language', '=', 'string', strtolower(trim($language)));

        $result = $this->runQuery();

        if ($result === null) {
            return false;
        }

        if ($language === 'string') {
            $this->parent_id = $result;
        }

        return true;
    }

    /**
     * Insert Language String
     *
     * @param   string $string
     * @param   string $type
     *
     * @return  boolean
     * @since   1.0.0
     */
    protected function insertString($string, $language = 'string')
    {
        $this->setQueryController('Molajo//Model//Datasource//LanguageStrings.xml', 'Create');

        $this->setQueryControllerDefaults(
            $process_events = 1,
            $query_object = 'result',
            $get_customfields = 0,
            $use_special_joins = 0,
            $use_pagination = 0,
            $check_view_level_access = 0,
            $get_item_children = 0
        );

        $row = $this->insertRow($string, $language);

        if ($language === 'string') {
            $this->parent_id = $row->id;
        }

        return $this;
    }

    /**
     * @param $string
     * @param $language
     *
     * @return object
     */
    protected function insertRow($string, $language)
    {
        $row = $this->query->initialiseRow();
        $row = $this->setInsertValues($row, $string, $language);
        $this->query->setInsertStatement($row);

        $row = $this->runQuery('insertData');

        return $row;
    }

    /**
     * Set Language Field Values for Insert
     *
     * @param   object $row
     * @param   string $string
     * @param   string $language
     *
     * @return  object
     * @since   1.0.0
     */
    protected function setInsertValues($row, $string, $language)
    {
        $row->id                    = null;
        $row->extension_instance_id = $this->language_catalog_type_id;
        $row->catalog_type_id       = $this->language_catalog_type_id;
        $row->title                 = strtolower($string);
        $row->path                  = 'languagestrings';
        $row->parent_id             = $this->parent_id;
        $row->translation_of_id     = $this->parent_id;
        $row->content_text          = $string;
        $row->language              = $language;
        $row->ordering              = 0;

        if ($language === 'string') {
            $row->status = 1;
        } else {
            $row->path .= '/' . $language;
            $row->status = 0;
        }

        return $row;
    }
}
