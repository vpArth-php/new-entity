<?php

namespace Arth\Util\Doctrine;

use Arth\Util\Doctrine\Creation\SimpleStrategy;
use Arth\Util\Doctrine\Identify\PrimaryKeyStrategy;
use Arth\Util\TimeMachine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Id\AssignedGenerator;

class EntityInstantiator implements Instantiator
{
  use GetManager;

  /** @var IdentifyStrategy */
  protected $identifyStrategy;
  /** @var CreationStrategy */
  private $creationStrategy;

  public function __construct(ManagerRegistry $registry, IdentifyStrategy $identifyStrategy = null, CreationStrategy $creationStrategy = null)
  {
    $this->registry = $registry;
    $this->setIdentifyStrategy($identifyStrategy ?? new PrimaryKeyStrategy());
    $this->setCreationStrategy($creationStrategy ?? new SimpleStrategy($this->registry));
  }
  public function setIdentifyStrategy(IdentifyStrategy $strategy): void { $this->identifyStrategy = $strategy; }
  public function setCreationStrategy(CreationStrategy $strategy): void { $this->creationStrategy = $strategy; }

  public function get($class, array $data = [])
  {
    $className = is_object($class) ? get_class($class) : $class;

    $em   = $this->getManager($className);
    $meta = $em->getClassMetadata($className);

    $entity = $this->create($class, $data);
    $this->update($entity, $data, $meta);

    return $entity;
  }

  public function create($class, array $data = [])
  {
    $className = is_object($class) ? get_class($class) : $class;
    $em        = $this->getManager($className);
    $meta      = $em->getClassMetadata($className);
    $id        = $this->getIdentifier($meta, $data);

    return $this->creationStrategy->create($meta, $id, is_object($class) ? $class : null, $data);
  }

  public function clearState(): void
  {
    $this->creationStrategy->clearState();
  }

  public function update($entity, array $data = [], ClassMetadata $meta = null)
  {
    $className = get_class($entity);

    $em = $this->getManager($className);

    if (null === $meta) {
      $meta = $em->getClassMetadata($className);
    }

    $this->initCollectionFields($meta, $entity);

    $associations = $meta->getAssociationNames();
    foreach ($associations as $field) {
      $mapping     = $meta->getAssociationMapping($field);
      $targetClass = $meta->getAssociationTargetClass($field);
      if (!array_key_exists($field, $data)) {
        continue;
      }
      $value = $data[$field];
      unset($data[$field]);
      if (!$meta->isAssociationInverseSide($field)) { // owning side
        if ($value !== null && !$value instanceof $targetClass) {
          if (is_scalar($value)) {
            $value = [$mapping['joinColumns'][0]['referencedColumnName'] => $value];
          }
          $value = $this->get($entity->$field ?? $targetClass, $value);
        }
        $this->setEntityFieldValue($entity, $field, $value);
      } elseif ($meta->isCollectionValuedAssociation($field)) {
        $this->mergeCollection($meta, $entity, $field, $value);
      } else {
        if ($value !== null) {
          $value = $this->get($entity->$field ?? $targetClass, $value);
        }
        $this->setEntityFieldValue($entity, $field, $value);
      }
    }

    $this->setFieldValues($meta, $entity, $data);

    return $entity;
  }

  protected function getIdentifier(ClassMetadata $meta, array &$data): ?array
  {
    $id = $this->identifyStrategy->getIdentifier($meta, $data);
    if ($id) {
      foreach ($id as $field => $value) {
        unset($data[$field]);
      }
    }
    return $id;
  }
  protected function initCollectionFields(ClassMetadata $meta, $entity): void
  {
    foreach ($meta->getAssociationNames() as $field) {
      if (!$entity->$field && $meta->isCollectionValuedAssociation($field)) {
        $entity->$field = new ArrayCollection();
      }
    }
  }
  protected function setEntityFieldValue($entity, $field, $value): void
  {
    if (method_exists($entity, 'set' . ucfirst($field))) {
      $entity->{'set' . ucfirst($field)}($value);
    } else {
      $entity->$field = $value;
    }
  }
  protected function mergeCollection(ClassMetadata $meta, $entity, $field, $value): void
  {
    $targetClass = $meta->getAssociationTargetClass($field);
    $mappedBy    = $meta->getAssociationMappedByTargetField($field);

    /** @var Collection $collection */
    $collection = $entity->$field;
    $collection->clear();
    foreach ($value as $itemData) {
      if ($item = $this->get($targetClass, $itemData)) {
        if (!$collection->contains($item)) {
          $collection->add($item);
        }
        $item->$mappedBy = $entity;
      }
    }
  }
  protected function setFieldValues(ClassMetadata $meta, $entity, &$data): void
  {
    if (!$meta->idGenerator instanceof AssignedGenerator) {
      foreach ($meta->getIdentifierFieldNames() as $idField) {
        unset($data[$idField]);
      }
    }
    foreach ($meta->getFieldNames() as $field) {
      if (!array_key_exists($field, $data)) {
        continue;
      }
      $type  = $meta->getTypeOfField($field);
      $value = $data[$field];
      unset($data[$field]);
      switch ($type) {
        case 'date':
        case 'date_immutable':
          $value = $value ? TimeMachine::getInstance()->getNow()->modify("$value 0:00:00") : null;
          break;
        case 'datetime':
        case 'datetime_immutable':
          // values like '-3 day' will be processed
          $value = $value ? TimeMachine::getInstance()->getNow()->modify($value) : null;
          break;
        default:
      }
      $this->setEntityFieldValue($entity, $field, $value);
    }
    foreach ($data as $field => $value) { // Unmapped fields, perhaps setters
      $this->setEntityFieldValue($entity, $field, $value);
    }
  }
}
