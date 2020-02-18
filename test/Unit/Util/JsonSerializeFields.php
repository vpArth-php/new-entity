<?php

namespace Test\Unit\Util;

use Doctrine\Common\Collections\Collection;

trait JsonSerializeFields
{
  private $_serializedMap = [];
  public function jsonSerialize()
  {
    return array_reduce(static::$fields ?? array_keys(get_object_vars($this)), function ($result, $field) {
      $serialized = $this->_serializedMap[$field] ?? static::serialize($this->$field);

      $this->_serializedMap[$field] = $serialized;
      $result[$field]               = $serialized;
      return $result;
    }, []);
  }

  protected static function serialize($val)
  {
    if ($val instanceof Collection) {
      $val = $val->toArray();
    }
    if (is_array($val)) {
      return array_map(static function ($item) {
        return static::serialize($item);
      }, $val);
    }

    return $val;
  }
}
