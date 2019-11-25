<?php

namespace Test\Unit;

use Arth\Util\EntityInstantiator;
use Arth\Util\Exception\NotFound;
use Arth\Util\TimeMachine;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
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
