<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\BootstrapCMS\Repositories;

use GrahamCampbell\BootstrapCMS\Models\Page;
use GrahamCampbell\BootstrapCMS\Repositories\PageRepository;
use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Contracts\Console\Kernel;

/**
 * This is the page repository test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class PageRepositoryTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function runInstallCommand()
    {
        $this->app->make(Kernel::class)->call('app:install');
    }

    /**
     * Get the page repository instance.
     *
     * @return \GrahamCampbell\BootstrapCMS\Repositories\PageRepository
     */
    protected function getRepository()
    {
        return $this->app['pagerepository'];
    }

    public function testCreate()
    {
        $page = $this->getRepository()->create([
            'title'      => 'Test Page',
            'nav_title'  => 'Test',
            'slug'       => 'test-page',
            'body'       => 'Hello world',
            'show_title' => true,
            'show_nav'   => false,
            'user_id'    => 1,
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('test-page', $page->slug);
        $this->assertSame('Test Page', $page->title);
        $this->assertSame(1, $page->user_id);
    }

    public function testFindBySlug()
    {
        $page = $this->getRepository()->find('home');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('home', $page->slug);
    }

    public function testFindMissingSlugReturnsNull()
    {
        $this->assertNull($this->getRepository()->find('does-not-exist'));
    }

    public function testValidatePasses()
    {
        $validator = $this->getRepository()->validate([
            'title'      => 'Test Page',
            'nav_title'  => 'Test',
            'slug'       => 'test-page',
            'body'       => 'Hello world',
            'show_title' => true,
            'show_nav'   => false,
            'user_id'    => 1,
        ], ['title', 'nav_title', 'slug', 'body', 'show_title', 'show_nav', 'user_id']);

        $this->assertFalse($validator->fails());
    }

    public function testValidateFailsOnMissingTitle()
    {
        $validator = $this->getRepository()->validate([
            'nav_title'  => 'Test',
            'slug'       => 'test-page',
            'body'       => 'Hello world',
            'show_title' => true,
            'show_nav'   => false,
            'user_id'    => 1,
        ], ['title', 'nav_title', 'slug', 'body', 'show_title', 'show_nav', 'user_id']);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('title'));
    }

    public function testValidateFailsOnInvalidSlug()
    {
        $validator = $this->getRepository()->validate([
            'title'      => 'Test Page',
            'nav_title'  => 'Test',
            'slug'       => 'not a valid slug!',
            'body'       => 'Hello world',
            'show_title' => true,
            'show_nav'   => false,
            'user_id'    => 1,
        ], ['title', 'nav_title', 'slug', 'body', 'show_title', 'show_nav', 'user_id']);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    public function testNavigationContainsNavPages()
    {
        $this->getRepository()->create([
            'title'      => 'Nav Page',
            'nav_title'  => 'Nav',
            'slug'       => 'nav-page',
            'body'       => 'Hello world',
            'show_title' => true,
            'show_nav'   => true,
            'user_id'    => 1,
        ]);

        $navigation = $this->getRepository()->navigation();

        $this->assertTrue(is_array($navigation));
        $this->assertNotEmpty($navigation);

        $slugs = array_column($navigation, 'slug');
        $this->assertContains('pages/nav-page', $slugs);
    }

    public function testCount()
    {
        $this->assertSame(3, $this->getRepository()->count());
    }

    public function testIndex()
    {
        $pages = $this->getRepository()->index();

        $this->assertCount(3, $pages);
    }

    public function testDeleteHomeThrows()
    {
        $page = $this->getRepository()->find('home');

        $this->setExpectedException('Exception', 'You cannot delete the homepage.');
        $page->delete();
    }
}
