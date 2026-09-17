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
 * This is the comment controller test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class CommentControllerTest extends AbstractTestCase
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

    public function testIndexSuccess()
    {
        $this->get('blog/posts/1/comments');

        $this->assertResponseOk();

        $json = json_decode($this->response->getContent(), true);
        $this->assertSame([['comment_id' => 1, 'comment_ver' => 1]], $json);
    }

    public function testIndexFail()
    {
        $this->get('blog/posts/999/comments');

        $this->assertResponseStatus(404);
    }

    public function testStoreValidationFails()
    {
        $this->post('blog/posts/1/comments', []);

        $this->assertResponseStatus(400);
    }

    public function testStoreSuccess()
    {
        $this->post('blog/posts/1/comments', ['body' => 'A brand new comment']);

        $this->assertResponseStatus(201);

        $json = json_decode($this->response->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame('Comment created successfully.', $json['msg']);
        $this->assertSame(2, $json['comment_id']);
    }

    public function testShowSuccess()
    {
        $this->get('blog/posts/1/comments/1');

        $this->assertResponseOk();

        $json = json_decode($this->response->getContent(), true);
        $this->assertSame('This is an example comment.', $json['comment_text']);
        $this->assertSame(1, $json['comment_ver']);
    }

    public function testShowFail()
    {
        $this->get('blog/posts/1/comments/999');

        $this->assertResponseStatus(404);
    }

    public function testUpdateValidationFails()
    {
        $this->patch('blog/posts/1/comments/1', []);

        $this->assertResponseStatus(400);
    }

    public function testUpdateMissingVersionFails()
    {
        $this->patch('blog/posts/1/comments/1', ['edit_body' => 'Updated comment']);

        $this->assertResponseStatus(400);
    }

    public function testUpdateConflictFails()
    {
        $this->patch('blog/posts/1/comments/1', [
            'edit_body' => 'Updated comment',
            'version'   => 99,
        ]);

        $this->assertResponseStatus(409);
    }

    public function testUpdateSuccess()
    {
        $this->patch('blog/posts/1/comments/1', [
            'edit_body' => 'Updated comment',
            'version'   => 1,
        ]);

        $this->assertResponseOk();

        $json = json_decode($this->response->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame('Updated comment', $json['comment_text']);
        $this->assertSame(2, $json['comment_ver']);
    }

    public function testDestroySuccess()
    {
        $this->delete('blog/posts/1/comments/1');

        $this->assertResponseOk();

        $json = json_decode($this->response->getContent(), true);
        $this->assertTrue($json['success']);
        $this->assertSame('Comment deleted successfully.', $json['msg']);
    }
}
