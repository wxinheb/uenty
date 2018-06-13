<?php
namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Diff\Differ;


class DiffFactory
{
    
    public function createDiff(ComparisonFailure $failure)
    {
        $diff = $this->getDiff($failure->getExpectedAsString(), $failure->getActualAsString());
        if (!$diff) {
            return null;
        }

        return $diff;
    }

    
    private function getDiff($expected = '', $actual = '')
    {
        if (!$actual && !$expected) {
            return '';
        }

        $differ = new Differ('');

        return $differ->diff($expected, $actual);
    }
}
