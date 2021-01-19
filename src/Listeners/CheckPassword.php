<?php

namespace ClarkWinkelmann\PasswordLess\Listeners;

use ClarkWinkelmann\PasswordLess\Token;
use Flarum\Foundation\ValidationException;
use Flarum\Locale\Translator;
use Flarum\User\Event\CheckingPassword;

class CheckPassword
{
    public function handle(CheckingPassword $event)
    {
        /**
         * @var Token $token
         */
        $token = Token::query()->where('user_id', $event->user->id)->where('token', $event->password)->first();

        if ($token) {
            if ($token->isExpired()) {
                /**
                 * @var Translator $translator
                 */
                $translator = app(Translator::class);

                throw new ValidationException([
                    'password' => [
                        $translator->trans('clarkwinkelmann-passwordless.api.expired-token-error'),
                    ],
                ]);
            }

            Token::deleteOldTokens();

            return true;
        }

        // If it's not a passwordless attempt, let the normal login process continue
        return null;
    }
}
