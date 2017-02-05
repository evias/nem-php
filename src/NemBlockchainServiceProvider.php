<?php
/**
 * Part of the evias/php-nem-laravel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/php-nem-laravel
 * @version    0.1.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace evias\NEMBlockchain;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

/**
 * This is the NemBlockchainServiceProvider class
 *
 * The boot automatism of this class includes merging the
 * configuration file present at config/nem.php
 *
 * @author Grégory Saive <greg@evias.be>
 */
class NemBlockchainServiceProvider
    extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [];
    }

    /**
     * Setup the NEM blockchain config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/nem.php');
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            // console laravel, use config_path helper
            $this->publishes([$source => config_path('nem.php')]);
        }
        elseif ($this->app instanceof LumenApplication) {
            // lumen configure app
            $this->app->configure('nem');
        }

        $this->mergeConfigFrom($source, 'nem');
    }
}