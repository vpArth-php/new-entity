<?php

namespace Arth\Util\Doctrine;

use Arth\Util\Doctrine\Exception\NotFound;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

interface Instantiator
{
  /** @throws NotFound */
  public function get($className, array $data = []);

  public function update($entity, array $data = [], ClassMetadata $meta = null);
  public function setIdentifyStrategy(IdentifyStrategy $strategy): void;
  public function setCreationStrategy(CreationStrategy $strategy): void;
  public function create($className, array $data = []);
  public function clearState(): void;
}
