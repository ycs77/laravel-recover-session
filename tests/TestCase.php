<?php

namespace Ycs77\LaravelRecoverSession\Tests;

use Illuminate\Contracts\Config\Repository as Config;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Config $config) {
            $config->set('app.key', 'base64:Wcss5GOQHb19G93cevYxpeZf39zhOvIxxY7ZzY/48lM=');
        });
    }
}
