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

		$basketUrl = $arParams['PATH_TO_BASKET'];
		$orderUrl = $arParams['PATH_TO_ORDER'];
		$catalogUrl = $arParams['PATH_TO_CATALOG'];

		$count = $arResult['ITEMS_COUNT'];
		$bEmptyBasket = !$count;
		$title_text = $bEmptyBasket ? GetMessage('EMPTY_BASKET') : GetMessage('TITLE_BASKET', array('#SUMM#' => $arResult['ITEMS_SUMM']));
		$txt = GetMessage('T_BASKET_TITLE');
		?>
		<div class="basket top">
			<!-- noindex -->
			<a rel="nofollow" title="<?=$title_text?>" href="<?=$basketUrl?>" class="fill-theme-hover light-opacity-hover HEADER">
				<span class="js-basket-block header-cart__inner<?=($bEmptyBasket ? ' header-cart__inner--empty' : '')?>">
					<?=TSolution::showIconSvg('basket banner-light-icon-fill menu-light-icon-fill', SITE_TEMPLATE_PATH.'/images/svg/Basket_black.svg', '');?>
					<span class="header-cart__count bg-more-theme count<?=($bEmptyBasket ? ' empted' : '')?>"><?=$count?></span>
				</span>

				<?if(strlen($txt)):?>
					<span class="header__icon-name header-cart__name dark_link menu-light-text banner-light-text"><?=$txt?></span>
				<?endif;?>
			</a>

			<div class="basket-dropdown">
				<div class="dropdown dropdown--relative color_333">
					<?if($arResult['ITEMS']):?>
						<div class="basket_wrap">
							<div class="items_wrap srollbar-custom scroll-deferred">
								<div class="cart flexbox flexbox--direction-row flexbox--wrap">
									<?foreach($arResult['ITEMS'] as $arItem):?>
										<?
										$imageSrc = (is_array($arItem['PICTURE']) && strlen($arItem['PICTURE']['IMAGE_70']['src']) ? $arItem['PICTURE']['IMAGE_70']['src'] : SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');
										$imageTitle = (is_array($arItem['PICTURE']) && strlen($arItem['PICTURE']['DESCRIPTION']) ? $arItem['PICTURE']['DESCRIPTION'] : $arItem['NAME']);
										$quantity = (isset($arItem['QUANTITY']) && $arItem['QUANTITY'] > 0 ? $arItem['QUANTITY'] : '');
										?>
										<div class="cart__item" data-item='{"ID":"<?=$arItem['ID']?>"}'>
											<div class="line-block line-block--20 line-block--align-normal">
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
														<div class="cart__item-remove remove stroke-theme-parent-all" title="<?=GetMessage('T_BUTTON_REMOVE_ITEM')?>">
															<?=TSolution::showIconSvg('remove stroke-theme-target ', SITE_TEMPLATE_PATH.'/images/svg/basket_remove.svg');?>
														</div>
													</div>
													<div class="cart__item-prices">
														<div class="line-block line-block--24 flexbox--justify-beetwen line-block--align-flex-start">
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
																<span class="cart__item-counter font_16">x  <span class="counter__count-wrapper font_bold"><?=$quantity?></span></span>
															</div>

															<?if(isset($arItem['SUMM']) && $arItem['SUMM'] > 0):?>
																<div class="line-block__item cart__item-summ">
																	<div class="price_new font_16 color_333 font_bold">
																		<span class="price_val"><?=$arItem['SUMM']?></span>
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
									<div class="line-block line-block--24 flexbox--justify-beetwen cart-total">
										<div class="line-block__item cart-total__text font_18 font_bold">
											<?=GetMessage('T_BASKET_TOTAL_TITLE');?>
										</div>
										<div class="line-block__item cart-total__value">
											<?if(
												isset($arResult['ITEMS_SUMM_WD']) &&
												$arResult['ITEMS_SUMM_WD'] > 0 &&
												$arResult['ITEMS_SUMM_WD'] != $arResult['ITEMS_SUMM']
											):?>
												<span class="price_old font_13 color_999">
													<span class="price__old-val"><?=$arResult['ITEMS_SUMM_WD']?></span>
												</span>
											<?endif;?>

											<span class="font_18 font_bold"><?=$arResult['ITEMS_SUMM']?></span>
										</div>
									</div>
									<div class="buttons">
										<div class="line-block line-block--16 flexbox--justify-beetwen">
											<div class="line-block__item flex-1">
												<a class="btn btn-default btn-transparent-border btn-wide" href="<?=$arParams['PATH_TO_BASKET']?>">
													<?=GetMessage('T_BASKET_BUTTON_BASKET');?>
												</a>
											</div>
											<div class="line-block__item flex-1">
												<a class="btn btn-default btn-wide  to-order" href="<?=$arParams['PATH_TO_ORDER']?>">
													<?=GetMessage('T_BASKET_BUTTON_ORDER');?>
												</a>
											</div>
										</div>
									</div>
								</div>
							<?endif;?>
						</div>
					<?endif;?>

					<div class="basket_empty height-100" <?=(!$bEmptyBasket ? 'style="display:none"' : '');?>>
						<div class="wrap">
							<?=TSolution::showIconSvg('basket', SITE_TEMPLATE_PATH."/images/svg/basket.svg");?>
							<h4 class="font_18"><?=GetMessage('T_BASKET_EMPTY_TITLE');?></h4>
							<div class="description color_666"><?=GetMessage('T_BASKET_EMPTY_DESCRIPTION');?></div>
							<div class="button">
								<a class="btn btn-default btn-transparent-border" href="<?=$catalogUrl?>">
									<?=GetMessage('T_BASKET_BUTTON_CATALOG');?>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /noindex -->
		</div>
		<script>
			$(document).ready(function() {
				if ($('.ajax_basket .basket.top').length) {
					$('.ajax_basket .basket.top .scroll-init').removeClass('scroll-init');
					$('.header-cart .basket.top').html($('.ajax_basket .basket.top').html());
					$('.header-cart .basket.top .scroll-deferred').mCustomScrollbarDeferred({
						mouseWheel: {
						scrollAmount: 150,
						preventDefault: true,
						},
					});
				}
			});
		</script>
		<?$frame->end();?>
	<?endif;?>
	<div class="fixed_wrapper">
		<div class="right-sidebar-wrapper">
			<?TSolution::showRightDok();?>
		</div>
	</div>
</div>