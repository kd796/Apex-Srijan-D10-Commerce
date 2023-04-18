<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set("register_globals", 0);
use \Drupal\Core\File\FileSystemInterface;

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

//$dotenv = new Dotenv\Dotenv(realpath(__DIR__));
//$dotenv->load();

class DatabaseSel
{
    protected $table;
    protected $stmt;

    protected $db_host = '';
    protected $db_user = '';
    protected $db_pass = '';
    protected $db_name = '';

    public $debug = true;

    public function __construct()
    {
        switch ($_SERVER['SERVER_NAME']) {
            case 'www.clecotools.com':
            case 'www.clecotools.de':
            case 'www.clecotools.co.uk':
            // EN.
            case 'dev-www.clecotools.com':
            case 'qa-www.clecotools.com':
            case 'stg-www.clecotools.com':
            case 'prod-www.clecotools.com':
            // DE.
            case 'dev-www.clecotools.de' :
            case 'qa-www.clecotools.de' :
            case 'stg-www.clecotools.de' :
            case 'prod-www.clecotools.de' :
            // GB.
            case 'dev-www.clecotools.co.uk' :
            case 'qa-www.clecotools.co.uk' :
            case 'stg-www.clecotools.co.uk' :
            case 'prod-www.clecotools.co.uk' :
                $this->db_host = $_ENV['AS_DB_HOST'] ?? getenv('AS_DB_HOST');
                $this->db_user = $_ENV['AS_DB_USER'] ?? getenv('AS_DB_USER');
                $this->db_pass = $_ENV['AS_DB_PSWD'] ?? getenv('AS_DB_PSWD');
                $this->db_name = $_ENV['AS_DB_NAME'] ?? getenv('AS_DB_NAME');
                break;
            case 'clecotools.ddev.site':
                $this->db_host = 'db';
                $this->db_user = 'db';
                $this->db_pass = 'db';
                $this->db_name = 'drupal_selector';
                break;
            default:
                $this->db_host = '';
                $this->db_user = '';
                $this->db_pass = '';
                $this->db_name = '';
                break;
        }

        try {
            $this->pdo = new PDO("mysql:host={$this->db_host};dbname={$this->db_name}", $this->db_user, $this->db_pass, []);
            if ($this->debug) {
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            die($this->debug ? $e->getMessage() : '');
        }
    }

    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    public function insert($data)
    {
        $keys = array_keys($data);
        // $fields = '`' . implode('`, `', $keys) . '`';
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $this->stmt = $this->pdo->prepare($sql);
        $this->stmt->execute($data);

        return ['id' => $this->pdo->lastInsertId()];
    }

    public function toggle($data)
    {
        $keys = array_keys($data);
        $sql_str = ' SET ';
        foreach ($keys as $key) :
            if ($key != 'id' && substr($key, -3) != '_id') :
                if ($data[$key] != null) :
                    $sql_str .= ' ' . $key . ' = NOT ' . $key;
                else :
                    unset($data[$key]);
                endif;
            else :
                $this_id = $data[$key];
                unset($data[$key]);
                $id_var = substr($key, -3) == '_id' ? $key : 'id';
            endif;
        endforeach;
        $sql_str .= " WHERE {$id_var} = {$this_id}";
        $sql = "UPDATE {$this->table} {$sql_str} "; //echo $sql;print_r($data);exit;
        $this->stmt = $this->pdo->prepare($sql);

        return $this->stmt->execute();
    }

    public function update($data)
    {
        $keys = array_keys($data);
        $sql_str = ' SET ';

        foreach ($keys as $key) :
            if ($key != 'id' && substr($key, -3) != '_id') :
                $sql_str .= ' ' . $key . '=:' . $key . ',';
            else :
                $this_id = $data[$key];
                unset($data[$key]);
                $id_var = substr($key, -3) == '_id' ? $key : 'id';
            endif;
        endforeach;

        //$bits = explode('_', $key);$prefix = $bits[0];
        $sql_str = substr($sql_str, 0, -1);

        if (isset($this_id)) :
            $sql_str .= " WHERE {$id_var} = {$this_id}";
        endif;

        $sql = "UPDATE {$this->table} {$sql_str} "; //echo $sql;print_r($data);exit;
        $this->stmt = $this->pdo->prepare($sql);

        return $this->stmt->execute($data);
    }

    public function where($field, $operator, $value)
    {
        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$field} {$operator} :value");
        $this->stmt->execute(['value' => $value]);

        return $this;
    }

