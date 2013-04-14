<?php
/**
 * Language Interface
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language\Api;

defined('MOLAJO') or die;

use Molajo\Language\Exception\LanguageException;

/**
 * Language Interface
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
interface LanguageInterface
{
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
    public function setLanguagePackage($email_class, $options = array());

    /**
     * Get Property
     *
     * @param   string  $key
     * @param   mixed   $default
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function get($key, $default);

    /**
     * Set value for a key
     *
     * @param   string $key
     * @param   array  $options
     *
     * @return  object
     * @since   1.0
     * @throws  LanguageException
     */
    public function set($key, $value);

    /**
     * Send email
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function send();
}
