<?php

namespace Drupal\gearwrench_migrations\Plugin\migrate\process;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a get_asset_id plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_asset_id
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "get_asset_id"
 * )
 */
class GetAssetId extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return (string) $value->attributes()->AssetID;
  }

}
