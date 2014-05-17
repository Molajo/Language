<?php
/**
 * Molajo Language Driver Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
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
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  $Driver
     */
    protected $driver;

    /**
     * Setup
     *
     * @covers  Molajo\Language\Driver::__construct
     * @covers  Molajo\Language\Adapter\Database::__construct
     * @covers  Molajo\Language\Adapter\DatabaseModel::__construct
     */
    protected function setUp()
    {
        /** Mock Adapter */
        $adapter = new MockAdapter();

        $this->driver = new Driver($adapter, 'en-GB');
    }

    /**
     * @covers  Molajo\Language\Driver::get
     * @covers  Molajo\Language\Driver::translateString
     *
     * @covers  Molajo\Language\Adapter\Database::get
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @covers  Molajo\Language\Adapter\DatabaseModel::get
     * @covers  Molajo\Language\Adapter\DatabaseModel::setInstalledLanguages
     * @covers  Molajo\Language\Adapter\DatabaseModel::getLanguageStrings
     * @covers  Molajo\Language\Adapter\DatabaseModel::setString
     * @covers  Molajo\Language\Adapter\DatabaseModel::exists
     * @covers  Molajo\Language\Adapter\DatabaseModel::saveLanguageString
     * @covers  Molajo\Language\Adapter\DatabaseModel::insertCatalogEntry
     * @covers  Molajo\Language\Adapter\DatabaseModel::getSEFRequest
     * @covers  Molajo\Language\Adapter\DatabaseModel::getApplications
     *
     * @return  $this
     * @since   1.0
     */
    public function testGet()
    {
        $results  = $this->driver->get('key', 'value');

        $this->assertEquals($results, 'value');

        return $this;
    }

    /**
     * @covers  Molajo\Language\Driver::get
     * @covers  Molajo\Language\Driver::translateString
     *
     * @covers  Molajo\Language\Adapter\Database::get
     * @covers  Molajo\Language\Adapter\Database::translateString
     * @covers  Molajo\Language\Adapter\Database::setString
     *
     * @covers  Molajo\Language\Adapter\DatabaseModel::get
     * @covers  Molajo\Language\Adapter\DatabaseModel::setInstalledLanguages
     * @covers  Molajo\Language\Adapter\DatabaseModel::getLanguageStrings
     * @covers  Molajo\Language\Adapter\DatabaseModel::setString
     * @covers  Molajo\Language\Adapter\DatabaseModel::exists
     * @covers  Molajo\Language\Adapter\DatabaseModel::saveLanguageString
     * @covers  Molajo\Language\Adapter\DatabaseModel::insertCatalogEntry
     * @covers  Molajo\Language\Adapter\DatabaseModel::getSEFRequest
     * @covers  Molajo\Language\Adapter\DatabaseModel::getApplications
     *
     * @return  $this
     * @since   1.0
     */
    public function testTranslateString()
    {
        $results  = $this->driver->translateString('This');

        $this->assertEquals($results, 'translatedThis');

        return $this;
    }
}


/**
 * Molajo Language Driver Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
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
     * @param   null|string $default
     *
     * @return  int  $this
     * @since   1.0.0
     */
    public function get($key = null, $default = null)
    {
        return $default;
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
