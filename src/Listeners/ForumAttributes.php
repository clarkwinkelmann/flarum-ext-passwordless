<?php

namespace ClarkWinkelmann\PasswordLess\Listeners;

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

class ForumAttributes
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Serializing::class, [$this, 'attributes']);
    }

    public function attributes(Serializing $event)
    {
        if ($event->isSerializer(ForumSerializer::class)) {
            /**
             * @var SettingsRepositoryInterface $settings
             */
            $settings = app(SettingsRepositoryInterface::class);

            $event->attributes['passwordless.passwordlessLoginByDefault'] = (bool)$settings->get('passwordless.passwordlessLoginByDefault', true);
            $event->attributes['passwordless.hideSignUpPassword'] = (bool)$settings->get('passwordless.hideSignUpPassword', true);
        }
    }
}
