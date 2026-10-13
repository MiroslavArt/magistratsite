<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<div class="ajax_basket">
	<?if(
		$arResult['USE_BASKET'] === 'Y' &&
		(
			$arParams['HIDE_ON_CART_PAGE'] !== 'Y' ||
			(
				$arResult['IS_BASKET_PAGE'] !== 'Y' &&
				$arResult['IS_ORDER_PAGE'] !== 'Y'
			)
		)
	):?>
		<?
		$frame = $this->createFrame()->begin();
		$frame->setAnimation(true);

		global $arTheme;
		$menu_class = (isset($arTheme['TOP_MENU']) && strlen($arTheme['TOP_MENU']['VALUE']) ? $arTheme['TOP_MENU']['VALUE'] : '');

		$basketUrl = $arParams['PATH_TO_BASKET'];
		$orderUrl = $arParams['PATH_TO_ORDER'];
		$catalogUrl = $arParams['PATH_TO_CATALOG'];

		$bEmptyBasket = empty($arResult['ITEMS']);
		$title_text = $bEmptyBasket ? GetMessage("EMPTY_BASKET") : GetMessage("TITLE_BASKET", array("#SUMM#" => $arResult['ITEMS_SUMM']));
		?>
		<div class="basket fly<?=(strlen($menu_class) ? ' '.$menu_class : '')?>">
			<div class="wrap cont <?=($bEmptyBasket ? 'empty-basket' : '');?>">
				<div class="right-sidebar-wrapper">
					<span class="header-cart opener fill-theme-hover" title="<?=$title_text ;?>">
						<span class="header-cart__inner">
							<?=TSolution::showIconSvg("basket", SITE_TEMPLATE_PATH."/images/svg/Basket_black.svg");?>
							<span class="header-cart__count bg-more-theme count<?=(intval($arResult['ITEMS_COUNT']) <= 0 ? ' empted' : '')?>">
								<?=$arResult['ITEMS_COUNT']?>
							</span>
						</span>
					</span>
					<?TSolution::showRightDok();?>
				</div>
				
				<div class="basket__heading">
					<div class="line-block flexbox--justify-beetwen">
						<div class="line-block__item">
							<h4 class="basket__heading-title font_24">
								<a href="<?=$basketUrl;?>" class="dark_link basket__heading-link animate-arrow-hover bg-theme-white-parent-hover stroke-theme-parent-all colored_theme_hover_bg-block">
									<?=GetMessage('T_BASKET_TITLE');?>
									<span class="arrow-all arrow-all--wide arrow-all--animate-right banners-arrow-action">
										<?=TSolution::showIconSvg(' arrow-all__item-arrow arrow-all--dark-stroke stroke-theme-target ', SITE_TEMPLATE_PATH.'/images/svg/Arrow_lg.svg');?>
										<span class="arrow-all__item-line arrow-all--dark-bgcolor bg-theme-target colored_theme_hover_bg-el"></span>
									</span>
								</a>
							</h4>
						</div>
						<div class="line-block__item">
							<span class="remove all color_999 font_13 color-theme-hover basket__heading-remove" data-remove_all="Y"><span><?=GetMessage('T_BUTTON_REMOVE_ALL');?></span></span>
						</div>
					</div>
				</div>

				<?if($arResult['ITEMS']):?>
					<div class="basket_wrap">
						<div class="items_wrap scrollbar">
							<div class="cart flexbox flexbox--direction-row flexbox--wrap">
								<?foreach($arResult['ITEMS'] as $arItem):?>
									<?
									$imageSrc = (is_array($arItem['PICTURE']) && strlen($arItem['PICTURE']['IMAGE_70']['src']) ? $arItem['PICTURE']['IMAGE_70']['src'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
									$imageTitle = (is_array($arItem['PICTURE']) && strlen($arItem['PICTURE']['DESCRIPTION']) ? $arItem['PICTURE']['DESCRIPTION'] : $arItem['NAME']);
									$quantity = (isset($arItem['QUANTITY']) && $arItem['QUANTITY'] > 0 ? $arItem['QUANTITY'] : '');
									?>
									<div class="cart__item" data-item='{"ID":"<?=$arItem['ID']?>"}'>
										<div class="line-block line-block--24 line-block--align-normal">
											<div class="line-block__item">
												<div class="cart__item-image">
													<a href="<?=$arItem['DETAIL_PAGE_URL']?>">
														<img class="img-responsive" src="<?=$imageSrc;?>" alt="<?=$imageTitle;?>" title="<?=$imageTitle;?>" />
													</a>
												</div>
											</div>
											<div class="line-block__item flex-1">
												<div class="cart__item-info">
													<div class="cart__item-title">
														<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="dark_link"><?=$arItem['NAME']?></a>
													</div>
													<?
													$bSatusProp = (isset($arItem['PROPERTY_STATUS']) && strlen($arItem['PROPERTY_STATUS']['VALUE']));
													$bArticleProp = (isset($arItem['PROPERTY_ARTICLE_VALUE']) && strlen($arItem['PROPERTY_ARTICLE_VALUE']));
													?>
													<?if ($bSatusProp || $bArticleProp):?>
														<div class="cart__item-props">
															<div class="line-block line-block--20 flexbox--wrap">
																<?if($bSatusProp):?>
																	<div class="line-block__item font_13">
																		<span class="status-icon <?=$arItem['PROPERTY_STATUS']['XML_ID']?>">
																			<?=$arItem['PROPERTY_STATUS']['VALUE']?>
																		</span>
																	</div>
																<?endif;?>
																<?if($bArticleProp):?>
																	<div class="line-block__item font_13 color_999">
																		<span class="article">
																			<?=GetMessage('S_ARTICLE')?>:&nbsp;<span><?=$arItem['PROPERTY_ARTICLE_VALUE']?></span>
																		</span>
																	</div>
																<?endif;?>
															</div>
														</div>
													<?endif;?>
													<div class="cart__item-remove remove stroke-theme-parent-all" title="<?=GetMessage('T_BUTTON_REMOVE_ITEM')?>">
														<?=TSolution::showIconSvg('remove stroke-theme-target ', SITE_TEMPLATE_PATH.'/images/svg/basket_remove.svg');?>
													</div>
												</div>
												<div class="cart__item-prices">
													<div class="line-block line-block--24 flexbox--justify-beetwen">
														<div class="line-block__item cart__item-cost">
															<?=TSolution\Functions::showPrice([
																'TYPE' => 'basket_fly',
																'ITEM' => $arItem,
																'PARAMS' => $arParams,
																'SHOW_SCHEMA' => false,
																'TO_LINE' => true,
																'PRICE_FONT' => 16,
																'PRICEOLD_FONT' => 12,
																'PRICES' => [
																	'PRICE' => $arItem['PROPERTY_PRICE_VALUE'],
																	'PRICE_OLD' => $arItem['PROPERTY_PRICEOLD_VALUE'],
																	'PRICE_CURRENCY' => $arItem['PROPERTY_PRICE_CURRENCY_VALUE'],
																]
															]);?>
														</div>
														<div class="line-block__item cart__item-counter buy_block">
															<div class="counter counter--basket bordered rounded-4">
																<span class="counter__action counter__action--minus"></span>
																<div class="counter__count-wrapper">
																	<input type="text" value="<?=$quantity;?>" class="counter__count" maxlength="6">
																</div>
																<span class="counter__action counter__action--plus"></span>
															</div>
															<input type="hidden" name="PRICE" value="<?=$arItem['PROPERTY_FILTER_PRICE_VALUE']?>" />
														</div>
														<?if(isset($arItem['SUMM']) && $arItem['SUMM'] > 0):?>
															<div class="line-block__item cart__item-summ">
																<div class="price_new form_16 color_333 font_bold">
																	<span class="price_val"><?=$arItem['SUMM'];?></span>
																</div>
															</div>
														<?endif;?>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?endforeach;?>
							</div>
						</div>
						<?if(isset($arResult['ITEMS_SUMM']) && strlen($arResult['ITEMS_SUMM'])):?>
							<div class="foot">
								<div class="line-block line-block--24 flexbox--justify-beetwen cart-total font_18 font_bold">
									<div class="line-block__item cart-total__text">
										<?=GetMessage('T_BASKET_TOTAL_TITLE');?>
									</div>
									<div class="line-block__item cart-total__value">
										<?=$arResult['ITEMS_SUMM']?>
									</div>
								</div>
							</div>
						<?endif;?>
						<div class="buttons">
							<div class="line-block line-block--16 flexbox--justify-beetwen">
								<div class="line-block__item flex-1">
									<a class="btn btn-default btn-transparent-border btn-lg btn-wide" href="<?=$arParams['PATH_TO_BASKET']?>">
										<?=GetMessage('T_BASKET_BUTTON_BASKET');?>
									</a>
								</div>
								<div class="line-block__item flex-1">
									<a class="btn btn-default btn-lg btn-wide  to-order" href="<?=$arParams['PATH_TO_ORDER']?>">
										<?=GetMessage('T_BASKET_BUTTON_ORDER');?>
									</a>
								</div>
							</div>
						</div>
					</div>
				<?endif;?>
				
				<div class="basket_empty height-100" <?=(!$bEmptyBasket ? 'style="display:none"' : '');?>>
					<div class="wrap scrollbar">
						<?=TSolution::showIconSvg("basket", SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
						<h4 class="font_18"><?=GetMessage('T_BASKET_EMPTY_TITLE');?></h4>
						<div class="description color_666"><?=GetMessage('T_BASKET_EMPTY_DESCRIPTION');?></div>
						<div class="button">
							<a class="btn btn-default btn-transparent-border" href="<?=$catalogUrl;?>">
								<?=GetMessage('T_BASKET_BUTTON_CATALOG');?>
							</a>
						</div>
					</div>
				</div>
				
				<span class="jqmClose top-close stroke-theme-hover" id="close-basket" title="<?=\Bitrix\Main\Localization\Loc::getMessage('CLOSE_BLOCK');?>"><?=TSolution::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg')?></span>
			</div>
		</div>
		<?$frame->end();?>
	<?else:?>
		<div class="fixed_wrapper">
			<div class="right-sidebar-wrapper">
				<?TSolution::showRightDok();?>
			</div>
		</div>
	<?endif;?>
</div>