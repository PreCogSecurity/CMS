<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\BootstrapCMS\Presenters;

use Illuminate\Support\Facades\View;
use McCool\LaravelAutoPresenter\BasePresenter;

/**
 * This is the page presenter class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class PagePresenter extends BasePresenter
{
    use OwnerPresenterTrait;

    /**
     * Get the content to render on the page.
     *
     * A page body is content, not code: the eval feature is off by default (see
     * config/cms.php), so the only thing expanded here is the contact form
     * placeholder, and that has to be rendered per request for its csrf token
     * to be any use. Everything else is output as the author wrote it.
     *
     * @return string
     */
    public function content()
    {
        $body = $this->getWrappedObject()->body;

        if (strpos($body, '{contact}') === false) {
            return $body;
        }

        if (!View::exists('contact::form')) {
            return str_replace('{contact}', '', $body);
        }

        return str_replace('{contact}', View::make('contact::form')->render(), $body);
    }
}
