<?php
/**
 * Language Test
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Test;

defined('MOLAJO') or die;

/**
 * Language Test
 *
 * @author    Amy Stephen
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Language Object
     */
    protected $emailInstance;

    /**
     * @var Language Object
     */
    protected $Language_folder;

    /**
     * Initialises Adapter
     */
    protected function setUp()
    {
        $class = 'Molajo\\Language\\Adapter';

        $email_type = 'PhpMailerType';
        $email_class = 'Phpmailer\\phpmailer';
        $options = array();

        $this->emailInstance = new $class($email_type, $email_class, $options);

        return;
    }

    /**
     * Create a Language entry or set a parameter value
     *
     * @covers Molajo\Language\Type\FileLanguage::set
     */
    public function testSet()
    {
        $this->emailInstance->set('to', 'AmyStephen@gmail.com,Fname Lname');
        $this->emailInstance->set('from', 'AmyStephen@gmail.com,Fname Lname');
        $this->emailInstance->set('reply_to', 'AmyStephen@gmail.com,FName LName');
        $this->emailInstance->set('cc', 'AmyStephen@gmail.com,FName LName');
        $this->emailInstance->set('bcc', 'AmyStephen@gmail.com,FName LName');
        $this->emailInstance->set('subject', 'Welcome to our Site');
        $this->emailInstance->set('body', '<h2>Stuff goes here</h2>') ;
        $this->emailInstance->set('mailer_html_or_text', 'html') ;

        $this->emailInstance->send() ;

    }


    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }
}
