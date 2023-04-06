<?php

// @codingStandardsIgnoreFile

/**
 * @file
 * Configuration file for multi-site support and directory aliasing feature.
 *
 * This file is required for multi-site support and also allows you to define a
 * set of aliases that map hostnames, ports, and pathnames to configuration
 * directories in the sites directory. These aliases are loaded prior to
 * scanning for directories, and they are exempt from the normal discovery
 * rules. See default.settings.php to view how Drupal discovers the
 * configuration directory when no alias is found.
 *
 * Aliases are useful on development servers, where the domain name may not be
 * the same as the domain of the live server. Since Drupal stores file paths in
 * the database (files, system table, etc.) this will ensure the paths are
 * correct when the site is deployed to a live server.
 *
 * To activate this feature, copy and rename it such that its path plus
 * filename is 'sites/sites.php'.
 *
 * Aliases are defined in an associative array named $sites. The array is
 * written in the format: '<port>.<domain>.<path>' => 'directory'. As an
 * example, to map https://www.drupal.org:8080/mysite/test to the configuration
 * directory sites/example.com, the array should be defined as:
 * @code
 * $sites = array(
 *   '8080.www.drupal.org.mysite.test' => 'example.com',
 * );
 * @endcode
 * The URL, https://www.drupal.org:8080/mysite/test/, could be a symbolic link
 * or an Apache Alias directive that points to the Drupal root containing
 * index.php. An alias could also be created for a subdomain. See the
 * @link https://www.drupal.org/documentation/install online Drupal installation guide @endlink
 * for more information on setting up domains, subdomains, and subdirectories.
 *
 * The following examples look for a site configuration in sites/example.com:
 * @code
 * URL: http://dev.drupal.org
 * $sites['dev.drupal.org'] = 'example.com';
 *
 * URL: http://localhost/example
 * $sites['localhost.example'] = 'example.com';
 *
 * URL: http://localhost:8080/example
 * $sites['8080.localhost.example'] = 'example.com';
 *
 * URL: https://www.drupal.org:8080/mysite/test/
 * $sites['8080.www.drupal.org.mysite.test'] = 'example.com';
 * @endcode
 *
 * @see default.settings.php
 * @see \Drupal\Core\DrupalKernel::getSitePath()
 * @see https://www.drupal.org/documentation/install/multi-site
 */

// Apex Tools.
$sites['apex-tools.docksal'] = 'apex-tools';
$sites['www.apex-tools.docksal'] = 'apex-tools';
$sites['apextoolgroupdev.prod.acquia-sites.com'] = 'apex-tools';
$sites['apextoolgroupstg.prod.acquia-sites.com'] = 'apex-tools';
$sites['apextoolgroup.prod.acquia-sites.com'] = 'apex-tools';

// gearwrench.com
$sites['gearwrench.docksal'] = 'gearwrench';
$sites['www.gearwrench.docksal'] = 'gearwrench';
$sites['gearwrench.docksal.site'] = 'gearwrench';
$sites['www.gearwrench.docksal.site'] = 'gearwrench';
$sites['gearwrench.com'] = 'gearwrench';
$sites['dev-www.gearwrench.com'] = 'gearwrench';
$sites['stg-www.gearwrench.com'] = 'gearwrench';
$sites['prod-www.gearwrench.com'] = 'gearwrench';
$sites['www.gearwrench.com'] = 'gearwrench';

// crescenttool.com
$sites['crescenttool.docksal'] = 'crescenttool';
$sites['www.crescenttool.docksal'] = 'crescenttool';
$sites['crescenttool.docksal.site'] = 'crescenttool';
$sites['www.crescenttool.docksal.site'] = 'crescenttool';
$sites['crescenttool.com'] = 'crescenttool';
$sites['dev-www.crescenttool.com'] = 'crescenttool';
$sites['stg-www.crescenttool.com'] = 'crescenttool';
$sites['prod-www.crescenttool.com'] = 'crescenttool';
$sites['www.crescenttool.com'] = 'crescenttool';

/**
 * Australia
 * -----------------
 */

// gearwrench.com.au
$sites['gearwrench.com.au.docksal'] = 'gearwrench_au';
$sites['www.gearwrench.com.au.docksal'] = 'gearwrench_au';
$sites['gearwrench.com.au.docksal.site'] = 'gearwrench_au';
$sites['www.gearwrench.com.au.docksal.site'] = 'gearwrench_au';
$sites['gearwrench.com.au'] = 'gearwrench_au';
$sites['dev-www.gearwrench.com.au'] = 'gearwrench_au';
$sites['stg-www.gearwrench.com.au'] = 'gearwrench_au';
$sites['prod-www.gearwrench.com.au'] = 'gearwrench_au';
$sites['www.gearwrench.com.au'] = 'gearwrench_au';

// crescenttool.com.au
$sites['crescenttool.com.au.docksal'] = 'crescenttool_au';
$sites['www.crescenttool.com.au.docksal'] = 'crescenttool_au';
$sites['crescenttool.com.au.docksal.site'] = 'crescenttool_au';
$sites['www.crescenttool.com.au.docksal.site'] = 'crescenttool_au';
$sites['crescenttool.com.au'] = 'crescenttool_au';
$sites['dev-www.crescenttool.com.au'] = 'crescenttool_au';
$sites['stg-www.crescenttool.com.au'] = 'crescenttool_au';
$sites['prod-www.crescenttool.com.au'] = 'crescenttool_au';
$sites['www.crescenttool.com.au'] = 'crescenttool_au';

