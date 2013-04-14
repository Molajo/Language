<?php
/**
 * Abstract Language Class
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language\Type;

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
class AbstractType implements LanguageInterface
{
    /**
     * Language Instance and Configuration
     */

    /**
     * Mail Instance
     *
     * @var     string
     * @since   1.0
     */
    protected $mailInstance;

    /**
     * Site Name
     *
     * @var     string
     * @since   1.0
     */
    protected $site_name;

    /**
     * Language Instance and Configuration
     */

    /**
     * Language Instance
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $email_instance;

    /**
     * Mailer Transport - smtp, sendmail, ismail
     *
     * @var     string
     * @since   1.0
     */
    protected $mailer_transport;

    /**
     * SMTP
     */

    /**
     * SMTP Authorisation
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $mailer_smtpauth;

    /**
     * SMTP Host
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $smtphost;

    /**
     * SMTP User
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $mailer_smtpuser;

    /**
     * SMTP Host
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $mailer_mailer_smtphost;

    /**
     * SMTP Secure
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $smtpsecure;

    /**
     * SMTP Port
     *
     * @var     LanguageInterface
     * @since   1.0
     */
    protected $smtpport;

    /**
     * Sendmail
     */

    /**
     * Sendmail Path
     *
     * @var     bool
     * @since   1.0
     */
    protected $sendmail_path = 0;

    /**
     * Message
     */

    /**
     * Sender
     *
     * @var     string
     * @since   1.0
     */
    protected $sender = '';

    /**
     * Recipient
     *
     * @var     array
     * @since   1.0
     */
    protected $recipient = array();

    /**
     * Reply To
     *
     * @var     array
     * @since   1.0
     */
    protected $reply_to = array();

    /**
     * From
     *
     * @var     array
     * @since   1.0
     */
    protected $from = array();

    /**
     * To
     *
     * @var     array
     * @since   1.0
     */
    protected $to = array();

    /**
     * Copy
     *
     * @var     array
     * @since   1.0
     */
    protected $cc = array();

    /**
     * Blind Copy
     *
     * @var     array
     * @since   1.0
     */
    protected $bcc = array();

    /**
     * Subject
     *
     * @var     string
     * @since   1.0
     */
    protected $subject = '';

    /**
     * Body
     *
     * @var     string
     * @since   1.0
     */
    protected $body = '';

    /**
     * HTML or Text
     *
     * @var     string
     * @since   1.0
     */
    protected $mailer_html_or_text = '';

    /**
     * Testing Support
     */

    /**
     * Disable Sending
     *
     * @var     bool
     * @since   1.0
     */
    protected $mailer_disable_sending = 0;

    /**
     * Only Deliver To
     *
     * @var     string
     * @since   1.0
     */
    protected $mailer_only_deliver_to = '';

    /**
     * Attachment
     *
     * @var     string
     * @since   1.0
     */
    protected $attachment = '';

    /**
     * List of Properties
     *
     * @var    object
     * @since  1.0
     */
    protected $property_array = array(
        'options',
        'email_instance',
        'mailer_transport',
        'mailer_smtpauth',
        'mailer_smtpuser',
        'mailer_mailer_smtphost',
        'smtpsecure',
        'smtpport',
        'sender',
        'recipient',
        'reply_to',
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'mailer_html_or_text',
        'mailer_disable_sending',
        'mailer_only_deliver_to',
        'attachment'
    );

    /**
     * Construct
     *
     * @param   string  $email_class
     * @param   array   $options
     *
     * @return  object  LanguageInterface
     * @since   1.0
     * @throws  LanguageException
     */
    public function __construct($email_class = 'phpmailer\\PHPMailer', $options = array())
    {
        $this->email_class = $email_class;
        $this->options = $options;
    }

    /**
     * Get the Language Type (PhpMailer, Swiftmailer)
     *
     * @param   string $email_type
     *
     * @return  $this
     * @since   1.0
     * @throws  LanguageException
     */
    public function setLanguagePackage($email_class, $options = array())
    {
        if (class_exists($email_class)) {
        } else {
            throw new LanguageException
            ('Language Package Class ' . $email_class . ' does not exist.');
        }

        $this->email_instance = new $email_class();

        return $this;
    }

    /**
     * Return value for a key
     *
     * @param   null|string $key
     * @param   mixed       $default
     *
     * @return  mixed
     * @since   1.0
     * @throws  LanguageException
     */
    public function get($key, $default = null)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new LanguageException
            ('Language Service: attempting to get value for unknown property: ' . $key);
        }

        if ($this->key === null) {
            $this->$key = $default;
        }

        return $this->$key;
    }

    /**
     * Set value for a key
     *
     * @param   string $key
     * @param   mixed  $value
     *
     * @return  object
     * @since   1.0
     * @throws  LanguageException
     */
    public function set($key, $value)
    {
        $key = strtolower($key);

        if (in_array($key, $this->property_array)) {
        } else {
            throw new LanguageException
            ('Language Service: attempting to set value for unknown key: ' . $key);
        }

        $this->$key = $value;

        return $this->$key;
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
        return $this;
    }
}
