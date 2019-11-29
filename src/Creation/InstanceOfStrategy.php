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
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    $className = $meta->getName();
    $res       = null;
    foreach ($this->classMap as $class => $strategy) {
      // '' is used as fallback
      if ($class === '' || is_a($className, $class, true)) {
        $res = $strategy->create($meta, $id, $entity, $data);
      }
      if ($res) {
        break;
      }
    }
    return $res;
  }
}
