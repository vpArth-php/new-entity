<?php

namespace Arth\Util\Doctrine\Identify;

use Arth\Util\Doctrine\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class FieldSetStrategy implements IdentifyStrategy
{
  /** @var array */
  protected $fields;

  public function __construct(array $fields)
  {
    $this->fields = $fields;
  }
  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    return static::getIdFromFields($this->fields, $identifier);
  }
  public static function getIdFromFields(array $idFields, array $identifier): ?array
  {
    $result = [];
    foreach ($idFields as $i => $idField) {
      if (array_key_exists($i, $identifier)) {
        $identifier[$idField] = $identifier[$i];
        unset($identifier[$i]);
      }
      if (!array_key_exists($idField, $identifier)) {
        return null;
      }
      $result[$idField] = $identifier[$idField];
      unset($identifier[$idField]);
    }
    return $result;
  }
}
