<?php

namespace Test\Unit\Entity\Simple;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PublicProps
{
  /** @ORM\Id @ORM\Column(type="integer") */
  public $id;
  /** @ORM\Column(type="string") */
  public $title;
}
