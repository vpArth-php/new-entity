<?php

namespace Test\Unit;

use Arth\Util\Doctrine\EntityInstantiator;
use Arth\Util\Doctrine\Exception\NotFound;
use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Test\Unit\Entity as E;

class ErrorTest extends TestCase
{
  /** @var EntityInstantiator */
  private $svc;

  protected function setUp(): void
  {
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
      ->willReturn(null);

    $this->svc = new EntityInstantiator($manager);
  }

  public function testGetManager(): void
  {
    $this->expectException(NotFound::class);
    $this->svc->getManager(E\Simple\PublicProps::class);
  }
}
