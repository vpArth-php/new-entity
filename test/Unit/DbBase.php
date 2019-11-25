<?php

namespace Test\Unit;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;

abstract class DbBase extends TestCase
{
  /** @var EntityManager */
  protected $em;

  protected function setUp(): void
  {
    $em = $this->createEm();

    $this->em = $em;

    $tool = new SchemaTool($this->em);
    $tool->dropDatabase();
    $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
  }

  /**
   * @return EntityManager
   * @throws ORMException
   */
  private function createEm(): EntityManager
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
        'path'   => '/tmp/test1.db',
        // 'path'   => ':memory:',
    ];
    return EntityManager::create($conn, $config);
  }
}
