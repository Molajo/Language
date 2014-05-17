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
use Molajo\Language\Adapter\StringArray;

/**
 * Molajo StringArray Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class StringArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  $adapter
     */
    protected $adapter;

    /**
     * Setup
     *
     * @covers  Molajo\Language\Adapter\StringArray::__construct
     * @covers  Molajo\Language\Adapter\StringArray::setLanguageMetadata
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
        $language_strings = array(
            'dog' => 'Dog',
            'catbook' => 'The Cat in the Hat',
            'error' => 'The error message is thus.',
            'z' => 'Zebra',
            'nothing' => 'Nada thing'
        );
        $model = new MockModel();
        $primary_language = true;
        $default_language = null;
        $en_gb_instance = null;

        $this->adapter = new StringArray($language, $title, $tag,
            $locale,
            $rtl,
            $direction,
            $first_day,
            $language_utc_offset,
            $language_strings,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance);
    }

    /**
     * @covers  Molajo\Language\Adapter\StringArray::get
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
     * @covers  Molajo\Language\Adapter\StringArray::get
     * @covers  Molajo\Language\Adapter\StringArray::getAll
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
     * @covers  Molajo\Language\Adapter\StringArray::get
     *
     * @expectedException \CommonApi\Exception\RuntimeException
     * @expectedExceptionMessage Language StringArray Adapter: Attempting to get value for unknown property: not_existing
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
     * @covers  Molajo\Language\Adapter\StringArray::translateString
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
     * @covers  Molajo\Language\Adapter\StringArray::translateString
     * @covers  Molajo\Language\Adapter\StringArray::translateStringNotFound
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\StringArray::setString
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
     * @covers  Molajo\Language\Adapter\StringArray::translateString
     * @covers  Molajo\Language\Adapter\StringArray::translateStringNotFound
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\StringArray::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringBackupLanguageFound()
    {
        $default_instance = new StringArray('en-DF',
            'Default Language', 'en-DF', 'en-DF', true, 'RTL', 1, 0,
            array(
                'abc' => 'The beginning of the alphabet.',
                'xyz' => 'The end of the alphabet.'
            ),
            new MockModel(), false, null, null);

        $current_instance = new StringArray('en-GB',
            'English Language', 'en-GB', 'en-GB', false, 'LTR', 0, 0,
            array(
                'dog' => 'Dog',
                'catbook' => 'The Cat in the Hat',
                'error' => 'The error message is thus.',
                'z' => 'Zebra',
                'nothing' => 'Nada thing'
            ),
            new MockModel(), true, $default_instance, null);

        $results = $current_instance->translateString('xyz');

        $this->assertEquals($results, 'The end of the alphabet.');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\StringArray::translateString
     * @covers  Molajo\Language\Adapter\StringArray::translateStringNotFound
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\StringArray::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringBackupLanguageNotFound()
    {
        $default_instance = new StringArray('en-DF',
            'Default Language', 'en-DF', 'en-DF', true, 'RTL', 1, 0,
            array(
                'abc' => 'The beginning of the alphabet.',
                'xyz' => 'The end of the alphabet.'
            ),
            new MockModel(), false, null, null);

        $current_instance = new StringArray('en-GB',
            'English Language', 'en-GB', 'en-GB', false, 'LTR', 0, 0,
            array(
                'dog' => 'Dog',
                'catbook' => 'The Cat in the Hat',
                'error' => 'The error message is thus.',
                'z' => 'Zebra',
                'nothing' => 'Nada thing'
            ),
            new MockModel(), true, $default_instance, null);

        $results = $current_instance->translateString('Not found');

        $this->assertEquals($results, 'String was set');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\StringArray::translateString
     * @covers  Molajo\Language\Adapter\StringArray::translateStringNotFound
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\StringArray::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringBaseLanguageFound()
    {
        $base_instance = new StringArray('en-BS',
            'Base Language', 'en-BS', 'en-BS', true, 'RTL', 1, 0,
            array(
                '3' => 'Third'
            ),
            new MockModel(), false, null, null);

        $default_instance = new StringArray('en-DF',
            'Default Language', 'en-DF', 'en-DF', true, 'RTL', 1, 0,
            array(
                'abc' => 'The beginning of the alphabet.',
                'xyz' => 'The end of the alphabet.'
            ),
            new MockModel(), false, null, null);

        $current_instance = new StringArray('en-GB',
            'English Language', 'en-GB', 'en-GB', false, 'LTR', 0, 0,
            array(
                'dog' => 'Dog',
                'catbook' => 'The Cat in the Hat',
                'error' => 'The error message is thus.',
                'z' => 'Zebra',
                'nothing' => 'Nada thing'
            ),
            new MockModel(), true, $default_instance, $base_instance);

        $results = $current_instance->translateString('3');

        $this->assertEquals($results, 'Third');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Adapter\StringArray::translateString
     * @covers  Molajo\Language\Adapter\StringArray::translateStringNotFound
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguages
     * @covers  Molajo\Language\Adapter\StringArray::searchBackupLanguage
     * @covers  Molajo\Language\Adapter\StringArray::setString
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateStringBaseLanguageNotFound()
    {
        $default_instance = new StringArray('en-DF',
            'Default Language', 'en-DF', 'en-DF', true, 'RTL', 1, 0,
            array(
                '3' => 'Third'
            ),
            new MockModel(), false, null, null);

        $current_instance = new StringArray('en-GB',
            'English Language', 'en-GB', 'en-GB', false, 'LTR', 0, 0,
            array(
                'dog' => 'Dog',
                'catbook' => 'The Cat in the Hat',
                'error' => 'The error message is thus.',
                'z' => 'Zebra',
                'nothing' => 'Nada thing'
            ),
            new MockModel(), true, $default_instance, null);

        $results = $current_instance->translateString('Not found');

        $this->assertEquals($results, 'String was set');

        return $this;
    }
}

class MockModel implements CaptureUntranslatedStringInterface
{
    public function setString($string)
    {
        return 'String was set';
    }
}
