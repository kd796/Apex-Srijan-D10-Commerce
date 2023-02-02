<?php

/**
 * @file
 * Default theme implementation to display the basic html structure of a single
 * Drupal page.
 *
 * Variables:
 * - $css: An array of CSS files for the current page.
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or 'rtl'.
 * - $rdf_namespaces: All the RDF namespace prefixes used in the HTML document.
 * - $grddl_profile: A GRDDL profile allowing agents to extract the RDF data.
 * - $head_title: A modified version of the page title, for use in the TITLE
 *   tag.
 * - $head_title_array: (array) An associative array containing the string parts
 *   that were used to generate the $head_title variable, already prepared to be
 *   output as TITLE tag. The key/value pairs may contain one or more of the
 *   following, depending on conditions:
 *   - title: The title of the current page, if any.
 *   - name: The name of the site.
 *   - slogan: The slogan of the site, if any, and if there is no title.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $page_top: Initial markup from any modules that have altered the
 *   page. This variable should always be output first, before all other dynamic
 *   content.
 * - $page: The rendered page content.
 * - $page_bottom: Final closing markup from any modules that have altered the
 *   page. This variable should always be output last, after all other dynamic
 *   content.
 * - $classes String of classes that can be used to style contextually through
 *   CSS.
 *
 * @see template_preprocess()
 * @see template_preprocess_html()
 * @see template_process()
 *
 * @ingroup themeable
 */


    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    if (!isset($_COOKIE['userid'])) :
        setcookie('userid', $_SERVER['UNIQUE_ID'], time() + (60 * 60 * 6), '/', $_SERVER['SERVER_NAME']);
        header('Location: /tools/advanced-drilling/');
    endif;

?>

<?php
	global $language;
	$lang_name = $language->language;
	$globalid = i18n_variable_get('apexconfiguration_global_item_node_id', $lang_name) ;
	$globalnode = node_load($globalid);
	if($globalnode)
	{
		$field_additional_stylesheet = apexbase_getFieldValue($globalnode,"field_additional_stylesheet","uri");
		$field_usability_code = apexbase_getFieldValue($globalnode,"field_usability_code","value");
	}
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" ng-app="Cleco"> <!--<![endif]-->


<head>
  <?php print $head; ?>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <meta name="viewport" content="initial-scale=0.85, maximum-scale=0.85">
  <title><?php print $head_title; ?></title>
  <?php print $styles; ?>

  <?php
		if(isset($field_additional_stylesheet) && $field_additional_stylesheet !== ""){
	?>
			<link type="text/css" rel="stylesheet" href="<?php echo file_create_url($field_additional_stylesheet) ?>" media="all" />
	<?php
		}
	?>

  <?php print $scripts; ?>


</head>
<body class="<?php print $classes; ?>" <?php print $attributes;?>>

  <?php print $page_top; ?>
  <?php print $page; ?>
  <?php print $page_bottom; ?>

  <?php
		if(isset($field_usability_code) && $field_usability_code !== ""){
			 echo $field_usability_code;
		}
	?>

</body>
</html>
