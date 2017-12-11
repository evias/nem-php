<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM;

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
        $this->registerPreConfiguredApiClients();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            "nem.config",
            "nem",
            "nem.ncc",
            "nem.sdk",
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
            $this->app->configure('nem.config');

        $this->mergeConfigFrom($source, 'nem.config');
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
        $this->app->bindIf('nem.config', function () {
            return $this->app['config']->get('nem.config');
        }, true);
    }

    /**
     * Register all pre-configured NEM API clients.
     *
     * This will register following IoC bindings:
     * - nem : using the primary NIS server configuration
     * - nem.testing : using the testing NIS server configuration
     * - nem.ncc : using the primary NCC client configuration
     * - nem.ncc.testing : using the testing NCC client configuration
     * - nem.sdk : The SDK interface
     *
     * All registered bindings will return an instance of
     * NEM\API.
     *
     * @see  \NEM\API
     * @return void
     */
    protected function registerPreConfiguredApiClients()
    {
        $this->app->bindIf("nem", function()
        {
            $environment = env("APP_ENV", "testing");
            $envConfig   = $environment == "production" ? "primary" : "testing";

            $config  = $this->app["nem.config"];
            $nisConf = $config["nis"][$envConfig];
            $client  = new API($nisConf);

            return $client;
        }, true); // shared=true

        $this->app->bindIf("nem.ncc", function()
        {
            $environment = env("APP_ENV", "testing");
            $envConfig   = $environment == "production" ? "primary" : "testing";

            $config  = $this->app["nem.config"];
            $nccConf = $config["ncc"][$envConfig];
            $client  = new API($nccConf);

            return $client;
        }, true); // shared=true

        $this->app->bindIf("nem.sdk", function()
        {
            $environment = env("APP_ENV", "testing");
            $envConfig   = $environment == "production" ? "primary" : "testing";

            $api = $this->app["nem"];
            $sdk = new SDK($this->app);

            return $sdk;
        }, true); // shared=true
    }
}
