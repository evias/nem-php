<?php
/**
 * Created by PhpStorm.
 * User: Eier
 * Date: 21.07.2017
 * Time: 19:35
 */

namespace evias\NEMBlockchain;

use Illuminate\Support\ServiceProvider;


class NemServiceProvider extends ServiceProvider {

	protected $defer = true;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		$this->app->bind( 'NemSDK', function () {

			return new NemSDK();

		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return [ 'NemSDK' ];
	}

}