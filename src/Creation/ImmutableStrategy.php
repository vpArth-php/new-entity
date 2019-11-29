<?php

namespace Arth\Util\Doctrine\Creation;

use Doctrine\Common\Persistence\ManagerRegistry;

class ImmutableStrategy extends ImmutableDecorator
{
  public function __construct(ManagerRegistry $registry, $excludeFields = [])
  {
    parent::__construct($registry, $excludeFields, new FromDbStrategy($registry));
  }
}

