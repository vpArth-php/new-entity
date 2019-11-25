<?php

namespace Arth\Util\Doctrine;

use Arth\Util\Doctrine\Exception\NotFound;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

trait GetManager
{
  /** @var ManagerRegistry */
  protected $registry;

  public function getManager(string $className): ObjectManager
  {
    $em = $this->registry->getManagerForClass($className);
    if (null === $em) {
      throw new NotFound("Manager for '$className' not found");
    }
    return $em;
  }
}
