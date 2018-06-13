<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Argument;


class ArgumentsWildcard
{
    
    private $tokens = array();
    private $string;

    
    public function __construct(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (!$argument instanceof Token\TokenInterface) {
                $argument = new Token\ExactValueToken($argument);
            }

            $this->tokens[] = $argument;
        }
    }

    
    public function scoreArguments(array $arguments)
    {
        if (0 == count($arguments) && 0 == count($this->tokens)) {
            return 1;
        }

        $arguments  = array_values($arguments);
        $totalScore = 0;
        foreach ($this->tokens as $i => $token) {
            $argument = isset($arguments[$i]) ? $arguments[$i] : null;
            if (1 >= $score = $token->scoreArgument($argument)) {
                return false;
            }

            $totalScore += $score;

            if (true === $token->isLast()) {
                return $totalScore;
            }
        }

        if (count($arguments) > count($this->tokens)) {
            return false;
        }

        return $totalScore;
    }

    
    public function __toString()
    {
        if (null === $this->string) {
            $this->string = implode(', ', array_map(function ($token) {
                return (string) $token;
            }, $this->tokens));
        }

        return $this->string;
    }

    
    public function getTokens()
    {
        return $this->tokens;
    }
}
