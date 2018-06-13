<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsFinite extends PHPUnit_Framework_Constraint
{
    
    protected function matches($other)
    {
        return is_finite($other);
    }

    
    public function toString()
    {
        return 'is finite';
    }
}
