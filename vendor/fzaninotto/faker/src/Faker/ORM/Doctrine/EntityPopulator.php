<?php

namespace Faker\ORM\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;


class EntityPopulator
{
    
    protected $class;
    
    protected $columnFormatters = array();
    
    protected $modifiers = array();

    
    public function __construct(ClassMetadata $class)
    {
        $this->class = $class;
    }

    
    public function getClass()
    {
        return $this->class->getName();
    }

    
    public function setColumnFormatters($columnFormatters)
    {
        $this->columnFormatters = $columnFormatters;
    }

    
    public function getColumnFormatters()
    {
        return $this->columnFormatters;
    }

    public function mergeColumnFormattersWith($columnFormatters)
    {
        $this->columnFormatters = array_merge($this->columnFormatters, $columnFormatters);
    }

    
    public function setModifiers(array $modifiers)
    {
        $this->modifiers = $modifiers;
    }

    
    public function getModifiers()
    {
        return $this->modifiers;
    }

    
    public function mergeModifiersWith(array $modifiers)
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);
    }

    
    public function guessColumnFormatters(\Faker\Generator $generator)
    {
        $formatters = array();
        $nameGuesser = new \Faker\Guesser\Name($generator);
        $columnTypeGuesser = new ColumnTypeGuesser($generator);
        foreach ($this->class->getFieldNames() as $fieldName) {
            if ($this->class->isIdentifier($fieldName) || !$this->class->hasField($fieldName)) {
                continue;
            }

            $size = isset($this->class->fieldMappings[$fieldName]['length']) ? $this->class->fieldMappings[$fieldName]['length'] : null;
            if ($formatter = $nameGuesser->guessFormat($fieldName, $size)) {
                $formatters[$fieldName] = $formatter;
                continue;
            }
            if ($formatter = $columnTypeGuesser->guessFormat($fieldName, $this->class)) {
                $formatters[$fieldName] = $formatter;
                continue;
            }
        }

        foreach ($this->class->getAssociationNames() as $assocName) {
            if ($this->class->isCollectionValuedAssociation($assocName)) {
                continue;
            }

            $relatedClass = $this->class->getAssociationTargetClass($assocName);

            $unique = $optional = false;
            $mappings = $this->class->getAssociationMappings();
            foreach ($mappings as $mapping) {
                if ($mapping['targetEntity'] == $relatedClass) {
                    if ($mapping['type'] == ClassMetadata::ONE_TO_ONE) {
                        $unique = true;
                        $optional = isset($mapping['joinColumns'][0]['nullable']) ? $mapping['joinColumns'][0]['nullable'] : false;
                        break;
                    }
                }
            }

            $index = 0;
            $formatters[$assocName] = function ($inserted) use ($relatedClass, &$index, $unique, $optional) {

                if (isset($inserted[$relatedClass])) {
                    if ($unique) {
                        $related = null;
                        if (isset($inserted[$relatedClass][$index]) || !$optional) {
                            $related = $inserted[$relatedClass][$index];
                        }

                        $index++;

                        return $related;
                    }

                    return $inserted[$relatedClass][mt_rand(0, count($inserted[$relatedClass]) - 1)];
                }

                return null;
            };
        }

        return $formatters;
    }

    
    public function execute(ObjectManager $manager, $insertedEntities, $generateId = false)
    {
        $obj = $this->class->newInstance();

        $this->fillColumns($obj, $insertedEntities);
        $this->callMethods($obj, $insertedEntities);

        if ($generateId) {
            $idsName = $this->class->getIdentifier();
            foreach ($idsName as $idName) {
                $id = $this->generateId($obj, $idName, $manager);
                $this->class->reflFields[$idName]->setValue($obj, $id);
            }
        }

        $manager->persist($obj);

        return $obj;
    }

    private function fillColumns($obj, $insertedEntities)
    {
        foreach ($this->columnFormatters as $field => $format) {
            if (null !== $format) {
                // Add some extended debugging information to any errors thrown by the formatter
                try {
                    $value = is_callable($format) ? $format($insertedEntities, $obj) : $format;
                } catch (\InvalidArgumentException $ex) {
                    throw new \InvalidArgumentException(sprintf(
                        "Failed to generate a value for %s::%s: %s",
                        get_class($obj),
                        $field,
                        $ex->getMessage()
                    ));
                }
                // Try a standard setter if it's available, otherwise fall back on reflection
                $setter = sprintf("set%s", ucfirst($field));
                if (method_exists($obj, $setter)) {
                    $obj->$setter($value);
                } else {
                    $this->class->reflFields[$field]->setValue($obj, $value);
                }
            }
        }
    }

    private function callMethods($obj, $insertedEntities)
    {
        foreach ($this->getModifiers() as $modifier) {
            $modifier($obj, $insertedEntities);
        }
    }

    
    private function generateId($obj, $column, EntityManagerInterface $manager)
    {
        /* @var $repository \Doctrine\ORM\EntityRepository */
        $repository = $manager->getRepository(get_class($obj));
        $result = $repository->createQueryBuilder('e')
                ->select(sprintf('e.%s', $column))
                ->getQuery()
                ->getResult();
        $ids = array_map('current', $result);

        $id = null;
        do {
            $id = mt_rand();
        } while (in_array($id, $ids));

        return $id;
    }
}
