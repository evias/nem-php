<?php

namespace evias\NEMBlockchain\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AdminLTE.
 */
class Nem extends Facade
{
	/**
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Nem';
	}
}
