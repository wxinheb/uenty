<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Promise;

use Prophecy\Exception\InvalidArgumentException;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;


class ReturnArgumentPromise implements PromiseInterface
{
    
    private $index;

    
    public function __construct($index = 0)
    {
        if (!is_int($index) || $index < 0) {
            throw new InvalidArgumentException(sprintf(
                'Zero-based index expected as argument to ReturnArgumentPromise, but got %s.',
                $index
            ));
        }
        $this->index = $index;
    }

    
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method)
    {
        return count($args) > $this->index ? $args[$this->index] : null;
    }
}
