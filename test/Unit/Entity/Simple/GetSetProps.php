<?php

namespace Test\Unit\Entity\Simple;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Test\Unit\Util\JsonSerializeFields;

/**
 * @ORM\Entity
 */
class GetSetProps implements JsonSerializable
{
  use JsonSerializeFields;

  /** @ORM\Id @ORM\Column(type="integer") */
  protected $id;
  /** @ORM\Column(type="string") */
  protected $title;

  public function getId(): int { return $this->id; }
  public function getTitle(): string { return $this->title; }

  public function setTitle(string $title): void { $this->title = $title; }
}
