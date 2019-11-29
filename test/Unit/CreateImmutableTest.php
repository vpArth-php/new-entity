<?php

namespace Test\Unit;

use Arth\Util\Doctrine\Creation\ImmutableStrategy;
use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\Identify\FieldSetStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\Entity\Library\Author;
use Test\Unit\Entity\Simple\Simple;

class CreateImmutableTest extends DbBase
{
  /** @var EntityInstantiator */
  protected $svc;
  /** @var ManagerRegistry|MockObject */
  protected $manager;
  public function testImmutableStrategy(): void
  {
    $a              = new Author();
    $a->title       = 'A1';
    $a->description = 'First';
    $this->em->persist($a);
    $this->em->flush();

    $a1 = $this->svc->get(Author::class, ['title' => 'A1', 'description' => 'First']);
    self::assertEquals($a, $a1, 'Unchanged entity should get the prev');
    $this->em->persist($a1);
    $this->em->flush();

    $this->svc->get(Author::class, ['title' => 'A1', 'description' => '2']);
    $this->svc->get(Author::class, ['title' => 'A1', 'description' => '3']);
    $a2 = $this->svc->get(Author::class, ['title' => 'A1', 'description' => 'Second']);
    self::assertNotEquals($a1, $a2, 'Changed entity should not be same');
    $this->em->persist($a2);
    $this->em->flush();
    $this->em->clear();

    $all = $this->em->getRepository(Author::class)->findAll();
    static::assertCount(1, $all);
    self::assertEquals('Second', $all[0]->description);
    self::assertEquals(2, $all[0]->id);
  }
  public function testImmutableStrategySimple(): void
  {
    $a1              = new Simple();
    $a1->title       = 'A1';
    $a1->description = 'first';
    $this->em->persist($a1);
    $this->em->flush();

    $e = $this->svc->get(Simple::class, ['title' => 'A1', 'description' => 'Second']);
    static::assertNotEquals($a1, $e);
    $this->em->persist($e);
    $this->em->flush();

    $all = $this->em->getRepository(Simple::class)->findAll();
    static::assertCount(1, $all);
    self::assertEquals('Second', $all[0]->description);
    self::assertEquals(2, $all[0]->id);
  }
  public function testImmutableStrategyJsonSerializable(): void
  {
    $a1 = $this->svc->get(Author::class, ['title' => 'A1', 'description' => 'First']);
    $a2 = $this->svc->get(Author::class, ['title' => 'A1', 'description' => 'Second']);
    static::assertNotEquals($a1, $a2);
  }
  protected function setUp(): void
  {
    parent::setUp();
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
        ->willReturn($this->em);

    $this->svc     = new EntityInstantiator($manager);
    $this->manager = $manager;
    $is            = new FieldSetStrategy(['title']);
    $this->svc->setIdentifyStrategy($is);
    $this->setupCreationStrategy();
  }
  protected function setupCreationStrategy(): void
  {
    $cs = new ImmutableStrategy($this->manager);
    $this->svc->setCreationStrategy($cs);
  }
}
