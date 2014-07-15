<?php

/**
 * Class CMigrations
 *
 * Содержит базовые свойства и методы необходимые для выпонения миграций в CMS 1C Bitrix
 *
 * @version Version 2.0
 * @author Matyuha Dima
 */
class CMigrations
{
	/**
	 * Метод initModules проверяет установлен ли модуль с которым собираемся работать
	 *
	 * @param string $strModuleName - строка, содержит название модуля
	 *
	 * @return bool
	 */
	public function initModules($strModuleName)
	{
		return CModule::IncludeModule($strModuleName) ? true : false;
	}


	/**
	 * Метод getSiteList предназначен для получения списка сайтов
	 *
	 * @param string $strSort - По какому полю сортируем
	 * @param string $strOrder - Порядок сортировки.
	 * @param array $arFilter - Массив вида array("фильтруемое поле"=>"значение" [, ...]).
	 *
	 * @return array
	 */
	public function getSiteList($strSort = 'sort', $strOrder = 'asc', $arFilter = array())
	{
		/**
		 * @var array $arResult
		 * @var CSite $obSite
		 */
		$arResult = array();
		$obSite = new CSite;

		$dbSite = $obSite->GetList($strSort, $strOrder, $arFilter);
		while ($arResult[] = $dbSite->GetNext()) {

		}

		return $arResult;
	}


	/**
	 * Метод writeArrayToFile записывает массив в файл
	 *
	 * @param array $arData - массив данных
	 * @param string $strFile - путь к файлу
	 *
	 * @return array - Массив вида array('RESULT' => bool, 'LOG' => string);
	 */
	public function writeArrayToFile ($arData, $strFile)
	{
		/**
		 * @var array $arResult
		 * @var string $strWriteArray
		 */
		$arResult = array(
			'RESULT' => false,
			'LOG' => ''
		);
		$strWriteArray = serialize($arData);

		$file = fopen($strFile, 'w');
		if (!$file) {
			$arResult['LOG'] = 'Ошибка: не удалось открыть файл';
			return $arResult;
		}

		if (fwrite($file, $strWriteArray) == false) {
			$arResult['LOG'] = 'Ошибка: не удалось записать данные в файл';
			return $arResult;
		}

		if (fclose($file)) {
			$arResult['RESULT'] = true;
		} else {
			$arResult['LOG'] = 'Ошибка: не удалось закрыть файл после записи';
		}

		return $arResult;
	}


	/**
	 * Метод extractArrayOfFile извлекает массив из файл
	 *
	 * @param string $strFile - путь к файлу
	 *
	 * @return mixed - вернет массив данных, или false в случае ошибки
	 */
	public function extractArrayOfFile ($strFile)
	{
		/** @var array $arResult */
		$arResult = array();

		$file = file_get_contents($strFile);

		return $file ?  $arResult = unserialize($file) : false;
	}

}