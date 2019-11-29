<?php

namespace Test\Unit;

use Arth\Util\Doctrine\Creation\ImmutableStrategy;
use Arth\Util\Doctrine\Creation\StatefulDecorator;
use Arth\Util\Doctrine\StatefulCreationStrategy;
use Test\Unit\Entity\Library\Author;

class StatefulImmutableTest extends CreateImmutableTest
{
  /** @var StatefulCreationStrategy */
  protected $cs;
  public function testClear(): void
  {
    $map = $this->cs->getStateMap();
    $map->set(Author::class, ['title' => 'Test 15'], 15);
    static::assertEquals(15, $map->get(Author::class, ['title' => 'Test 15']));
    $this->svc->clearState();
    static::assertNull($map->get(Author::class, ['title' => 'Test 15']));
  }
  protected function setupCreationStrategy(): void
  {
    $cs       = new ImmutableStrategy($this->manager);
    $this->cs = new StatefulDecorator($this->manager, $cs);
    $this->svc->setCreationStrategy($this->cs);
  }
}
