<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

if (!$arResult) return false;

$uid = uniqid();

?><div class='ram-calc__calculator-wrap ram-calc__calculator_theme-<?=$uid?>' data-calculator-wrap='<?=$arResult["ID"]?>'><?
if ($arParams["MULTYPLE"] === "Y")
{
	?><div class='ram-calc__calculator-tabs'>
		<div class='ram-calc__tab ram-calc__tab_active' data-index='1' onclick='return CRamCalc.SelectCalculator(this);'><?=$arResult["TITLE"]?> #1<div class='ram-calc__tab-delete' onclick='return CRamCalc.DeleteCalculator(this);'></div></div>
		<div class='ram-calc__tab' onclick='return CRamCalc.AddCalculator(this, "<?=$arResult["ID"]?>");'><?=GetMessage("RAM_CALC_ADD")?></div>
	</div><?
}
?><div class='ram-calc__calculator' data-calculator='<?=$arResult["ID"]?>'><?
if ($arParams["MULTYPLE"] !== "Y" && strlen($arResult["TITLE"]))
{
	?><div class='ram-calc__calculator-title'><?=$arResult["TITLE"]?></div><?
}
?><form><div class='ram-calc__calculator-properties'><?
foreach ($arResult["PROPERTIES"] as $arProperty)
{
	if ($arProperty["CODE"] === "base") continue;
	$propertyDisplay = $arParams["PROPERTY_".$arProperty["CODE"]."_DISPLAY"];
	
	?><div class='ram-calc__property' data-property='<?=$arProperty["CODE"]?>'><div class='ram-calc__property-title'><?=$arProperty["TITLE"]?></div><?
	if (strlen($arProperty["HINT"]))
	{
		?><div class='ram-calc__property-hint'><?=$arProperty["HINT"]?></div><?
	}
	if ($arProperty["TYPE"] === "list")
	{
		if (!$propertyDisplay) $propertyDisplay = "select";
		?><div class='ram-calc__property-list ram-calc__property-list_<?=$propertyDisplay?>'><?
		if ($propertyDisplay === "items")
		{
			foreach ($arProperty["VALUES"] as $valueID => $arValue)
			{
				if ($arValue["ACTIVE"] !== "Y") continue;
				
				$checked = $arValue["CHECKED"] ? "checked='checked'" : "";
				
				if ($arParams["PROPERTY_".$arProperty["CODE"]."_IMAGES"] === "Y")
				{
					$imageWidth = isset($arParams["PROPERTY_".$arProperty["CODE"]."_IMAGE_WIDTH"]) ? $arParams["PROPERTY_".$arProperty["CODE"]."_IMAGE_WIDTH"] : 60;
					$imageHeight = isset($arParams["PROPERTY_".$arProperty["CODE"]."_IMAGE_HEIGHT"]) ? $arParams["PROPERTY_".$arProperty["CODE"]."_IMAGE_HEIGHT"] : 60;
					
					$arImage = CFile::ResizeImageGet($arValue["PICTURE"], array("width" => $imageWidth, "height" => $imageHeight), BX_RESIZE_IMAGE_EXACT, true);
					?>
					<label class='ram-calc__value ram-calc__value_image'>
						<?
						if ($arProperty["MULTYPLE"] === "Y")
						{
							?><input <?=$checked?> onchange='return CRamCalc.OnListItemClick(this);' type='checkbox' name='<?=$arProperty["FIELD_NAME"]?>' value='<?=$valueID?>'/><?
						}
						else
						{
							?><input <?=$checked?> onchange='return CRamCalc.OnListItemClick(this);' type='radio' name='<?=$arProperty["FIELD_NAME"]?>' value='<?=$valueID?>'/><?
						}
						?>
						<div class='ram-calc__value-image' title='<?=$arValue["TITLE"]?>'>
							<img src='<?=$arImage["src"]?>'/>
							<?
							if ($arParams["PROPERTY_".$arProperty["CODE"]."_IMAGES_TITLE"] !== "N")
							{
								?><div class='ram-calc__value-title'><?=$arValue["TITLE"]?></div><?
							}
							?>
						</div>
					</label>
					<?
				}
				else
				{
					?><div class='ram-calc__value ram-calc__value_noimage'><label><?
					if ($arProperty["MULTYPLE"] === "Y")
					{
						?><input <?=$checked?> onchange='return CRamCalc.OnListItemClick(this);' type='checkbox' name='<?=$arProperty["FIELD_NAME"]?>' value='<?=$valueID?>'/><?
					}
					else
					{
						?><input <?=$checked?> onchange='return CRamCalc.OnListItemClick(this);' type='radio' name='<?=$arProperty["FIELD_NAME"]?>' value='<?=$valueID?>'/><?
					}
					?><div class='ram-calc__value-title'><?=$arValue["TITLE"]?></div></label><?
					if (strlen($arValue["HINT"]) && $arParams["PROPERTY_".$arProperty["CODE"]."_HINTS"] === "Y")
					{
						?><div class='ram-calc__value-hint' data-active><div class='ram-calc__value-hint-value'><?=$arValue["HINT"]?></div></div><?
					}
					?></div><?
				}
			}
		}
		else
		{
			?><select onchange='CRamCalc.OnSelectChange(this);' <?=$arProperty["MULTYPLE"] === "Y" ? "multiple='multiple'" : ""?> name='<?=$arProperty["FIELD_NAME"]?>'><?
			foreach ($arProperty["VALUES"] as $valueID => $arValue)
			{
				if ($arValue["ACTIVE"] !== "Y") continue;
				
				$selected = $arValue["CHECKED"] ? "selected='selected'" : "";
				
				?><option <?=$selected?> value="<?=$valueID?>"><?=$arValue["TITLE"]?></option><?
			}
			?></select><?
		}
		?></div><?
	}
	else if ($arProperty["TYPE"] === "number")
	{
		if ($propertyDisplay === "range")
		{
			?>
			<div class='ram-calc__property-range'>
				<div class='ram-calc__range'>
					<input 
						oninput='CRamCalc.OnRangeSliderMove(this);' 
						onchange='CRamCalc.OnRangeSliderChange(this);' 
						type='range' 
						name='<?=$arProperty["FIELD_NAME"]?>' 
						value='<?=$arProperty["NUMBER_VALUE"]?>' 
						min='<?=$arProperty["NUMBER_MIN"]?>' 
						max='<?=$arProperty["NUMBER_MAX"]?>' 
						step='<?=$arProperty["NUMBER_STEP"]?>'
					/>
					<div class='ram-calc__range-min'><?=$arProperty["NUMBER_MIN"]?></div>
					<div class='ram-calc__range-max'><?=$arProperty["NUMBER_MAX"]?></div>
					<div class='ram-calc__range-valuewrap'>
						<input 
							onchange='CRamCalc.OnRangeInputChange(this);' 
							onkeyup='CRamCalc.OnNumberInput(this);' 
							type='text' 
							class='ram-calc__range-value' 
							value='<?=$arProperty["NUMBER_VALUE"]?>'
						/>
						<?
						if (strlen($arProperty["MEASURE"]))
						{
							?><div class='ram-calc__range-measure'><?=$arProperty["MEASURE"]?></div><?
						}
						?>
					</div>
				</div>
			</div>
			<?
		}
		else
		{
			?>
			<div class='ram-calc__property-number'>
				<input 
					type='text' 
					onchange='CRamCalc.OnNumberChange(this);' 
					onkeyup='CRamCalc.OnNumberInput(this);' 
					value='<?=$arProperty["NUMBER_VALUE"]?>' 
					name='<?=$arProperty["FIELD_NAME"]?>' 
					min='<?=$arProperty["NUMBER_MIN"]?>' 
					max='<?=$arProperty["NUMBER_MAX"]?>' 
					step='<?=$arProperty["NUMBER_STEP"]?>'
				/>
				<div class='ram-calc__number-measure'><?=$arProperty["MEASURE"]?></div>
			</div>
			<?
		}
	}
	?></div><?
}
?></div><?
if (!empty($arResult["CALCULATIONS"]))
{
	?>
	<div class='ram-calc__calculator-calculations'>
	<?
	foreach ($arResult["CALCULATIONS"] as $calculationID => $arCalculation)
	{
		?>
		<div class='ram-calc__calculation'>
			<div class='ram-calc__calculation-title'><?=(strlen($arCalculation["TITLE"])?$arCalculation["TITLE"]:$arCalculation["NAME"])?>:</div>
			<div class='ram-calc__calculation-result' data-calculation='<?=$arCalculation["CODE"]?>'>0</div>
			<div class='ram-calc__calculation-measure'><?=$arCalculation["MEASURE"]?></div>
		</div>
		<?
	}
	?>
	</div>
	<?
}
?></form></div><?
if ($arParams["MULTYPLE"] === "Y")
{
	if (!empty($arResult["CALCULATIONS"]))
	{
		?>
		<div class='ram-calc__calculator-total-calculations'>
			<div class='ram-calc__calculations-title'><?=GetMessage("RAM_CALC_TOTAL_CALCULATIONS")?></div>
			<?
			foreach ($arResult["CALCULATIONS"] as $calculationID => $arCalculation)
			{
				if (!empty($arParams["HIDDEN_CALCULATIONS"]) && in_array($arCalculation["CODE"], $arParams["HIDDEN_CALCULATIONS"])) continue;
				
				?>
				<div class='ram-calc__calculation'>
					<div class='ram-calc__calculation-title'><?=(strlen($arCalculation["TITLE"])?$arCalculation["TITLE"]:$arCalculation["NAME"])?>:</div>
					<div class='ram-calc__calculation-result' data-calculation='<?=$arCalculation["CODE"]?>'>0</div>
					<div class='ram-calc__calculation-measure'><?=$arCalculation["MEASURE"]?></div>
				</div>
				<?
			}
			?>
		</div>
		<?
	}
}
if ($arParams["ACTION"] === "basket")
{
	?><div class='ram-calc__request'><div class='ram-calc__request-submit'><input onclick='CRamCalc.Submit(this);' type='button' value='<?=$arParams["REQUEST_BASKET_BTN"]?>' /></div></div><?
}
else if ($arParams["ACTION"] === "request")
{
	?><div class='ram-calc__request' id='request-<?=$arResult["ID"]?>'><?
	if (strlen($arParams["REQUEST_DESCRIPTION"]))
	{
		?><div class='ram-calc__request-description'><?=$arParams["REQUEST_DESCRIPTION"]?></div><?
	}
	foreach ($arParams["REQUEST_FIELDS"] as $k => $userField)
	{
		$fieldType = isset($arParams["REQUEST_FIELD_".$k]) ? $arParams["REQUEST_FIELD_".$k] : "text";
		
		?>
		<label class='ram-calc__request-field'>
			<div class='ram-calc__request-field-title'>
				<?=trim($userField)?>
				<?
				if (!empty($arParams["REQUEST_FIELDS_REQUIRED"]) && in_array($k, $arParams["REQUEST_FIELDS_REQUIRED"]))
				{
					?><span class='ram-calc__request-field-required'>*</span><?
				}
				?>
			</div>
			<?
			switch ($fieldType)
			{
				case "text":
				{
					?><input type='text' name='requestFields[]' /><?
					
					break;
				}
				case "textarea":
				{
					?><textarea name='requestFields[]'></textarea><?
					
					break;
				}
				case "file":
				{
					?>
					<div class='ram-calc__file'>
						<div class='ram-calc__file-title' data-default='<?=GetMessage("RAM_CALC_UPLOAD")?>'><?=GetMessage("RAM_CALC_UPLOAD")?></div>
						<input 
							type='file'  
							onchange='CRamCalc.OnFileUpload(this);' 
							data-max-size='<?=$arParams["REQUEST_FIELD_".$k."_MAX_SIZE"]?>' 
							accept='<?=$arParams["REQUEST_FIELD_".$k."_MIME"]?>' 
						/>
						<input
							type='text'
							name='requestFields[]'
						/>
					</div>
					<div class='ram-calc__file-size-error'><?=GetMessage("RAM_CALC_UPLOAD_ERROR", Array("#MAX_SIZE#" => $arParams["REQUEST_FIELD_".$k."_MAX_SIZE"]))?></div><?
					
					break;
				}
			}
			if (!empty($arParams["REQUEST_FIELDS_REQUIRED"]) && in_array($k, $arParams["REQUEST_FIELDS_REQUIRED"]))
			{
				?><div class='ram-calc__request-field-error'><?=GetMessage("RAM_CALC_REQUIRED")?></div><?
			}
			?>
		</label>
		<?
	}
	?><div class='ram-calc__content-place' data-consent-place='<?=$arResult["ID"]?>'></div><div class='ram-calc__request-submit'><input onclick='CRamCalc.Submit(this);' type='button' value='<?=$arParams["REQUEST_BTN"]?>' /></div></div><?
}
?></div>
<?
// echo("<pre>");print_r($arResult);echo("</pre>");

