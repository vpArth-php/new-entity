<?php

namespace Test\Unit\Util;

use Doctrine\Common\Collections\Collection;
use JsonSerializable;

trait JsonSerializeFields
{
  public function jsonSerialize()
  {
    return array_reduce(static::$fields ?? array_keys(get_object_vars($this)), function ($result, $field) {
      $result[$field] = static::serialize($this->$field);
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
    if ($val instanceof JsonSerializable) {
      $val = $val->jsonSerialize();
    }
    return $val;
  }
}
