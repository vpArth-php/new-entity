<?php

namespace Arth\Util\Doctrine\Creation;

use Doctrine\Common\Persistence\ManagerRegistry;

class SimpleStrategy extends SimpleDecorator
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, new FromDbStrategy($registry));
  }
}
