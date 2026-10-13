<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$application = \Bitrix\Main\Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();

if ($arItemsForm) {
	global $arGoods;
	$arGoods = [];
	$arProductsID = [];
	foreach($arItemsForm as $arItem) {
		if (!$arItem) continue;
		$arTmpItem = explode(',', $arItem);
		$arTmp = [];
		$key = rand(1,1000);
		foreach($arTmpItem as $value) {
			if (strpos($value, 'ID') !== false) {
				$key = trim(explode(':', $value)[1]);
				$arProductsID[] = $key;
			}
			if (strpos($value, GetMessage('T_HEAD_TITLE_NAME')) !== false) {
				$arTmp['NAME'] = trim(explode(':', $value)[1]);
			}
			if (strpos($value, GetMessage('T_HEAD_TITLE_QUANTITY')) !== false) {
				$arTmp['QUANTITY'] = trim(explode(':', $value)[1]);
			}
			if (strpos($value, GetMessage('T_HEAD_TITLE_SUMM')) !== false) {
				$arTmp['PRICE'] = trim(str_replace(' ', '', explode(':', $value)[1])/$arTmp['QUANTITY']);
			}
		}
		$arGoods[$key] = $arTmp;
	}
	if ($arProductsID) {
		$rsItems = CIBlockElement::GetList([], ['ID' => $arProductsID], false, false, ['ID', 'NAME', 'PROPERTY_PRINT_NAME']);
		while($arItem = $rsItems->Fetch()) {
			if ($arItem['PROPERTY_PRINT_NAME_VALUE']) {
				$arGoods[$arItem['ID']]['NAME'] = $arItem['PROPERTY_PRINT_NAME_VALUE'];
			}
		}
	}

}
?>
<?\Aspro\Allcorp3\Basket::getInstance()->clean();?>
<div class="confirm-order text-center">
	<?=TSolution::showIconSvg("basket", SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
	<h4 class="font_18 confirm-order__title"><?=GetMessage('T_CONFIRM_ORDER_TITLE');?></h4>

	<?if ($arResult['PAY_SYSTEM'] !== 'NOT'):?>
		<p class="font_15 color_666">
			<?$APPLICATION->IncludeComponent(
				"bitrix:main.include",
				"",
				Array(
					"AREA_FILE_SHOW" => "page",
					"AREA_FILE_SUFFIX" => "payment",
					"EDIT_TEMPLATE" => ""
				)
			);?>
		</p>
		<button class="btn btn-default btn-pay"><?=GetMessage('ORDER_PAY');?></button>
		<div class="text-left payment-info" <?=($_SERVER['REQUEST_METHOD'] === 'POST' ? 'style="display:block"' : '');?>>
			<?if ($arResult['PAY_SYSTEM'] === 'ASPRO_INVOICEBOX'):?>
				<?include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/payments/invoicebox.php')?>
			<?elseif ($arResult['PAY_SYSTEM'] === 'WEBFLY_SBRF'):?>
				<?include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/payments/sbrf.php')?>
			<?elseif($arResult['PAY_SYSTEM'] === 'ROVER_TINKOFF'):?>
				<?include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/payments/tinkoff.php')?>
			<?endif;?>
		</div>
	<?else:?>
		<p class="font_15 color_666">
			<?$APPLICATION->IncludeComponent(
				"bitrix:main.include",
				"",
				Array(
					"AREA_FILE_SHOW" => "page",
					"AREA_FILE_SUFFIX" => "inc",
					"EDIT_TEMPLATE" => ""
				)
			);?>
		</p>
		<div class="confirm-order__buttons line-block line-block--12 flexbox--inline">
			<div class="line-block__item">
				<a class="btn btn-default btn-transparent-border" href="<?=$arParams['PATH_TO_CATALOG'];?>">
					<?=GetMessage('T_HEAD_LINK_CATALOG');?>
				</a>
			</div>
			<div class="line-block__item">
				<a class="btn btn-default" href="<?=SITE_DIR;?>"><?=GetMessage('T_HEAD_LINK_MAIN');?></a>
			</div>
		</div>
	<?endif;?>
</div>

<script>

$(document).ready(function(){
	<?if(!isset($_SESSION['ORDERS'][$request['RESULT_ID']])):?>
		if (
			arAllcorp3Options['THEME']['YA_GOALS'] == 'Y' 
			&& arAllcorp3Options['THEME']['YA_COUNTER_ID'] 
		) {
			if (arAllcorp3Options['THEME']['USE_SALE_GOALS'] !== 'N') {
				const eventdata = {
					goal: 'goal_order_success',
				};
				BX.onCustomEvent('onCounterGoals', [eventdata]);
			}
			
			const eventdata = {
				goal: 'goal_webform_success',
			};
			if (arAllcorp3Options['THEME']['USE_FORMS_GOALS'] !== 'COMMON') {
				eventdata.goal += '_<?=$request['WEB_FORM_ID'] ?? $request['IBLOCK_ID'];?>';
			}
			BX.onCustomEvent('onCounterGoals', [eventdata]);
		}
		<?$_SESSION['ORDERS'][$request['RESULT_ID']] = $request['RESULT_ID'];?>
	<?endif;?>

	$('input[name="wf-fio"]').val('<?=$name;?>')
	$('input[name="wf-phone"]').val('<?=$phone;?>')
	$('input[name="wf-email"]').val('<?=$email;?>')

	if($('.basket_top').length){
		$.ajax({
			url: arAllcorp3Options['SITE_DIR'] + 'include/footer/site-basket.php',
			type: 'POST',
		}).success(function(html){
			$('.basket_top').html(html);
		});
	}
});
</script>
