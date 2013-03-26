<?php

namespace app\services;

use Nette\Localization;



class Translator implements Localization\ITranslator
{
	
	/**
	 * @param string $message
	 * @param int $count
	 * @return string 
	 */
	public function translate($message, $count = NULL)
	{
		return $message;
	}
}