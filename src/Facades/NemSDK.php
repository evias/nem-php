<?php

namespace NEM\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AdminLTE.
 */
class NemSDK extends Facade
{
	/**
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'NemSDK';
	}
}