    public function where_multi($data, $operator)
    {
        $fields = array_keys($data);
        $chunk = '';

        foreach ($fields as $field) :
            $chunk .= $field . ' ' . $operator . ' :' . $field . ' AND ';
        endforeach;

        $chunk = substr($chunk, 0, strlen($chunk) - 4);
        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$chunk} ");
        $this->stmt->execute($data);

        return $this;
    }

    public function where_multi_genre($data, $operator)
    {
        $fields = array_keys($data);
        $chunk = '';

        foreach ($fields as $field) :
            $chunk .= $field . ' ' . $operator . ' :' . $field . ' AND ';
        endforeach;

        $chunk = substr($chunk, 0, strlen($chunk) - 4);
        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} INNER JOIN related_genre ON rg_related_artist_fk = f_artist_id WHERE {$chunk} GROUP BY rg_name ORDER BY rg_name ");
        $this->stmt->execute($data);

        return $this;
    }

    public function where_sorted($field, $operator, $value, $sort_field, $sort_dir = 'ASC')
    {
        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$field} {$operator} :value ORDER BY {$sort_field} {$sort_dir} ");
        $this->stmt->execute(['value' => $value]);

        return $this;
    }

    public function delete($field, $operator, $value)
    {
        $this->stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$field} {$operator} :value");
        $this->stmt->execute(['value' => $value]);

        return $this;
    }

    public function listAll()
    {
        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ");
        $this->stmt->execute();

        return $this;
    }

    public function retrieve($sort_field, $sort_dir = 'ASC', $start = 0, $total = 10, $groupby = '')
    {
        if (strlen($groupby)) :
            $groupby = 'GROUP BY ' . $groupby;
        endif;

        $this->stmt = $this->pdo->prepare("SELECT * FROM {$this->table} {$groupby} ORDER BY {$sort_field} {$sort_dir} LIMIT {$start}, {$total}  ");
        $this->stmt->execute();

        return $this;
    }

    public function count()
    {
        $obj = $this->get();

        return count($obj);
    }

    public function exists($data)
    {
        $field = array_keys($data);
        $field = $field[0];

        return $this->where($field, '=', $data[$field])->count() ? 1 : 0;
    }

    public function exists_multi($data)
    {
        return $this->where_multi($data, '=')->count() ? 1 : 0;
    }

    public function get($skip_fields = [])
    {
        $obj = $this->stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($obj as $item) :
            foreach ($skip_fields as $field) :
                unset($item->$field);
            endforeach;
        endforeach;

        return $obj;
    }

    public function first()
    {
        $obj = $this->get();

        return count($obj) ? $obj[0] : false;
    }

    public static function translate($string = '')
    {
        $translated                = $string;
        $translations              = require(__DIR__ . '/translations.php');
        $translations_language     = $translations['language'];
        $translations_translations = $translations['translations'];

        if (!is_numeric($string) && !empty($string) && $translations_language != 'en') {
            if (array_key_exists($string, $translations_translations) && array_key_exists($translations_language, $translations_translations[$string])) {
                $translated = $translations_translations[$string][$translations_language];
            }
        }
        return $translated;
    }

    public static function export_pdf($id, $conversion_array)
    {
        $db   = new DatabaseSel;
        $data = $db->table('drilling_equipment')->where('de_cookie', '=', $id)->first();
        $apps = $db->table('drilling_equipment_app')->where_sorted('dea_cookie', '=', $id, 'dea_application_number', 'ASC')->get();

        $ignore_keys = [
            'de_id',
            'de_date_modified',
            'de_date_created',
            'de_sort_order',
            'de_active_flag',
            'de_cookie',
            'dea_id', 'dea_date_modified',
            'dea_date_created',
            'dea_sort_order',
            'dea_active_flag',
            'dea_cookie',
            'dea_application_number'
        ];

        $cclamp__ignore_keys = [
            'dea_accessories_pilot_on_cutter',
            'dea_accessories_pilot_on_cutter_value',
            'dea_accessories_pilot_on_cutter_unit',
            'dea_accessories_pin_on_clamp',
            'dea_accessories_pin_on_clamp_value',
            'dea_accessories_pin_on_clamp_unit'
        ];

        $template_foot__ignore_keys = [
            'dea_accessories_twistlock_21000_series',
            'dea_accessories_twistlock_22000_series',
            'dea_accessories_twistlock_23000_series',
            'dea_accessories_twistlock_24000_series',
            'dea_accessories_twistlock_other_value',
            'dea_accessories_jig_mounting_hole_diameter',
            'dea_accessories_jig_mounting_hole_diameter_value',
            'dea_accessories_jig_mounting_hole_diameter_unit',
            'dea_accessories_jig_thickness',
            'dea_accessories_jig_thickness_value',
            'dea_accessories_jig_thickness_unit',
            'dea_accessories_chip_clearance',
            'dea_accessories_chip_clearance_value',
            'dea_accessories_chip_clearance_unit'
        ];

        $twistlock__ignore_keys = [
            'dea_accessories_template_strip_thickness',
            'dea_accessories_template_strip_thickness_value',
            'dea_accessories_template_strip_thickness_unit',
            'dea_accessories_template_hole_diameter',
            // 'dea_accessories_template_hole_diameter_value',
            'dea_accessories_template_hole_diameter_unit',
        ];

        $concentric_collet__ignore_keys = [
      'dea_accessories_template_strip_thickness',
            'dea_accessories_template_strip_thickness_unit',
            'dea_accessories_template_strip_thickness_value',
            'dea_accessories_template_hole_diameter_unit',
            // 'dea_accessories_template_hole_diameter_value',
            'dea_accessories_template_hole_diameter',
            'dea_accessories_twistlock_21000_series',
            'dea_accessories_twistlock_22000_series',
            'dea_accessories_twistlock_23000_series',
            'dea_accessories_twistlock_24000_series',
            'dea_accessories_twistlock_other_value'
        ];

        // $acc_keys      = [];
        $form_title    = 'Apex Advanced Drilling Inquiry Submission';
        $email         = make_safe_i($data->de_email_address);
        $fullname      = make_safe_i($data->de_contact_name);
        $to_emails = [
            'craig.wooley@apextoolgroup.com',
            'kevin.myhill@apextoolgroup.com',
            'dwayne.fisher@apextoolgroup.com',
            'alexis.colin@apextoolgroup.com',
            'emmanuel.fund@apextoolgroup.com',
        ];
        $to_email      = implode(', ', $to_emails);
        $from_email    = 'no-reply@apextoolgroup.com';
        $solution_html = "<tr><th class='boldtext' colspan='2'>Solution Issues</th></tr>";
        $html          = "
    <html>
    <head>
      <title>$form_title</title>
      <style type='text/css'>
            td, th {
                background: white;
                color: #666666;
                font-family: Arial,Helvetica,Sans-Serif;
                font-size: 16px;
                line-height: 21px;
                vertical-align: top;
            }
            th {
                background:#eeeeee;
                color: #222222;
                font-size: 16px;
                text-transform: uppercase;
                padding: 10px;
            }
            .headline {
                font-size: 28px;
                text-align: center;
                padding: 0.25em 0 0.5em;
            }
            .boldtext {
                font-weight:bold;
            }
        </style>
    </head>
    <body>
        <table cellpadding='0' cellspacing='15' width='600'><tr><td class='headline' colspan='2' align='center'><font size='2'>Apex Advanced Drilling Inquiry Results</font></td></tr><tr><th class='boldtext' colspan='2'>" . self::translate('Business Information') . "</th></tr>
        ";

        foreach ($data as $key => $value) :
            if (in_array($key, $ignore_keys) || strlen($value) == 0) :
                continue;
            endif;

            if (substr($key, -7) == '_option') :
              $key = substr($key, 0, strlen($key) - 7);
            endif;

            // if (in_array($key, $acc_keys)) :
            //     if (strlen($value)) :
            //         $value = 'Yes';
            //     endif;
            // endif;

            $value = str_replace(' amp ', ' & ', trim($value));

            if (substr($key, 0, 11) == 'de_solution') :
                $solution_html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . self::translate($conversion_array[$key]) . "</td>
                        <td width='400'>" . self::translate($value) . "</td>
                    </tr>
                ";
            else :
                $html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . self::translate($conversion_array[$key]) . "</td>
                        <td width='400'>" . self::translate($value) . "</td>
                    </tr>
                ";
            endif;
        endforeach;

        $app_num = 0;
        $appFieldsArray = [
            'dea_accessories_jig_mounting_hole_diameter_value',
            'dea_accessories_jig_thickness_value',
            'dea_accessories_chip_clearance_value'
        ];

        foreach ($apps as $app) :
            $app_num++;
            $html .= "<tr><th class='boldtext' colspan='2'>" . self::translate('Application') . " #{$app_num}" . "</th></tr>";

            foreach ($app as $key => $value) :
                if ($key == 'dea_accessories_cutter') : $html .= "<tr><th class='boldtext' colspan='2'>" . self::translate('Application') . " #{$app_num} " . self::translate('Accessories') . "</th></tr>";
                endif;

                if (in_array($key, $ignore_keys)) :
                    continue;
                endif;

                if ($app->dea_fixture_being_used === 'Twistlock' && in_array($key, $twistlock__ignore_keys)) :
                    continue;
                endif;

                if ($app->dea_fixture_being_used === 'Template Foot' && in_array($key, $template_foot__ignore_keys)) :
                    continue;
                endif;

                if ($app->dea_fixture_being_used === 'Concentric Collet' && in_array($key, $concentric_collet__ignore_keys)) :
                    continue;
                endif;

                if ($app->dea_fixture_being_used === 'C Clamp' && in_array($key, $cclamp__ignore_keys)) :
                    continue;
                endif;

                if (strlen($value) == 0) :
                    continue;
                endif;

                $value = str_replace(' amp ', ' & ', trim($value));

                if ($value == '1' && !in_array($key, $appFieldsArray)) : $value = 'Yes';
                endif;

                $html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . self::translate($conversion_array[$key]) . "</td>
                        <td width='400'>" . self::translate($value) . "</td>
                    </tr>
                ";
            endforeach;
        endforeach;

        //Add Solution Issues Information
        $html .= $solution_html;

        $html .= "
                <tr><td colspan='2' align='center' style='border-top: 2px solid #ddd;'><br>Copyright &copy;" . date("Y") . " Apex Tool Group, LLC. All Rights Reserved.</td></tr>
            </table>
        </body>
        </html>";

        $file_system = \Drupal::service('file_system');
        $tmpDir = $file_system->realpath(\Drupal::config('system.file')->get('default_scheme') . '://') . '/advanced_selector/';
        if (!$file_system->getDestinationFilename($tmpDir, FileSystemInterface::EXISTS_ERROR) === false) {
          $file_system->mkdir($tmpDir, 0755);
        }

        if (!defined('_MPDF_TTFONTDATAPATH')) {
            define('_MPDF_TTFONTDATAPATH', $tmpDir);
        }

      try {
        $mpdf = new \Mpdf\Mpdf([
          'tempDir' => $tmpDir, // uses the current directory's parent "tmp" subfolder
          'setAutoTopMargin' => 'stretch',
          'setAutoBottomMargin' => 'stretch'
        ]);
      } catch (\Mpdf\MpdfException $e) {
        \Drupal::logger('Advanced Drilling')->error("Creating an mPDF object failed with" . $e->getMessage());
      }

        $mpdf->WriteHTML($html);

        $filename = implode('-', [str_replace(' ', '-', $fullname), 'advanced-drilling-inquiry', time()]) . '.pdf';

        $mpdf->Output($tmpDir . $filename, 'F');

        return $filename;
    }

    public static function send_drilling_email($id, $conversion_array, $to_email, $custom_message = '', $subject = '')
    {
        $db = new DatabaseSel;
        $data = $db->table('drilling_equipment')->where('de_cookie', '=', $id)->first();
        $apps = $db->table('drilling_equipment_app')->where_sorted('dea_cookie', '=', $id, 'dea_application_number', 'ASC')->get();

        $ignore_keys = [
            'de_id',
            'de_date_modified',
            'de_date_created',
            'de_sort_order',
            'de_active_flag',
            'de_cookie',
            'dea_id',
            'dea_date_modified',
            'dea_date_created',
            'dea_sort_order',
            'dea_active_flag',
            'dea_cookie',
            'dea_application_number'
        ];
        // $acc_keys      = [];
        $form_title    = 'Apex Advanced Drilling Inquiry Submission';
        if (strlen($subject)) :
          $form_title = $subject;
        endif;
        $email         = make_safe_i($data->de_email_address);
        $fullname      = make_safe_i($data->de_contact_name);
        $from_email    = 'no-reply@apextoolgroup.com';
        $solution_html = "<tr><th class='boldtext' colspan='2'>" . self::translate('Solution Issues') . "</th></tr>";
        $html          = "
    <html>
    <head>
      <title>$form_title</title>
      <style type='text/css'>
            td, th {
                background: white;
                color: #666666;
                font-family: Arial,Helvetica,Sans-Serif;
                font-size: 16px;
                line-height: 21px;
                vertical-align: top;
            }
            th {
                background:#eeeeee;
                color: #222222;
                font-size: 16px;
                text-transform: uppercase;
                padding: 10px;
            }
            .headline {
                font-size: 28px;
                text-align: center;
                padding: 0.25em 0 0.5em;
            }
            .boldtext {
                font-weight:bold;
            }
        </style>
    </head>
    <body>
        <table cellpadding='0' cellspacing='15' width='600'><tr><td class='headline' colspan='2' align='center'><font size='2'>Apex Advanced Drilling Inquiry Results</font></td></tr>";
        if (strlen($custom_message)) : $html .= "<tr><td colspan='2'>" . $custom_message . "</td></tr>";
        endif;
        $html .= "<tr><th class='boldtext' colspan='2'>" . self::translate('Business Information') . "</th></tr>";
        foreach ($data as $key => $value) :
            if (in_array($key, $ignore_keys) || strlen($value) == 0) :
                continue;
            endif;

            if (substr($key, -7) == '_option') :
                $key = substr($key, 0, strlen($key) - 7);
            endif;

            // if (in_array($key, $acc_keys)) :
            //     if (strlen($value)) :
            //         $value = 'Yes';
            //     endif;
            // endif;

            $value = str_replace(' amp ', ' & ', trim($value));

            if (substr($key, 0, 11) == 'de_solution') :
                $solution_html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . $conversion_array[$key] . "</td>
                        <td width='400'>$value</td>
                    </tr>
                ";
            else :
                $html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . $conversion_array[$key] . "</td>
                        <td width='400'>$value</td>
                    </tr>
                ";
            endif;
        endforeach;

        $app_num = 0;
        $appFieldsArray = [
            'dea_accessories_jig_mounting_hole_diameter_value',
            'dea_accessories_jig_thickness_value',
            'dea_accessories_chip_clearance_value'
        ];
        foreach ($apps as $app) :
            $app_num++;
            $html .= "<tr><th class='boldtext' colspan='2'>Application #{$app_num}</th></tr>";
            foreach ($app as $key => $value) :
                if ($key == 'dea_accessories_cutter') :
                  $html .= "<tr><th class='boldtext' colspan='2'>Application #{$app_num} Accessories</th></tr>";
                endif;

                if (in_array($key, $ignore_keys)) :
                  continue;
                endif;

                if (strlen($value) == 0) :
                  continue;
                endif;

                $value = str_replace(' amp ', ' & ', trim($value));

                // if ($value == '1' && !in_array($key, $appFieldsArray)) :
                //   $value = 'Yes';
                // endif;

                $html .= "
                    <tr>
                        <td width='200' class='boldtext'>" . $conversion_array[$key] . "</td>
                        <td width='400'>$value</td>
                    </tr>
                ";
            endforeach;
        endforeach;

        //Add Solution Issues Information
        $html .= $solution_html;

        $html .= "
                <tr><td colspan='2' align='center' style='border-top: 2px solid #ddd;'><br>Copyright &copy;" . date("Y") . " Apex Tool Group, LLC. All Rights Reserved.</td></tr>
            </table>
        </body>
        </html>";
      try {
        $smtp_username = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
        $smtp_password = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');

        $transport = new Swift_SmtpTransport('smtp.office365.com', 587);
        $transport->setUsername($smtp_username);
        $transport->setPassword($smtp_password);
        $transport->setLocalDomain('127.0.0.1');
        $transport->setEncryption('tls');

        $mailer = new Swift_Mailer($transport);
        // Create a new message.
        $message = new Swift_Message($subject, $html, 'text/html');
        $message->setFrom([$from_email]);
        $message->setTo($to_email);

        $mailer->send($message);
      }
      catch (\Exception $e) {
        echo "Error";
        return FALSE;
      }

      return '|success|';
    }

}
