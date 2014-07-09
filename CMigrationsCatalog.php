<?php

/**
 * Class CMigrationsCatalog
 *
 * Класс содержит свойства и методы необходимые для работы с модулем 'Торговый каталог'.
 *
 * @version Version 1.0
 *
 * @author Matyuha Dima
 *
 * todo Добавить параметр в класс для ID инфоблока. Если надо работать в рамках одного инфоблока.
 */
class CMigrationsCatalog extends CMigrations
{

	function __construct()
	{
		/**
		 * todo Добавить проверку инфоблока на товарный каталог по ID.
		 */
		if (!parent::initModules('catalog')) {
			throw new Exception('Ошибка создания экземпляра класса. Модуль «Интернет-Магазин» не найден');
		}
	}

	/**
	 * Метод getDiscountList возвращает результат выборки записей скидок в соответствии со своими параметрами.
	 *
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arGroupBy
	 * @param array $arNavStartParams
	 * @param array $arSelectFields
	 *
	 * @return array
	 */

	public function getDiscountList(
		$arOrder = array('SORT' => 'ASC'),
		$arFilter = array(),
		$arGroupBy = array(),
		$arNavStartParams = array(),
		$arSelectFields = array()
	) {
		/**
		 * @var array            $arResult
		 * @var CCatalogDiscount $objDiscount
		 */
		$arResult = array();
		$objDiscount = new CCatalogDiscount;

		$arGroupBy ? : $arGroupBy = false;
		$arNavStartParams ? : $arGroupBy = false;

		$dbDiscountList = $objDiscount->GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
		while ($arDiscount = $dbDiscountList->GetNext()) {
			$arResult[] = $arDiscount;
		}

		return $arResult;
	}

	/**
	 * Метод addDiscount добавляет новую скидку в соответствии с данными из массива arFields.
	 *
	 * @param array $arFields
	 *
	 * @return array - Массив вида array('RESULT' => int, 'LOG' => string);
	 */

	public function addDiscount($arFields)
	{
		/**
		 * @var array            $arResult
		 * @var CCatalogDiscount $objDiscount
		 */
		$arResult = array();
		$objDiscount = new CCatalogDiscount;

		$arResult['RESULT'] = $objDiscount->Add($arFields);
		$arResult['RESULT'] ?: $arResult['LOG'] = 'Ошибка добавления скидки. Проверте параметры';

		return $arResult;
	}


	/**
	 * Метод addDiscountCoupon добавляет купон для выбранной скидки.
	 *
	 * @param array $arFields
	 *
	 * @return array - Массив вида array('RESULT' => int, 'LOG' => string);
	 */

	public function addDiscountCoupon($arFields)
	{
		/**
		 * @var array            $arResult
		 * @var CCatalogDiscount $objDiscount
		 */
		$arResult = array();
		$objDiscount = new CCatalogDiscountCoupon;

		$arResult['RESULT'] = $objDiscount->Add($arFields);
		$arResult['RESULT'] ?: $arResult['LOG'] = 'Ошибка добавления купона '.$arFields['COUPON'].'. Проверте параметры';

		return $arResult;
	}
} 