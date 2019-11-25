<?php

namespace Test\Unit;

use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\TimeMachine;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Test\Unit\Entity as E;

class RecursionTest extends DbBase
{
  private const NOW = '2019-05-15 15:00:00';
  /** @var EntityInstantiator */
  private $svc;

  protected function setUp(): void
  {
    parent::setUp();
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
        ->willReturn($this->em);

    $this->svc = new EntityInstantiator($manager);

    $tm = TimeMachine::getInstance();
    $tm->setNow(new DateTimeImmutable(self::NOW));
    $tm->setFrozenMode();
  }

  public function testGetByPK(): void
  {
    $author        = new E\Library\Author();
    $author->title = 'Пушкин А.С.';
    $this->em->persist($author);
    $this->em->flush();
    $this->em->clear();

    /** @var E\Library\Author $e */
    $e = $this->svc->get(E\Library\Author::class, [
        'id'    => $author->id,
        'title' => 'Пушкин',
    ]);

    static::assertNotEmpty($e);
    static::assertEquals($author->id, $e->id);
    static::assertEquals('Пушкин', $e->title);
    static::assertEquals($this->em->find(E\Library\Author::class, $author->id), $e);
  }

  public function testRecursion(): void
  {
    $author        = new E\Library\Author();
    $author->title = 'Пушкин А.С.';
    $this->em->persist($author);
    $this->em->flush();
    $this->em->clear();

    /** @var E\Library\Book $book */
    $book = $this->svc->get(E\Library\Book::class, [
        'title'  => 'Руслан и Людмила',
        'author' => [
            'id'    => $author->id,
            'title' => 'Пушкин',
        ],
    ]);

    static::assertNotEmpty($book);
    static::assertEquals('Руслан и Людмила', $book->title);
    static::assertEquals('Пушкин', $book->author->title);
    static::assertCount(1, $book->author->books);
  }

  public function testSetCollection(): void
  {
    /** @var E\Library\Book $book */
    $book = $this->svc->get(E\Library\Book::class, [
        'author' => ['title' => 'А.С. Пушкин'],
        'title'  => 'О золотой рыбке',
    ]);
    $this->em->persist($book);
    $this->em->flush();
    $this->em->clear();

    /** @var E\Library\Author $entity */
    $entity = $this->svc->get(E\Library\Author::class, [
        'id'    => $book->author->id,
        'title' => 'Пушкин',
        'books' => [
            ['title' => 'Евгений Онегин'],
            ['title' => 'Руслан и Людмила'],
            ['title' => 'Сказка о золотой рыбке', 'id' => $book->id],
        ],
    ]);
    $this->em->persist($entity);
    $this->em->flush();
    $this->em->clear();
    $author = $this->em->find(E\Library\Author::class, $entity->id);

    static::assertEquals('Пушкин', $author->title);
    static::assertEquals('Сказка о золотой рыбке', $this->em->find(E\Library\Book::class, $book->id)->title);
    static::assertCount(3, $entity->books);
  }
}
