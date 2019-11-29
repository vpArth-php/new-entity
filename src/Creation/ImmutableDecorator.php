<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\GetManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use JsonSerializable;

class ImmutableDecorator implements CreationStrategy
{
  use GetManager;

  /** @var array */
  protected $excludeFields;
  /** @var CreationStrategy */
  protected $strategy;

  public function __construct(ManagerRegistry $registry, $excludeFields = [], CreationStrategy $strategy = null)
  {
    $this->registry      = $registry;
    $this->excludeFields = $excludeFields;
    $this->strategy      = $strategy ?? new SimpleStrategy($registry);
  }
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    $className = $meta->getName();
    $em        = $this->getManager($className);

    $entity = $this->strategy->create($meta, $id, $entity, $data);
    if (isset($entity) && $this->hasEntityChanged($entity, $id, $data)) {
      $em->remove($entity);
      $entity = null;
    }

    return $entity ?? $meta->newInstance();
  }
  protected function hasEntityChanged($entity, array $id, array $data): bool
  {
    $fields = is_callable($this->excludeFields) ? ($this->excludeFields)($entity) : $this->excludeFields;

    if ($entity instanceof JsonSerializable) {
      $entityData = $entity->jsonSerialize();
    } else {
      $entityData = get_object_vars($entity);
    }

    $serviceFieldMap = array_flip($fields);
    $changed         = false;
    foreach ($entityData as $field => $value) {
      if (array_key_exists($field, $id) || array_key_exists($field, $serviceFieldMap)) {
        continue;
      }
      if (strpos($field, '_') === 0) {
        continue;
      }
      $old = $entity->$field ?? null;
      $new = $data[$field] ?? null;
      if ((array_key_exists($field, $data)) && ($old !== $new)) {
        $changed = true;
        break;
      }
    }
    return $changed;
  }
}
