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
 * @version    0.0.2
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/php-nem-laravel
 */
namespace evias\NEMBlockchain;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

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
        $this->registerConfig();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            "nem",
        ];
    }

    /**
     * Setup the NEM blockchain config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config') . "/nem.php";
        if (! $this->isLumen())
            // console laravel, use config_path helper
            $this->publishes([$source => config_path('nem.php')]);
        else
            // lumen configure app
            $this->app->configure('nem');

        $this->mergeConfigFrom($source, 'nem');
    }

    /**
     * Check if we are running Lumen or not.
     *
     * @return bool
     */
    protected function isLumen()
    {
        return strpos($this->app->version(), 'Lumen') !== false;
    }

    /**
     * Check if we are running on PHP 7.
     *
     * @return bool
     */
    protected function isRunningPhp7()
    {
        return version_compare(PHP_VERSION, '7.0-dev', '>=');
    }

    /**
     * Register Twig config option bindings.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->app->bindIf('nem', function () {
            return $this->app['config']->get('nem');
        });
    }
}
