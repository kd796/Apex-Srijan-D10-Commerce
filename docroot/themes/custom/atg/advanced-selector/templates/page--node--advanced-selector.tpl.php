<?php
	switch ($_SERVER["SERVER_NAME"]){
		case 'local.quakenbush.com':
			// $server_id = "_local";
			break;
		case 'quackenbushtools.is.starkmedia.com':
			// $server_id = "_is";
			break;
		case 'devquackenbushtools.apextoolgroup.com':
			// $server_id = "_cs";
			break;
		default:
			// $server_id = "";
			break;
	}
?>
<?php print render($page['header']); ?>
<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/javascript.php"); ?>
<?php
	render($page['content'])
?>
	<script type="text/javascript" src="/sites/all/themes/apexbase/js/vendor/featherlight.min.js"></script>
	<script type="text/javascript">
		!function(t){function o(o,e){if(!(o.originalEvent.touches.length>1)){var n=o.originalEvent.changedTouches[0],u=document.createEvent("MouseEvents");t(n.target).is("input")||t(n.target).is("textarea")?o.stopPropagation():o.preventDefault(),u.initMouseEvent(e,!0,!0,window,1,n.screenX,n.screenY,n.clientX,n.clientY,!1,!1,!1,!1,0,null),o.target.dispatchEvent(u)}}if(t.support.touch="ontouchend"in document,t.support.touch){var e,n=t.ui.mouse.prototype,u=n._mouseInit;n._touchStart=function(t){var n=this;!e&&n._mouseCapture(t.originalEvent.changedTouches[0])&&(e=!0,n._touchMoved=!1,o(t,"mouseover"),o(t,"mousemove"),o(t,"mousedown"))},n._touchMove=function(t){e&&(this._touchMoved=!0,o(t,"mousemove"))},n._touchEnd=function(t){e&&(o(t,"mouseup"),o(t,"mouseout"),this._touchMoved||o(t,"click"),e=!1)},n._mouseInit=function(){var o=this;o.element.bind("touchstart",t.proxy(o,"_touchStart")).bind("touchmove",t.proxy(o,"_touchMove")).bind("touchend",t.proxy(o,"_touchEnd")),u.call(o)}}}(jQuery);
    </script>
    <script type="text/javascript">
	$(function() {
		$('.subnav').find('h4').click(function(){
			$(this).next('ul').slideToggle();
			$(this).toggleClass('closed-heading');
			return false;
		});
		$('.toggle li').click(function(){
			$(this).toggleClass('check');
			return false;
		});
		$( "#sortable" ).sortable();
	});
	</script>
	<style>
		#page {
			width: auto !important;
		}
	</style>

<div id="page">
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/header.php"); ?>
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/navigation.php"); ?>
	<section class="interior" >
		<div class="ade contain clearfix">
			<div class="grid-12">
				<div class="sel-logo right">
					<a href="/"><img src="/sites/all/themes/quackenbush/advanced-selector/pics/recoules-quackenbush.png" width="300" alt="Recoules Quackenbush Power Tools" border="0" /></a>
				</div>
				<h1>Advanced Drilling Equipment Inquiry</h1>
				<p>Application request for quotation only, this is not an order.</p>
				<p><em><span class="orange">*</span> Required fields.</em></p>
				<div class="sel-ade" ng-view ng-cloak>

				</div>
			</div>
		</div>

	</section>
	<?php include_once ($_SERVER['DOCUMENT_ROOT']. "/sites/all/themes/quackenbush/templates/include/footer.php"); ?>
</div>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/jquery.validate.js"></script>
<script>
	$(document).ready(function(){
		$("#businessform").validate();
		$("#emailform").validate();
	});
</script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/angular.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/angular-route.min.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/angular-resource.min.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/ngStorage.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/angular-sanitize.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-sortable/0.13.3/sortable.min.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/js/vendor/sortable.js"></script>

<script src="/sites/all/themes/quackenbush/advanced-selector/app/app.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/app/controllers/businessController.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/app/controllers/applicationController.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/app/controllers/accessoriesController.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/app/controllers/solutionController.js"></script>
<script src="/sites/all/themes/quackenbush/advanced-selector/app/controllers/completeController.js"></script>

