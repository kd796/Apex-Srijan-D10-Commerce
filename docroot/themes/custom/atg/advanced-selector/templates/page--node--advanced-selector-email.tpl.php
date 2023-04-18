<?php
?>
<?php print render($page['header']); ?>
<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/javascript.php"); ?>
<?php
	render($page['content'])
?>

<div id="page" ng-app="spindleApp">
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/header.php"); ?>
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/navigation.php"); ?>
	<section class="interior" ng-controller="expanderBusinessController">
		<div class="expander contain clearfix">
			<div class="grid-12">
				<div class="sel-logo right">
					<a href="/"><img src="/sites/all/themes/quackenbush/advanced-selector/pics/recoules-quackenbush.png" width="300" alt="Recoules Quackenbush Power Tools" border="0" /></a>
				</div>
				<h1>Apex Advanced Drilling Equipment Inquiry</h1>
				<p>Email sent to colleague successfully. </p>

			</div>
		</div>
	</section>
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/footer.php"); ?>
</div>

