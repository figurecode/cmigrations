<?php

/**
 * Class CMigrationsIBlock
 *
 * Класс содержит свойства и методы необходимые для работы с модулем 'Информационные блоки'.
 *
 * @version Version 1.0
 *
 * @author Matyuha Dima
 */
class CMigrationsIBlock extends CMigrations
{

	function __construct()
	{
		if (!parent::initModules('iblock')) {
			throw new Exception('Ошибка создания экземпляра класса. Модуль «Информационные блоки» не найден');
		}
	}


	/**
	 * Метод newBlock добавляет новый информационный блок.
	 *
	 * @param array $arNewBlockFields - массив параметров инфоблока
	 *
	 * @return array - Массив вида array('RESULT' => int, 'LOG' => string);
	 */

	public function newBlock($arNewBlockFields)
	{
		/**
		 * @var array   $arResult
		 * @var CIBlock $obBlock
		 */
		$arResult = array(
			'RESULT' => 0,
			'LOG' => ''
		);
		$obBlock = new CIBlock;

		$arResult['RESULT'] = $obBlock->Add($arNewBlockFields);
		if ($arResult['RESULT'] > 0) {
			$arFields = CIBlock::getFields($arResult['RESULT']);
			$arFields['CODE']['IS_REQUIRED'] = 'Y';
			$arFields['CODE']['DEFAULT_VALUE']['UNIQUE'] = 'Y'; //Если код задан, то проверять на уникальность
			$arFields['CODE']['DEFAULT_VALUE']['TRANSLITERATION'] = 'Y'; //Транслитерировать из названия при добавлении элемента
			CIBlock::setFields($arResult['RESULT'], $arFields);
		} else {
			$arResult['LOG'] = 'ERROR: Ошибка создания нового информационного блока.';
		}

		return $arResult;
	}

	/**
	 * Метод newBlockProperty добавляет новое свойство в инфоблок
	 *
	 * @param array $arNewPropertyList - многомерный массив, параметров, новых свойств инфоблока
	 * Пример массива который передается в функцию:
	 * $arNewPropertyList = array('новое свойство 1' => array('поле'=>'значение'), 'новое свойство 2' => array('поле'=>'значение'));
	 *
	 * @return array - Массив вида array('RESULT' => bool, 'LOG' => string);
	 */
	public function newBlockProperty($arNewPropertyList)
	{
		/** @var array $arResult */
		$arResult = array(
			'RESULT' => true,
			'LOG' => ''
		);

		/** @var CIBlockProperty $obPropertyBlock */
		$obPropertyBlock = new CIBlockProperty;

		foreach ($arNewPropertyList as $arProperty) {
			$result = $obPropertyBlock->Add($arProperty);
			if (!($result > 0)) {
				$arResult['RESULT'] = false;
				$arResult['LOG'] = $arResult['LOG'] . $arNewPropertyList['NAME'] . ' : ' . $obPropertyBlock->LAST_ERROR . '<br/>';
			}
		}

		return $arResult;
	}


	/**
	 * Метод getBlockPropertyEnum позволяет получить ID варианта значения типа список.
	 *
	 * @param $IBLOCK_ID - ID инфоблока
	 * @param $strCodeProp - Код свойства
	 * @param $strCodePropValue - Код значения
	 * @param $strCodePropValue - Значение
	 *
	 * @return int
	 */
	public function getBlockPropertyEnum($IBLOCK_ID, $strCodeProp, $strCodePropValue = '', $strValuePropValue = '')
	{
		/**
		 * @var int $intEnum
		 * @var CIBlockPropertyEnum $obPropertyEnum
		 */
		$intEnum = 0;
		$obPropertyEnum = new CIBlockPropertyEnum;

		$dbPropertyEnums = $obPropertyEnum->GetList(
			Array('SORT' => 'ASC'),
			array('IBLOCK_ID' => $IBLOCK_ID, 'CODE' => $strCodeProp)
		);
		while ($arPropertyEum = $dbPropertyEnums->GetNext()) {
			if (($arPropertyEum['XML_ID'] === $strCodePropValue) or ($arPropertyEum['VALUE'] == $strValuePropValue)) {
				$intEnum = $arPropertyEum['ID'];
			}
		}

		return $intEnum;
	}


