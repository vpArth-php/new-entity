<?php

namespace Test\Unit;

use Arth\Util\EntityInstantiator;
use Arth\Util\TimeMachine;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Test\Unit\Entity as E;

class CreationTest extends TestCase
{
  private const NOW = '2019-05-15 15:00:00';
  /** @var EntityInstantiator */
  private $svc;
  /** @var EntityManager */
  private $em;

  protected function setUp(): void
  {
    $em      = $this->getEm();
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
      ->willReturn($em);

    $this->svc = new EntityInstantiator($manager);
    $this->em = $em;

    $tool = new SchemaTool($this->em);
    $tool->dropDatabase();
    $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

    $tm = TimeMachine::getInstance();
    $tm->setNow(new DateTimeImmutable(self::NOW));
    $tm->setFrozenMode();
  }

  public function testPublicProps(): void
  {
    /** @var E\Simple\PublicProps $entity */
    $entity = $this->svc->get(E\Simple\PublicProps::class, [
        'title' => 'First',
    ]);

    static::assertNotEmpty($entity);
    static::assertEquals('First', $entity->title);
  }

  public function testMagicProps(): void
  {
    /** @var E\Simple\MagicProps $entity */
    $entity = $this->svc->get(E\Simple\MagicProps::class, [
        'title' => 'First',
    ]);

    static::assertNotEmpty($entity);
    static::assertEquals('First', $entity->title);

    $this->svc->setDataForEntity($entity, [
        'title' => 'Entity',
    ]);
    static::assertEquals('Entity', $entity->title);
  }

  public function testGetSetProps(): void
  {
    /** @var E\Simple\GetSetProps $entity */
    $entity = $this->svc->get(E\Simple\GetSetProps::class, [
        'title' => 'First',
    ]);

    static::assertNotEmpty($entity);
    static::assertEquals('First', $entity->getTitle());
  }

  public function testLibrary(): void
  {
    /** @var E\Library\Author $author1 */
    $author1 = $this->svc->get(E\Library\Author::class, [
        'title' => 'Пушкин А.С.',
    ]);
    $this->em->persist($author1);
    $this->em->flush();
    static::assertNotEmpty($author1);
    static::assertEquals('Пушкин А.С.', $author1->title);

    /** @var E\Library\Book $book */
    // relation by PK
    $book = $this->svc->get(E\Library\Book::class, [
       'title' => 'Евгений Онегин',
       'author' => $author1->id,
    ]);
    $this->em->persist($book);
    $this->em->flush();
    static::assertNotEmpty($book);
    static::assertEquals($author1->id, $book->author->id);
    static::assertEquals(self::NOW, $book->createdAt->format('Y-m-d H:i:s'));

    // relation by object
    $book = $this->svc->get(E\Library\Book::class, [
       'title' => 'Евгений Онегин',
       'author' => $author1,
       'descriptionText' => 'Роман в стихах',
       'createdAt' => self::NOW,
       'writtenAt' => '1830-09-25',
    ]);
    $this->em->persist($book);
    $this->em->flush();

    static::assertNotEmpty($book);
    static::assertEquals($author1->id, $book->author->id);
    static::assertEquals('РОМАН В СТИХАХ', $book->description);
    static::assertEquals(self::NOW, $book->createdAt->format('Y-m-d H:i:s'));
    static::assertEquals('1830-09-25 00:00:00', $book->writtenAt->format('Y-m-d H:i:s'));
  }

  /**
   * @return EntityManager
   * @throws ORMException
   */
  private function getEm(): EntityManager
  {
    $config = Setup::createAnnotationMetadataConfiguration(
        array(__DIR__ . '/Entity'),
        true, // Metadata use cache if false here
        null,
        null,
        false
    );
    $conn   = [
        'driver' => 'pdo_sqlite',
        'path'   => ':memory:',
    ];
    return EntityManager::create($conn, $config);
  }
}
