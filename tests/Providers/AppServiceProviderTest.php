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

use GrahamCampbell\BootstrapCMS\Providers\AppServiceProvider;
use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;

/**
 * This is the app service provider test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class AppServiceProviderTest extends AbstractTestCase
{
    public function testPlaceholderKeysAreWeak()
    {
        $this->assertTrue(AppServiceProvider::hasWeakAppKey('SomeRandomString'));
        $this->assertTrue(AppServiceProvider::hasWeakAppKey(''));
        $this->assertTrue(AppServiceProvider::hasWeakAppKey(null));
        $this->assertTrue(AppServiceProvider::hasWeakAppKey(str_repeat('a', 31)));
    }

    public function testGeneratedKeysAreStrong()
    {
        $this->assertFalse(AppServiceProvider::hasWeakAppKey(str_repeat('a', 32)));
    }
}
