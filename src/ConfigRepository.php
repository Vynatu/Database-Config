<?php
/**
 *
 * This file is part of Vynatu/Database-Config.
 *
 * (c) 2017 Vynatu Cyberlabs, Inc. <felix@vynatu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vynatu\DatabaseConfig;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Database\Connection;

class ConfigRepository extends Repository
{
    /**
     * @var Connection
     */
    protected $database;

    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @var array
     */
    protected $packageConfig;

    /**
     * @var array
     */
    protected $configCache;

    public function __construct(array $items = [], CacheManager $cache = null)
    {
        $this->cache = $cache;

        parent::__construct($items);
    }

    /**
     * Fetches a configuration item
     *
     * @param string $key
     * @param null   $default
     * @param bool   $forceFileSystem Gets the value set in the environment file or the config files regardless of the
     *                                database value.
     *
     * @return mixed
     */
    public function get($key, $default = null, $forceFileSystem = false)
    {
        if ($forceFileSystem) {
            return parent::get($key, $default);
        }

        if (! $this->isConstrained($key)) {
            return $this->fetchFromDb($key) ?: parent::get($key, $default);
        }

        return parent::get($key, $default);
    }


    /**
     * Fetches a value from the database (basically from the cache)
     *
     * @param $key
     *
     * @return mixed|null
     */
    public function fetchFromDb($key)
    {
        return isset($this->configCache[$key]) ? json_decode($this->configCache[$key]) : null;
    }

    /**
     * Sets a config item
     *
     * @param array|string $key
     * @param null         $value
     * @param bool         $persist Persists in the database (permanently save)
     */
    public function set($key, $value = null, $persist = false)
    {
        if ($persist) {
            // Will not save the value if the config item is constrained
            if($this->isConstrained($key)) {
                return;
            }

            $exist_query = $this->database->table(
                $this->packageConfig['table']
            )->where('item', $key);

            if ($exist_query->first()) {
                if ($value === null) {
                    $this->removeFromDatabase($key);

                    return;
                }

                $exist_query->update(['value' => json_encode($value)]);
                parent::set($key, $value);
                $this->clearCache(true);
            } else {
                $this->database->table(
                    $this->packageConfig['table']
                )->insert(['item' => $key, 'value' => json_encode($value)]);
                parent::set($key, $value);

                $this->clearCache(true);

                return;
            }
        } else {
            parent::set($key, $value);
        }
    }

    /**
     * Permanently remove a config item from the database
     *
     * @param $item
     */
    public function removeFromDatabase($item)
    {
        $this->database->table(
            $this->packageConfig['table']
        )->where('item', $item)->delete();

        $this->clearCache();
    }

    /**
     * Clear local cache
     *
     * @param bool $global
     */
    public function clearCache($global = false)
    {
        $this->configCache = [];

        if ($global) {
            $this->cache->forget('database_config');
        }

        $this->populateCache();
    }

    /**
     * Re-populates the cache
     */
    public function populateCache()
    {
        $this->configCache = [];

        $data = $this->cache->rememberForever(
            'database_config',
            function () {
                return $this->database->table('config')->get();
            }
        );

        foreach ($data as $config) {
            $this->configCache[$config->item] = $val = json_decode($config->value);
            parent::set($config->item, $val);
        }
    }

    /**
     * Sets the package config
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->packageConfig = $config;
    }

    public function setDatabase(Connection $connection)
    {
        $this->database = $connection;
    }

    /**
     * Checks wether an item is constrained or not.
     *
     * @param $item
     *
     * @return bool
     */
    protected function isConstrained($item)
    {
        return $this->packageConfig['enable_constraints'] ? (
        in_array($item, $this->packageConfig['constaints']) ? false : true
        ) : false;
    }
}