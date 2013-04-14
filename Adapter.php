<?php
/**
 * Adapter for Language
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language;

defined('MOLAJO') or die;

use Molajo\Language\Api\LanguageInterface;

use Molajo\Language\Exception\LanguageException;

/**
 * Adapter for Language
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @since     1.0
 */
class Adapter implements LanguageInterface
{
    /**
     * Language Type
     *
     * @var     object
     * @since   1.0
     */
    public $et;

    /**
     * Construct
     *
     * @param   string $email_type
     * @param   string $filesystem_instance
     *
     * @since   1.0
     * @throws  LanguageException
     */
    public function __construct($email_type = '', $email_class = '', $options = array())
    {
        if ($email_type == '') {
            $email_type = 'PhpMailerType';
        }

        $this->getLanguageType($email_type, $email_class, $options);
    }

    /**
     * Get the Language Type (Css, Js, Links, Metadata)
     *
     * @param   string $email_type
     *
     * @return  $this
     * @since   1.0
     * @throws  LanguageException
     */
    protected function getLanguageType($email_type, $email_class = '', $options = array())
    {
        if ($email_type == '') {
            $email_type = 'PhpMailerType';
        }

        $class = 'Molajo\\Language\\Type\\' . $email_type;

        if (class_exists($class)) {
        } else {
            throw new LanguageException
            ('Language Type class ' . $class . ' does not exist.');
        }

        $this->et = new $class($email_class, $options);

        return $this;
    }

    /**
     * Set the Language Type (PhpMailer, Swiftmailer)
     *
     * @param   string  $email_type
     * @param   array() $options
     *
     * @return  $this
     * @since   1.0
     * @throws  LanguageException
     */
    public function setLanguagePackage($email_class, $options = array())
    {
        return $this->et->setLanguagePackage($email_class, $options);
    }

    /**
     * Get Property
     *
     * @param   string $key
     * @param   mixed  $default
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function get($key, $default)
    {
        return $this->et->get($key, $default);
    }

    /**
     * Set Property
     *
     * @param   string $key
     * @param   array  $options
     *
     * @return  object
     * @since   1.0
     * @throws  LanguageException
     */
    public function set($key, $options = array())
    {
        return $this->et->set($key, $options);
    }

    /**
     * Send email
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function send()
    {
        return $this->et->send();
    }
}
