<?php

namespace Arth\Util\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

interface CreationStrategy
{
  public function create(ClassMetadata $meta, ?array $id);
  public function clearState(): void;
}


