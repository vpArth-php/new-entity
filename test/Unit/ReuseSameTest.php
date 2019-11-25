<?php

namespace Test\Unit;

use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\Identify\FieldSetStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use Test\Unit\Entity\Library\Author;

class ReuseSameTest extends DbBase
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
  public function testSetCollection(): void
  {
    $this->svc->setIdentifyStrategy(new FieldSetStrategy(['title']));
    /** @var Author $entity */
    $entity = $this->svc->get(Author::class, [
        'title' => 'Пушкин',
        'books' => [
            ['title' => 'Евгений Онегин', 'description' => 'First'],
            ['title' => 'Евгений Онегин', 'description' => 'second'],
            ['title' => 'Руслан и Людмила'],
            ['title' => 'Сказка о золотой рыбке'],
        ],
    ]);
    $this->svc->clearState();
    static::assertCount(3, $entity->books);
    $this->em->persist($entity);
    $this->em->flush();
    $this->em->clear();

    /** @var Author $author */
    $author = $this->em->find(Author::class, $entity->id);
    static::assertEquals('Пушкин', $author->title);
    static::assertCount(3, $author->books);
  }
}
