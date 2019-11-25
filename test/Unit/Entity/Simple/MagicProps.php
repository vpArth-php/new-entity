<?php

namespace Test\Unit\Entity\Simple;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Test\Unit\Util\JsonSerializeFields;

/**
 * @ORM\Entity
 * @property-read number id
 * @property      string title
 */
class MagicProps implements JsonSerializable
{
  use JsonSerializeFields;

  /** @ORM\Id @ORM\Column(type="integer") */
  protected $id;
  /** @ORM\Column(type="string") */
  protected $title;

  public function __set($name, $value) { $this->$name = $value; }
  public function __get($name) { return $this->$name; }
  public function __isset($name) { return isset($this->$name); }
}
