Cleco.controller('applicationController',
  function ($scope, $location, $http, $window, $localStorage, $filter) {
    $scope.t = $filter('translate');

    $scope.loadData = function() {
      $scope.layerlist = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
      $scope.appFields = [];
      $scope.requiredFields = ['dea_operation', 'dea_fixture_being_used', 'dea_number_of_material_layers', 'dea_application_orientation', 'dea_application_access', 'dea_cutter_information'];
      $scope.displaylist = [];
      $scope.active_app = 0;
      $scope.submitted = false;
      $scope.loading = true;
      $http.get('/themes/custom/atg/advanced-selector/api/read.php')
        .success(function(data, status) {
          $scope.loading = false;
      $scope.user = data.user;
      if ($scope.user && $scope.user.apps ) {
        $scope.user.apps[0].show = 1;
      }
      if ($scope.user === undefined) {
        // Define the $scope.user object if it is undefined
        $scope.user = {};
        $scope.user.apps = [];
        $scope.user.apps[0] = {};
        $scope.user.apps[0].show = 1;
      }

        console.log($scope.user.apps);
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.set_show = function(elem) {
      elem.show = 1;
    }

    $scope.toggleTabs = function(this_num, active_num) {
      $scope.active_app = this_num;
      $scope.saveAllApps($scope.user);
    }; //toggleTabs

    $scope.deleteApp = function(index) {
      confirmation = $scope.t('Are you sure you want to delete application #@index? This action cannot be undone.').replace('@index', index + 1);
      if (window.confirm(confirmation)) {
        $scope.loading = true;
        $http.post('/themes/custom/atg/advanced-selector/api/update.php', {
            refer: 'deleteApp',
            dea_application_number: index
          }).success(function(data) {
            $scope.loading = false;
            $scope.user = data.user;
            $scope.active_app = 0;
            $scope.user.apps[0].show = 1;
          })
          .error(function(data, status, headers, config) {
            $scope.loading = false;
            console.log(data, status, headers, config);
          });
        $scope.user.apps.splice(index, 1);
      }
    }; //deleteApp

    $scope.saveAllApps = function(user) {
      $scope.loading = true;
      user.refer = 'saveAllApps';
      $http.post('/themes/custom/atg/advanced-selector/api/update.php', user)
        .success(function(data) {
          $scope.loading = false;
          $scope.user = data.user;
          for (i = 0; i < $scope.user.apps.length; i++) {
            $scope.user.apps[i].show = 0;
          }
          $scope.user.apps[$scope.active_app].show = 1;
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //saveAllApps

    $scope.saveApplications = function(user) {
      $scope.submitted = true;
      $scope.loading = true;
      isOK = 1;
      if (!user || !user.apps || !Array.isArray(user.apps)) {
        console.error('Apps property of user object is undefined or not an array.');
        $scope.loading = false;
        return false;
      }
      for (a = 0; a < user.apps.length; a++) {
        console.log(user.apps[a]);
        success = $scope.checkAppFields(a);
        if (!success) {
          thisNum = a + 1;
          alert('Please fill in all required fields for Application ' + thisNum + '.');
          $scope.active_app = thisNum - 1;
          for (i = 0; i < $scope.user.apps.length; i++) {
            $scope.user.apps[i].show = 0;
          }
          $scope.user.apps[$scope.active_app].show = 1;
          isOK = 0;
          break;
        }
      }
      if (isOK == 0) {
        $scope.loading = false;
        return false;
      }
      user.refer = 'saveAllApps';
      $http.post('/themes/custom/atg/advanced-selector/api/update.php', user)
        .success(function(data) {
          $scope.loading = false;
          $scope.submitted = false;
          if (isOK == 1) {
            $location.path('/accessories');
          }
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //saveApplications

    $scope.toggleCheck = function(item, val, index) {
      if ($scope.user['apps'][index][item] === val) {
        $scope.user['apps'][index][item] = '';
        if (item == 'dea_product_type_other') {
          $scope.user['apps'][index]['dea_product_type_other_value'] = '';
        }
      } else {
        $scope.user['apps'][index][item] = val;
      }
    }; //toggleCheck

    $scope.clearChildren = function(items, index) {
      item_array = items.split('|');
      if (item_array.length == 1) {
        $scope.user['apps'][index][items] = '';
      } else {
        item_array.forEach(function(item) {
          $scope.user['apps'][index][item] = '';
        });
      }
    }; //clearChildren

    $scope.disabledNextStep = function() {
      disabled = false;

      if (typeof $scope.user !== 'undefined' && typeof $scope.user.apps !== 'undefined') {
        if ($scope.loading) disabled = true;
        if ($scope.user.apps.length === 0) disabled = true;

        for (a = 0; a <= $scope.user.apps.length; a++) {
          if (!$scope.checkAppFields(a)) {
            disabled = true;
          }
        }
      }

      return disabled;
    }; //disabledNextStep

    $scope.setLayers = function(num, index) {
      $scope.displaylist[index] = [];
      for (i = 1; i <= num; i++) {
        $scope.displaylist[index][i] = i;
      }
      $scope.displaylist[index].splice(0, 1); //Remove the mystery item at index 0
      //Clear out existing values if layers are set to smaller number
      clear_start = $scope.layerlist.length - num;
      if (clear_start > 0) {
        for (c = i; c <= $scope.layerlist.length; c++) {
          $scope.user['apps'][index]['dea_layer_' + c + '_material'] = '';
          $scope.user['apps'][index]['dea_layer_' + c + '_thickness'] = '';
          $scope.user['apps'][index]['dea_layer_' + c + '_units'] = '';
        }
      }
    }; //setLayers

    $scope.deleteLayer = function(index, num) {
      $scope.user['apps'][index]['dea_layer_' + num + '_material'] = '';
      $scope.user['apps'][index]['dea_layer_' + num + '_thickness'] = '';
      $scope.user['apps'][index]['dea_layer_' + num + '_units'] = '';
      $scope.user['apps'][index]['dea_number_of_material_layers'] = num - 1;
      $scope.displaylist[index].splice(num - 1, 1);
    }; //deleteLayer

    $scope.checkAppFields = function(index) {
      success = true;
      fields = $scope.requiredFields;

      if (typeof $scope.user['apps'][index] !== 'undefined') {
        for (i = 0; i < fields.length; i++) {
          field = $scope.user['apps'][index][fields[i]];
          if (!field) {
            $scope.appFields.push(fields[i]);
            success = false;
            break;
          }
          if (fields[i] == 'dea_cutter_information' && field) {
            if (field.toLowerCase() == 'yes') {
              cutter_fields = ['dea_cutter_information_dimensions', 'dea_cutter_material'];
              for (k = 0; k < cutter_fields.length; k++) {
                subfield = $scope.user['apps'][index][cutter_fields[k]];
                if (!subfield) {
                  $scope.appFields.push(cutter_fields[k]);
                  success = false;
                  break;
                }
              }
            }
          }
          if (field == 'Other - Please Specify') {
            otherfield = $scope.user['apps'][index][fields[i] + '_other'];
            if (!otherfield) {
              $scope.appFields.push(fields[i] + '_other');
              success = false;
              break;
            }
          }
        }
        for (i = 1; i <= $scope.user['apps'][index]['dea_number_of_material_layers']; i++) {
          field = $scope.user['apps'][index]['dea_layer_' + i + '_material'];
          if (!field) {
            success = false;
            break;
          }
          field = $scope.user['apps'][index]['dea_layer_' + i + '_thickness'];
          if (!field) {
            success = false;
            break;
          }
          field = $scope.user['apps'][index]['dea_layer_' + i + '_units'];
          if (!field) {
            success = false;
            break;
          }
        }
      }
      return success;
    }; //checkAppFields

    $scope.addApplication = function(index) {
      success = $scope.checkAppFields(index);
      if (success) {
        fields = {
          'dea_operation': '',
          'dea_operation_other': '',
          'dea_predrilled_hole_size': '',
          'dea_predrilled_hole_size_units': '',
          'dea_countersink_diameter': '',
          'dea_countersink_angle': '',
          'dea_final_hole_size': '',
          'dea_final_hole_size_units': '',
          'dea_final_hole_tolerance_1': '',
          'dea_final_hole_tolerance_1_unit': '',
          'dea_final_hole_tolerance_2': '',
          'dea_final_hole_tolerance_2_unit': '',
          'dea_fixture_being_used': '',
          'dea_fixture_being_used_other': '',
          'dea_number_of_material_layers': '',
          'dea_layer_1_material': '',
          'dea_layer_1_thickness': '',
          'dea_layer_1_units': '',
          'dea_layer_2_material': '',
          'dea_layer_2_thickness': '',
          'dea_layer_2_units': '',
          'dea_layer_3_material': '',
          'dea_layer_3_thickness': '',
          'dea_layer_3_units': '',
          'dea_layer_4_material': '',
          'dea_layer_4_thickness': '',
          'dea_layer_4_units': '',
          'dea_layer_5_material': '',
          'dea_layer_5_thickness': '',
          'dea_layer_5_units': '',
          'dea_layer_6_material': '',
          'dea_layer_6_thickness': '',
          'dea_layer_6_units': '',
          'dea_layer_7_material': '',
          'dea_layer_7_thickness': '',
          'dea_layer_7_units': '',
          'dea_layer_8_material': '',
          'dea_layer_8_thickness': '',
          'dea_layer_8_units': '',
          'dea_layer_9_material': '',
          'dea_layer_9_thickness': '',
          'dea_layer_9_units': '',
          'dea_layer_10_material': '',
          'dea_layer_10_thickness': '',
          'dea_layer_10_units': '',
          'dea_product_type_positive_feed': '',
          'dea_product_type_air_power_feed': '',
          'dea_product_type_manual_hand_drill': '',
          'dea_product_type_other': '',
          'dea_product_type_other_value': '',
          'dea_accessories_cutter': '',
          'dea_accessories_twistlock': '',
          'dea_accessories_twistlock_21000_series': '',
          'dea_accessories_twistlock_22000_series': '',
          'dea_accessories_twistlock_23000_series': '',
          'dea_accessories_twistlock_24000_series': '',
          'dea_accessories_twistlock_other': '',
          'dea_accessories_twistlock_other_value': '',
          'dea_accessories_concentric_collet': '',
          'dea_accessories_c_clamp': '',
          'dea_accessories_other_attachment': '',
          'dea_accessories_other_attachment_value': '',
          'dea_accessories_lubricator': '',
          'dea_accessories_cycle_counter': '',
          'dea_accessories_handles': '',
          'dea_accessories_chip_fragmentation': '',
          'dea_accessories_chip_vacuum': '',
          'dea_accessories_other': '',
          'dea_accessories_other_value': '',
          'dea_accessories_template_strip_thickness': '',
          'dea_accessories_template_strip_thickness_value': '',
          'dea_accessories_template_strip_thickness_unit': '',
          'dea_accessories_template_hole_diameter': '',
          'dea_accessories_template_hole_diameter_value': '',
          'dea_accessories_template_hole_diameter_unit': '',
          'dea_application_orientation': '',
          'dea_application_orientation_other': '',
          'dea_application_access': '',
          'dea_application_access_cad_data': '',
          'dea_accessories_addons': '',
          'dea_accessories_clearance_height_h1': '',
          'dea_accessories_clearance_height_h1_value': '',
          'dea_accessories_clearance_height_h1_unit': '',
          'dea_accessories_clearance_height_h2': '',
          'dea_accessories_clearance_height_h2_value': '',
          'dea_accessories_clearance_height_h2_unit': '',
          'dea_accessories_side_to_center_dimension_l1': '',
          'dea_accessories_side_to_center_dimension_l1_value': '',
          'dea_accessories_side_to_center_dimension_l1_unit': '',
          'dea_accessories_side_to_center_dimension_l2': '',
          'dea_accessories_side_to_center_dimension_l2_value': '',
          'dea_accessories_side_to_center_dimension_l2_unit': '',
          'dea_accessories_pilot_on_cutter': '',
          'dea_accessories_pilot_on_cutter_value': '',
          'dea_accessories_pilot_on_cutter_unit': '',
          'dea_accessories_pin_on_clamp': '',
          'dea_accessories_pin_on_clamp_value': '',
          'dea_accessories_pin_on_clamp_unit': '',
          'dea_accessories_prehole_size': '',
          'dea_accessories_prehole_size_value': '',
          'dea_accessories_prehole_size_unit': '',
          'dea_accessories_template_strip': '',
          'dea_accessories_template_strip_value': '',
          'dea_accessories_template_strip_unit': '',
          'dea_accessories_locate_on_top_clamp': '',
          'dea_accessories_locate_on_bottom_clamp': '',
          'dea_cutter_information': '',
          'dea_cutter_information_dimensions': '',
          'dea_cutter_material': '',
          'dea_cutter_material_other': '',
          'dea_cutter_mounting_type': '',
          'dea_cutter_shank_diameter_units': '',
          'dea_cutter_shank_diameter_value': '',
          'dea_cutter_thread_units': '',
          'dea_cutter_thread_value': '',
          'dea_cutter_internal_thread_units': '',
          'dea_cutter_internal_thread_value': '',
          'dea_cutter_thread_length_units': '',
          'dea_cutter_thread_length_value': '',
          'dea_cutter_pilot_diameter_units': '',
          'dea_cutter_pilot_diameter_value': '',
          'dea_cutter_pilot_length_units': '',
          'dea_cutter_pilot_length_value': '',
          'dea_cutter_counterbore_diameter_units': '',
          'dea_cutter_counterbore_diameter_value': '',
          'dea_cutter_counterbore_depth_units': '',
          'dea_cutter_counterbore_depth_value': '',
          'dea_cutter_length_3_units': '',
          'dea_cutter_length_3_value': '',
          'dea_cutter_seat_angle_value': '',
          'dea_cutter_body_diameter_units': '',
          'dea_cutter_body_diameter_value': '',
          'dea_cutter_body_length_units': '',
          'dea_cutter_body_length_value': '',
          'dea_cutter_cutter_diameter_units': '',
          'dea_cutter_cutter_diameter_value': '',
          'dea_cutter_overall_length_units': '',
          'dea_cutter_overall_length_value': '',
          'dea_cutter_drill_countersink_drill_length_units': '',
          'dea_cutter_drill_countersink_drill_length_value': '',
          'dea_cutter_drill_countersink_drill_diameter_units': '',
          'dea_cutter_drill_countersink_drill_diameter_value': '',
          'dea_cutter_drill_countersink_body_diameter_units': '',
          'dea_cutter_drill_countersink_body_diameter_value': '',
          'dea_cutter_drill_countersink_angle_value': ''
        };
        $scope.user.apps.push(fields);
        $scope.active_app = $scope.user.apps.length - 1;
        $scope.saveAllApps($scope.user);
      } else {
        thisNum = index + 1;
        alert('Please fill in all required fields for Application ' + thisNum + '.');
      }
    }; //addApplication
  }); //applicationController
