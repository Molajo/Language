<?php
/**
 * Molajo Database Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Test;

use CommonApi\Language\CaptureUntranslatedStringInterface;
use CommonApi\Language\LanguageInterface;
use CommonApi\Language\TranslateInterface;
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

        $results = $two_language_adapter->translateString('This is not in any of the three.');

        $this->assertEquals($results, 'String was set');

        return $this;
    }
}

class MockModel implements CaptureUntranslatedStringInterface
{

    public function getLanguageStrings($language)
    {
        return array(
            'dog' => 'Dog',
            'catbook' => 'The Cat in the Hat',
            'error' => 'The error message is thus.',
            'z' => 'Zebra',
            'nothing' => 'Nada thing'
        );
    }

    public function setString($string)
    {
        return 'String was set';
    }
}

class MockBackupLanguage extends AbstractAdapter implements LanguageInterface, TranslateInterface
{
    protected $language_strings = array(
        'abc' => 'The beginning of the alphabet.',
        'xyz' => 'The end of the alphabet.'
    );

    protected $primary_language = false;

    public function get($key = null, $default = null)
    {
        return $default;
    }

    public function translateString($string)
    {
        $key = strtolower($string);

        if (isset($this->language_strings[$key])) {
            return $this->language_strings[$key];
        }

        if ($this->primary_language === true) {
            return $this->translateStringNotFound($key, $string);
        }

        return $string;
    }

    protected function translateStringNotFound($key, $string)
    {
        return $string;
    }
}

class MockBackupLanguage2 extends AbstractAdapter implements LanguageInterface, TranslateInterface
{
    protected $language_strings = array(
        'interesting' => 'Found in MockBackupLanguage2'
    );

    protected $primary_language = false;

    public function get($key = null, $default = null)
    {
        return $default;
    }

    public function translateString($string)
    {
        $key = strtolower($string);

        if (isset($this->language_strings[$key])) {
            return $this->language_strings[$key];
        }

        if ($this->primary_language === true) {
            return $this->translateStringNotFound($key, $string);
        }

        return $string;
    }

    protected function translateStringNotFound($key, $string)
    {
        return $string;
    }
}
