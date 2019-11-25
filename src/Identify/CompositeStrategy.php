<?php

namespace Arth\Util\Identify;

use Arth\Util\Exception\InvalidArgument;
use Arth\Util\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class CompositeStrategy implements IdentifyStrategy
{
  /** @var IdentifyStrategy[] */
  protected $list;
  public function __construct(array $list)
  {
    foreach ($list as $strategy) {
      if (!$strategy instanceof IdentifyStrategy) {
        throw new InvalidArgument(get_class($strategy) . 'is not ' . IdentifyStrategy::class);
      }
    }
    $this->list = $list;
  }
  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    $id = null;
    foreach ($this->list as $strategy) {
      $id = $strategy->getIdentifier($meta, $identifier);
      if ($id) {
        break;
      }
    }
    return $id;
  }
}
