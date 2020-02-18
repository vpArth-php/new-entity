<?php

namespace Arth\Util\Doctrine\Creation;

use Arth\Util\Doctrine\CreationStrategy;
use Arth\Util\Doctrine\Exception\NotFound;
use Arth\Util\Doctrine\GetManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

class FromDbStrategy implements CreationStrategy
{
  use GetManager;
  public function __construct(ManagerRegistry $registry)
  {
    $this->registry = $registry;
  }
  public function create(ClassMetadata $meta, ?array $id, $entity = null, $data = null)
  {
    $className = $meta->getName();
    if ($id) {
      $entity = $this->getFromDbById($className, $id) ??
          $this->getInsertedById($className, $id) ??
          $entity;
    }

    return $entity;
  }

  protected function getFromDbById($className, array $id)
  {
    /** @var EntityManagerInterface $em */
    $em        = $this->getManager($className);
    $uow       = $em->getUnitOfWork();
    $deletions = $uow->getScheduledEntityDeletions();

    $entity = $em->getRepository($className)->findOneBy($id);
    if ($entity) {
      // Check we do not schedule to remove it
      // uow.isScheduledForDelete is not work, because it can be other instance
      $deletions = array_filter($deletions, static function ($e) use ($className) {
        return $e instanceof $className;
      });
      $deletions = array_filter($deletions, static function ($e) use ($id) {
        $key = array_map(static function ($k) use ($e) {
          return $e->$k ?? null;
        }, array_keys($id));

        return $key === array_values($id);
      });
      if ($deletions) {
        $entity = null;
      }
    }

    return $entity;
  }

  /**
   * Find scheduled for insert but not inserted yet entity
   *
   * @throws NotFound
   */
  protected function getInsertedById($className, array $id)
  {
    /** @var EntityManagerInterface $em */
    $em         = $this->getManager($className);
    $uow        = $em->getUnitOfWork();
    $insertions = $uow->getScheduledEntityInsertions();
    foreach ($insertions as $entity) {
      if (!$entity instanceof $className) {
        continue;
      }
      foreach ($id as $field => $value) {
        if (null === $value || $entity->$field !== $value) {
          continue 2;
        }
      }

      return $entity;
    }

    return null;
  }
}
