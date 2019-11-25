<?php

namespace Test\Unit\Entity\Library;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @property-read int id
 * @property      string title
 * @property Book[]|ArrayCollection books
 */
class Author
{
  /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
  protected $id;
  /** @ORM\Column(type="string") */
  protected $title;
  /** @ORM\OneToMany(targetEntity="Book", mappedBy="author") */
  protected $books;

  public function __construct()
  {
    $this->setBooks(new ArrayCollection());
  }

  public function setBooks($books): void
  {
    $this->books = $books;
  }

  public function __set($name, $value) { $this->$name = $value; }
  public function __get($name) { return $this->$name; }
  public function __isset($name) { return isset($this->$name); }
}
