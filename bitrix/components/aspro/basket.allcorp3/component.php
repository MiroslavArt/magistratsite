<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once( $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/vendor/php/solution.php');

if (!CModule::IncludeModule('iblock')) {
	ShowError(GetMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}
if (!class_exists('TSolution') || !CModule::IncludeModule(TSolution::moduleID)) {
	ShowError(GetMessage('ASPRO_MODULE_NOT_INSTALLED'));
	return;
}

$arModuleOptions = TSolution::GetFrontParametrsValues(SITE_ID);
$bUseBasket = ($arModuleOptions['ORDER_VIEW'] === 'Y');

$arParams['PATH_TO_BASKET'] = $pageBasket = trim($arParams['PATH_TO_BASKET'] ?? $arModuleOptions['BASKET_PAGE_URL'] ?? '');
$arParams['PATH_TO_ORDER'] = $pageOrder = trim($arParams['PATH_TO_ORDER'] ?? $arModuleOptions['ORDER_PAGE_URL'] ?? '');
$arParams['PATH_TO_CATALOG'] = $pageCatalog = trim($arParams['PATH_TO_CATALOG'] ?? $arModuleOptions['CATALOG_PAGE_URL'] ?? '');
$arParams['SHOW_BASKET_PRINT'] = $arParams['SHOW_BASKET_PRINT'] ?? $arModuleOptions['SHOW_BASKET_PRINT'];

$isBasketPage = TSolution::IsBasketPage($pageBasket);
$isOrderPage = TSolution::IsOrderPage($pageOrder);

if(!$bUseBasket){
	if($arParams['SHOW_404'] !== 'N'){
		TSolution::goto404Page();

		return;
	}
}

global $USER;
$userID = $USER->GetID();
$userID = ($userID > 0 ? $userID : 0);

$arResult = array(
	'ITEMS' => array(),
	'ITEMS_COUNT' => 0,
	'ITEMS_SUMM' => 0,
	'ITEMS_SUMM_WD' => 0,
	'USE_BASKET' => $bUseBasket ? 'Y' : 'N',
	'IS_BASKET_PAGE' => $isBasketPage ? 'Y' : 'N',
	'IS_ORDER_PAGE' => $isOrderPage ? 'Y' : 'N',
	'USER_ID' => $userID,
	'PAY_SYSTEM' => $arModuleOptions['PAY_SYSTEM'],
);

if($bUseBasket){
	if(
		$arParams['HIDE_ON_CART_PAGE'] !== 'Y' ||
		(
			!$isBasketPage &&
			!$isOrderPage
		)
	){
		$arSessionItems = \Aspro\Allcorp3\Basket::getInstance()->items;
		$summ = $summ_wd = 0;
		
		foreach($arSessionItems as $arItem){
			if(
				!($arItem['ID']) ||
				!strlen($arItem['NAME'])
			){
				continue;
			}
		
			$arItem['PICTURE'] = strlen($arItem['PREVIEW_PICTURE'] ?? '')
				? $arItem['PREVIEW_PICTURE'] 
				: (strlen($arItem['DETAIL_PICTURE'] ?? '') ? $arItem['DETAIL_PICTURE'] : '');
			if (
				(!$arItem['PICTURE'] || $arItem['PICTURE'] === 'false')
				&& TSolution::GetFrontParametrValue('REPLACE_NOIMAGE_WITH_SECTION_PICTURE') === 'Y'
				&& $arItem['IBLOCK_ID'] == TSolution::GetFrontParametrValue('CATALOG_IBLOCK_ID')
			) {
				$arElement = \Bitrix\Iblock\ElementTable::getList([
					'select' => array('IBLOCK_SECTION_ID'),
					'filter' => array('ID' => $arItem['ID'], 'IBLOCK_ID' => $arItem['IBLOCK_ID']),
				])->Fetch();
				if ($arElement['IBLOCK_SECTION_ID']) {
					$arSection = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ["MULTI" =>"N", "TAG" => TSolution\Cache::GetIBlockCacheTag($arItem['IBLOCK_ID'])]], ["IBLOCK_ID" => $arItem['IBLOCK_ID'], 'ID' => $arElement['IBLOCK_SECTION_ID']], false, ['ID', 'PICTURE']);
					if ($arSection['PICTURE']) {
						$arItem['PICTURE'] = $arSection['PICTURE'];
					}
				}
			}
			
			if ($arItem['PICTURE']) {
				$arItem['PICTURE'] = CFile::GetFileArray($arItem['PICTURE']);
				$arItem['PICTURE']['IMAGE_70'] = CFile::ResizeImageGet($arItem['PICTURE']['ID'], array('width' => 70, 'height' => 70), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true );
				$arItem['PICTURE']['IMAGE_110'] = CFile::ResizeImageGet($arItem['PICTURE']['ID'], array('width' => 110, 'height' => 110), BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true );
			}
		
			$arItem['PROPERTY_STATUS'] = CIBlockPropertyEnum::GetByID($arItem['PROPERTY_STATUS_VALUE']);
		
			if (strlen(trim($arItem['PROPERTY_PRICE_VALUE']))) {
				if ($arItem['PROPERTY_PRICE_CURRENCY_VALUE']) {
					$arItem['PROPERTY_PRICE_VALUE'] = str_replace('#CURRENCY#', $arItem['PROPERTY_PRICE_CURRENCY_VALUE'], $arItem['PROPERTY_PRICE_VALUE']);
				}
		
				$arItem['PROPERTIES']['PRICE']['VALUE'] = $arItem['PROPERTY_PRICE_VALUE'];
		
				$arItem['SUMM'] = TSolution::FormatSumm($arItem['PROPERTY_FILTER_PRICE_VALUE'], $arItem['QUANTITY']);
				$summ += floatval(str_replace(' ', '', $arItem['PROPERTY_FILTER_PRICE_VALUE'])) * $arItem['QUANTITY'];
			}
		
			if (strlen(trim($arItem['PROPERTY_PRICEOLD_VALUE']))) {
				if ($arItem['PROPERTY_PRICE_CURRENCY_VALUE']) {
					$arItem['PROPERTY_PRICEOLD_VALUE'] = str_replace('#CURRENCY#', $arItem['PROPERTY_PRICE_CURRENCY_VALUE'], $arItem['PROPERTY_PRICEOLD_VALUE']);
				}

				$arItem['PROPERTIES']['PRICEOLD']['VALUE'] = $arItem['PROPERTY_PRICEOLD_VALUE'];

				$arItem['SUMM_WD'] = TSolution::FormatSumm($arItem['PROPERTY_PRICEOLD_VALUE'], $arItem['QUANTITY']);
			}

			$summ_wd += strlen(trim($arItem['PROPERTY_PRICEOLD_VALUE'])) ? (floatval(str_replace(' ', '', $arItem['PROPERTY_FILTER_PRICE_VALUE'])) * $arItem['QUANTITY']) : (strlen(trim($arItem['PROPERTY_PRICEOLD_VALUE'])) ? (floatval(str_replace(' ', '', $arItem['PROPERTY_PRICEOLD_VALUE'])) * $arItem['QUANTITY']) : 0);
		
			$arResult['ITEMS'][$arItem['ID']] = $arItem;
		}
		
		$arResult['ITEMS_SUMM'] = TSolution::FormatSumm($summ, 1);
		$arResult['ITEMS_SUMM_RAW'] = $summ;
		$arResult['ITEMS_SUMM_WD'] = TSolution::FormatSumm($summ_wd, 1);
		$arResult['ITEMS_SUMM_WD_RAW'] = $summ_wd;
		$arResult['ITEMS_COUNT'] = count($arResult['ITEMS']);
	}
}

$this->IncludeComponentTemplate();