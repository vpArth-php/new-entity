<?php

namespace Arth\Util\Doctrine;

use Arth\Util\Doctrine\Helper\EntityMapInterface;

interface StatefulCreationStrategy extends CreationStrategy
{
  public function getStateMap(): EntityMapInterface;
  public function clearState(): void;
}


