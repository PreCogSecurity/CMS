<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\BootstrapCMS\Http\Controllers;

use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Contracts\Console\Kernel;

/**
 * This is the page controller test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class PageControllerTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function runInstallCommand()
    {
        $this->app->make(Kernel::class)->call('app:install');
    }

    /**
     * @before
     */
    public function mockCredentials()
    {
        $credentials = \Mockery::mock('GrahamCampbell\Credentials\Credentials');
        $credentials->shouldReceive('check')->andReturn(true);
        $credentials->shouldReceive('hasAccess')->andReturn(true);
        $credentials->shouldReceive('getuser')->andReturn((object) ['id' => 1]);
        $credentials->shouldReceive('getUser')->andReturn((object) ['id' => 1]);
        $credentials->shouldReceive('getDecoratedUser')->andReturn((object) ['email' => 'admin@example.com']);

        $this->app->instance('credentials', $credentials);
        $this->app->instance('GrahamCampbell\Credentials\Credentials', $credentials);

        \Illuminate\Support\Facades\Facade::clearResolvedInstance('credentials');
    }

    public function testIndexRedirectsToHome()
    {
        $this->get('pages');

        $this->assertRedirectedTo('pages/home');
    }

    public function testShowSuccess()
    {
        $this->get('pages/home');

        $this->assertResponseOk();
    }

    public function testShowFail()
    {
        $this->get('pages/error');

        $this->assertResponseStatus(404);
    }

    public function testStoreValidationFails()
    {
        $this->post('pages', []);

        $this->assertRedirectedTo('pages/create');
        $this->assertSessionHasErrors();
        $this->assertHasOldInput();
    }

    public function testStoreSuccess()
    {
        $this->post('pages', [
            'title'      => 'New Page',
            'nav_title'  => 'Herro',
            'slug'       => 'foobar',
            'icon'       => '',
            'body'       => 'Why herro there!',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->assertRedirectedTo('pages/foobar');
        $this->assertSessionHas('success');
    }

    public function testStoreRejectsASlugThatIsAlreadyInUse()
    {
        // 'home' is seeded, and the homepage is not ours to shadow
        $this->post('pages', [
            'title'      => 'Impostor',
            'nav_title'  => 'Impostor',
            'slug'       => 'home',
            'icon'       => '',
            'body'       => 'Not really the homepage.',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->assertRedirectedTo('pages/create');
        $this->assertSessionHasErrors('slug');
        $this->assertHasOldInput();
    }

    public function testUpdateValidationFails()
    {
        $this->patch('pages/home', []);

        $this->assertRedirectedTo('pages/home/edit');
        $this->assertSessionHasErrors();
        $this->assertHasOldInput();
    }

    public function testUpdateHomeSlugChangeFails()
    {
        $this->patch('pages/home', [
            'title'      => 'New Page',
            'nav_title'  => 'Herro',
            'slug'       => 'foobar',
            'icon'       => '',
            'body'       => 'Why herro there!',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->assertRedirectedTo('pages/home/edit');
        $this->assertSessionHas('error');
        $this->assertHasOldInput();
    }

    public function testUpdateHomeNavOffFails()
    {
        $this->patch('pages/home', [
            'title'      => 'New Page',
            'nav_title'  => 'Herro',
            'slug'       => 'home',
            'icon'       => '',
            'body'       => 'Why herro there!',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'off',
        ]);

        $this->assertRedirectedTo('pages/home/edit');
        $this->assertSessionHas('error');
        $this->assertHasOldInput();
    }

    public function testUpdateSuccess()
    {
        $this->patch('pages/home', [
            'title'      => 'New Page',
            'nav_title'  => 'Herro',
            'slug'       => 'home',
            'icon'       => '',
            'body'       => 'Why herro there!',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->assertRedirectedTo('pages/home');
        $this->assertSessionHas('success');
    }

    public function testUpdateRejectsASlugThatIsAlreadyInUse()
    {
        // moving a page onto the homepage slug would leave two live pages
        // answering on the same url
        $this->patch('pages/about', [
            'title'      => 'About',
            'nav_title'  => 'About',
            'slug'       => 'home',
            'icon'       => '',
            'body'       => 'About us',
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->assertRedirectedTo('pages/about/edit');
        $this->assertSessionHasErrors('slug');
        $this->assertHasOldInput();
    }

    public function testEvalIsDisabledByDefault()
    {
        $this->assertFalse($this->app['config']->get('cms.eval'));
    }

    public function testPageBodyIsNotExecuted()
    {
        $this->patch('pages/home', [
            'title'      => 'Home',
            'nav_title'  => 'Home',
            'slug'       => 'home',
            'icon'       => '',
            'body'       => "<?php echo strrev('desrever'); ?>",
            'css'        => '',
            'js'         => '',
            'show_title' => 'on',
            'show_nav'   => 'on',
        ]);

        $this->get('pages/home');

        $this->assertResponseOk();

        // the body is output as it is, it is never handed to php's eval
        $this->assertNotContains('reversed', $this->response->getContent());
    }

    public function testContactPlaceholderIsExpanded()
    {
        $this->get('pages/contact');

        $this->assertResponseOk();

        $content = $this->response->getContent();

        // the contact form is pulled in per request, so its csrf token is fresh
        $this->assertNotContains('{contact}', $content);
        $this->assertContains('name="first_name"', $content);
    }

    public function testDestroyHomeFails()
    {
        $this->delete('pages/home');

        $this->assertRedirectedTo('pages/home');
        $this->assertSessionHas('error');
    }

    public function testDestroySuccess()
    {
        $this->delete('pages/about');

        $this->assertRedirectedTo('pages/home');
        $this->assertSessionHas('success');
    }
}
