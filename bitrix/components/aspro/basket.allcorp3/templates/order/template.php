<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?if($arResult['USE_BASKET'] === 'Y'):?>
	<?
	$frame = $this->createFrame()->begin();
	$frame->setAnimation(true);

	global $USER, $arTheme;
	$userID = $USER->GetID();
	$userID = ($userID > 0 ? $userID : 0);
	$checkSessionID = false;

	if (isset($_REQUEST['RESULT_ID']) && intval($_REQUEST['RESULT_ID']) > 0) {
		$arItemsForm = [];
		if ($arTheme['USE_BITRIX_FORM']['VALUE'] == 'Y' && \Bitrix\Main\Loader::includeModule('form')) {
			$arData = \CFormResult::GetDataByID($_GET['RESULT_ID'], array('NAME', 'PHONE', 'EMAIL', 'TOTAL_SUMM', 'MESSAGE', 'SESSION_ID', 'ORDER_LIST'), $arFormResult, $arAnswer);
			if ($arData) {
				$checkSessionID = (bitrix_sessid() === trim($arData['SESSION_ID'][0]['USER_TEXT']) ? true : false);
				
				$name = $arData['NAME'][0]['USER_TEXT'];
				$phone = $arData['PHONE'][0]['USER_TEXT'];
				$email = $arData['EMAIL'][0]['USER_TEXT'];
				$comment = $arData['MESSAGE'][0]['USER_TEXT'];
				$totalSumm = $arData['TOTAL_SUMM'][0]['USER_TEXT'];

				if ($arData['ORDER_LIST'][0]['USER_TEXT']) {
					$arItemsForm = explode(";\r\n", $arData['ORDER_LIST'][0]['USER_TEXT']);
				}
			}
		} else {
			$rsRes = CIBlockElement::GetList(false, array('ID' => intval($_REQUEST['RESULT_ID'])), false, false, array('PROPERTY_SESSION_ID', 'PROPERTY_TOTAL_SUMM', 'PROPERTY_NAME', 'PROPERTY_PHONE', 'PROPERTY_EMAIL', 'PROPERTY_ORDER_LIST'));
			
			while($arRes =$rsRes->Fetch()) {
				$checkSessionID = (bitrix_sessid() === trim($arRes['PROPERTY_SESSION_ID_VALUE']) ? true : false);
				$totalSumm = $arRes['PROPERTY_TOTAL_SUMM_VALUE'];
	
				$name = $arRes['PROPERTY_NAME_VALUE'];
				$phone = $arRes['PROPERTY_PHONE_VALUE'];
				$email = $arRes['PROPERTY_EMAIL_VALUE'];
				$arItemsForm[] = $arRes['PROPERTY_ORDER_LIST_VALUE'];
			}
		}
		
		if (
			$checkSessionID && 
			isset($_REQUEST['formresult']) && 
			trim(strtolower($_REQUEST['formresult'])) === 'addok'
		) {
			$addOrder = true;
		}
	}

	if(!$arResult['ITEMS'] && !$addOrder){
		LocalRedirect($arParams['PATH_TO_BASKET']);
	}
	?>
	<div class="basket_order basket">
		<div class="line-block line-block--align-normal basket__inner">
			<div class="line-block__item flex-grow-1">
				<div class="bordered rounded-4 order-info">
					<?
					if($addOrder){
						include_once('confirm.php');
					}
					else{
						include_once('form.php');
					}
					?>
				</div>
			</div>
			<?if($arResult['ITEMS'] && !$addOrder):?>
				<div class="line-block__item basket-side">
					<div class="sticky-block">
						<div class="basket-side__wrapper rounded-4 basket-side__wrapper--bordered">
							<div class="items">
								<div class="head">
									<div class="line-block flexbox--justify-beetwen">
										<div class="line-block__item font_18 font_bold color_333">
											<?=GetMessage('T_HEAD_TITLE_USER_ORDER')?>
										</div>
										<div class="line-block__item font_13">
											<a class="" href="<?=$arParams['PATH_TO_BASKET']?>"><?=GetMessage('T_HEAD_CHANGE_ORDER');?></a>
										</div>
									</div>
								</div>
								<div class="wrap">
									<?foreach($arResult['ITEMS'] as $arItem):?>
										<div class="item">
											<div class="item__name">
												<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="dark_link font_15"><?=$arItem['NAME']?></a>
											</div>
											<?if(strlen($arItem['PROPERTY_PRICE_VALUE'])):?>
												<div class="line-block line-block--align-normal flexbox--justify-beetwen item__prices">
													<div class="line-block__item font_16 font_bold color_333 flex-1">
														<?=str_replace('#CURRENCY#', $arItem['PROPERTY_PRICE_CURRENCY_VALUE'], $arItem['PROPERTY_PRICE_VALUE'])?><?=($arItem['QUANTITY'] ? ' <span class="font_16 color_666 font_normal">x</span> '.$arItem['QUANTITY'] : '')?>
													</div>
													<?if(strlen($arItem['SUMM'])):?>
														<div class="line-block__item font_16 font_bold color_333 flex-1 text-right">
															<?=$arItem['SUMM']?>
														</div>
													<?endif;?>
												</div>
											<?endif;?>
										</div>
									<?endforeach;?>
								</div>
								<?if(isset($arResult['ITEMS_SUMM']) && strlen($arResult['ITEMS_SUMM'])):?>
									<div class="foot">
										<div class="line-block flexbox--justify-beetwen">
											<div class="line-block__item font_18 font_bold color_333">
												<?=GetMessage('T_BASKET_TOTAL_TITLE')?>:
											</div>
											<div class="line-block__item font_18 font_bold color_333">
												<?=$arResult['ITEMS_SUMM']?>
											</div>
										</div>
									</div>
								<?endif;?>
							</div>
						</div>
						<div class="hidden" id="js-prices-order"></div>
					</div>
				</div>
			<?endif;?>
		</div>
		<script>
			$(document).ready(function(){
				var index = 0,
					inputOrder = $('input#ORDER_LIST'),
					arItems = <?=CUtil::PhpToJSObject($arResult['ITEMS'], false)?>;

				const $jsPrices = document.getElementById('js-prices-order');
				
				if(inputOrder.length)
				{
					var inputOrderName = inputOrder.attr('name');
					
					for (key in arItems) {
						var inputValue = ''
							+(typeof(arItems[key].ID) !== 'undefined' && parseInt(arItems[key].ID) > 0 ? 'ID: '+arItems[key].ID : '')
							+(typeof(arItems[key].PROPERTY_ARTICLE_VALUE) !== 'undefined' && arItems[key].PROPERTY_ARTICLE_VALUE.length ? ', '+BX.message('T_JS_ARTICLE')+arItems[key].PROPERTY_ARTICLE_VALUE : '')
							+(typeof(arItems[key].NAME) !== 'undefined' && arItems[key].NAME.length ? ', '+BX.message('T_JS_NAME')+arItems[key].NAME : '')
							+(typeof(arItems[key].PROPERTY_PRICE_VALUE) !== 'undefined' && arItems[key].PROPERTY_PRICE_VALUE.length ? ', '+BX.message('T_JS_PRICE')+arItems[key].PROPERTY_PRICE_VALUE : '')
							+(typeof(arItems[key].QUANTITY) !== 'undefined' && parseFloat(arItems[key].QUANTITY) > 0 ? ', '+BX.message('T_JS_QUANTITY')+arItems[key].QUANTITY : '')
							+(typeof(arItems[key].SUMM) !== 'undefined' && arItems[key].SUMM.length ? ', '+BX.message('T_JS_SUMM')+arItems[key].SUMM : '');

						inputOrder.clone().attr('name', inputOrderName+'['+index+']').val(inputValue).appendTo(inputOrder.parent());
						++index;
					}
					
					inputOrder.detach();
				}
				else //with web-form
				{
					inputOrder = $('textarea[data-sid=ORDER_LIST]');
					if (inputOrder.length){
						var inputValue = '';
						for (key in arItems) {
							if (
								typeof(arItems[key].PROPERTY_PRICE_VALUE) !== 'undefined' && 
								arItems[key].PROPERTY_PRICE_VALUE.length
							) {
								$jsPrices.textContent = '';
								$jsPrices.insertAdjacentHTML('beforeend', arItems[key].PROPERTY_PRICE_VALUE)
							}
							inputValue += ''
								+(typeof(arItems[key].ID) !== 'undefined' && parseInt(arItems[key].ID) > 0 ? 'ID: '+arItems[key].ID : '')
								+(typeof(arItems[key].PROPERTY_ARTICLE_VALUE) !== 'undefined' && arItems[key].PROPERTY_ARTICLE_VALUE.length ? ', '+BX.message('T_JS_ARTICLE')+arItems[key].PROPERTY_ARTICLE_VALUE : '')
								+(typeof(arItems[key].NAME) !== 'undefined' && arItems[key].NAME.length ? ', '+BX.message('T_JS_NAME')+arItems[key].NAME : '')
								+(typeof(arItems[key].PROPERTY_PRICE_VALUE) !== 'undefined' && arItems[key].PROPERTY_PRICE_VALUE.length ? ', '+BX.message('T_JS_PRICE')+$jsPrices.textContent : '')
								+(typeof(arItems[key].QUANTITY) !== 'undefined' && parseFloat(arItems[key].QUANTITY) > 0 ? ', '+BX.message('T_JS_QUANTITY')+arItems[key].QUANTITY : '')
								+(typeof(arItems[key].SUMM) !== 'undefined' && arItems[key].SUMM.length ? ', '+BX.message('T_JS_SUMM')+arItems[key].SUMM : '')
								+';\r\n';
						}
						inputOrder.val(inputValue);
					}
				}
			});
		</script>
	</div>
	<?$frame->end();?>
<?endif;?>