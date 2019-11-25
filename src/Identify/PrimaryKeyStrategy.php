<?php

namespace Arth\Util\Identify;

use Arth\Util\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class PrimaryKeyStrategy implements IdentifyStrategy
{

  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    $idFields = $meta->getIdentifierFieldNames();

    $result = [];
    foreach ($idFields as $i => $idField) {
      if (array_key_exists($i, $identifier)) {
        $identifier[$idField] = $identifier[$i];
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
