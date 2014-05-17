<?php
/**
 * Molajo Database Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Test;

use CommonApi\Database\DatabaseInterface;
use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Language\LanguageInterface;
use CommonApi\Language\TranslateInterface;
use CommonApi\Query\QueryInterface;
use Molajo\Language\Adapter\AbstractAdapter;
use Molajo\Language\Adapter\Database;

/**
 * Molajo Database Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  $adapter
     */
    protected $adapter;

    /**
     * Setup
     *
     * @covers  Molajo\Language\Adapter\Database::__construct
     * @covers  Molajo\Language\Adapter\Database::setLanguageMetadata
     * @covers  Molajo\Language\Adapter\DatabaseModel::getLanguageStrings
     */
    protected function setUp()
    {
        $language = 'en-GB';
        $title = 'English';
        $tag = 'en-GB';
        $locale = 'en-GB';
        $rtl = 'false';
        $direction = 'ltr';
        $first_day = 0;
        $language_utc_offset = 0;
        $model = new MockModel();
        $primary_language = true;
        $default_language = null;
        $en_gb_instance = null;

        $this->adapter = new Database($language,
            $title,
            $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance);
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::get
     *
     * @return  $this
     * @since   1.0
     */
    public function testGet()
    {
        $results = $this->adapter->get('language');

        $this->assertEquals($results, 'en-GB');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::get
     * @covers  Molajo\Language\Adapter\Database::getAll
     *
     * @return  $this
     * @since   1.0
     */
    public function testGetAll()
    {
        $results = $this->adapter->get(null);

        $this->assertEquals($results->language, 'en-GB');
        $this->assertEquals($results->title, 'English');
        $this->assertEquals($results->tag, 'en-GB');
        $this->assertEquals($results->locale, 'en-GB');
        $this->assertEquals($results->rtl, 'false');
        $this->assertEquals($results->direction, 'ltr');
        $this->assertEquals($results->first_day, 0);
        $this->assertEquals($results->language_utc_offset, 0);
        $this->assertEquals($results->primary_language, true);

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::get
     *
     * @expectedException \CommonApi\Exception\RuntimeException
     * @expectedExceptionMessage Language Database Adapter: Attempting to get value for unknown property: not_existing
     *
     * @return  $this
     * @since   1.0
     */
    public function testGetNotExisting()
    {
        $results = $this->adapter->get('not_existing');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringFound()
    {
        $results = $this->adapter->translateString('dog');

        $this->assertEquals($results, 'Dog');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::translateStringNotFound
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringNotFound()
    {
        $results = $this->adapter->translateString('xyz');

        $this->assertEquals($results, 'String was set');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::translateStringNotFound
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringBackupLanguageFound()
    {
        $language = 'en-GB';
        $title = 'English';
        $tag = 'en-GB';
        $locale = 'en-GB';
        $rtl = 'false';
        $direction = 'ltr';
        $first_day = 0;
        $language_utc_offset = 0;
        $model = new MockModel();
        $primary_language = true;
        $default_language = new MockBackupLanguage();
        $en_gb_instance = null;

        $two_language_adapter = new Database($language,
            $title,
            $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance);

        $results = $two_language_adapter->translateString('xyz');

        $this->assertEquals($results, 'The end of the alphabet.');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::translateStringNotFound
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringNotFoundBothCurrentAndBackupLanguage()
    {
        $language = 'en-GB';
        $title = 'English';
        $tag = 'en-GB';
        $locale = 'en-GB';
        $rtl = 'false';
        $direction = 'ltr';
        $first_day = 0;
        $language_utc_offset = 0;
        $model = new MockModel();
        $primary_language = true;
        $default_language = new MockBackupLanguage();
        $en_gb_instance = null;

        $two_language_adapter = new Database($language,
            $title,
            $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance);

        $results = $two_language_adapter->translateString('This is not in either language');

        $this->assertEquals($results, 'String was set');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::translateStringNotFound
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringSecondBackupLanguageFound()
    {
        $language = 'en-GB';
        $title = 'English';
        $tag = 'en-GB';
        $locale = 'en-GB';
        $rtl = 'false';
        $direction = 'ltr';
        $first_day = 0;
        $language_utc_offset = 0;
        $model = new MockModel();
        $primary_language = true;
        $default_language = new MockBackupLanguage();
        $en_gb_instance = new MockBackupLanguage2();

        $two_language_adapter = new Database($language,
            $title,
            $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance);

        $results = $two_language_adapter->translateString('interesting');

        $this->assertEquals($results, 'Found in MockBackupLanguage2');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::translateStringNotFound
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\Database::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringNotFoundInCurrentOrEitherBackupLanguage()
    {
        $language = 'en-GB';
        $title = 'English';
        $tag = 'en-GB';
        $locale = 'en-GB';
        $rtl = 'false';
        $direction = 'ltr';
        $first_day = 0;
        $language_utc_offset = 0;
        $model = new MockModel();
        $primary_language = true;
        $default_language = new MockBackupLanguage();
        $en_gb_instance = new MockBackupLanguage2();

        $two_language_adapter = new Database($language, $title, $tag, $locale, $rtl, $direction,
            $first_day, $language_utc_offset, $model, $primary_language, $default_language, $en_gb_instance);

        $results = $two_language_adapter->translateString('This is not in any of the three.');

        $this->assertEquals($results, 'String was set');

        return $this;
    }
}

class MockQuery implements QueryInterface
{

    /**
     * Clear Query String
     *
     * @return  $this
     * @since   1.0
     */
    public function clearQuery()
    {

    }

    /**
     * Set Query Type - create, select (default), update, delete, exec
     *
     * @param   string $query_type
     *
     * @return  $this
     * @since   1.0
     */
    public function setType($query_type = 'select')
    {

    }

    /**
     * Retrieves the PHP date format compliant with the database driver
     *
     * @return  string
     * @since   1.0
     */
    public function getDateFormat()
    {

    }

    /**
     * Retrieves the current date and time formatted in a manner compliant with the database driver
     *
     * @return  string
     * @since   1.0
     */
    public function getDate()
    {

    }

    /**
     * Returns a value for null date that is compliant with the database driver
     *
     * @return  string
     * @since   1.0
     */
    public function getNullDate()
    {

    }

    /**
     * Set Distinct Indicator
     *
     * @param   boolean $distinct
     *
     * @return  $this
     * @since   1.0
     */
    public function setDistinct($distinct = false)
    {

    }

    /**
     * Used for select, insert, and update to specify column name, alias (optional)
     *  For Insert and Update, only, value and data_type
     *
     * @param   string      $column_name
     * @param   null|string $alias
     * @param   null|string $value
     * @param   null|string $data_type
     *
     * @return  $this
     * @since   1.0
     * @throws \CommonApi\Exception\RuntimeException
     */
    public function select($column_name, $alias = null, $value = null, $data_type = null)
    {

    }

    /**
     * Set From table name and optional value for alias
     *
     * @param   string      $table_name
     * @param   null|string $alias
     *
     * @return  $this
     * @since   1.0
     */
    public function from($table_name, $alias = null);

    /**
     * Create a grouping for conditions for 'and' or 'or' treatment between groups of conditions
     *
     * @param   string $group
     * @param   string $group_connector
     *
     * @return  $this
     * @since   1.0
     */
    public function whereGroup($group, $group_connector = 'and')
    {

    }

    /**
     * Set Where Conditions for Query
     *
     * @param   string      $left_filter
     * @param   string      $left
     * @param   string      $condition
     * @param   string      $right_filter
     * @param   string      $right
     * @param   string      $connector
     * @param   null|string $group
     *
     * @return  $this
     * @since   1.0
     */
    public function where(
        $left_filter = 'column',
        $left,
        $condition,
        $right_filter = 'column',
        $right,
        $connector = 'and',
        $group = null
    )
    {

    }

    /**
     * Set Group By column name and optional value for alias
     *
     * @param   string      $column_name
     * @param   null|string $alias
     *
     * @return $this
     * @since  1.0
     */
    public function groupBy($column_name, $alias = null)
    {

    }

    /**
     * Create a grouping for having statements for 'and' or 'or' treatment between groups of conditions
     *
     * @param   string $group
     * @param   string $group_connector
     *
     * @return  $this
     * @since   1.0
     */
    public function havingGroup($group, $group_connector = 'and')
    {

    }

    /**
     * Set Having Conditions for Query
     *
     * @param   string $left_filter
     * @param   string $left
     * @param   string $condition
     * @param   string $right_filter
     * @param   string $right
     * @param   string $connector
     *
     * @return  $this
     * @since   1.0
     */
    public function having(
        $left_filter = 'column',
        $left,
        $condition,
        $right_filter = 'column',
        $right,
        $connector = 'and'
    )
    {

    }

    /**
     * Set Order By column name and optional value for alias
     *
     * @param   string      $column_name
     * @param   null|string $direction
     *
     * @return  $this
     * @since   1.0
     */
    public function orderBy($column_name, $direction = 'ASC')
    {

    }

    /**
     * Set Offset and Limit
     *
     * @param   int $offset
     * @param   int $limit
     *
     * @return  $this
     * @since   1.0
     */
    public function setOffsetAndLimit($offset = 0, $limit = 15)
    {

    }

    /**
     * Get SQL (optionally setting the SQL)
     *
     * @param   null|string $sql
     *
     * @return  string
     * @since   1.0
     */
    public function getSQL($sql = null)
    {

    }
}

class MockDatabase implements DatabaseInterface
{

    /**
     * Escape the value
     *
     * @param   string $value
     *
     * @return  string
     * @since   1.0
     */
    public function escape($value)
    {

    }

    /**
     * Query the database and return a single value as the result
     *
     * @param   string $sql
     *
     * @return  object
     * @since   1.0
     */
    public function loadResult($sql)
    {

    }

    /**
     * Query the database and return an array of object values returned from query
     *
     * @param   string $sql
     *
     * @return  object
     * @since   1.0
     */
    public function loadObjectList($sql)
    {

    }

    /**
     * Execute the Database Query
     *
     * @param   string $sql
     *
     * @return  object
     * @since   1.0
     */
    public function execute($sql)
    {

    }

    /**
     * Returns the primary key following insert
     *
     * @return  integer
     * @since   1.0
     */
    public function getInsertId()
    {

    }
}
