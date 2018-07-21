<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;


abstract class FilterIterator extends \FilterIterator
{
    
    public function rewind()
    {
        if (PHP_VERSION_ID > 50607 || (PHP_VERSION_ID > 50523 && PHP_VERSION_ID < 50600)) {
            parent::rewind();

            return;
        }

        $iterator = $this;
        while ($iterator instanceof \OuterIterator) {
            $innerIterator = $iterator->getInnerIterator();

            if ($innerIterator instanceof RecursiveDirectoryIterator) {
                // this condition is necessary for iterators to work properly with non-local filesystems like ftp
                if ($innerIterator->isRewindable()) {
                    $innerIterator->next();
                    $innerIterator->rewind();
                }
            } elseif ($innerIterator instanceof \FilesystemIterator) {
                $innerIterator->next();
                $innerIterator->rewind();
            }

            $iterator = $innerIterator;
        }

        parent::rewind();
    }
}