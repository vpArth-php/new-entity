<?php

namespace Test\Unit\Entity\Simple;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Test\Unit\Util\JsonSerializeFields;

/**
 * @ORM\Entity
 */
class PublicProps implements JsonSerializable
{
  use JsonSerializeFields;

  /** @ORM\Id @ORM\Column(type="integer") */
  public $id;
  /** @ORM\Column(type="string") */
  public $title;
}
