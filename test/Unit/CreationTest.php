<?php

namespace Test\Unit;

use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\TimeMachine;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Generator;
use JsonSerializable;
use Test\Unit\Entity as E;

class CreationTest extends DbBase
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

  /** @dataProvider data */
  public function testCreation($className, $data, $expected): void
  {
    /** @var JsonSerializable $entity */
    $entity = $this->svc->get($className, $data);

    $entityData = $entity->jsonSerialize();
    foreach ($expected as $field => $expectedValue) {
      static::assertEquals($expectedValue, $entityData[$field]);
    }
  }
  /** @dataProvider data */
  public function testLateDataSet($className, $data, $expected): void
  {
    /** @var JsonSerializable $entity */
    $entity = $this->svc->get($className, []);
    $this->svc->update($entity, $data);

    $entityData = $entity->jsonSerialize();
    foreach ($expected as $field => $expectedValue) {
      static::assertEquals($expectedValue, $entityData[$field]);
    }
  }

  public function testAssociations(): void
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
        'title'  => 'Евгений Онегин',
        'author' => $author1->id,
    ]);
    $this->em->persist($book);
    $this->em->flush();
    static::assertNotEmpty($book);
    static::assertEquals($author1->id, $book->author->id);
    static::assertEquals(self::NOW, $book->createdAt->format('Y-m-d H:i:s'));

    // relation by object
    $book = $this->svc->get(E\Library\Book::class, [
        'title'           => 'Евгений Онегин',
        'author'          => $author1,
        'descriptionText' => 'Роман в стихах',
        'createdAt'       => self::NOW,
        'writtenAt'       => '1830-09-25',
    ]);
    $this->em->persist($book);
    $this->em->flush();

    static::assertNotEmpty($book);
    static::assertEquals($author1->id, $book->author->id);
    static::assertEquals('РОМАН В СТИХАХ', $book->description);
    static::assertEquals(self::NOW, $book->createdAt->format('Y-m-d H:i:s'));
    static::assertEquals('1830-09-25 00:00:00', $book->writtenAt->format('Y-m-d H:i:s'));
  }

  public function data(): ?Generator
  {
    yield [E\Simple\PublicProps::class, ['title' => 'First'], ['title' => 'First']];
    yield [E\Simple\MagicProps::class, ['title' => 'First'], ['title' => 'First']];
    yield [E\Simple\GetSetProps::class, ['title' => 'First'], ['title' => 'First']];
  }
}
