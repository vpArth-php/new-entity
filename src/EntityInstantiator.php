<?php

namespace Arth\Util;

use Arth\Util\Exception\NotFound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

class EntityInstantiator implements Instantiator
{
  /** @var ManagerRegistry */
  protected $registry;

  public function __construct(ManagerRegistry $registry)
  {
    $this->registry = $registry;
  }

  public function get($className, array $data = [])
  {
    $em   = $this->getManager($className);
    $meta = $em->getClassMetadata($className);
    $id   = $this->getIdentifier($meta, $data);

    $entity = $id ? $em->find($className, $id) : $meta->newInstance();

    $this->setDataForEntity($entity, $data, $meta);

    return $entity;
  }

  protected function getIdentifier(ClassMetadata $meta, array &$data)
  {
    $idFields = $meta->getIdentifierFieldNames();

    $result = [];
    foreach ($idFields as $i => $idField) {
      if (array_key_exists($i, $data)) {
        $data[$idField] = $data[$i];
        unset($data[$i]);
      }
      if (!array_key_exists($idField, $data)) {
        return null;
      }
      $result[$idField] = $data[$idField];
      unset($data[$idField]);
    }
    return $result;
  }

  public function setDataForEntity($entity, array $data = [], ClassMetadata $meta = null)
  {
    $className = get_class($entity);

    $em = $this->getManager($className);

    if (null === $meta) {
      $meta = $em->getClassMetadata($className);
    }

    $this->initCollectionFields($meta, $entity);

    $associations = $meta->getAssociationNames();
    foreach ($associations as $field) {
      $targetClass = $meta->getAssociationTargetClass($field);
      if (!array_key_exists($field, $data)) {
        continue;
      }
      $value = $data[$field];
      unset($data[$field]);
      if (!$meta->isAssociationInverseSide($field)) { // owning side
        if (!$value instanceof $targetClass) {
          $value = $this->get($targetClass, is_scalar($value) ? [$value] : $value);
        }
        $this->setEntityFieldValue($entity, $field, $value);
      }
    }

    $this->setFieldValues($meta, $entity, $data);

    return $entity;
  }
  protected function initCollectionFields(ClassMetadata $meta, $entity): void
  {
    foreach ($meta->getAssociationNames() as $field) {
      if (!$entity->$field && $meta->isCollectionValuedAssociation($field)) {
        $entity->$field = new ArrayCollection();
      }
    }
  }

  public function getManager(string $className): ObjectManager
  {
    $em = $this->registry->getManagerForClass($className);
    if (null === $em) {
      throw new NotFound("Manager for '$className' not found");
    }
    return $em;
  }

  protected function setEntityFieldValue($entity, $field, $value): void
  {
    if (method_exists($entity, 'set' . ucfirst($field))) {
      $entity->{'set' . ucfirst($field)}($value);
    } else {
      $entity->$field = $value;
    }
  }
  protected function setFieldValues(ClassMetadata $meta, $entity, &$data): void
  {
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
