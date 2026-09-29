<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\BootstrapCMS\Acceptance;

/**
 * This is the page test class.
 *
 * The create, store, edit, update and destroy flows all need an authenticated
 * user with edit permission, so they are driven through the http kernel with a
 * stubbed credentials service in tests/Http/Controllers/PageControllerTest
 * instead. They used to live here as permanently skipped tests, which counted
 * as coverage without ever asserting anything.
 *
 * @group page
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class PageTest extends AbstractTestCase
{
    public function testIndex()
    {
        $this->visit('/')->seePageIs('pages/home');
    }

    public function testShowFail()
    {
        $this->get('pages/error');

        $this->assertEquals(404, $this->response->status());
    }

    public function testShowSuccess()
    {
        $this->visit('pages/home')->see('Bootstrap CMS');
    }
}
