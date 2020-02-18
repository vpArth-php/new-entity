<?php

namespace Test\Unit;

use Arth\Util\Doctrine\Creation\SimpleStrategy;
use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\Exception\InvalidArgument;
use Arth\Util\Doctrine\Identify\CompositeStrategy;
use Arth\Util\Doctrine\Identify\FieldSetStrategy;
use Arth\Util\Doctrine\Identify\InstanceOfStrategy;
use Arth\Util\Doctrine\Identify\PrimaryKeyStrategy;
use Arth\Util\Doctrine\IdentifyStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use StdClass;
use Test\Unit\Entity\Library\Author;
use Test\Unit\Entity\Library\Book;
use Test\Unit\Entity\Simple\GetSetProps;

class IdStrategyTest extends DbBase
{
  /** @var EntityInstantiator */
  private $svc;

  /** @var ManagerRegistry */
  private $manager;

  protected function setUp(): void
  {
    parent::setUp();
    $this->manager = $this->createMock(ManagerRegistry::class);
    $this->manager
        ->method('getManagerForClass')
        ->willReturn($this->em);

    $this->svc = new EntityInstantiator($this->manager);
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

  public function testSimpleStrategy(): void
  {
    $this->svc->setCreationStrategy(new SimpleStrategy($this->manager));
    for ($i = 0; $i < 2; $i++) {
      $created = $this->svc->get(Author::class, ['id' => null, 'title' => 'Mark Twain']);
      $this->em->persist($created);
    }
    $this->em->flush();

    $repo = new EntityRepository($this->em, new ClassMetadata(Author::class));
    $res  = $repo->findBy(['title' => 'Mark Twain']);
    static::assertCount(2, $res);
  }

  public function testInstanceOfStrategy(): void
  {
    $strategy = new InstanceOfStrategy([
        Author::class => new PrimaryKeyStrategy(),
        Book::class   => new FieldSetStrategy(['title']),
    ]);
    $id       = $strategy->getIdentifier($this->em->getClassMetadata(Author::class), ['id' => 3, 'title' => 'Three']);
    static::assertEquals(['id' => 3], $id);
    $id = $strategy->getIdentifier($this->em->getClassMetadata(Book::class), ['id' => 3, 'title' => 'Three']);
    static::assertEquals(['title' => 'Three'], $id);
    $id = $strategy->getIdentifier($this->em->getClassMetadata(GetSetProps::class), ['id' => 3, 'title' => 'Three']);
    static::assertNull($id);
  }
  public function testSvcInstanceOfStrategy(): void
  {
    $strategy = new InstanceOfStrategy([
        Author::class => new PrimaryKeyStrategy(),
        Book::class   => new FieldSetStrategy(['title']),
    ]);
    $this->svc->setIdentifyStrategy($strategy);

    $author1 = $this->createAuthor('A1');
    $author2 = $this->createAuthor('A2');
    /** @var Author $a */
    $a        = $this->svc->get(Author::class, ['id' => $author2->id, 'title' => $author1->title]);
    $a->title = 'A selected';
    static::assertEquals($author2, $a);
    /** @var Author $a */
    $a        = $this->svc->get(Author::class, [$author2->id]);
    $a->title = 'A selected';
    static::assertEquals($author2, $a);

    $book1 = $this->createBook($a, 'B1', 'First');
    $book2 = $this->createBook($a, 'B2', 'Second');
    $this->em->flush();
    /** @var Book $b */
    $b = $this->svc->get(Book::class, ['id' => $book2->id, 'title' => $book1->title]);
    static::assertEquals($book1, $b);
    $this->em->flush();

    $allBooks = $this->em->getRepository(Book::class)->findAll();
    static::assertCount(2, $allBooks);
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

    return $entity;
  }
  protected function createBook(Author $author, $title, $description): Book
  {
    $entity = new Book($author);

    $entity->title       = $title;
    $entity->description = $description;

    $this->em->persist($entity);
    $this->em->flush();

    return $entity;
  }
}
