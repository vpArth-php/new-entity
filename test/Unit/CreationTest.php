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

    $isbn = $this->svc->get(E\Library\ISBN::class, [
        'title' => '978-5-17-103766-6',
    ]);
    $this->em->persist($isbn);
    $this->em->flush();

    /** @var E\Library\Book $b1 */
    // relation by PK
    $b1 = $this->svc->get(E\Library\Book::class, [
        'title'  => 'Евгений Онегин',
        'author' => $author1->id,
        'isbn'   => $isbn->id,
    ]);
    $this->em->persist($b1);
    $this->em->flush();
    static::assertNotEmpty($b1);
    static::assertEquals($author1->id, $b1->author->id);
    static::assertEquals(self::NOW, $b1->createdAt->format('Y-m-d H:i:s'));

    // relation by object
    $b2 = $this->svc->get(E\Library\Book::class, [
        'id'              => $b1->id,
        'title'           => 'Евгений Онегин',
        'author'          => $author1,
        'isbn'            => $isbn,
        'descriptionText' => 'Роман в стихах',
        'createdAt'       => self::NOW,
        'writtenAt'       => '1830-09-25',
    ]);
    static::assertEquals($b1, $b2);
    $this->em->persist($b2);
    $this->em->flush();
    $i2 = $this->svc->get(E\Library\ISBN::class, [
        'id'   => $isbn->id,
        'book' => ['id' => $b2->id],
    ]);
    static::assertEquals($isbn, $i2);

    static::assertNotEmpty($b2);
    static::assertEquals($author1->id, $b2->author->id);
    static::assertEquals('РОМАН В СТИХАХ', $b2->description);
    static::assertEquals(self::NOW, $b2->createdAt->format('Y-m-d H:i:s'));
    static::assertEquals('1830-09-25 00:00:00', $b2->writtenAt->format('Y-m-d H:i:s'));
  }

  public function data(): ?Generator
  {
    yield [E\Simple\PublicProps::class, ['title' => 'First'], ['title' => 'First']];
    yield [E\Simple\MagicProps::class, ['title' => 'First'], ['title' => 'First']];
    yield [E\Simple\GetSetProps::class, ['title' => 'First'], ['title' => 'First']];
  }
}
