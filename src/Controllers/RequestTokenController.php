<?php

namespace ClarkWinkelmann\PasswordLess\Controllers;

use Carbon\Carbon;
use ClarkWinkelmann\PasswordLess\Token;
use Flarum\Foundation\ValidationException;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\Translator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Exception\NotAuthenticatedException;
use Flarum\User\UserRepository;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\EmptyResponse;

class RequestTokenController implements RequestHandlerInterface
{
    protected $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $identification = Arr::get($body, 'identification');
        $remember = (bool)Arr::get($body, 'remember');

        $user = $this->users->findByIdentification($identification);

        if (!$user) {
            throw new NotAuthenticatedException();
        }

        if (Token::query()->where('user_id', $user->id)->where('created_at', '>', Carbon::now()->subSecond(30))->exists()) {
            /**
             * @var Translator $translator
             */
            $translator = app(Translator::class);

            throw new ValidationException([
                'password' => [
                    $translator->trans('clarkwinkelmann-passwordless.api.request-throttle-error'),
                ],
            ]);
        }

        /**
         * @var SettingsRepositoryInterface $settings
         */
        $settings = app(SettingsRepositoryInterface::class);

        $expireMinutes = $settings->get('passwordless.tokenLifeInMinutes') ?: 5;

        $token = Token::generate($user->id, $remember, $expireMinutes);
        $token->save();

        /**
         * @var Mailer $mailer
         */
        $mailer = app(Mailer::class);

        /**
         * @var UrlGenerator $url
         */
        $url = app(UrlGenerator::class);

        $mailer->send('passwordless::mail', [
            'link' => $url->to('forum')->route('clarkwinkelmann.passwordless') . '?user=' . $user->id . '&token=' . $token->token,
            'token' => $token->token,
            'expireMinutes' => $expireMinutes,
        ], function (Message $message) use ($user, $settings) {
            /**
             * @var Translator $translator
             */
            $translator = app(Translator::class);

            $message->to($user->email);
            $message->subject($translator->trans('clarkwinkelmann-passwordless.mail.subject', [
                '{title}' => $settings->get('forum_title'),
            ]));
        });

        return new EmptyResponse();
    }
}
