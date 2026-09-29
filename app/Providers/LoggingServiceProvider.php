<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\BootstrapCMS\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\JsonFormatter;

/**
 * This is the logging service provider class.
 *
 * Laravel 5.1 is configured with a single log driver, so rather than pulling
 * in a logging framework we reuse monolog, which is already on the page, and
 * swap its formatter for a structured one when the operator asks for it. That
 * turns the daily log file into one json object per line, which log shippers,
 * dashboards and grep based incident response can all consume.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app['config']->get('app.log_json')) {
            return;
        }

        $this->useJsonFormatter();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Turn every configured log handler into a structured one.
     *
     * @return $this
     */
    protected function useJsonFormatter()
    {
        $logger = Log::getMonolog();

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new JsonFormatter());
        }

        return $this;
    }
}