	/**
	 * Метод addSectionBlock добавляет новый раздел/подраздел в инфоблок.
	 *
	 * @param array $arFields - массив с полями раздела
	 *
	 * @return array $arResult = array (
	 * 		'ID' => 'ID нового раздела, в случае если метод отработал без ошибок'
	 * 		'LOG' => 'текст ошибки',
	 * );
	 */
	public function addSectionBlock($arFields)
	{
		$arResult = array();

		/** @var CIBlockSection $obBlockSection */
		$obBlockSection = new CIBlockSection;

		$arResult['ID'] = $obBlockSection->Add($arFields);
		if ($arResult['ID'] <= 0) {
			$arResult['LOG'] = $obBlockSection->LAST_ERROR;
		}

		return $arResult;
	}


	/**
	 * Метод addElementBlock добавляет новый элемент в информационный блок
	 *
	 * @param int $IBLOCK_ID - ID инфоблока, в который будут добавлены элементы
	 * @param int $SECTION_ID - ID раздела, в который будут добавлены элементы
	 * @param array $arElementField - параметры элемента
	 *
	 * @return array - Массив вида array('RESULT' => int, 'LOG' => string);
	 */
	public function addElementBlock($IBLOCK_ID, $arElementField, $SECTION_ID = 0)
	{
		/**
		 * @var array $arResult
		 * @var CIBlockElement $obBlockElement
		 * @var array $arCodeParams
		 * @var array $arElement
		 */
		$arResult = array();
		$obBlockElement = new CIBlockElement;
		$arCodeParams = Array(
			'max_len' => '100', // обрезает символьный код до 100 символов
			'change_case' => 'L', // буквы преобразуются к нижнему регистру
			'replace_space' => '-', // Замена для символа пробела
			'replace_other' => '-', // Замена для прочих символов
			'delete_repeat_replace' => 'true', // Удалять лишние символы замены
			'use_google' => 'false', // отключаем использование google
		);
		$arElement = Array(
			'IBLOCK_SECTION_ID' => $SECTION_ID,
			'IBLOCK_ID' => $IBLOCK_ID,
			'CODE' => CUtil::translit($arElementField['CODE'], 'ru', $arCodeParams),
			'ACTIVE' => 'Y'
		);
		$arElement = array_merge($arElement, $arElementField);

		$arResult['RESULT'] = $obBlockElement->Add($arElement);
		if (!$arResult['RESULT']) {
			$arResult['LOG'] = 'ERROR: Ошибка добавления элемента: ' . $arElementField['NAME'] . '<br/>' . $obBlockElement->LAST_ERROR;
		}

		return $arResult;
	}


	/**
	 * Метод iblockAttachCatalog служит для добавления новой записи в таблицу привязывания информационного блока к модулю торгового каталога.
	 *
	 * @param array $arFields
	 *
	 * @return bool
	 */
	public function iblockAttachCatalog($arFields)
	{
		if (parent::initModules('catalog')) {
			return CCatalog::Add($arFields) ? true : false;
		} else {
			return false;
		}
	}


	/**
	 * Метод elementSetBasePrice служит для установки базовой цены, для элемента инфоблока который является
	 * торговым каталогом.
	 *
	 * @param int $intElementId
	 * @param int $intPrice
	 *
	 * @return bool
	 */
	public function elementSetBasePrice($intElementId, $intPrice)
	{
		if (parent::initModules('catalog')) {
			return CPrice::SetBasePrice($intElementId, $intPrice, CCurrency::GetBaseCurrency()) ? true : false;
		} else {
			return false;
		}
	}


	/**
	 * Метод elementAttachProduct добавляет (или обновляет) параметры товара к элементу каталога.
	 *
	 * @param array $arFields
	 *
	 * @return bool
	 */

	public function elementAttachProduct($arFields)
	{
		if (parent::initModules('catalog')) {
			return CCatalogProduct::Add($arFields) ? true : false;
		} else {
			return false;
		}
	}
}