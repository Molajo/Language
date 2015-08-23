<?php
/**
 * Dummy Capture
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Language\Capture;

use CommonApi\Language\CaptureUntranslatedStringInterface;

/**
 * Dummy Capture
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class Dummy implements CaptureUntranslatedStringInterface
{
    /**
     * Save untranslated strings for use by translators
     *
     * @param   string $string
     *
     * @return  string
     * @since   1.0.0
     */
    public function setString($string)
    {
        return $string;
    }
}
