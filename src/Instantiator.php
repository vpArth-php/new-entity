<?php

namespace Arth\Util;

use Arth\Util\Exception\NotFound;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

interface Instantiator
{
  /**
   * @throws NotFound
   */
  public function get($className, array $data = []);
  public function setDataForEntity($entity, array $data = [], ClassMetadata $meta = null);
}
