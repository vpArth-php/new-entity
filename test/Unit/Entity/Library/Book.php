<?php

namespace Test\Unit\Entity\Library;

use Arth\Util\TimeMachine;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Test\Unit\Util\JsonSerializeFields;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @property-read number id
 * @property      string title
 * @property      string description
 * @property      Author author
 * @property      DateTimeImmutable createdAt
 * @property      DateTimeImmutable writtenAt
 */
class Book implements JsonSerializable
{
  use JsonSerializeFields;
  protected static $fields = ['id', 'title', 'description', 'createdAt', 'writtenAt', 'author'];

  /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
  protected $id;
  /** @ORM\Column(type="string") */
  protected $title;
  /** @ORM\Column(type="string", nullable=true) */
  protected $description;

  /** @ORM\Column(type="datetime_immutable") */
  protected $createdAt;
  /** @ORM\Column(type="date", nullable=true) */
  protected $writtenAt;

  /** @ORM\ManyToOne(targetEntity="Author", inversedBy="books", cascade={"persist"}) */
  protected $author;

  public function __construct(Author $author) {
    $this->setAuthor($author);
  }

  public function setAuthor(Author $author): void
  {
    $this->author = $author;
    if (empty($author->books)) {
      $author->setBooks(new ArrayCollection());
    }
    $author->books->add($this);
  }

  /** @noinspection PhpUnused */
  public function setDescriptionText($text): void
  {
    $this->description = mb_strtoupper($text);
  }

  public function __set($name, $value) { $this->$name = $value; }
  public function __get($name) { return $this->$name; }
  public function __isset($name) { return isset($this->$name); }

  /** @ORM\PrePersist() */
  public function prePersist(): void
  {
    if (empty($this->createdAt)) {
      $this->createdAt = TimeMachine::getInstance()->getNow();
    }
  }
}
