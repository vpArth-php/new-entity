<?php

namespace Arth\Util\Identify;

use Arth\Util\IdentifyStrategy;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class PrimaryKeyStrategy implements IdentifyStrategy
{

  public function getIdentifier(ClassMetadata $meta, array $identifier): ?array
  {
    $idFields = $meta->getIdentifierFieldNames();

    return FieldSetStrategy::getIdFromFields($idFields, $identifier);
  }
}
