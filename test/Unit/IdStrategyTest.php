<?php

namespace Test\Unit;

use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\Exception\InvalidArgument;
use Arth\Util\Doctrine\Identify\CompositeStrategy;
use Arth\Util\Doctrine\Identify\FieldSetStrategy;
use Arth\Util\Doctrine\Identify\PrimaryKeyStrategy;
use Arth\Util\Doctrine\IdentifyStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use StdClass;
use Test\Unit\Entity\Library\Author;

class IdStrategyTest extends DbBase
{
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
  }

  public function testDefaultStrategy(): void
  {
    $entity  = $this->createAuthor('Shakespear');
    $created = $this->svc->get(Author::class, ['id' => $entity->id]);

    static::assertNotEmpty($created);
    static::assertEquals($entity->id, $created->id);
  }

  public function testPKStrategy(): void
  {
    $this->svc->setIdentifyStrategy(new PrimaryKeyStrategy());
    $entity  = $this->createAuthor('Shakespear');
    $created = $this->svc->get(Author::class, ['id' => $entity->id]);

    static::assertNotEmpty($created);
    static::assertEquals($entity->id, $created->id);
  }

  public function testFieldsStrategy(): void
  {
    $entity  = $this->createAuthor('Shakespear');
    $created = $this->svc->get(Author::class, ['title' => 'Shakespear']);
    static::assertNull($created->id); // Does not work with default strategy

    $this->svc->setIdentifyStrategy(new FieldSetStrategy(['title']));
    $created = $this->svc->get(Author::class, ['title' => 'Shakespear']);

    static::assertNotEmpty($created);
    static::assertEquals($entity->id, $created->id);
  }

  public function testCompositeStrategy(): void
  {
    $entity = $this->createAuthor('Shakespear');

    $strategy = new CompositeStrategy([
        new PrimaryKeyStrategy(),
        new FieldSetStrategy(['title']),
    ]);
    $this->svc->setIdentifyStrategy($strategy);

    $created = $this->svc->get(Author::class, ['title' => 'Shakespear']);

    static::assertNotEmpty($created);
    static::assertEquals($entity->id, $created->id);
  }

  public function testInvalidCompositeArgument(): void
  {
    $this->expectException(InvalidArgument::class);

    $strategy = new CompositeStrategy([
        new PrimaryKeyStrategy(),
        new StdClass(),
    ]);
    static::assertInstanceOf(IdentifyStrategy::class, $strategy);
  }

  protected function createAuthor($title): Author
  {
    $entity        = new Author();
    $entity->title = $title;

    $this->em->persist($entity);
    $this->em->flush();
    $this->em->clear();

    return $entity;
  }
}
