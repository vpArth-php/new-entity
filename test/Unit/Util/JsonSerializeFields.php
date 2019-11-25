<?php

namespace Test\Unit\Util;

trait JsonSerializeFields
{
  public function jsonSerialize()
  {
    return array_reduce(static::$fields ?? array_keys(get_object_vars($this)), function ($result, $field) {
      $result[$field] = $this->$field;

      return $result;
    }, []);
  }
}
