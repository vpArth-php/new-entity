<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class InstanceOfStrategy implements CreationStrategy
{
  /** @var CreationStrategy[] */
  protected $classMap;

  /** @var CreationStrategy[] $classMap */
  public function __construct(array $classMap)
  {
    $this->classMap = $classMap;
  }
  public function create(ClassMetadata $meta, ?array $id)
  {
    $className = $meta->getName();
    foreach ($this->classMap as $class => $strategy) {
      // '' is used as fallback
      if ($class === '' || is_a($className, $class, true)) {
        return $strategy->create($meta, $id);
      }
    }
    return null;
  }
  public function clearState(): void
  {
    foreach ($this->classMap as $strategy) {
      $strategy->clearState();
    }
  }
}
