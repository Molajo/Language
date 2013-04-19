<?php
/**
 * Language Exception
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
namespace Molajo\Language\Exception;

defined('MOLAJO') or die;

use Exception;

use Molajo\Language\Api\ExceptionInterface;

/**
 * Language Exception
 *
 * @package   Molajo
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @since     1.0
 */
class LanguageException extends Exception implements ExceptionInterface
{

}
