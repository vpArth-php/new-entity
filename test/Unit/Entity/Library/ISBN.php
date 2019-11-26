<?php

namespace Test\Unit\Entity\Library;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Test\Unit\Util\JsonSerializeFields;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @property-read number            id
 * @property      string            title
 * @property      string            description
 * @property      Author            author
 * @property      DateTimeImmutable createdAt
 * @property      DateTimeImmutable writtenAt
 */
class ISBN implements JsonSerializable
{
  use JsonSerializeFields;
  protected static $fields = ['id', 'title', 'book'];

  /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
  public $id;
  /** @ORM\Column(type="string") */
  public $title;
  /** @ORM\OneToOne(targetEntity="Book", mappedBy="isbn", cascade={"persist"}) */
  public $book;

  public function setBook(Book $book)
  {
    $this->book = $book;
    $book->isbn = $this;
  }
}
