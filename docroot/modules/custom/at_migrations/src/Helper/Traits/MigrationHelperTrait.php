<?php

namespace Drupal\at_migrations\Helper\Traits;

/**
 * Trait for migration.
 */
trait MigrationHelperTrait {

  /**
   * Get hash data.
   *
   * @param mixed $data
   *   Data to be processed.
   *
   * @return string
   *   Return hashed data.
   */
  public function getHashKey($data) {
    $value = (!is_array($data)) ? (array) $data : $data;
    return hash('sha256', serialize(array_map('strval', $value)));
  }

  /**
   * Process multiple paragraph.
   *
   * @param int $tid
   *   Test to be processed.
   * @param string $source_id
   *   Sourceid of the migration.
   * @param string $migration_id
   *   Name of the migration of source taxonomy.
   */
  public function mapAddedTaxonomyTerm($tid, $source_id, $migration_id) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $this->connection->insert($table)
      ->fields(
        [
          'sourceid1' => $source_id,
          'destid1' => $tid,
          'source_ids_hash' => $this->getHashKey($source_id),
        ]
      )->execute();
  }

  /**
   * Get migrated taxonomy tid.
   *
   * @param string $source_id1
   *   Test to be processed.
   * @param string $migration_id
   *   Sourceid of the migration.
   *
   * @return int
   *   Returns migrated tid.
   */
  public function getMigratedTaxonomyTid($source_id1, $migration_id) {
    $tid = NULL;
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->addField('t', 'destid1');
    $query->condition('t.sourceid1', $source_id1, '=');
    $tid = $query->execute()->fetchField();
    return $tid;
  }

  /**
   * Get migrated taxonomy status.
   *
   * @param string $source_id1
   *   Test to be processed.
   * @param string $migration_id
   *   Sourceid of the migration.
   *
   * @return int
   *   Returns migrated taxonomy migration status.
   */
  public function getMigratedTaxonomyTidStatus($source_id1, $migration_id) {
    $status = 0;
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->addField('t', 'source_row_status');
    $query->condition('t.sourceid1', $source_id1, '=');
    $status = $query->execute()->fetchField();
    return $status;
  }

  /**
   * Update hash information.
   *
   * @param string $hash_key
   *   Hash data of the image.
   * @param string $sourceid1
   *   Source id.
   * @param int $destid1
   *   Destination id.
   * @param string $migration_id
   *   Migraion instance.
   * @param string $source_row_status
   *   Migraion source row status.
   */
  public function updateMigrationRecord($hash_key, $sourceid1, $destid1, $migration_id, $source_row_status = 0) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    if (empty($table)) {
      return;
    }
    $this->connection->merge($table)
      ->key('source_ids_hash', $hash_key)
      ->fields([
        'source_ids_hash' => $hash_key,
        'sourceid1' => $sourceid1,
        'destid1' => $destid1,
        'source_row_status' => $source_row_status,
      ])
      ->execute();
  }

  /**
   * List of Attribute Mapped with key.
   *
   * @return mixed
   *   Returns attribute list.
   */
  public function getAttributeKeyList() {
    $list = [
      "ATT666316" => "female_thread",
      "ATT669761" => "magnetism",
      "ATT728149" => "description",
      "ATT339" => "point_size",
      "ATT666320" => "recess",
      "ATT17319" => "nominal_size",
      "ATT835" => "drive_size",
      "ATT666310" => "mortorq_size",
      "ATT533" => "screw_size",
      "ATT26835" => "drive",
      "ATT667001" => "tap_size",
      "ATT22562" => "drill_size",
      "ATT666315" => "male_thread",
      "ATT728154" => "description_0",
      "ATT666986" => "drive_size_0",
      "ATT666325" => "type_of_lock",
      "ATT666973" => "drive_end_sex",
      "ATT669755" => "socket_type_length",
      "ATT666982" => "fastener_end_type",
      "ATT666983" => "fastener_end_sex",
      "ATT666984" => "assembly_features",
      "ATT675357" => "hub_type",
      "ATT575" => "used_on",
      "ATT27860" => "style",
      "ATT675562" => "cover",
      "ATT675403" => "front_bore_id",
      "ATT675404" => "rear_bore_id",
      "ATT675266" => "outside_diameter_a_in",
      "ATT675243" => "outside_diameter_a_mm",
      "ATT675368" => "exposed_hub_length_in",
      "ATT675364" => "exposed_hub_length_mm",
      "ATT675369" => "max_cover_diameter_in",
      "ATT675365" => "max_cover_diameter_mm",
      "ATT675267" => "overall_length_b_in",
      "ATT675244" => "overall_length_b_mm",
      "ATT675269" => "bore_depth_c_in",
      "ATT675246" => "bore_depth_c_mm",
      "ATT675270" => "bore_diameter_g_in",
      "ATT675247" => "bore_diameter_g_mm",
      "ATT675366" => "keyaway_width_in",
      "ATT675361" => "keyaway_width_mm",
      "ATT675367" => "keyaway_depth_in",
      "ATT675362" => "keyaway_depth_mm",
      "ATT728095" => "hole_location_in",
      "ATT728098" => "hole_location_mm",
      "ATT661955" => "weight_lbs",
      "ATT661950" => "weight_kg",
      "ATT675273" => "torsional_play_test_torque_lbs",
      "ATT675264" => "torsional_play_test_torque_nm",
      "ATT675251" => "maximum_angle",
      "ATT675274" => "min_ultimate_static_torsional_strength_lbs",
      "ATT675261" => "min_ultimate_static_torsional_strength_nm",
      "ATT675275" => "min_ultimate_static_torsional_strength_apex_avg_lbs",
      "ATT675262" => "min_ultimate_static_torsional_strength_apex_avg_nm",
      "ATT675371" => "ultimate_axial_strength_lbs",
      "ATT675370" => "ultimate_axial_strength_nm",
      "ATT675375" => "max_momentary_stall_torque_lbs",
      "ATT675372" => "max_momentary_stall_torque_nm",
      "ATT675376" => "max_peak_torque_lbs",
      "ATT675374" => "max_peak_torque_nm",
      "ATT675277" => "axial_tension_compression_lbs",
      "ATT675265" => "axial_tension_compression_nm",
      "ATT675252" => "endurance_torque_test_angle",
      "ATT675276" => "endurance_torque_test_lbs",
      "ATT675263" => "endurance_torque_test_lbs_nm",
    ];
    return $list;
  }

  /**
   * List of valid atrribite ID. Will be used for dynamic key generation.
   *
   * @return mixed
   *   Returns attribute ids.
   */
  public function getAttributeList() {
    return [
      "ATT131" => "Blade Thickness",
      "ATT666982" => "Fastener End Type",
      "ATT666981" => "Fastener End Size",
      "ATT728095" => "Hole Location (in)",
      "ATT675276" => "Endurance Torque Test (in lbs)",
      "ATT696024" => "Weight (oz)",
      "ATT675249" => "Maximum Cover Diameter (J) mm (+/- .381)",
      "asset.uploaded" => "asset.uploaded",
      "asset.extension" => "asset.extension",
      "asset.mime-type" => "asset.mime-type",
      "asset.compression" => "asset.compression",
      "asset.class" => "asset.class",
      "asset.pixel-width" => "asset.pixel-width",
      "asset.colorspace" => "asset.colorspace",
      "asset.profile" => "asset.profile",
      "asset.ydpi" => "asset.ydpi",
      "asset.format" => "asset.format",
      "asset.pixel-height" => "asset.pixel-height",
      "asset.width" => "asset.width",
      "asset.height" => "asset.height",
      "asset.depth" => "asset.depth",
      "asset.samples" => "asset.samples",
      "asset.colors" => "asset.colors",
      "asset.dsc-conformance" => "asset.dsc-conformance",
      "asset.creator" => "asset.creator",
      "asset.filename" => "asset.filename",
      "asset.extra-samples" => "asset.extra-samples",
      "asset.pages" => "asset.pages",
      "asset.preview-format" => "asset.preview-format",
      "asset.format-version" => "asset.format-version",
      "ATT666977" => "Socket Length",
      "ATT666973" => "Drive End Sex",
      "ATT666984" => "Assembly Features",
      "ATT666983" => "Fastener End Sex",
      "ATT675275" => "Minimum Ultimate Static Torsional Strength - Apex Avg (in lbs)",
      "ATT326" => "Overall Length",
      "ATT665735" => "SKU Group Note",
      "ATT675252" => "Endurance Torque Test Angle",
      "ATT339" => "Point Size",
      "ATT672448" => "Fastener Type",
      "ATT26835" => "Drive",
      "ATT675262" => "Minimum Ultimate Static Torsional Strength - Apex Avg (in Nm)",
      "ATT675272" => "Maximum Cover Diameter (J) (in) (+/- .015)",
      "ATT728145" => "Weight Max (kg)",
      "ATT675277" => "Axial Tension / Compression (lbs)",
      "ATT678998" => "Weight Max (lb)",
      "ATT675263" => "Endurance Torque Test (Nm)",
      "ATT675265" => "Axial Tension / Compression (N)",
      "ATT728098" => "Hole Location (mm)",
      "ATT728101" => "Minimum Length (mm)",
      "ATT675250" => "Torsional Play Angle",
      "ATT666305" => "Clearance Depth (E)",
      "ATT667017" => "Diameter Drive End (C)",
      "ATT667014" => "Diameter Nose End (B)",
      "ATT667022" => "Diameter Nose End (D)",
      "ATT666186" => "Socket Length (B)",
      "ATT666296" => "Socket Diameter (C)",
      "ATT667008" => "Overall Length (A)",
      "ATT666324" => "Square Drive",
      "ATT835" => "Drive Size",
      "ATT533" => "Screw Size",
      "ATT675356" => "Hub Length (E) (REF) (in)",
      "ATT666309" => "Measurement Across Lobes",
      "ATT667223" => "Flathead Screw Size",
      "ATT667221" => "Cap Screw Size",
      "ATT666136" => "Hex Size",
      "ATT667222" => "Set Screw Size",
      "ATT667000" => "Male Hex Size",
      "ATT106" => "Copy Point 07",
      "ATT102" => "Copy Point 03",
      "ATT669754" => "u-GUARD",
      "ATT241" => "Hex Opening",
      "ATT668491" => "Product Family",
      "ATT104" => "Copy Point 05",
      "ATT420" => "Tool Type",
      "ATT103" => "Copy Point 04",
      "ATT101" => "Copy Point 02",
      "ATT666187" => "Turned Length",
      "ATT105" => "Copy Point 06",
      "ATT669761" => "Magnetism",
      "ATT28568" => "Vertical Market",
      "ATT100" => "Copy Point 01",
      "ATT919" => "Coupon Headline",
      "ATT669756" => "Opening Modifier",
      "ATT669755" => "Socket Type Length",
      "ATT539" => "UPC",
      "ATT425" => "Type",
      "ATT676136" => "Attribute Sort Order",
      "ATT107" => "Copy Point 08",
      "ATT675376" => "Maximum Peak Torque (in lbs)",
      "ATT675267" => "Overall Length B (in)",
      "ATT675361" => "Keyaway Width (mm)",
      "ATT675266" => "Outside Diameter A (in)",
      "ATT675375" => "Maximum Momentary Stall Torque (in lbs)",
      "ATT675264" => "Torsional Play Test Torque (Nm)",
      "ATT675357" => "Hub Type",
      "ATT675251" => "Maximum Angle",
      "ATT675362" => "Keyaway Depth (mm)",
      "ATT675366" => "Keyaway Width (in)",
      "ATT675358" => "Center Length (K) (REF) (mm)",
      "ATT675359" => "Center Length (K) (REF) (in)",
      "ATT15738" => "Web Display Sort Order",
      "ATT675369" => "Maximum Cover Diameter (in)",
      "ATT675368" => "Exposed Hub Length (in)",
      "ATT675367" => "Keyaway Depth (in)",
      "ATT675365" => "Maximum Cover Diameter (mm)",
      "ATT675364" => "Exposed Hub Length (mm)",
      "ATT675374" => "Maximum Peak Torque (Nm)",
      "ATT675247" => "Bore Diameter (G) (mm)",
      "ATT675363" => "Cover Style",
      "ATT921" => "Long Description",
      "ATT675274" => "Minimum Ultimate Static Torsional Strength (in lbs)",
      "ATT675273" => "Torsional Play Test Torque (in lbs)",
      "ATT108" => "Copy Point 09",
      "ATT675270" => "Bore Diameter (G) (in)",
      "ATT675269" => "Bore Depth (C) (in)",
      "ATT675395" => "Weight of Solid-Hub Covered Assembly (kg)",
      "ATT675370" => "Ultimate Axial Strength (N)",
      "ATT675372" => "Maximum Momentary Stall Torque (Nm)",
      "ATT675261" => "Minimum Ultimate Static Torsional Strength (Nm)",
      "ATT675246" => "Bore Depth (C) (mm)",
      "ATT180" => "Table Description",
      "ATT675244" => "Overall Length B (mm)",
      "ATT675371" => "Ultimate Axial Strength (lbs)",
      "ATT675396" => "Weight of  Solid-Hub Covered Assembly (lb)",
      "ATT675243" => "Outside Diameter A (mm)",
      "ATT675355" => "Hub Length (E) (REF) (mm)",
      "ATT666190" => "Turned OD (B)",
      "ATT666293" => "Socket Nose Diameter",
      "ATT666308" => "Blade Projection",
      "ATT666329" => "In Column Note 2",
      "ATT666304" => "Depth of Clearance (A)",
      "ATT667030" => "Socket Diameter",
      "ATT667001" => "Tap Size",
      "ATT948" => "Size",
      "ATT667029" => "Socket Length",
      "ATT666142" => "Hex Flat Type",
      "ATT666320" => "Recess",
      "ATT666176" => "Bit Stick Out (B)",
      "ATT666310" => "MorTorq Size",
      "ATT666298" => "Sleeve Number",
      "ATT666312" => "Screw Size Shear Head",
      "ATT666311" => "Screw Size Tension Head",
      "ATT666319" => "Recess Size",
      "ATT344" => "Product Name",
      "ATT675544" => "Overall Length B Min (in)",
      "ATT675545" => "Overall Length B Max (in)",
      "SAP_SALES_ORG_STATUS" => "SAP Sales Org Status",
      "ATT133" => "Blade Width",
      "ATT666180" => "Length of Insert (A)",
      "ATT675405" => "End OD (A) mm (+.03/-.15)",
      "ATT675403" => "Front Bore ID",
      "ATT22562" => "Drill Size",
      "ATT27860" => "Style",
      "ATT675542" => "Overall Length B Min (mm)",
      "ATT675562" => "Cover",
      "ATT575" => "Used On",
      "ATT675404" => "Rear Bore ID",
      "ATT666191" => "Body Diameter",
      "ATT667125" => "Sleeve Diameter",
      "ATT17319" => "Nominal Size",
      "ATT666289" => "Blade Width/Body Diameter",
      "ATT675543" => "Overall Length B Max (mm)",
      "ATT666300" => "Opening Depth",
      "ATT666322" => "Square Drive (B)",
      "ATT666316" => "Female Thread",
      "ATT666292" => "Overall Diameter",
      "ATT666303" => "Opening Depth (F)",
      "ATT666302" => "Opening Depth (E)",
      "ATT666291" => "Body Diameter (D)",
      "ATT666294" => "Sleeve Diameter (E)",
      "ATT666138" => "Hex Open. (A)",
      "ATT666986" => "Driver Size",
      "ATT667024" => "Spline Size",
      "ATT666988" => "Square Opening",
      "ATT666299" => "Nose End Diameter (D)",
      "ATT666318" => "Hex Drive",
      "ATT667036" => "Magnet Depth",
      "ATT666177" => "Length of Insert",
      "ATT667033" => "Broach Depth",
      "ATT666181" => "Shank Length (A)",
      "ATT667007" => "Largest Diameter",
      "ATT666188" => "Turned Length (A)",
      "ATT666325" => "Type of Lock",
      "ATT667025" => "Roll Pin Part Number",
      "ATT325" => "Outside Diameter",
      "ATT666315" => "Male Thread",
      "ATT666999" => "Male Hex Power Drive",
      "ATT728154" => "Description",
      "ATT728149" => "Description",
      "ATT728148" => "Male Square Drive",
      "ATT728152" => "Female Square",
      "ATT728147" => "Bit Length (A)",
      "ATT781" => "Length",
      "ATT667216" => "Torque Range Max (cm-kg)",
      "ATT661955" => "Weight (lbs)",
      "ATT667217" => "Torque Range Min (cm-kg)",
      "ATT667214" => "Torque Range Max (in-oz)",
      "ATT667215" => "Torque Range Min (in-oz)",
      "ATT660052" => "Torque Range Min (in lbs)",
      "ATT28147" => "Torque Range Min",
      "ATT28148" => "Torque Range Max",
      "ATT28581" => "Increments",
      "ATT584486" => "Torque Range Min (Nm)",
      "ATT28572" => "Collar Color",
      "ATT660051" => "Torque Range Max (in lbs)",
      "ATT660049" => "Torque Display",
      "ATT28567" => "Regions",
      "ATT662" => "Key Sizes",
      "ATT659133" => "Torque Range Min (ft lbs)",
      "ATT659132" => "Torque Range Max (ft-lbs)",
      "ATT140" => "Body Type",
      "ATT28570" => "Opening Size",
      "ATT109" => "Copy Point 10",
      "ATT708" => "Repair Kit",
      "ATT678995" => "Drive Size",
      "ATT592" => "Magnetic",
      "ATT728153" => "Opening Depth (D) (in)",
      "ATT728151" => "Drive Size (in)",
      "Footnotes" => "Footnotes",
      "WeightSAP" => "Weight (SAP)",
      "Set" => "Set",
      "UnitsOfMeasure" => "Units of Measure",
      "DistChannel" => "Distribution Channel",
      "Catalog Number" => "Catalog Number",
      "DivCode" => "Division Code",
      "BaseUnitOfMeasure" => "Base Unit of Measure",
      "CurrencyCode" => "Currency Code",
      "SalesOrg" => "SalesOrg",
      "Brand" => "Brand",
      "ListPrice" => "List Price",
      "DeliveryUnit" => "Delivery Unit",
      "ForeignTradeCode" => "Foreign Trade Code",
      "SAP_Description" => "Description (SAP)",
      "CustomerPrice" => "Customer Price",
      "Table Sort Order" => "Table Sort Order",
      "SAP Material Status" => "Material Status (MRP)",
      "Pro Landing Body" => "Pro Landing Body",
    ];
  }

  /**
   * List of Excluded atrribite ID.
   *
   * @return mixed
   *   Returns attribute ids.
   */
  public function getExcludedAttributeList() {
    return [
      // Generic attribute IDs.
      // Footnotes.
      "Footnotes",
      // SKU Overview.
      "ATT17711",
      // Coupon Headline.
      "ATT919",
      // UPC.
      "ATT539",
      "Pro Landing Body",
      "Table Sort Order",
      "Table Sort Order Set Components",
      // Web Display Sort Order.
      "ATT15738",
      // Attribute Sort Order.
      "ATT676136",
      // Feature atrribute IDs [Copy Point 01 - Copy Point 16].
      "ATT100",
      "ATT101",
      "ATT102",
      "ATT103",
      "ATT104",
      "ATT105",
      "ATT106",
      "ATT107",
      "ATT108",
      "ATT109",
      "ATT22085",
      "ATT22086",
      "ATT22087",
      "ATT22088",
      "ATT22089",
      "ATT22090",
      "Catalog Number",
      "SAP Material Status",
      "SAP_SALES_ORG_STATUS",
      "ATT676136",
    ];
  }

  /**
   * Get migrated taxonomy tids.
   *
   * @param string $type_id
   *   Test to be processed.
   *
   * @return array
   *   Returns allowed download type.
   */
  public function allowedDownloadTypes($type_id) {
    $allowed_type = '';
    $download_type_list = [
      'certificates' => 'certificates',
      'dxf file' => 'dxe_file',
      'igs file' => 'igs_file',
      'line art' => 'line_art',
      'repair manual' => 'repair_manual',
      'stp file' => 'stp_file',
      'trouble shooting' => 'trouble_shooting',
      'warranty' => 'warranty',
      'installation note' => 'installation_note',
      'maintenance instruction' => 'maintenance_instruction',
      '3d model' => '3d_model',
      'assembly instruction' => 'assembly_instruction',
      'catalog' => 'catalog',
      'ce documentation' => 'ce_documentation',
      'data sheet' => 'data_sheet',
      'dimensional diagram' => 'dimensional_diagram',
      'engineering drawings' => 'engineering_drawings',
      'flyer/brochure' => 'flyer_brochure',
      'hardware manual' => 'hardware_manual',
      'homologation' => 'homologation',
      'installation manual' => 'installation_manual',
      'instruction manual' => 'instruction_manual',
      'manual' => 'manual',
      'msds' => 'msds',
      'operating instructions' => 'operating_instructions',
      'parts list' => 'parts_list',
      'programming manual' => 'programming_manual',
      'quick installation guide' => 'quick_installation_guide',
      'service manual' => 'service_manual',
      'software' => 'software',
      'system manual' => 'system_manual',
      'user guide' => 'user_guide',
      'owners manual' => 'owners_manual',
    ];
    if (array_key_exists(strtolower($type_id), $download_type_list)) {
      $allowed_type = $download_type_list[strtolower($type_id)];
    }
    return $allowed_type;
  }

  /**
   * Get migrated taxonomy tids.
   *
   * @param array $source_id1
   *   Test to be processed.
   * @param string $migration_id
   *   Sourceid of the migration.
   *
   * @return array
   *   Returns migrated tids.
   */
  public function getAllMigratedTaxonomyTid(array $source_id1, $migration_id) {
    $tid = NULL;
    if (empty($source_id1)) {
      return $tid;
    }
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->addField('t', 'destid1');
    $query->condition('t.sourceid1', $source_id1, 'IN');
    $tid = $query->execute()->fetchCol();
    return $tid;
  }

  /**
   * Write various log information in the file.
   *
   * @param string $logfile
   *   Name of the log file.
   * @param string $message
   *   Log message to be written.
   * @param string $type
   *   Type to identify log path.
   */
  public function logMessage($logfile, $message, $type = '') {
    if (empty($logfile)) {
      $logfile = $this->getDefaultLogfile($type);
    }
    $dir = dirname($logfile);
    if (!file_exists($dir)) {
      mkdir($dir, 0770, TRUE);
    }
    file_put_contents($logfile, $message . PHP_EOL, FILE_APPEND);
  }

  /**
   * Write various log information in the file.
   *
   * @param string $type
   *   Type to identify log path.
   */
  public function getDefaultLogfile($type = '') {
    $default_logfile = "public://import/pim_data/logs/notification.txt";
    switch ($type) {
      case 'notification':
        $default_logfile = "public://import/pim_data/logs/notification.txt";
        break;

      default:
        $default_logfile = "public://import/pim_data/logs/notification.txt";
        break;
    }
    return $default_logfile;
  }

  /**
   * Get product classification list.
   *
   * @param mixed $classification_reference
   *   Classification data.
   *
   * @return array
   *   Returns all classification tid.
   */
  public function getClassificationList($classification_reference) {
    $list = [];
    $match_type_list = $this->configuration['classification_type'];
    foreach ($classification_reference as $child) {
      $id = (string) $child->attributes()->ClassificationID;
      $type = (string) $child->attributes()->Type;
      if (in_array($type, $match_type_list)) {
        $list[] = $id;
      }
    }
    return $list;
  }

  /**
   * Get product image asset list.
   *
   * @param mixed $asset_crossreference
   *   Asset data.
   *
   * @return array
   *   Returns all asset mid.
   */
  public function getImageList($asset_crossreference) {
    $list = [];
    $match_type_list = $this->configuration['asset_type'];
    foreach ($asset_crossreference as $child) {
      $id = (string) $child->attributes()->AssetID;
      $type = (string) $child->attributes()->Type;
      if (in_array($type, $match_type_list)) {
        $list[] = $id;
      }
    }
    return $list;
  }

  /**
   * Get migrated mapped Ids.
   *
   * @param array $source_id1
   *   Source Id list to be processed.
   * @param string $migration_id
   *   Instance of the migration.
   *
   * @return array
   *   Returns migrated Ids.
   */
  public function getAllMigratedMapId(array $source_id1, $migration_id) {
    $migrated_ids = NULL;
    if (empty($source_id1)) {
      return $migrated_ids;
    }
    if (empty($migration_id)) {
      return $migrated_ids;
    }
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->fields('t', ['sourceid1', 'destid1']);
    $query->condition('t.sourceid1', $source_id1, 'IN');
    $migrated_ids = $query->execute()->fetchAllKeyed();
    return $migrated_ids;
  }

  /**
   * Get file extension for the asset download.
   *
   * @param string $extension
   *   Asset extension.
   *
   * @return string
   *   Returns the constructed file extension.
   */
  public function getFileExtensionMapped($extension = '') {
    $extension = strtolower($extension);
    $file_extension = $extension;
    if (empty($extension)) {
      $file_extension = 'jpg';
    }
    $list = [
      'eps' => 'jpg',
      'png' => 'jpg',
      'gif' => 'jpg',
      'tif' => 'jpg',
      'pdf' => 'pdf',
    ];
    if (isset($list[$extension])) {
      $file_extension = $list[$extension];
    }
    return $file_extension;
  }

  /**
   * Get Assets associated products classifications.
   *
   * @param int $mid
   *   Asset tid.
   * @param string $langcode
   *   Language code.
   *
   * @return array
   *   Returns associated products classification tids.
   */
  public function getAllProductCategories(int $mid, $langcode = '') {
    $tids = [];
    if (empty($mid)) {
      return $tids;
    }
    $query = $this->connection->select('node__field_downloads', 'nd');
    $query->leftjoin('node__field_product_classifications', 'np', 'np.entity_id = nd.entity_id');
    $query->leftjoin('node_field_data', 'n', 'n.nid = nd.entity_id');
    $query->addField('np', 'field_product_classifications_target_id');
    $query->condition('nd.field_downloads_target_id', $mid);
    if (!empty($langcode)) {
      $query->condition('nd.langcode', $langcode);
      $query->condition('np.langcode', $langcode);
    }
    $query->condition('n.status', 1);
    $query->condition('n.type', 'product');
    $query->condition('nd.bundle', 'product');
    $query->condition('np.bundle', 'product');
    $query->condition('nd.deleted', 0);
    $query->condition('np.deleted', 0);

    $tids = $query->execute()->fetchAllKeyed(0, 0);
    if (!empty($tids)) {
      $tids = array_keys($tids);
    }
    return $tids;
  }

  /**
   * Construct attribute key.
   *
   * @param string $attribute_id
   *   Attribute ID.
   * @param string $unit
   *   Unit of the attribute.
   *
   * @return string
   *   Returns constructed attribute key.
   */
  public function constructAttributeKey($attribute_id, $unit = '') {
    $key = '';
    $list = $this->getAttributeList();
    $name = "";
    if (isset($list[$attribute_id])) {
      $name = $list[$attribute_id];
    }

    // If name doesn't exist in the list, return empty.
    if (empty($name)) {
      return '';
    }

    // If atrribute ID and name is same, return the ID.
    if ($name == $attribute_id) {
      $key = strtolower($name);
      return $key;
    }

    // Get attribute name processed.
    $name = $this->processAtrributeName($name);

    // If unit is not present.
    $unit_key = '';
    if (!empty($unit)) {
      $unit_key = $this->processUnit($unit);
    }
    if (!empty($unit_key)) {
      $key = $name . "_" . $unit_key;
    }
    if (empty($unit_key)) {
      $key = $name;
    }
    return $key;
  }

  /**
   * Process attribute name for key generation.
   *
   * @param string $name
   *   Name of the attribute ID.
   *
   * @return string
   *   Returns processed attribute name.
   */
  public function processAtrributeName($name = '') {
    // Replace with underscore for: whitespace, /, &, -.
    $name = str_ireplace("-", "_", $name);
    $name = str_ireplace(" ", "_", $name);
    $name = str_ireplace("/", "_", $name);
    $name = str_ireplace("&", "_", $name);
    $name = str_ireplace("&", "-", $name);

    // Replace with blank for: (, ), .,  #, +.
    $name = str_ireplace("(", "", $name);
    $name = str_ireplace(")", "", $name);
    $name = str_ireplace(".", "", $name);
    $name = str_ireplace("#", "", $name);
    $name = str_ireplace("+", "", $name);
    $name = str_ireplace("@", "", $name);
    $name = str_ireplace("__", "_", $name);
    $name = strtolower($name);
    return $name;
  }

  /**
   * Process attribute unit for key generation.
   *
   * @param string $unit
   *   Complete Unit value.
   *
   * @return string
   *   Returns processed unit value for key generation.
   */
  public function processUnit($unit) {
    $final_unit = '';
    // Process for dot(.) separated unit id.
    if (stristr($unit, '.') !== FALSE) {
      $dot_arr = explode(".", $unit);
      $total = count($dot_arr);
      $unit_last = $dot_arr[$total - 1];
      if (!empty($unit_last)) {
        $final_unit = strtolower($unit_last);
      }
      // Take first two character only for Apex Tool (AT).
      if ($final_unit != "") {
        $final_unit = substr($final_unit, 0, 2);
      }
      return $final_unit;
    }
    return $final_unit;
  }

}
