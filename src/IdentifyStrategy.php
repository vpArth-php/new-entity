<?php

namespace Arth\Util;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

interface IdentifyStrategy
{
  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array;
}
