<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface PHPUnit_Framework_MockObject_Stub extends PHPUnit_Framework_SelfDescribing
{
    
    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation);
}
