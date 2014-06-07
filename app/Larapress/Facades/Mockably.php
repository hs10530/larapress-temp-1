<?php

namespace Larapress\Facades;

use Illuminate\Support\Facades\Facade;

class Mockably extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'mockably';
	}

}
