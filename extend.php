<?php

namespace ClarkWinkelmann\PasswordLess;

use Flarum\Extend;
use Flarum\Foundation\Application;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\Routes('forum'))
        ->get('/passwordless-login', 'clarkwinkelmann.passwordless', Controllers\LoginFromTokenController::class),

    (new Extend\Routes('api'))
        ->post('/passwordless-request', 'clarkwinkelmann.passwordless', Controllers\RequestTokenController::class),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    function (Application $app, Dispatcher $events) {
        $app['view']->addNamespace('passwordless', __DIR__ . '/resources/views');

        $events->subscribe(Listeners\CheckPassword::class);
        $events->subscribe(Listeners\DontRequirePasswordOnSignUp::class);
        $events->subscribe(Listeners\ForumAttributes::class);
    },
];
