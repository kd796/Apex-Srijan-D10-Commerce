<?php

namespace Drupal\atg_translation;

use Drupal;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Language\LanguageInterface;

class ConfigOverrides implements ConfigFactoryOverrideInterface {

    protected $configFactory;

    protected $frontPageSettings;

    public function __construct(ConfigFactoryInterface $config_factory) {
        $this->configFactory     = $config_factory;
        $this->frontPageSettings = $this->configFactory->get('atg_translation.settings');
    }

    /**
     * Returns config overrides.
     *
     * @param array $names
     *   A list of configuration names that are being loaded.
     *
     * @return array
     *   An array keyed by configuration name of override data. Override data
     *   contains a nested array structure of overrides.
     */
    public function loadOverrides($names) {
        $language      = Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_URL);
        $overrides     = [];
        $valueKey      = sprintf('front_page.%s', $language->getId());
        $overrideValue = $this->frontPageSettings->getOriginal($valueKey);
        if (in_array('system.site', $names) && $overrideValue) {
            $overrides['system.site'] = [
                'page' => [
                    'front' => $overrideValue,
                ],
            ];
        }

        return $overrides;
    }

    /**
     * The string to append to the configuration static cache name.
     *
     * @return string
     *   A string to append to the configuration static cache name.
     */
    public function getCacheSuffix() {
        return 'atg_translation_front_page';
    }

    /**
     * Creates a configuration object for use during install and synchronization.
     *
     * If the overrider stores its overrides in configuration collections then
     * it can have its own implementation of
     * \Drupal\Core\Config\StorableConfigBase. Configuration overriders can link
     * themselves to a configuration collection by listening to the
     * \Drupal\Core\Config\ConfigEvents::COLLECTION_INFO event and adding the
     * collections they are responsible for. Doing this will allow installation
     * and synchronization to use the overrider's implementation of
     * StorableConfigBase.
     *
     * @see \Drupal\Core\Config\ConfigCollectionInfo
     * @see \Drupal\Core\Config\ConfigImporter::importConfig()
     * @see \Drupal\Core\Config\ConfigInstaller::createConfiguration()
     *
     * @param string $name
     *   The configuration object name.
     * @param string $collection
     *   The configuration collection.
     *
     * @return \Drupal\Core\Config\StorableConfigBase
     *   The configuration object for the provided name and collection.
     */
    public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
        return NULL;
    }

    /**
     * Gets the cacheability metadata associated with the config factory override.
     *
     * @param string $name
     *   The name of the configuration override to get metadata for.
     *
     * @return \Drupal\Core\Cache\CacheableMetadata
     *   A cacheable metadata object.
     */
    public function getCacheableMetadata($name) {
        $metadata = new CacheableMetadata();
        $metadata->addCacheableDependency($this->frontPageSettings);
        $metadata->addCacheContexts(['url']);

        return $metadata;
    }
}
