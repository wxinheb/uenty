<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Warning extends PHPUnit_Framework_Exception implements PHPUnit_Framework_SelfDescribing
{
    
    public function toString()
    {
        return $this->getMessage();
    }
}
