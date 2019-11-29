<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\GetManager;
use Arth\Util\Doctrine\Helper\EntityMap;
use Arth\Util\Doctrine\Helper\EntityMapInterface;
use Arth\Util\Doctrine\StatefulCreationStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class StatefulDecorator implements StatefulCreationStrategy
{
  use GetManager;

  /** @var EntityMap */
  protected $map;
  /** @var CreationStrategy */
  protected $strategy;

  public function __construct(ManagerRegistry $registry, CreationStrategy $strategy, EntityMap $map = null)
  {
    $this->registry = $registry;
    $this->strategy = $strategy;
    $this->map      = $map ?? new EntityMap();
  }
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    $className = $meta->getName();

    if ($id) {
      $entity = $this->map->get($className, $id);
    }

    $entity = $this->strategy->create($meta, $id, $entity, $data);

    if ($id) {
      $this->map->set($className, $id, $entity);
    }

    return $entity;
  }
  public function clearState(): void
  {
    $this->map->clear();
  }
  public function getStateMap(): EntityMapInterface
  {
    return $this->map;
  }
}
