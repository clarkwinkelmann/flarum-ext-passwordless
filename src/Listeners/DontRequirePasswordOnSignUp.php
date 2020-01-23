<?php

namespace ClarkWinkelmann\PasswordLess\Listeners;

use Flarum\User\Event\Saving;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class DontRequirePasswordOnSignUp
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'defaultPassword']);
    }

    public function defaultPassword(Saving $event)
    {
        // If a user is being created and didn't set a password, we generate a random one so that the validation passes
        if (!$event->user->exists && !$event->user->password) {
            $event->user->password = Str::random(20);
        }
    }
}
