<?php

namespace DeepCopy\Filter\Doctrine;

use DeepCopy\Filter\Filter;
use ReflectionProperty;


class DoctrineCollectionFilter implements Filter
{
    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);

        $reflectionProperty->setAccessible(true);
        $oldCollection = $reflectionProperty->getValue($object);

        $newCollection = $oldCollection->map(
            function ($item) use ($objectCopier) {
                return $objectCopier($item);
            }
        );

        $reflectionProperty->setValue($object, $newCollection);
    }
}
