<?php


use aabc\base\InvalidConfigException;
use aabc\caching\DbCache;
use aabc\db\Migration;


class m150909_153426_cache_init extends Migration
{

    
    protected function getCache()
    {
        $cache = Aabc::$app->getCache();
        if (!$cache instanceof DbCache) {
            throw new InvalidConfigException('You should configure "cache" component to use database before executing this migration.');
        }
        return $cache;
    }

    
    public function up()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($cache->cacheTable, [
            'id' => $this->string(128)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary(),
            'PRIMARY KEY ([[id]])',
            ], $tableOptions);
    }

    
    public function down()
    {
        $cache = $this->getCache();
        $this->db = $cache->db;

        $this->dropTable($cache->cacheTable);
    }
}
