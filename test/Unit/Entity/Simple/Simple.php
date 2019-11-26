<?php

namespace Test\Unit\Entity\Simple;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Simple
{
  /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
  public $id;
  /** @ORM\Column(type="string") */
  public $title;
  /** @ORM\Column(type="string") */
  public $description;
}
