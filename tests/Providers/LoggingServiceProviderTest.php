<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\BootstrapCMS\Providers;

use GrahamCampbell\BootstrapCMS\Providers\LoggingServiceProvider;
use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Support\Facades\Log;
use Monolog\Formatter\JsonFormatter;

/**
 * This is the logging service provider test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class LoggingServiceProviderTest extends AbstractTestCase
{
    public function testLogsAreNotStructuredByDefault()
    {
        $this->assertNotInstanceOf(JsonFormatter::class, $this->getHandler()->getFormatter());
    }

    public function testLogsAreStructuredWhenEnabled()
    {
        $handler = $this->getHandler();

        $this->app['config']->set('app.log_json', true);

        (new LoggingServiceProvider($this->app))->boot();

        $this->assertInstanceOf(JsonFormatter::class, $handler->getFormatter());
    }

    public function testStructuredOutputIsValidJson()
    {
        $this->app['config']->set('app.log_json', true);

        (new LoggingServiceProvider($this->app))->boot();

        $record = [
            'message' => 'Failed to delete page',
            'context' => ['slug' => 'home'],
            'level' => 400,
            'level_name' => 'ERROR',
            'channel' => 'local',
            'datetime' => '2026-01-01T00:00:00+00:00',
            'extra' => [],
        ];

        $decoded = json_decode($this->getHandler()->getFormatter()->format($record), true);

        $this->assertSame('Failed to delete page', $decoded['message']);
        $this->assertSame('home', $decoded['context']['slug']);
        $this->assertSame('ERROR', $decoded['level_name']);
    }

    /**
     * Get the log handler the application is configured with.
     *
     * @return \Monolog\Handler\HandlerInterface
     */
    protected function getHandler()
    {
        $handlers = Log::getMonolog()->getHandlers();

        return $handlers[0];
    }
}
