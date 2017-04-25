<?php
namespace Vynatu\DatabaseConfig;

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Published resources
         */
        $this->publishes(
            [
                __DIR__ . '/config/config.php' => config_path('database_config.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php',
            'database_config'
        );

        $this->publishes(
            [
                __DIR__ . '/migrations', $this->app->databasePath() . '/migrations',
            ],
            'migrations'
        );

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        /*
         * Register the caching provider (and redis) in case it was not previously registered
         */

        if ($this->app['config']['cache.default'] == 'redis') {
            $this->app->register(RedisServiceProvider::class);
        }

        $this->app->register(CacheServiceProvider::class);


        /*
         * Override the config repository
         */

        $repo = new ConfigRepository([], $this->app['cache']);

        // Set config (table, lazy-load)
        $repo->setConfig(
            $this->app['config']['database_config']
        );

        // Set database
        $repo->setDatabase(
            $this->app['db']->connection()
        );

        foreach ($this->app['config']->all() as $key => $value) {
            $repo->set($key, $value);
        }

        $this->app->instance('config', $repo);

        try {
            $repo->populateCache();
        } catch (\Exception $abc) {
            //
        }

    }
}