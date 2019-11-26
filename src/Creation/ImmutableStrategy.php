<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\GetManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class ImmutableStrategy implements CreationStrategy
{
  use GetManager;

  /** @var array */
  protected $serviceFields;
  private   $idMap = [];

  public function __construct(ManagerRegistry $registry, $serviceFields = [])
  {
    $this->registry      = $registry;
    $this->serviceFields = $serviceFields;
  }
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    $className = $meta->getName();
    $em        = $this->getManager($className);
    if ($id) {
      $key    = implode('.', array_keys($id)) . '#' . implode('|', $id);
      $entity = $this->idMap[$className][$key] ?? $em->getRepository($className)->findOneBy($id) ?? $entity;
    }
    if (isset($entity) && $this->hasEntityChanged($entity, $id, $data)) {
      $em->remove($entity);
      $entity = null;
    }
    if (!$entity) {
      $entity = $meta->newInstance();
    }

    if (isset($key)) {
      $this->idMap[$className][$key] = $entity;
    }

    return $entity;
  }
  public function clearState(): void
  {
    $this->idMap = [];
  }
  private function hasEntityChanged($entity, array $id, array $data): bool
  {
    $fields = is_callable($this->serviceFields) ? ($this->serviceFields)($entity) : $this->serviceFields;

    $entityData      = get_object_vars($entity);
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
