<?php

namespace ClarkWinkelmann\PasswordLess\Listeners;

use Flarum\Foundation\Event\Validating;
use Flarum\User\Event\Saving;
use Flarum\User\UserValidator;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DontRequirePasswordOnSignUp
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Saving::class, [$this, 'defaultPassword']);
        $events->listen(Validating::class, [$this, 'validating']);
    }

    public function defaultPassword(Saving $event)
    {
        // If a user is being created and didn't set a password, we generate a random one so that the validation passes
        if (!$event->user->exists && !$event->user->password) {
            $event->user->password = Str::random(20);
        }
    }

    public function validating(Validating $event)
    {
        // because the password being checked is the one stored in the $password variable of the RegisterUserHandler,
        // our random password isn't taken into account
        // So we need to change the default validator so that it can accept an empty user-provided value
        if ($event->type instanceof UserValidator) {
            $rules = $event->validator->getRules();
            $passwordRules = Arr::get($rules, 'password', []);

            if (count($passwordRules)) {
                $rules['password'] = array_map(function ($rule) {
                    if ($rule === 'required') {
                        return 'sometimes';
                    }

                    return $rule;
                }, $passwordRules);

                $event->validator->setRules($rules);
            }
        }
    }
}
