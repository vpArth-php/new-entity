<?php

namespace Test\Unit;

use Arth\Util\Doctrine\Creation\InstanceOfStrategy;
use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\StatefulCreationStrategy;
use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\Entity\Library\Author;
use Test\Unit\Entity\Library\Book;
use Test\Unit\Entity\Simple\GetSetProps;

class CreateStrategyTest extends DbBase
{
  /** @var EntityInstantiator */
  private $svc;
  /** @var ManagerRegistry|MockObject */
  private $manager;
  protected function setUp(): void
  {
    parent::setUp();
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
        ->willReturn($this->em);

    $this->svc     = new EntityInstantiator($manager);
    $this->manager = $manager;
  }

  public function testInstanceOfStrategy(): void
  {
    $as = $this->createMock(CreationStrategy::class);
    $bs = $this->createMock(StatefulCreationStrategy::class);
    $as->method('create')->willReturn('A!');
    $bs->method('create')->willReturn('B!');
    $strategy = new InstanceOfStrategy([
        Book::class   => $bs,
        Author::class => $as,
    ]);

    $a = $strategy->create($this->em->getClassMetadata(Author::class), []);
    $b = $strategy->create($this->em->getClassMetadata(Book::class), []);
    $n = $strategy->create($this->em->getClassMetadata(GetSetProps::class), []);

    static::assertEquals('A!', $a);
    static::assertEquals('B!', $b);
    static::assertNull($n);

    // $as->expects($this->once())->method('clearState');
    // $bs->expects($this->once())->method('clearState');
    // $strategy->clearState();
  }
}
