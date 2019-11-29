<?php

namespace Arth\Util\Doctrine\Helper;

interface EntityMapInterface
{
  public function set(string $class, $id, $entity): void;
  public function get(string $class, $id);
  public function clear(): void;
}
