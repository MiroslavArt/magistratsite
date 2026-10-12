<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;

if (!\Bitrix\Main\Loader::includeModule("ram.calc")) return;

if ($arParams["JQUERY"] === "Y")
{
	CJSCore::Init(Array("jquery"));
}

if ($arParams["ACTION"] !== "request")
{
	unset($arParams["USER_CONSENT"]);
	unset($arParams["USER_CONSENT_ID"]);
	unset($arParams["USER_CONSENT_IS_CHECKED"]);
	unset($arParams["USER_CONSENT_IS_LOADED"]);
}

if (!empty($arParams["REQUEST_FIELDS"]))
{
	foreach ($arParams["REQUEST_FIELDS"] as $k => $userField)
	{
		if (!strlen(trim($userField))) unset($arParams["REQUEST_FIELDS"][$k]);
	}
}

if ($this->startResultCache(false, false, "ram.calc"))
{
	if (defined("BX_COMP_MANAGED_CACHE") && is_object($GLOBALS["CACHE_MANAGER"]))
	{
		$GLOBALS["CACHE_MANAGER"]->StartTagCache($this->__cachePath);
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("ram.calc_".$arParams["SERVICE_ID"]);
		$GLOBALS["CACHE_MANAGER"]->EndTagCache();
	}
	
	if (!$arParams["SERVICE_ID"])
	{
		$this->abortResultCache();
		ShowError(Loc::getMessage("RAM_CALC_CHOOSE_CALCULATOR"));
		return;
	}
	
	$arService = \Ram\Calc\ServiceTable::getList(Array("filter" => Array("=ID" => $arParams["SERVICE_ID"], "ACTIVE" => "Y")))->fetch();
	
	if (!$arService)
	{
		$this->abortResultCache();
		ShowError(Loc::getMessage("RAM_CALC_CALCULATOR_NOT_FOUND"));
		return;
	}
	
	if ($arService["PROPERTIES"]["SOURCE"] !== "manual")
	{
		if (!$arParams["ELEMENT_ID"])
		{
			$this->abortResultCache();
			ShowError(Loc::getMessage("RAM_CALC_CHOOSE_ELEMENT"));
			return;
		}
		else
		{
			if ($arService["PROPERTIES"]["SOURCE"] === "iblock")
			{
				\Bitrix\Main\Loader::includeModule("iblock");
				$dbIblockItem = CIBlockElement::GetList(Array(), Array("ID" => $arParams["ELEMENT_ID"], "IBLOCK_ID" => $arService["PROPERTIES"]["SOURCE_IBLOCK"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"), false, false, Array("ID"));
				if (!$dbIblockItem->GetNextElement())
				{
					$this->abortResultCache();
					ShowError(Loc::getMessage("RAM_CALC_ELEMENT_NOT_FOUND"));
					return;
				}
			}
			else if ($arService["PROPERTIES"]["SOURCE"] === "highload")
			{
				\Bitrix\Main\Loader::includeModule("highloadblock");
				$highload = Bitrix\Highloadblock\HighloadBlockTable::getById($arService["PROPERTIES"]["SOURCE_HIGHLOAD"])->fetch();
				$highloadEntity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highload);
				$highloadClass = $highloadEntity->getDataClass();
				
				$dbHighloadItem = $highloadClass::getList(Array("select" => array_values($arSourceFieldsList), "filter" => Array("UF_XML_ID" => $arParams["ELEMENT_ID"])));
				if (!$dbHighloadItem->Fetch())
				{
					$this->abortResultCache();
					ShowError(Loc::getMessage("RAM_CALC_ELEMENT_NOT_FOUND"));
					return;
				}
			}
		}
	}
	
	$arResult = \CRamCalc::Data($arParams);
	
	$this->includeComponentTemplate();
}
else
{
	$this->AbortResultCache();
}
if ($arParams["ACTION"] === "request")
{
	if ($arParams["USER_CONSENT"] == "Y")
	{
		?><div class='ram-calc__request-field ram-calc__request-consent' data-consent='<?=$arResult["ID"]?>'><?$APPLICATION->IncludeComponent(
			"bitrix:main.userconsent.request",
			"",
			array(
				"ID" => $arParams["USER_CONSENT_ID"],
				"IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
				"IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
				"AUTO_SAVE" => "N",
				"SUBMIT_EVENT_NAME" => "request-event-".$arResult["ID"],
				"REPLACE" => array(
					"button_caption" => $arParams["REQUEST_BTN"],
					"fields" => $arParams["REQUEST_FIELDS"]
				),
			),
			false,
			array("HIDE_ICONS" => "Y")
		);?></div>
		<script type="text/javascript">
		$(document).ready(function()
		{
			BX.ready(function ()
			{
				if (BX.UserConsent)
				{
					$("[data-consent='<?=$arResult["ID"]?>']").appendTo("[data-consent-place='<?=$arResult["ID"]?>']");
					<?
					if ($arParams["USER_CONSENT_IS_CHECKED"] !== "Y")
					{
						?>$("#request-<?=$arResult["ID"]?> .ram-calc__request-submit").hide();<?
					}
					?>
					var control<?=$arResult["ID"]?> = BX.UserConsent.load(BX("request-<?=$arResult["ID"]?>"));
					BX.addCustomEvent(control<?=$arResult["ID"]?>, BX.UserConsent.events.accepted, function()
					{
						$("#request-<?=$arResult["ID"]?> .ram-calc__request-submit").show();
					});
					BX.addCustomEvent(control<?=$arResult["ID"]?>, BX.UserConsent.events.refused, function()
					{
						$("#request-<?=$arResult["ID"]?> .ram-calc__request-submit").hide();
					});
					
				}
			});
		});
		</script>
		<?
	}
}
?>