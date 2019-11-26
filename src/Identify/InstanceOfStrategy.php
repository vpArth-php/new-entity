<?php

namespace Arth\Util\Doctrine\Identify;

use Arth\Util\Doctrine\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class InstanceOfStrategy implements IdentifyStrategy
{
  /** @var IdentifyStrategy[] */
  protected $classMap;

  /** @var IdentifyStrategy[] $classMap */
  public function __construct(array $classMap)
  {
    $this->classMap = $classMap;
  }

  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    $className = $meta->getName();
    $id        = null;
    foreach ($this->classMap as $class => $strategy) {
      if ($class === '' || is_a($className, $class, true)) {
        $id = $strategy->getIdentifier($meta, $identifier);
        if ($id) {
          break;
        }
      }
    }
    return $id;
  }
}
