<?php

namespace Arth\Util\Doctrine\Identify;

use Arth\Util\Doctrine\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class PrimaryKeyStrategy implements IdentifyStrategy
{

  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    $idFields = $meta->getIdentifierFieldNames();

    return FieldSetStrategy::getIdFromFields($idFields, $identifier);
  }
}
