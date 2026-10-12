<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (isset($_FILES["file"]))
{
	$_FILES["file"]["MODULE_ID"] = "ram.calc";
	
	$id = CFile::SaveFile($_FILES["file"], "ram.calc");
	
	$path = CFile::GetPath($id);
	
	echo(json_encode(Array("ID" => $id, "PATH" => $_SERVER["HTTP_ORIGIN"].$path)));
}
?>