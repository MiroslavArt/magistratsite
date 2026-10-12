<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if ($arResult && strlen($arResult["SERVICE_PROPERTIES"]["RECAPTCHA_CLIENT_KEY"]))
{
	Bitrix\Main\Page\Asset::getInstance()->addJs("https://www.google.com/recaptcha/api.js?render=".$arResult["SERVICE_PROPERTIES"]["RECAPTCHA_CLIENT_KEY"]);
}
?>