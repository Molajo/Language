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
     * @var  array $primary_language_array
     */
    protected $primary_language_array
        = array(
            'language'            => 'en-GB',
            'title'               => 'English',
            'tag'                 => 'en-GB',
            'locale'              => 'en-GB',
            'rtl'                 => 'false',
            'direction'           => 'ltr',
            'first_day'           => 0,
            'language_utc_offset' => 0,
            'primary_language'    => true
        );

    /**
     * @var  array $primary_language_strings
     */
    protected $primary_language_strings
        = array(
            'dog'     => 'Dog',
            'catbook' => 'The Cat in the Hat',
            'error'   => 'The error message is thus.',
            'z'       => 'Zebra',
            'nothing' => 'Nada thing'
        );

    /**
     * @var  array $default_language_array
     */
    protected $default_language_array
        = array(
            'language'            => 'en-DF',
            'title'               => 'English DF',
            'tag'                 => 'en-DF',
            'locale'              => 'en-DF',
            'rtl'                 => 'true',
            'direction'           => 'rtl',
            'first_day'           => 0,
            'language_utc_offset' => 0,
            'primary_language'    => false
        );

    /**
     * @var  array $default_language_strings
     */
    protected $default_language_strings
        = array(
            'abc' => 'The beginning of the alphabet.',
            'xyz' => 'The end of the alphabet.'
        );

    /**
     * @var  array $default_language_array
     */
    protected $en_gb_array
        = array(
            'language'            => 'en-DF',
            'title'               => 'English DF',
            'tag'                 => 'en-DF',
            'locale'              => 'en-DF',
            'rtl'                 => 'true',
            'direction'           => 'rtl',
            'first_day'           => 0,
            'language_utc_offset' => 0,
            'primary_language'    => false
        );

    /**
     * @var  array $default_language_strings
     */
    protected $en_gb_strings
        = array(
            '3' => 'Third'
        );

    /**
     * Setup
     *
     * @covers  Molajo\Language\Adapter\StringArray::__construct
     * @covers  Molajo\Language\Adapter\StringArray::setLanguageMetadata
     */
    protected function setUp()
    {
        $model            = new MockModel();
        $primary_language = true;
        $default_language = null;
        $en_gb_instance   = null;

        $this->adapter = new StringArray(
            $this->primary_language_array,
            $this->primary_language_strings,
            $model,
            $primary_language,
            $default_language,
            $en_gb_instance
        );
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
     * @covers                   Molajo\Language\Adapter\StringArray::get
     *
     * @expectedException        \CommonApi\Exception\RuntimeException
     * @expectedExceptionMessage Language StringArray Adapter: Attempting to get value for unknown property: not_existing
     *
     * @return  $this
     * @since                    1.0
     */
    public function testGetNotExisting()
    {
        $results = $this->adapter->get('not_existing');

        $this->assertEquals($results, 'String was set');

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
        $model = new MockModel();

        $default_instance = new StringArray(
            $this->default_language_array,
            $this->default_language_strings,
            $model, false, null, null
        );

        $current_instance = new StringArray(
            $this->primary_language_array,
            $this->primary_language_strings,
            $model, true, $default_instance, null
        );

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
        $model = new MockModel();

        $default_instance = new StringArray(
            $this->default_language_array,
            $this->default_language_strings,
            $model, false, null, null
        );

        $current_instance = new StringArray(
            $this->primary_language_array,
            $this->primary_language_strings,
            $model, true, $default_instance, null
        );

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
        $model = new MockModel();

        $base_instance = new StringArray(
            $this->en_gb_array,
            $this->en_gb_strings,
            $model, false, null, null
        );

        $default_instance = new StringArray(
            $this->default_language_array,
            $this->default_language_strings,
            $model, false, null, null
        );

        $current_instance = new StringArray(
            $this->primary_language_array,
            $this->primary_language_strings,
            $model, true, $default_instance, $base_instance
        );

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
        $model = new MockModel();

        $base_instance = new StringArray(
            $this->en_gb_array,
            $this->en_gb_strings,
            $model, false, null, null
        );

        $default_instance = new StringArray(
            $this->default_language_array,
            $this->default_language_strings,
            $model, false, null, null
        );

        $current_instance = new StringArray(
            $this->primary_language_array,
            $this->primary_language_strings,
            $model, true, $default_instance, $base_instance
        );

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
