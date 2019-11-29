<?php

namespace Arth\Util\Doctrine\Helper;

class EntityMap implements EntityMapInterface
{
  private $map = [];
  public function set(string $class, $id, $entity): void
  {
    $key = static::getKey($id);

    $this->map[$class][$key] = $entity;
  }
  public function get(string $class, $id)
  {
    $key = static::getKey($id);

    return $this->map[$class][$key] ?? null;
  }
  public function clear(): void
  {
    $this->map = [];
  }

  public static function getKey($id): string
  {
    return is_array($id) ? implode('|', array_keys($id)) . ':' . implode('|', $id) : (string)$id;
  }
}