/**
 * SATA Sites
 * -----------------
 */

// SATA Brazil
$sites['sataferramentas.com.br.docksal'] = 'sata_brazil';
$sites['www.sataferramentas.com.br.docksal'] = 'sata_brazil';
$sites['sataferramentas.com.br.docksal.site'] = 'sata_brazil';
$sites['www.sataferramentas.com.br.docksal.site'] = 'sata_brazil';
$sites['sataferramentas.com.br'] = 'sata_brazil';
$sites['www.sataferramentas.com.br'] = 'sata_brazil';
$sites['prod-www.sataferramentas.com.br'] = 'sata_brazil';
$sites['stg-www.sataferramentas.com.br'] = 'sata_brazil';
$sites['dev-www.sataferramentas.com.br'] = 'sata_brazil';

// SATA Colombia
$sites['sata.com.co.docksal'] = 'sata_colombia';
$sites['www.sata.com.co.docksal'] = 'sata_colombia';
$sites['sata.com.co.docksal.site'] = 'sata_colombia';
$sites['www.sata.com.co.docksal.site'] = 'sata_colombia';
$sites['sata.com.co'] = 'sata_colombia';
$sites['www.sata.com.co'] = 'sata_colombia';
$sites['prod-www.sata.com.co'] = 'sata_colombia';
$sites['stg-www.sata.com.co'] = 'sata_colombia';
$sites['dev-www.sata.com.co'] = 'sata_colombia';

// SATA EMEA
$sites['satatools.eu.docksal'] = 'sata_emea';
$sites['www.satatools.eu.docksal'] = 'sata_emea';
$sites['satatools.eu.docksal.site'] = 'sata_emea';
$sites['www.satatools.eu.docksal.site'] = 'sata_emea';
$sites['satatools.eu'] = 'sata_emea';
$sites['www.satatools.eu'] = 'sata_emea';
$sites['prod-www.satatools.eu'] = 'sata_emea';
$sites['stg-www.satatools.eu'] = 'sata_emea';
$sites['dev-www.satatools.eu'] = 'sata_emea';

// SATA US (North America)
$sites['satatools.us.docksal'] = 'sata_us';
$sites['www.satatools.us.docksal'] = 'sata_us';
$sites['satatools.us.docksal.site'] = 'sata_us';
$sites['www.satatools.us.docksal.site'] = 'sata_us';
$sites['satatools.us'] = 'sata_us';
$sites['www.satatools.us'] = 'sata_us';
$sites['prod-www.satatools.us'] = 'sata_us';
$sites['stg-www.satatools.us'] = 'sata_us';
$sites['dev-www.satatools.us'] = 'sata_us';

// New Sites for CLECOTOOLS
$sites['dev-www.clecotools.com'] = 'clecotools';
$sites['dev-www.clecotools.de'] = 'clecotools';
$sites['dev-www.clecotools.co.uk'] = 'clecotools';
$sites['qa-www.clecotools.com'] = 'clecotools';
$sites['qa-www.clecotools.de'] = 'clecotools';
$sites['qa-www.clecotools.co.uk'] = 'clecotools';
$sites['stg-www.clecotools.com'] = 'clecotools';
$sites['stg-www.clecotools.de'] = 'clecotools';
$sites['stg-www.clecotools.co.uk'] = 'clecotools';
$sites['prod-www.clecotools.com'] = 'clecotools';

// New Sites for CAMPBELL
$sites['dev-www.campbellchainandfittings.com'] = 'campbell';
$sites['qa-www.campbellchainandfittings.com'] = 'campbell';
$sites['stg-www.campbellchainandfittings.com'] = 'campbell';
$sites['prod-www.campbellchainandfittings.com'] = 'campbell';

// New Sites for APEXTOOLGROUP
$sites['dev-www.apextoolgroup.com'] = 'apextoolgroup';
$sites['qa-www.apextoolgroup.com'] = 'apextoolgroup';
$sites['stg-www.apextoolgroup.com'] = 'apextoolgroup';
$sites['prod-www.apextoolgroup.com'] = 'apextoolgroup';

// CLECOTOOLS
$sites['clecotools.ddev.site'] = 'clecotools';

// CAMPBELL
$sites['campbell.ddev.site'] = 'campbell';

// Local config with DDEV for all local sites
$sites['apex-tools-local.ddev.site'] = 'apex-tools';
$sites['crescenttool-local.ddev.site'] = 'crescenttool';
$sites['crescenttool-au-local.ddev.site'] = 'crescenttool_au';
$sites['gearwrench-local.ddev.site'] = 'gearwrench';
$sites['gearwrench-au-local.ddev.site'] = 'gearwrench_au';
$sites['sata-brazil-local.ddev.site'] = 'sata_brazil';
$sites['sata-colombia-local.ddev.site'] = 'sata_colombia';
$sites['sata-emea-local.ddev.site'] = 'sata_emea';
$sites['sata-us-local.ddev.site'] = 'sata_us';
