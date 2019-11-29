<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\GetManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class SimpleDecorator implements CreationStrategy
{
  use GetManager;
  /** @var CreationStrategy */
  protected $strategy;

  public function __construct(ManagerRegistry $registry, CreationStrategy $strategy)
  {
    $this->registry = $registry;
    $this->strategy = $strategy;
  }
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    return $this->strategy->create($meta, $id, $entity, $data) ?? $meta->newInstance();
  }
}
