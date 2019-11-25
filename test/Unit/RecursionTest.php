<?php

namespace Test\Unit;

use Arth\Util\EntityInstantiator;
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

}
