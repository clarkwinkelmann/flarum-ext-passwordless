<?php

namespace ClarkWinkelmann\PasswordLess;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\CheckingPassword;
use Flarum\User\Event\Saving;
use Flarum\User\UserValidator;

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

    (new Extend\View())
        ->namespace('passwordless', __DIR__ . '/resources/views'),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->mutate(function () {
            /**
             * @var SettingsRepositoryInterface $settings
             */
            $settings = app(SettingsRepositoryInterface::class);

            return [
                'passwordless.passwordlessLoginByDefault' => (bool)$settings->get('passwordless.passwordlessLoginByDefault', true),
                'passwordless.hideSignUpPassword' => (bool)$settings->get('passwordless.hideSignUpPassword', true),
            ];
        }),

    (new Extend\Event())
        ->listen(CheckingPassword::class, Listeners\CheckPassword::class)
        ->listen(Saving::class, Listeners\SaveUser::class),

    (new Extend\Validator(UserValidator::class))
        ->configure(MakePasswordOptional::class),
];
