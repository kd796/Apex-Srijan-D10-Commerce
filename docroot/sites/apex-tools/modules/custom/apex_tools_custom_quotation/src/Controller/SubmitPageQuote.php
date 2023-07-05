<?php

namespace Drupal\apex_tools_custom_quotation\Controller;

use Drupal\Core\Controller\ControllerBase;
/**
* Provides route responses for the quotation submit page.
*/
class SubmitPageQuote extends ControllerBase {
 /**
  * Returns a simple message page.
  *
  * @return array
  *   A simple renderable array.
  */
  public function submitPageData() {
    $query = \Drupal::request()->query->get('quotation');
    $file_url = '/print/pdf/quotation/'.$query;
    $content['raw_markup'] = array (
      '#type' => 'markup',
      '#markup' =>
        '<div class="Submitted-content">
        <div class="title"><h2>Thank you for your interest in Apex Custom Solutions</h2></div>
        <p>You have successfully submitted your request. We will get back to you shortly with your custom solution quote.</p>
        <p>In case of any questions, please Email us at SpecialQuote@apextoolgroup.com or call us at 1-866-569-9449. In the meantime, you can download your results or forward on to a colleague.</p>
        <a href="'.$file_url.'" class="">Download</a>
        </div>'
    );
    return $content;
  }
}