<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

if (!isset($_POST) || empty($_POST)) :
    $_POST = json_decode(file_get_contents('php://input'), true);
endif;

require_once('./cookie.php');
$userid = ade_get_cookie();

header('Content-Type: application/json');

if ($userid !== false) :
    require_once('./Database.php');

    $db = new DatabaseSel;
    $skip_fields = array();
    $object = $db->table('drilling_equipment')->where('de_cookie', '=', $userid)->first();

    if (!isset($object->de_id)) :
        $fields = array(
          'de_contact_name',
          'de_contact_company',
          'de_email_address',
          'de_phone',
          'de_state',
          'de_country',
          'de_industry',
          'de_industry_other',
          'de_inquiry',
          'de_inquiry_product_types',
          'de_inquiry_product_types_other',
          'de_inquiry_application',
          'de_inquiry_application_info'
        );

        foreach ($fields as $field) :
        $object[$field] = '';
        endforeach;

        $currTime = date('Y-m-d H:i:s');
        $object['de_date_modified'] = $currTime;
        $object['de_date_created'] = $currTime;
        $object['de_cookie'] = $userid;
        $insert = $db->table('drilling_equipment')->insert($object);
        $object['de_id'] = $insert['id'];
    endif;

    //$apps = $db->table('drilling_equipment_app')->where('dea_cookie', '=', $userid)->get();
    $apps = $db->table('drilling_equipment_app')->where_sorted('dea_cookie', '=', $userid, 'dea_application_number', 'ASC')->get();

    //echo '<pre>',print_r($apps,1),'</pre>';

    if (!isset($apps[0])) :
        $fields = array(
          // application
          'dea_operation',
          'dea_operation_other',
          'dea_countersink_diameter',
          'dea_countersink_angle',
          'dea_final_hole_size',
          'dea_final_hole_size_units',
          'dea_final_hole_tolerance_1',
          'dea_final_hole_tolerance_1_unit',
          'dea_final_hole_tolerance_2',
          'dea_final_hole_tolerance_2_unit',
          'dea_fixture_being_used',
          'dea_fixture_being_used_other',
          'dea_number_of_material_layers',
          'dea_layer_1_material',
          'dea_layer_1_thickness',
          'dea_layer_1_units',
          'dea_layer_2_material',
          'dea_layer_2_thickness',
          'dea_layer_2_units',
          'dea_layer_3_material',
          'dea_layer_3_thickness',
          'dea_layer_3_units',
          'dea_layer_4_material',
          'dea_layer_4_thickness',
          'dea_layer_4_units',
          'dea_layer_5_material',
          'dea_layer_5_thickness',
          'dea_layer_5_units',
          'dea_layer_6_material',
          'dea_layer_6_thickness',
          'dea_layer_6_units',
          'dea_layer_7_material',
          'dea_layer_7_thickness',
          'dea_layer_7_units',
          'dea_layer_8_material',
          'dea_layer_8_thickness',
          'dea_layer_8_units',
          'dea_layer_9_material',
          'dea_layer_9_thickness',
          'dea_layer_9_units',
          'dea_layer_10_material',
          'dea_layer_10_thickness',
          'dea_layer_10_units',
          'dea_product_type_positive_feed',
          'dea_product_type_air_power_feed',
          'dea_product_type_manual_hand_drill',
          'dea_product_type_other',
          'dea_product_type_other_value',
          'dea_application_orientation',
          'dea_application_orientation_other',
          'dea_application_access',

          // accessories
          'dea_accessories_indexer_for_attachment',
          'dea_accessories_indexer_for_concentric_collet',
          'dea_accessories_cutter',
          'dea_accessories_twistlock',
          'dea_accessories_twistlock_21000_series',
          'dea_accessories_twistlock_22000_series',
          'dea_accessories_twistlock_23000_series',
          'dea_accessories_twistlock_24000_series',
          'dea_accessories_twistlock_other',
          'dea_accessories_twistlock_other_value',
          'dea_accessories_concentric_collet',
          'dea_accessories_c_clamp',
          'dea_accessories_other_attachment',
          'dea_accessories_other_attachment_value',
          'dea_accessories_lubricator',
          'dea_accessories_cycle_counter',
          'dea_accessories_handles',
          'dea_accessories_chip_fragmentation',
          'dea_accessories_chip_vacuum',
          'dea_accessories_other_value',
          'dea_accessories_other',
          'dea_accessories_clearance_height_h1',
          'dea_accessories_clearance_height_h1_value',
          'dea_accessories_clearance_height_h1_unit',
          'dea_accessories_clearance_height_h2',
          'dea_accessories_clearance_height_h2_value',
          'dea_accessories_clearance_height_h2_unit',
          'dea_accessories_side_to_center_dimension_l1',
          'dea_accessories_side_to_center_dimension_l1_value',
          'dea_accessories_side_to_center_dimension_l1_unit',
          'dea_accessories_side_to_center_dimension_l2',
          'dea_accessories_side_to_center_dimension_l2_value',
          'dea_accessories_side_to_center_dimension_l2_unit',
          'dea_accessories_pilot_on_cutter',
          'dea_accessories_pilot_on_cutter_value',
          'dea_accessories_pilot_on_cutter_unit',
          'dea_accessories_pin_on_clamp',
          'dea_accessories_pin_on_clamp_value',
          'dea_accessories_pin_on_clamp_unit',
          'dea_accessories_prehole_size',
          'dea_accessories_prehole_size_value',
          'dea_accessories_prehole_size_unit',
          'dea_accessories_template_strip',
          'dea_accessories_template_strip_value',
          'dea_accessories_template_strip_unit',
          'dea_accessories_locate_on_top_clamp',
          'dea_accessories_locate_on_bottom_clamp',

          // cutter information
          'dea_cutter_material',
          'dea_cutter_material_other',
          'dea_cutter_mounting_type',
          'dea_cutter_shank_diameter_units',
          'dea_cutter_shank_diameter_value',
          'dea_cutter_thread_units',
          'dea_cutter_thread_value',
          'dea_cutter_internal_thread_units',
          'dea_cutter_internal_thread_value',
          'dea_cutter_thread_length_units',
          'dea_cutter_thread_length_value',
          'dea_cutter_pilot_diameter_units',
          'dea_cutter_pilot_diameter_value',
          'dea_cutter_pilot_length_units',
          'dea_cutter_pilot_length_value',
          'dea_cutter_counterbore_diameter_units',
          'dea_cutter_counterbore_diameter_value',
          'dea_cutter_counterbore_depth_units',
          'dea_cutter_counterbore_depth_value',
          'dea_cutter_length_3_units',
          'dea_cutter_length_3_value',
          'dea_cutter_seat_angle_value',
          'dea_cutter_body_diameter_units',
          'dea_cutter_body_diameter_value',
          'dea_cutter_body_length_units',
          'dea_cutter_body_length_value',
          'dea_cutter_cutter_diameter_units',
          'dea_cutter_cutter_diameter_value',
          'dea_cutter_overall_length_units',
          'dea_cutter_overall_length_value',

          // drill & countersink
          'dea_cutter_drill_countersink_drill_length_units',
          'dea_cutter_drill_countersink_drill_length_value',
          'dea_cutter_drill_countersink_drill_diameter_units',
          'dea_cutter_drill_countersink_drill_diameter_value',
          'dea_cutter_drill_countersink_body_diameter_units',
          'dea_cutter_drill_countersink_body_diameter_value',
          'dea_cutter_drill_countersink_angle_value'
        );

        foreach ($fields as $field) :
        $apps[0][$field] = '';
        endforeach;
    endif;

    $object = (object)$object;
    $object->apps = $apps;

    echo ($object === false)
        ? json_encode(array(
            'error' => true,
            'message' => 'An error has ocurred loading your information.'
        ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
        : json_encode(array(
            'error' => false,
            'user' => $object
        ), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
else :
  echo json_encode(array());
endif;
