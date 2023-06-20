<?php

namespace Drupal\apex_tools_custom_quotation;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the quotation entity type.
 */
class QuotationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view quotation');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit quotation', 'administer quotation'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete quotation', 'administer quotation'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create quotation', 'administer quotation'], 'OR');
  }

}
