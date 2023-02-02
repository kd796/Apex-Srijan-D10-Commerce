<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('register_globals', 0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

define('ADE_TRANSLATE', true);

if (!isset($_POST) || empty($_POST)) :
    $_POST = json_decode(file_get_contents('php://input'), true);
endif;

require_once('./cookie.php');
$userid = ade_get_cookie();

header('Content-Type: application/json');

if ($userid !== false) :
    require_once('./convert_variables.php');
    require_once('./Database.php');

    $db    = new DatabaseSel;
    $refer = $_POST['refer'];

    unset($_POST['refer']);

    switch ($refer) {
        case 'business':
        case 'general':
          unset($_POST['apps']);
          $object = $db->table('drilling_equipment')->update($_POST);
          break;

        case 'application':
          unset($_POST['show']);
          $_POST['dea_cookie'] = $userid;
          $load_obj = array('dea_cookie' => $userid, 'dea_application_number' => $_POST['dea_application_number']);
          $existing_obj = $db->table('drilling_equipment_app')->where_multi($load_obj, '=')->get();
          foreach ($existing_obj as $item) :
              //echo 'Need to delete item id: '.$item->dea_id."\n";
              $db->table('drilling_equipment_app')->delete('dea_id', '=', $item->dea_id);
          endforeach;
          unset($_POST['dea_id']);
          $object = $db->table('drilling_equipment_app')->insert($_POST);
          break;

        case 'saveAllApps':
          $delete_all_apps = $db->table('drilling_equipment_app')->delete('dea_cookie', '=', $userid);
          $num = 0;
          foreach ($_POST['apps'] as $app) :
              if (isset($app['show'])) : unset($app['show']); endif;
              if (!isset($app['dea_cookie'])) : $app['dea_cookie'] = $userid; endif;
              $app['dea_application_number'] = $num;
              //echo '<pre>',print_r($app,1),'</pre>';
              $insert_app = $db->table('drilling_equipment_app')->insert($app);
              $num++;
          endforeach;
          $object = $db->table('drilling_equipment')->where('de_cookie', '=', $userid)->first();
          $apps = $db->table('drilling_equipment_app')->where_sorted('dea_cookie', '=', $userid, 'dea_application_number', 'ASC')->get();
          $object->apps = $apps;
          break;

        case 'deleteApp':
          $load_obj = array('dea_cookie' => $userid, 'dea_application_number' => $_POST['dea_application_number']);
          $existing_obj = $db->table('drilling_equipment_app')->where_multi($load_obj, '=')->first();
          if (isset($existing_obj->dea_id)) :
              $dea_id = $existing_obj->dea_id;
              $delete = $db->table('drilling_equipment_app')->delete('dea_id', '=', $dea_id);
          endif;
          $object = $db->table('drilling_equipment')->where('de_cookie', '=', $userid)->first();
          $apps = $db->table('drilling_equipment_app')->where('dea_cookie', '=', $userid)->get();
          $object->apps = $apps;
          break;

        case 'accessories':
          foreach ($_POST['apps'] as $app) :
              $_POST['dea_cookie'] = $userid;
              $load_obj = array('dea_cookie' => $userid, 'dea_application_number' => $app['dea_application_number']);
              $existing_obj = $db->table('drilling_equipment_app')->where_multi($load_obj, '=')->get();
              foreach ($existing_obj as $item) :
              //echo 'Need to delete item id: '.$item->dea_id."\n";
              $db->table('drilling_equipment_app')->delete('dea_id', '=', $item->dea_id);
              endforeach;
              unset($app['dea_id']);
              $object = $db->table('drilling_equipment_app')->insert($app);
          endforeach;
          break;

        case 'solutions':
          $sorting =  array(
              'de_id' => $_POST['de_id'],
              'de_solution_issue_other_value' => $_POST['de_solution_issue_other_value'],
              'de_solution_issue_sortingLog' => $_POST['de_solution_issue_sortingLog'],
              'de_solution_details' => $_POST['de_solution_details']
          );
          $object = $db->table('drilling_equipment')->update($sorting);
          $to_emails = [
              'craig.wooley@apextoolgroup.com',
              'kevin.myhill@apextoolgroup.com',
              'dwayne.fisher@apextoolgroup.com',
              'alexis.colin@apextoolgroup.com',
              'emmanuel.fund@apextoolgroup.com',
          ];

          DatabaseSel::send_drilling_email($userid, $conversion_array, implode(', ', $to_emails));
          break;

        case 'colleague':
          $email = $_POST['colleague_email'];
          $custom_message = $_POST['brief_msg'];
          $from_name = $_POST['from_name'];
          $subject = $from_name . ' Sent You Their Apex Advanced Drilling Inquiry...';
          DatabaseSel::send_drilling_email($userid, $conversion_array, $email, $custom_message, $subject);
          $object = 'Email sent to colleague successfully.';
          break;
    }

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
