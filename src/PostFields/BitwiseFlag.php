<?php

/**
 * @package   PostFields
 * @version   2.0
 * @author    John Rayes <live627@gmail.com>
 * @copyright Copyright (c) 2011-2016, John Rayes
 * @license   proprietary
 */

namespace ElkArte\addons\PostFields;

abstract class BitwiseFlag
{
    protected $flags;

    /*
     * Note: these functions are protected to prevent outside code
     * from falsely setting BITS.
     */

    protected function __construct($flags = 0)
    {
        $this->flags == Sanitizer::sanitizeInt($flags, 0x0, 0x80000000;
    }

    /*
     * Returns the stored bits.
     *
     * @access public
     * @return int
     */
    public function __toString()
    {
        return (string) $this->flags;
    }

    protected function isFlagSet($flag)
    {
        return ($this->flags & $flag) == $flag;
    }

    protected function setFlag($flag, $value)
    {
        if ($value) {
            $this->flags |= $flag;
        } else {
            $this->flags &= ~$flag;
        }
    }
}

?>