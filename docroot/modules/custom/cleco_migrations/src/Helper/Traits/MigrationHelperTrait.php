<?php

namespace Drupal\cleco_migrations\Helper\Traits;

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
      "ATT18209" => "long_description",
      "ATT665402" => "input_style",
      "ATT664420" => "cutter_thread",
      "ATT665264" => "mounting_base_style",
      "ATT728132" => "window_style",
      "ATT665405" => "nose_insert_style",
      "ATT665420" => "stroke_in",
      "ATT665419" => "shank_in",
      "ATT665414" => "shank_mm_mmt",
      "ATT662540" => "diameter_max_in",
      "ATT584484" => "diameter_max_mm",
      "ATT665422" => "max_overall_length_in",
      "ATT665417" => "max_overall_length_mm",
      "ATT665421" => "min_overall_length_in",
      "ATT665416" => "min_overall_length_mm",
      "ATT665424" => "weight_g",
      "ATT670151" => "nominal_power_hp",
      "ATT670152" => "nominal_power_kw",
      "ATT670154" => "thrust_lbs",
      "ATT670153" => "thrust_n",
      "ATT670147" => "feed_inches_rev_ipr",
      "ATT670146" => "feed_mm_rev",
      "ATT661955" => "weight_lbs",
      "ATT661950" => "weight_kg",
      "ATT16694" => "air_inlet_size_inh",
      "ATT16693" => "chuck_size",
      "ATT664421" => "dia_required_mm_tolerance",
      "ATT664440" => "number_of_flutes",
      "ATT672818" => "cutter_flute_length_in",
      "ATT664417" => "cutter_flute_length_mm",
      "ATT664426" => "flutes",
      "ATT664427" => "ground_points",
      "ATT672823" => "max_diameter_in",
      "ATT664429" => "max_diameter_mm",
      "ATT664444" => "taper_lead_length_mm",
      "ATT664416" => "countersinking_angle_degrees_dd",
      "ATT672821" => "drill_dia_in",
      "ATT664424" => "drill_dia_mm",
      "ATT674028" => "shank_diameter_inh",
      "ATT674029" => "shank_diameter_inh",
      "ATT659133" => "torque_range_min_ft_lbs",
      "ATT584486" => "torque_range_min_nm",
      "ATT659132" => "torque_range_max_ft_lbs",
      "ATT584487" => "torque_range_max_nm",
      "ATT662378" => "min_hose_id_in",
      "ATT662379" => "min_hose_id_mm",
      "ATT662382" => "air_consumption_at_free_speed_scfm_scfm",
      "ATT662381" => "air_consumption_at_free_speed_l_s",
      "ATT662377" => "breakaway_torque_ft_lbs",
      "ATT662376" => "breakaway_torque_nm",
      "ATT835" => "drive_size",
      "ATT499" => "drive_type",
      "ATT728082" => "output_drive_in_mm",
      "ATT145" => "capacity",
      "ATT16670" => "speed_spm",
      "ATT584368" => "free_speed_rpm_26v",
      "ATT584369" => "free_speed_rpm_44v",
      "ATT661604" => "length_w_26v_battery_in",
      "ATT584478" => "length_w_26v_battery_mm",
      "ATT584360" => "side_to_center_in",
      "ATT584357" => "side_to_center_mm",
      "ATT661599" => "angle_head_height_in",
      "ATT584465" => "angle_head_height_mm",
      "ATT661602" => "head_blade_ht_b_in",
      "ATT661600" => "head_end_cntr_d_in",
      "ATT661601" => "head_opening_c_in",
      "ATT584476" => "head_opening_depth_d_dim_mm",
      "ATT584475" => "head_opening_width_c_dim_mm",
      "ATT584473" => "head_thickness_b_dim_mm",
      "ATT661603" => "head_width_a_dim_in",
      "ATT584472" => "head_width_a_dim_mm",
      "ATT696034" => "livewire_speed_rpm_vmax",
      "ATT661605" => "socket_size_in",
      "ATT584466" => "socket_size_mm",
      "ATT585085" => "bore_in",
      "ATT16698" => "bore_mm",
      "ATT16674" => "abrasive_capacity_grinder_1",
      "ATT16672" => "blows_per_minute",
      "ATT22563" => "chisel",
      "ATT699" => "hose_inside_diameter_inh",
      "ATT665662" => "rivet_squeezer_set_shank_dia",
      "ATT728133" => "maximum_force_at_6_3_bar_90_psi",
      "ATT350" => "reach",
      "ATT665657" => "riveting_cylinder_with_connection_block",
      "ATT665654" => "riveting_cylinder_with_manual_control_on_cylinder",
      "ATT670159" => "accessories",
      "ATT662541" => "diameter_min_in",
      "ATT584483" => "diameter_min_mm",
      "ATT672816" => "body_dia_in",
      "ATT664414" => "body_dia_mm",
      "ATT665609" => "jaws",
      "ATT728134" => "max_opening_mm_mmt",
      "ATT16697" => "retainer_type",
      "ATT665438" => "shank",
      "ATT575" => "used_on",
      "ATT26811" => "abrasive_capacity_sander_2",
      "ATT663752" => "abrasive_type",
      "ATT664087" => "saw_blade_capacity_in",
      "ATT664086" => "saw_blade_capacity_mm",
      "ATT584578" => "maximum_depth_of_cut_in",
      "ATT16688" => "maximum_depth_of_cut_mm",
      "ATT16676" => "shank_diameter_capacity_in_mm_inh",
      "ATT670156" => "air_consumption_scfm",
      "ATT672392" => "air_consumption_at_free_speed_m3_min",
      "ATT584377" => "height_in",
      "ATT584376" => "height_mm",
      "ATT584380" => "depth_in",
      "ATT584379" => "depth_mm",
      "ATT584375" => "width_in",
      "ATT584374" => "width_mm",
      "ATT661607" => "length_in",
      "ATT584477" => "length_mm",
      "ATT28172" => "float_mm_mmt",
      "ATT28174" => "min_center_tocenter_mm_mmt",
      "ATT664089" => "head_height_in",
      "ATT664082" => "head_height_mm",
      "ATT726474" => "vibration_m_s",
      "ATT726475" => "vibration_k",
      "ATT665420" => "stroke_in",
      "ATT665418" => "stroke_mm",
      "ATT728124" => "speeds",
      "ATT728137" => "spindle_attachment",
      "ATT16699" => "stroke",
      "ATT670158" => "fixturing_options",
      "ATT670157" => "recommended_hose_size_id",
      "ATT670486" => "air_consumption",
      "ATT675745" => "spindle_type",
      "ATT583308" => "rpm_at_max_hp",
      "ATT22507" => "free_speed",
      "ATT584371" => "free_speed_rpm",
      "ATT732" => "gear_ratio",
      "ATT583306" => "control",
      "ATT16696" => "exhaust",
      "ATT242" => "horsepower_hj",
      "ATT583311" => "length_pilot_inh",
      "ATT670145" => "motor_configuration",
      "ATT556" => "mount",
      "ATT669469" => "tool_compatibility",
      "ATT713557" => "database_required",
      "ATT713554" => "minimum_disk_space",
      "ATT713556" => "minimum_operating_system",
      "ATT713555" => "minimum_processor",
      "ATT713522" => "minimum_ram",
      "ATT713553" => "minimum_virtual_memory",
      "ATT713558" => "price_method",
      "ATT180" => "table_description",
      "ATT729" => "output_drive",
      "ATT727431" => "barcode_reader",
      "ATT16691" => "collet_size",
      "ATT19744" => "horsepower_range",
      "ATT19743" => "rmpm_range",
      "ATT26650" => "overhose",
      "ATT16692" => "spindle_size",
      "ATT726473" => "at_load_scfm_l_s",
      "ATT17992" => "collet_guard",
      "ATT26649" => "throttle_type",
      "ATT727184" => "tool_configuration",
      "ATT26820" => "termination",
      "ATT17322" => "tool_termination",
      "ATT420" => "tool_type",
      "ATT434" => "vacuum",
      "ATT384" => "speed_rpm",
    ];
    return $list;
  }

  /**
   * List of valid atrribite ID.
   *
   * @return mixed
   *   Returns attribute ids.
   */
  public function getAttributeList() {
    return [
      "ATT756190" => "Top Seller",
      "ATT665413" => "Window Size",
      "ATT670148" => "Tool adapter options",
      "ATT26930" => "Abrasive Capacity (Grinder)",
      "asset.size" => "asset.size",
      "Footnotes" => "Footnotes",
      "ATT678745" => "Thread Size",
      "asset.uploaded" => "asset.uploaded",
      "ATT713553" => "Minimum Virtual Memory",
      "ATT28176" => "Floating Adapter w/ Guide Housing (Order #)",
      "ATT713554" => "Minimum Disk Space",
      "ATT28175" => "Floating Adaptor (Order #)",
      "ATT28174" => "Min Center-toCenter (mm)",
      "ATT26835" => "Drive",
      "ATT713555" => "Minimum Processor",
      "ATT28172" => "Float (mm)",
      "ATT713556" => "Minimum Operating System",
      "ATT835" => "Drive Size",
      "ATT688984" => "Associated Product",
      "ATT669469" => "Tool Compatibility",
      "ATT145" => "Capacity",
      "ATT17992" => "Collet Guard",
      "ATT682309" => "Length (w/44v Battery) (in)",
      "ATT661950" => "Weight (kg)",
      "ATT728192" => "For Use with Tool Series",
      "ATT682308" => "Length (w/44v Battery) (mm)",
      "ATT661599" => "Angle Head Height (in)",
      "ATT661600" => "Head End/Cntr. 'D' (in)",
      "ATT713557" => "Database Required",
      "Brand" => "Brand",
      "ATT713558" => "Price Method",
      "ATT661601" => "Head Opening 'C' (in)",
      "ATT661602" => "Head Blade Ht 'B' (in)",
      "ATT661603" => "Head Width 'A' Dim (in)",
      "asset.extension" => "asset.extension",
      "ATT661604" => "Length (w/26V Battery) (in)",
      "ATT584578" => "Maximum Depth of Cut (in)",
      "ATT661605" => "Socket Size (in)",
      "asset.mime-type" => "asset.mime-type",
      "ATT661607" => "Length (in)",
      "ATT167" => "Connector Type",
      "asset.compression" => "asset.compression",
      "ATT22387" => "Material",
      "ATT670152" => "Nominal Power (kw)",
      "asset.xdpi" => "asset.xdpi",
      "ATT675755" => "Retaining Ring (Part #)",
      "asset.class" => "asset.class",
      "ATT19744" => "Horsepower Range",
      "asset.pixel-width" => "asset.pixel-width",
      "asset.colorspace" => "asset.colorspace",
      "ATT670145" => "Motor Configuration",
      "asset.profile" => "asset.profile",
      "ATT19743" => "RPM Range",
      "asset.ydpi" => "asset.ydpi",
      "ATT180" => "Table Description",
      "asset.format" => "asset.format",
      "ATT182" => "Diameter",
      "ATT662514" => "Number of Channels",
      "ATT727414" => "Shut Off",
      "ATT583306" => "Control",
      "ATT28568" => "Vertical Market",
      "ATT664414" => "Body Dia. (mm)",
      "ATT664415" => "Countersink Diameter (mm)",
      "ATT670156" => "Air Consumption (SCFM)",
      "ATT664082" => "Head Height (mm)",
      "ATT584357" => "Side to Center (mm)",
      "ATT670154" => "Thrust (lbs)",
      "ATT670486" => "Air Consumption",
      "asset.pixel-height" => "asset.pixel-height",
      "ListPrice" => "List Price",
      "ATT583311" => "Length Pilot",
      "ATT664420" => "Cutter Thread",
      "ATT670485" => "Noise Level",
      "ATT664418" => "Cutter Length (mm)",
      "ATT664419" => "Cutter Dia D +/- 0.1mm",
      "CustomerPrice" => "Customer Price",
      "ATT664417" => "Cutter Flute Length (mm)",
      "asset.width" => "asset.width",
      "ATT664416" => "Countersinking Angle (degrees)",
      "ATT672487" => "Max Torque Range (nm)",
      "ATT670157" => "Recommended Hose Size ID",
      "ATT664425" => "Across the Flats (mm)",
      "ATT583308" => "RPM at Max HP",
      "ATT670153" => "Thrust (N)",
      "asset.height" => "asset.height",
      "ATT664424" => "Drill dia (mm)",
      "ATT28567" => "Regions",
      "ATT672488" => "Min Torque Range (nm)",
      "ATT670159" => "Accessories",
      "ATT667662" => "Free Speed (RPM)",
      "asset.depth" => "asset.depth",
      "ATT664422" => "Diameter (mm)",
      "ATT664423" => "Drill Capacity (mm)",
      "ATT499" => "Drive Type",
      "ATT670158" => "Fixturing Options",
      "ATT664421" => "Dia Required (mm/tolerance)",
      "asset.samples" => "asset.samples",
      "ATT498" => "Fastener Size",
      "ATT664431" => "Max Radius (mm)",
      "ATT664429" => "Max Diameter (mm)",
      "ATT26811" => "Abrasive Capacity (Sander 2)",
      "ATT584360" => "Side to Center (in)",
      "ATT16699" => "Stroke",
      "asset.colors" => "asset.colors",
      "ATT16698" => "Bore (mm)",
      "ATT664428" => "Head Diameter (mm) (-.02/-.05)",
      "ATT16697" => "Retainer Type",
      "ATT664426" => "Flutes",
      "ATT664427" => "Ground Points",
      "ATT16696" => "Exhaust",
      "ATT665661" => "Minimum Underhead Cutting Length",
      "ATT16695" => "kW",
      "ATT664436" => "Min Radius (mm)",
      "ATT26649" => "Throttle Type",
      "ATT665662" => "Rivet Squeezer Set Shank Dia",
      "ATT664434" => "Min Diameter (mm)",
      "ATT16694" => "Air Inlet Size",
      "ATT664433" => "For Use w/ Microstop Cages",
      "ATT16693" => "Chuck Size",
      "ATT16692" => "Spindle Size",
      "ATT583314" => "Torque Stall",
      "ATT665660" => "Tolerance Over Length",
      "ATT556" => "Mount",
      "ATT26863" => "Collet Option",
      "ATT16691" => "Collet Size",
      "ATT727185" => "Power Type",
      "ATT664086" => "Saw Blade Capacity (mm)",
      "ATT16689" => "Type Housing",
      "ATT727184" => "Tool Configuration",
      "ATT664440" => "Number of Flutes",
      "ATT16688" => "Maximum Depth of Cut (mm)",
      "ATT727183" => "Control Type",
      "ATT584366" => "Spindle Retraction (in)",
      "ATT664439" => "Nominal Rivet Diameter (mm)",
      "ATT229" => "Head Style",
      "ATT584365" => "Spindle Retraction (mm)",
      "asset.dsc-conformance" => "asset.dsc-conformance",
      "ATT674089" => "Reversible Gears",
      "asset.creator" => "asset.creator",
      "ATT664087" => "Saw Blade Capacity (in)",
      "ATT584368" => "Free Speed (RPM) (26V)",
      "ATT664444" => "Taper Lead Length (mm)",
      "ATT664442" => "Pilot Thread",
      "ATT664443" => "Spotfacing dia (mm)",
      "ATT583320" => "Max Overhead Load @ Stall",
      "ATT583321" => "NPT",
      "ATT584369" => "Free Speed (RPM) (44V)",
      "asset.filename" => "asset.filename",
      "ATT584371" => "Free Speed (RPM)",
      "ATT664089" => "Head Height (in)",
      "asset.extra-samples" => "extra-samples",
      "ATT26838" => "Configuration",
      "ATT584375" => "Width (in)",
      "ATT584374" => "Width (mm)",
      "ATT726473" => "At Load SCFM (l/s)",
      "ATT584377" => "Height (in)",
      "ATT584376" => "Height (mm)",
      "ATT726472" => "F/S SCFM (l/s)",
      "ATT665609" => "Jaws",
      "ATT665400" => "Mounting Base with Vacuum",
      "ATT584379" => "Depth (mm)",
      "ATT726477" => "ISO 15744 Sound Pressure (dBA)",
      "ATT22563" => "Chisel",
      "ATT726476" => "ISO 15744 Sound Power (dBA)",
      "ATT665611" => "Rivet Dia.",
      "ATT728236" => "Wheel Size",
      "ATT726475" => "Vibration k",
      "ATT584380" => "Depth (in)",
      "ATT665612" => "Capacity Aluminum Rivet",
      "ATT726474" => "Vibration m/s",
      "ATT665404" => "Offset Base + 3 Nylon Pins",
      "ATT665617" => "Snap Holder Adj.",
      "ATT665403" => "Threaded Mounting Base with Vacuum",
      "ATT665402" => "Input Style",
      "ATT665615" => "Gap",
      "ATT665401" => "Threaded Mounting Base",
      "SAP_SALES_ORG_STATUS" => "SAP Sales Org Status",
      "ATT675402" => "Default UnitOfMeasure",
      "ATT889" => "X Dimension",
      "ATT665408" => "Celeron Rotary Nose",
      "ATT665621" => "Max Cylinder Stroke",
      "ATT665407" => "Nose Insert Part Number",
      "ATT665618" => "Piston Stroke",
      "ATT665406" => "Mounting Base Part Number",
      "ATT665619" => "Max Travel",
      "ATT665405" => "Nose Insert Style",
      "ATT665620" => "Adjusting Nut for Return Stroke",
      "ATT665411" => "Tripod + 3 Nylon Studs",
      "Pro Landing Body" => "Pro Landing Body",
      "ATT18209" => "Description",
      "ATT728082" => "Output Drive (in|mm)",
      "ATT665410" => "Start Control",
      "ATT22085" => "Copy Point 11",
      "ATT665409" => "Nose",
      "ATT22087" => "Copy Point 13",
      "ATT665414" => "Shank (mm)",
      "ATT22086" => "Copy Point 12",
      "ATT617" => "Dim. B",
      "ATT22090" => "Copy Point 16",
      "ATT22088" => "Copy Point 14",
      "ATT618" => "Dim. C",
      "ATT22089" => "Copy Point 15",
      "ATT665419" => "Shank (in)",
      "ATT665417" => "Max Overall Length (mm)",
      "ATT727431" => "Barcode Reader",
      "ATT665416" => "Min Overall Length (mm)",
      "ATT661684" => "Length (ft)",
      "ATT664878" => "Language",
      "ATT665422" => "Max Overall Length (in)",
      "ATT575" => "Used On",
      "ATT242" => "Horsepower",
      "ATT665421" => "Min Overall Length (in)",
      "ATT243" => "Hose Length",
      "ATT665420" => "Stroke (in)",
      "ATT665418" => "Stroke (mm)",
      "ATT663751" => "Orbital Pattern Size",
      "ATT663752" => "Abrasive Type",
      "ATT22507" => "Free Speed",
      "ATT26650" => "Overhose",
      "Catalog Number" => "Catalog Number",
      "ATT728124" => "Speeds",
      "ATT26820" => "Termination",
      "ATT17322" => "Tool Termination",
      "ATT17321" => "Tool Series",
      "ATT664790" => "ID=Region",
      "ATT678997" => "Weight Max (kg)",
      "ATT17319" => "Nominal Size",
      "ATT17318" => "Max. Pressure",
      "ATT17317" => "M.S.D.S. No.",
      "ATT665259" => "Basic Drill Cage",
      "ATT727457" => "Torque Transducer",
      "ATT16675" => "Drill Diameter Capacity",
      "ATT665424" => "Weight (g)",
      "ATT16674" => "Abrasive Capacity (Grinder 1)",
      "ATT664961" => "Description",
      "ATT16672" => "Blows Per Minute",
      "ATT665264" => "Mounting Base Style",
      "ATT665263" => "Mounting Base Offset Bearing",
      "ATT16670" => "Speed SPM",
      "ATT728114" => "Stroke",
      "ATT665262" => "Mounting Base Flat Bearing",
      "ATT728113" => "Stroke",
      "ATT665261" => "Mounting Base",
      "ATT619" => "Dim. D",
      "ATT728135" => "Feed Rate",
      "ATT679076" => "Swing Bar",
      "ATT665265" => "Mounting Base Flat Bearing with Vacuum",
      "ATT728137" => "Spindle Attachment",
      "asset.pages" => "",
      "ATT26657" => "Extension",
      "ATT662376" => "Breakaway Torque (nm)",
      "ATT728136" => "Feed Rate",
      "ATT670146" => "Feed mm/rev",
      "ATT670147" => "Feed Inches/rev (ipr)",
      "ATT662377" => "Breakaway Torque (ft-lbs)",
      "ATT662378" => "Min Hose ID (in)",
      "ATT728132" => "Window Style",
      "ATT662379" => "Min Hose ID (mm)",
      "ATT662381" => "Air Consumption at Free Speed (l/s)",
      "ATT728131" => "Pilot Diameter (mm)",
      "ATT662382" => "Air Consumption at Free Speed (SCFM)",
      "ATT728134" => "Max Opening (mm)",
      "ATT662384" => "SubFamily",
      "ATT728133" => "Maximum force at 6.3 bar/90 PSI",
      "ATT584484" => "Diameter Max. (mm)",
      "ATT678643" => "Bolt Size (in)",
      "ATT584483" => "Diameter Min. (mm)",
      "ATT674416" => "Motor Size",
      "ATT727474" => "Angle Encoder",
      "ATT350" => "Reach",
      "ATT584487" => "Torque Range Max (Nm)",
      "ATT678639" => "Torque Range Nm",
      "ATT668491" => "Product Family",
      "ATT584486" => "Torque Range Min (Nm)",
      "ATT678640" => "Torque Range Ft. Lbs.",
      "ATT678642" => "Bolt Size (mm)",
      "ATT672392" => "Air Consumption at Free Speed (m3/min)",
      "ATT670151" => "Nominal Power  (hp)",
      "ATT699" => "Hose Inside Diameter",
      "ATT727466" => "Tool Type Compatibility",
      "ATT698650" => "Power",
      "ATT727471" => "Controller Type",
      "ATT661691" => "Air Flow Volume (m3/m)",
      "ATT364" => "Rope Size",
      "ATT661692" => "Air Flow Volume (SCFM)",
      "asset.preview-format" => "asset.preview-format",
      "ATT17711" => "SKU Overview",
      "ATT584466" => "Socket Size (mm)",
      "ATT676136" => "Attribute Sort Order",
      "ATT584465" => "Angle Head Height (mm)",
      "SAP Material Status" => "Material Status (MRP)",
      "ATT662540" => "Diameter Max. (in)",
      "ATT665650" => "Minimum Force",
      "ATT340" => "Point Style",
      "ATT665651" => "Titane / Titanium",
      "ATT344" => "Product Name",
      "ATT662541" => "Diameter Min. (in)",
      "ATT345" => "Material",
      "ATT665654" => "Riveting Cylinder with Manual Control on Cylinder",
      "ATT665655" => "Remote Pedal Control",
      "ATT672817" => "Countersink Diameter (in)",
      "ATT675745" => "Spindle Type",
      "ATT919" => "Coupon Headline",
      "ATT665652" => "Capacity Monel",
      "ATT665653" => "Capacity Light Alloy",
      "ATT672818" => "Cutter Flute Length (in)",
      "ATT16676" => "Shank Diameter Capacity (in|mm)",
      "ATT672816" => "Body Dia. (in)",
      "ATT665656" => "Hydropneumatic Generator with 3 m Hose",
      "ATT584471" => "Head Angle",
      "ATT665657" => "Riveting Cylinder with Connection Block",
      "ATT584473" => "Head Thickness B Dim (mm)",
      "ATT584472" => "Head Width A Dim (mm)",
      "ATT662009" => "Torque Headline",
      "ATT15738" => "Web Display Sort Order",
      "ATT696034" => "Livewire Speed_RPM (Vmax)",
      "ATT584476" => "Head Opening Depth D Dim (mm)",
      "ATT668501" => "For Use With",
      "ATT584475" => "Head Opening Width C Dim (mm)",
      "ATT584478" => "Length (w/26V Battery) (mm)",
      "ATT584477" => "Length (mm)",
      "ATT696033" => "Governed RPM Range",
      "ATT696024" => "Weight (oz)",
      "ATT712" => "Capacity Minimum",
      "ATT711" => "Capacity Maximum",
      "ATT100" => "Copy Point 01",
      "ATT948" => "Size",
      "ATT101" => "Copy Point 02",
      "ATT102" => "Copy Point 03",
      "ATT922" => "Weight (Catalog)",
      "ATT375" => "Shelf Pack Qty.",
      "ATT104" => "Copy Point 05",
      "ATT103" => "Copy Point 04",
      "ATT672823" => "Max Diameter (in)",
      "ATT105" => "Copy Point 06",
      "ATT921" => "Long Description",
      "ATT106" => "Copy Point 07",
      "ATT672824" => "Max Radius (in)",
      "ATT108" => "Copy Point 09",
      "ATT107" => "Copy Point 08",
      "ATT669519" => "Suitable for Bolt Size",
      "ATT27351" => "Sound Level",
      "ATT696018" => "Max Flow",
      "Display Sequence" => "Display Sequence",
      "ATT109" => "Copy Point 10",
      "ATT672821" => "Drill dia (in)",
      "DimensionA" => "Dim. A",
      "ATT696019" => "Min Flow",
      "ATT584387" => "Length (m)",
      "ATT110" => "Air Flow Volume",
      "ATT672822" => "Head Diameter (in) (-.0007/-.0020)",
      "ATT111" => "Air Input/Output",
      "Table Sort Order" => "Table Sort Order",
      "ATT729" => "Output Drive",
      "ATT672819" => "Cutter Length (in)",
      "ATT384" => "Speed",
      "Table Display Sequence" => "Table Display Sequence [Attr]",
      "ATT732" => "Gear Ratio",
      "ATT672820" => "Drill Capacity (in)",
      "ATT662011" => "Table Sub Headline",
      "ATT662010" => "Table Headline",
      "ATT781" => "Length",
      "ATT674031" => "Pilot Only",
      "ATT728177" => "Visible",
      "ATT674030" => "Cutter Only",
      "ATT660049" => "Torque Display",
      "ATT439" => "Weight",
      "ATT661955" => "Weight (lbs)",
      "ATT28149" => "SCFM",
      "ATT674029" => "Shank Diameter (in)",
      "ATT670298" => "Table Display Sequence – SATA Mini",
      "ATT713522" => "Minimum Ram",
      "ATT674028" => "Shank Diameter (in)",
      "ATT662434" => "Chip Recognition Capability",
      "Table Name" => "Table Name",
      "ATT660051" => "Torque Range Max (in lbs)",
      "ATT728166" => "Fittings",
      "ATT660052" => "Torque Range Min (in lbs)",
      "ATT802" => "Shank Diameter",
      "ATT17332" => "Maximum Depth of Cut",
      "ATT664496" => "Table Display Sequence – SATA",
      "Table Sort Order Set Components" => "Table Sort Order Set Components",
      "ATT938" => "Component Quantity",
      "ATT659132" => "Torque Range Max (ft-lbs)",
      "ATT420" => "Tool Type",
      "ATT659133" => "Torque Range Min (ft lbs)",
      "ATT675633" => "Average Air Consumption (cfm)",
      "ATT425" => "Type",
      "ATT674034" => "Cutter Radius (in)",
      "ATT585085" => "Bore (in)",
      "asset.format-version" => "asset.format-version",
      "ATT665438" => "Shank",
      "ATT434" => "Vacuum",
      "ATT674033" => "Cutter Radius (mm)",
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
      return $final_unit;
    }
    return $final_unit;
  }

}
