<?php

namespace Test\Unit;

use Arth\Util\EntityInstantiator;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;
use Test\Unit\Entity as E;

class CreationTest extends TestCase
{
  /** @var EntityInstantiator */
  private $svc;

  protected function setUp(): void
  {
    $em      = $this->getEm();
    $manager = $this->createMock(ManagerRegistry::class);
    $manager
        ->method('getManagerForClass')
      ->willReturn($em);

    $this->svc = new EntityInstantiator($manager);
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

  /**
   * @return EntityManager
   * @throws ORMException
   */
  private function getEm(): EntityManager
  {
    $config = Setup::createAnnotationMetadataConfiguration(
        array(__DIR__ . '/Entity'),
        false,
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
