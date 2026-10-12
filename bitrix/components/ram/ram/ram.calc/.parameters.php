<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!\Bitrix\Main\Loader::includeModule("ram.calc")) return;
$arServices = Array("0" => GetMessage("RAM_CALC_NOT_SELECTED"));
$dbServices = \Ram\Calc\ServiceTable::getList(Array("order" => Array("ID" => "asc"), "select" => Array("ID", "NAME")));
while ($arService = $dbServices -> fetch())
{
	$arServices[$arService["ID"]] = "[".$arService["ID"]."] ".$arService["NAME"];
}
$arActionValues = Array
(
	"calc" => GetMessage("RAM_CALC_ACTION_CALC"),
	"request" => GetMessage("RAM_CALC_ACTION_REQUEST")
);
if (!$arCurrentValues["SERVICE_ID"])
{
	foreach ($arServices as $serviceId => $arService)
	{
		$arCurrentValues["SERVICE_ID"] = $serviceId;
		break;
	}
}
$arComponentParameters["PARAMETERS"]["SERVICE_ID"] = Array
(
	"PARENT" => "BASE",
	"NAME" => GetMessage("RAM_CALC_SERVICE_ID"),
	"TYPE" => "LIST",
	"VALUES" => $arServices,
	"REFRESH" => "Y",
);
if ($arCurrentValues["SERVICE_ID"])
{
	$arService = \Ram\Calc\ServiceTable::getRowById($arCurrentValues["SERVICE_ID"]);
	
	$arProperties = Array();
	$dbProperties = \Ram\Calc\PropertyTable::getList(Array("filter" => Array("=SERVICE_ID" => $arCurrentValues["SERVICE_ID"])));
	while ($arProperty = $dbProperties -> fetch())
	{
		$arProperties[] = $arProperty;
	}
	usort($arProperties, function($a, $b){return $a["PARAMETERS"]["SORT"] - $b["PARAMETERS"]["SORT"];});
	
	if ($arService["PROPERTIES"]["SOURCE"] !== "manual")
	{
		$arComponentParameters["PARAMETERS"]["ELEMENT_ID"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
		);
	}
	$arComponentParameters["PARAMETERS"]["MULTYPLE"] = Array
	(
		"PARENT" => "BASE",
		"NAME" => GetMessage("RAM_CALC_MULTYPLE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"REFRESH" => "Y",
	);
	
	if ($arCurrentValues["MULTYPLE"] === "Y")
	{
		$arCalculations = Array();
		foreach ($arService["CALCULATIONS"] as $arCalculation)
		{
			$arCalculations[$arCalculation["CODE"]] = $arCalculation["NAME"];
		}
		
		$arComponentParameters["PARAMETERS"]["HIDDEN_CALCULATIONS"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_HIDDEN_CALCULATIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arCalculations,
			"MULTIPLE" => "Y",
		);
	}
	
	$arComponentParameters["PARAMETERS"]["JQUERY"] = Array
	(
		"PARENT" => "BASE",
		"NAME" => GetMessage("RAM_CALC_JQUERY"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
	);

	if (\Bitrix\Main\Loader::includeModule("sale") && \Bitrix\Main\Loader::includeModule("catalog") && $arService["PROPERTIES"]["SOURCE"] === "iblock")
	{
		$arActionValues["basket"] = GetMessage("RAM_CALC_ACTION_BASKET");
	}
	else
	{
		if ($arCurrentValues["ACTION"] === "basket")
		{
			$arCurrentValues["ACTION"] = "calc";
		}
	}
	$arComponentParameters["PARAMETERS"]["ACTION"] = Array
	(
		"PARENT" => "BASE",
		"NAME" => GetMessage("RAM_CALC_ACTION"),
		"TYPE" => "LIST",
		"VALUES" => $arActionValues,
		"DEFAULT" => "calc",
		"REFRESH" => "Y",
	);

	if ($arCurrentValues["ACTION"] === "request")
	{
		$dbSites = CSite::GetByID(SITE_ID);
		$arSite = $dbSites->Fetch();
		
		if (empty($arCurrentValues["REQUEST_FIELDS"]))
		{
			$arCurrentValues["REQUEST_FIELDS"] = Array(GetMessage("RAM_CALC_REQUEST_FIELDS_DEFAULT"));
		}
		
		$arComponentParameters["PARAMETERS"]["USER_CONSENT"] = Array();
		$arComponentParameters["PARAMETERS"]["REQUEST_FIELDS"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_FIELDS"),
			"TYPE" => "STRING",
			"DEFAULT" => Array(GetMessage("RAM_CALC_REQUEST_FIELDS_DEFAULT")),
			"MULTIPLE" => "Y",
			"REFRESH" => "Y",
		);
		$requestFields = Array();
		foreach ($arCurrentValues["REQUEST_FIELDS"] as $field)
		{
			if (strlen(trim($field)))
			{
				$requestFields[] = trim($field);
			}
		}
		$arComponentParameters["PARAMETERS"]["REQUEST_FIELDS_REQUIRED"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_FIELDS_REQUIRED"),
			"TYPE" => "LIST",
			"VALUES" => $requestFields,
			"MULTIPLE" => "Y",
		);
		foreach ($arCurrentValues["REQUEST_FIELDS"] as $k => $field)
		{
			if (strlen(trim($field)))
			{
				$arComponentParameters["GROUPS"]["REQUEST_FIELD_".$k] = Array
				(
					"NAME" => GetMessage("RAM_CALC_REQUEST_FIELD")." \"".trim($field)."\"",
					"SORT" => 210+$k,
				);
				
				$arComponentParameters["PARAMETERS"]["REQUEST_FIELD_".$k] = Array
				(
					"PARENT" => "REQUEST_FIELD_".$k,
					"NAME" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE"),
					"TYPE" => "LIST",
					"VALUES" => Array
					(
						"text" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE_TEXT"),
						"textarea" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE_TEXTAREA"),
						"file" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE_FILE"),
					),
					"DEFAULT" => "text",
					"REFRESH" => "Y",
				);
				
				if ($arCurrentValues["REQUEST_FIELD_".$k] === "file")
				{
					$arComponentParameters["PARAMETERS"]["REQUEST_FIELD_".$k."_MAX_SIZE"] = Array
					(
						"PARENT" => "REQUEST_FIELD_".$k,
						"NAME" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE_FILE_MAX_SIZE"),
						"TYPE" => "STRING",
						"DEFAULT" => "100"
					);
					$arComponentParameters["PARAMETERS"]["REQUEST_FIELD_".$k."_MIME"] = Array
					(
						"PARENT" => "REQUEST_FIELD_".$k,
						"NAME" => GetMessage("RAM_CALC_REQUEST_FIELD_TYPE_FILE_MIME"),
						"TYPE" => "STRING",
						"DEFAULT" => ".jpg,.pdf,.doc",
					);
				}
			}
		}
		$arComponentParameters["PARAMETERS"]["REQUEST_EMAIL"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_EMAIL"),
			"TYPE" => "STRING",
			"DEFAULT" => $arSite["EMAIL"],
		);
		$userEmailFields = $requestFields;
		$userEmailFields[-1] = GetMessage("RAM_CALC_REQUEST_USER_EMAIL_EMPTY");
		$arComponentParameters["PARAMETERS"]["REQUEST_USER_EMAIL"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_USER_EMAIL"),
			"TYPE" => "LIST",
			"VALUES" => $userEmailFields,
			"DEFAULT" => -1,
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_DESCRIPTION"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_DESCRIPTION"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_DESCRIPTION_DEFAULT"),
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_BTN"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_BTN"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_BTN_DEFAULT"),
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_SUCCESS"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_SUCCESS"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_SUCCESS_DEFAULT"),
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_FAIL"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_FAIL"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_FAIL_DEFAULT"),
		);
	}
	else if ($arCurrentValues["ACTION"] === "basket")
	{
		$catalogsList = Bitrix\Catalog\CatalogIblockTable::getList(Array("filter" => Array()))->fetchAll();
		$catalogsIds = Array();
		foreach ($catalogsList as $catalog)
		{
			$catalogsIds[] = $catalog["IBLOCK_ID"];
		}
		
		$iblocksList = Bitrix\Iblock\IblockTable::getList(Array("filter" => Array("ACTIVE" => "Y", "ID" => $catalogsIds)))->fetchAll();
		$catalogs = Array();
		foreach ($iblocksList as $iblock)
		{
			$catalogs[$iblock["ID"]] = "[".$iblock["ID"]."] ".$iblock["NAME"].(strlen($iblock["CODE"])?" (".$iblock["CODE"].")":"");
		}
		
		$groupsList = Bitrix\Catalog\GroupTable::getList(Array("filter" => Array()))->fetchAll();
		$groups = Array();
		foreach ($groupsList as $group)
		{
			$groupLang = Bitrix\Catalog\GroupLangTable::getList(Array("filter" => Array("CATALOG_GROUP_ID" => $group["ID"], "LANG" => LANG)))->fetch();
			$groups[$group["ID"]] = "[".$group["ID"]."] ".$groupLang["NAME"]." (".$group["NAME"].")";
		}
		
		$currenciesList = Bitrix\Currency\CurrencyTable::getList(Array("filter" => Array()))->fetchAll();
		$currencies = Array();
		foreach ($currenciesList as $currency)
		{
			$currencyLang = Bitrix\Currency\CurrencyLangTable::getList(Array("filter" => Array("CURRENCY" => $currency["CURRENCY"], "LID" => LANG)))->fetch();
			$currencies[$currency["CURRENCY"]] = "[".$currency["CURRENCY"]."] ".$currencyLang["FULL_NAME"];
		}
		
		// $arComponentParameters["PARAMETERS"]["BASKET_CATALOG"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_BASKET_CATALOG"),
			// "TYPE" => "LIST",
			// "VALUES" => $catalogs,
		// );
		
		// $arComponentParameters["PARAMETERS"]["BASKET_GROUP"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_BASKET_GROUP"),
			// "TYPE" => "LIST",
			// "VALUES" => $groups,
		// );
		
		// $arComponentParameters["PARAMETERS"]["BASKET_CURRENCY"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_BASKET_CURRENCY"),
			// "TYPE" => "LIST",
			// "VALUES" => $currencies,
		// );
		
		// $arListProperties = Array();
		
		// foreach ($arProperties as $k => $arProperty)
		// {
			// if ($arProperty["PROPERTIES"]["TYPE"] === "list" && $arProperty["PROPERTIES"]["MULTYPLE"] === "N")
			// {
				// $arListProperties[$arProperty["PROPERTIES"]["CODE"]] = "[".$arProperty["PROPERTIES"]["CODE"]."] ".$arProperty["PROPERTIES"]["NAME"];
			// }
		// }
		
		// $arComponentParameters["PARAMETERS"]["BASKET_IMAGE"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_BASKET_IMAGE"),
			// "TYPE" => "LIST",
			// "VALUES" => $arListProperties,
		// );
		
		
		$arPrices = Array();
		foreach ($arService["CALCULATIONS"] as $k => $arCalculation)
		{
			$arPrices[$arCalculation["CODE"]] = "[".$arCalculation["CODE"]."] ".$arCalculation["NAME"];
		}
		$arComponentParameters["PARAMETERS"]["PRICE"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_PRICE"),
			"TYPE" => "LIST",
			"VALUES" => $arPrices,
		);
		$arComponentParameters["PARAMETERS"]["QUANTITY"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_QUANTITY"),
			"TYPE" => "LIST",
			"VALUES" => $arPrices,
		);
		// $arComponentParameters["PARAMETERS"]["WEIGHT"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_WEIGHT"),
			// "TYPE" => "LIST",
			// "VALUES" => $arPrices,
		// );
		// $arComponentParameters["PARAMETERS"]["WIDTH"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_WIDTH"),
			// "TYPE" => "LIST",
			// "VALUES" => $arPrices,
		// );
		// $arComponentParameters["PARAMETERS"]["LENGTH"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_LENGTH"),
			// "TYPE" => "LIST",
			// "VALUES" => $arPrices,
		// );
		// $arComponentParameters["PARAMETERS"]["HEIGHT"] = Array
		// (
			// "PARENT" => "BASE",
			// "NAME" => GetMessage("RAM_CALC_HEIGHT"),
			// "TYPE" => "LIST",
			// "VALUES" => $arPrices,
		// );
		$arComponentParameters["PARAMETERS"]["REQUEST_BASKET_BTN"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_BTN"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_BASKET_BTN_DEFAULT"),
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_BASKET_SUCCESS"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_BASKET_SUCCESS"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_BASKET_SUCCESS_DEFAULT")."</a>.",
		);
		$arComponentParameters["PARAMETERS"]["REQUEST_BASKET_FAIL"] = Array
		(
			"PARENT" => "BASE",
			"NAME" => GetMessage("RAM_CALC_REQUEST_BASKET_FAIL"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("RAM_CALC_REQUEST_BASKET_FAIL_DEFAULT"),
		);
	}	
	$arComponentParameters["PARAMETERS"]["CSS_COLOR"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_COLOR"),
		"TYPE" => "COLORPICKER",
		"DEFAULT" => "#333333"
	);
	$arComponentParameters["PARAMETERS"]["CSS_BACKGROUND_COLOR"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_BACKGROUND_COLOR"),
		"TYPE" => "COLORPICKER",
		"DEFAULT" => "#FFFFFF"
	);
	$arComponentParameters["PARAMETERS"]["CSS_ACTIVE_COLOR"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_ACTIVE_COLOR"),
		"TYPE" => "COLORPICKER",
		"DEFAULT" => "#2222EE"
	);
	$arComponentParameters["PARAMETERS"]["CSS_BORDER_COLOR"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_BORDER_COLOR"),
		"TYPE" => "COLORPICKER",
		"DEFAULT" => "#AAAAAA"
	);
	$arComponentParameters["PARAMETERS"]["CSS_OFFSET"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_OFFSET"),
		"TYPE" => "string",
		"DEFAULT" => "20"
	);
	$arComponentParameters["PARAMETERS"]["CSS_BORDER"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_BORDER"),
		"TYPE" => "string",
		"DEFAULT" => "1"
	);
	$arComponentParameters["PARAMETERS"]["CSS_BORDER_RADIUS"]  = Array
	(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("RAM_CALC_CSS_BORDER_RADIUS"),
		"TYPE" => "string",
		"DEFAULT" => "2"
	);
	$arComponentParameters["PARAMETERS"]["CACHE_TIME"] = Array
	(
		"DEFAULT" => 36000000
	);
	foreach ($arProperties as $k => $arProperty)
	{
		$code = $arProperty["PROPERTIES"]["CODE"];
		$arComponentParameters["GROUPS"]["PROPERTY_".$code] = Array
		(
			"NAME" => GetMessage("RAM_CALC_PROPERTY")." \"".$arProperty["PROPERTIES"]["NAME"]."\"",
			"SORT" => 410+$arProperty["PROPERTIES"]["SORT"],
		);
		if ($arProperty["PROPERTIES"]["TYPE"] === "list")
		{
			$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_DISPLAY"] = Array
			(
				"PARENT" => "PROPERTY_".$code,
				"NAME" => GetMessage("RAM_CALC_PROPERTY_DISPLAY"),
				"TYPE" => "LIST",
				"VALUES" => Array
				(
					"select" => GetMessage("RAM_CALC_PROPERTY_SELECT"),
					"items" => GetMessage("RAM_CALC_PROPERTY_LIST"),
				),
				"DEFAULT" => "select",
				"REFRESH" => "Y",
			);
			if ($arCurrentValues["PROPERTY_".$code."_DISPLAY"] === "items")
			{
				$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_IMAGES"] = Array
				(
					"PARENT" => "PROPERTY_".$code,
					"NAME" => GetMessage("RAM_CALC_PROPERTY_IMAGES"),
					"TYPE" => "CHECKBOX",
					"DEFAULT" => "N",
					"REFRESH" => "Y",
				);
				if ($arCurrentValues["PROPERTY_".$code."_IMAGES"] === "Y")
				{
					$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_IMAGE_WIDTH"] = Array
					(
						"PARENT" => "PROPERTY_".$code,
						"NAME" => GetMessage("RAM_CALC_PROPERTY_IMAGE_WIDTH"),
						"TYPE" => "STRING",
						"DEFAULT" => "60",
					);
					$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_IMAGE_HEIGHT"] = Array
					(
						"PARENT" => "PROPERTY_".$code,
						"NAME" => GetMessage("RAM_CALC_PROPERTY_IMAGE_HEIGHT"),
						"TYPE" => "STRING",
						"DEFAULT" => "60",
					);
				}
				else
				{
					$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_HINTS"] = Array
					(
						"PARENT" => "PROPERTY_".$code,
						"NAME" => GetMessage("RAM_CALC_PROPERTY_HINTS"),
						"TYPE" => "CHECKBOX",
						"DEFAULT" => "Y",
					);
				}
			}
		}
		else
		{
			$arComponentParameters["PARAMETERS"]["PROPERTY_".$code."_DISPLAY"] = Array
			(
				"PARENT" => "PROPERTY_".$code,
				"NAME" => GetMessage("RAM_CALC_PROPERTY_DISPLAY"),
				"TYPE" => "LIST",
				"VALUES" => Array("input" => GetMessage("RAM_CALC_PROPERTY_INPUT"), "range" => GetMessage("RAM_CALC_PROPERTY_RANGE")),
				"DEFAULT" => "input",
			);
		}
	}
}
?>