$arJsParams = $arParams;
$arJsParams["AJAX_URL"] = $componentPath."/ajax.php";
$arJsParams["UPLOAD_URL"] = $componentPath."/upload.php";
$arJsParams["AJAX"] = "Y";
$arJsParams["RECAPTCHA"] = $arResult["SERVICE_PROPERTIES"]["RECAPTCHA"];
$arJsParams["RECAPTCHA_CLIENT_KEY"] = $arResult["SERVICE_PROPERTIES"]["RECAPTCHA_CLIENT_KEY"];
?>
<script type='text/javascript'>
CRamCalc.AddData(
{
	id: "<?=$arResult["ID"]?>",
	title: "<?=$arResult["TITLE"]?>",
	conditions: <?=CUtil::PhpToJSObject($arResult["CONDITIONS"])?>,
	calculations: <?=CUtil::PhpToJSObject($arResult["CALCULATIONS"])?>,
	properties: <?=CUtil::PhpToJSObject($arResult["PROPERTIES"])?>,
	multyple: <?=($arParams["MULTYPLE"] === "Y" ? "true" : "false")?>,
	params: "<?=base64_encode(json_encode($arJsParams))?>"
});
BX.message({"CONFIRM_CALCULATOR_DELETE": "<?=GetMessage("RAM_CALC_CONFIRM_CALCULATOR_DELETE")?>"});
<?
if ($arParams["ACTION"] === "request")
{
	?>CRamCalc.InitPopup(
	{
		id: "<?=$arResult["ID"]?>",
		successMessage: "<?=htmlspecialchars_decode($arParams["REQUEST_SUCCESS"])?>",
		failMessage: "<?=htmlspecialchars_decode($arParams["REQUEST_FAIL"])?>"
	});<?
}
else if ($arParams["ACTION"] === "basket")
{
	?>CRamCalc.InitPopup(
	{
		id: "<?=$arResult["ID"]?>",
		successMessage: "<?=htmlspecialchars_decode($arParams["REQUEST_BASKET_SUCCESS"])?>",
		failMessage: "<?=htmlspecialchars_decode($arParams["REQUEST_BASKET_FAIL"])?>"
	});<?
}
?>
</script>
<?
if (isset($arParams["CSS_COLOR"]))
{
	?>
	<style type='text/css'>
	.ram-calc__calculator_theme-<?=$uid?>
	{
		--ramcalc-color: <?=$arParams["CSS_COLOR"]?>;
		--ramcalc-background-color: <?=$arParams["CSS_BACKGROUND_COLOR"]?>;
		--ramcalc-active-color: <?=$arParams["CSS_ACTIVE_COLOR"]?>;
		--ramcalc-border-color: <?=$arParams["CSS_BORDER_COLOR"]?>;
		--ramcalc-offset: <?=$arParams["CSS_OFFSET"]?>px;
		--ramcalc-border: <?=$arParams["CSS_BORDER"]?>px;
		--ramcalc-border-radius: <?=$arParams["CSS_BORDER_RADIUS"]?>px;
	}
	</style>
	<?
}
?>