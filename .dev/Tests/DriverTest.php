<?php
/**
 * Molajo Language Driver Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Test;

use CommonApi\Language\LanguageInterface;
use CommonApi\Language\TranslateInterface;
use Molajo\Language\Driver;

/**
 * Molajo Language Driver Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  $driver
     */
    protected $driver;

    /**
     * Setup
     *
     * @covers  Molajo\Language\Driver::__construct
     */
    protected function setUp()
    {
        $adapter = new MockAdapter();

        $this->driver = new Driver($adapter, 'en-GB');
    }

    /**
     * @covers  Molajo\Language\Driver::get
     * @covers  Molajo\Language\Driver::translateString
     *
     * @return  $this
     * @since   1.0.0
     */
    public function testGet()
    {
        $results = $this->driver->get('key');

        $this->assertEquals($results, null);

        return $this;
    }

    /**
     * @covers  Molajo\Language\Driver::get
     * @covers  Molajo\Language\Driver::translateString
     *
     * @return  $this
     * @since   1.0.0
     */
    public function testTranslateString()
    {
        $results = $this->driver->translateString('This');

        $this->assertEquals($results, 'translatedThis');

        return $this;
    }
}

/**
 * Molajo Language Driver Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class MockAdapter implements LanguageInterface, TranslateInterface
{
    /**
     * Get Language Properties
     *
     * Specify null for key to have all language properties for current language
     * returned aas an object
     *
     * @param   null|string $key
     *
     * @return  int  $this
     * @since   1.0.0
     */
    public function get($key = null)
    {
        return null;
    }

    /**
     * Translate String
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    public function translateString($string)
    {
        return 'translated' . $string;
    }
}
