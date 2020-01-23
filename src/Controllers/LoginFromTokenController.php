<?php

namespace ClarkWinkelmann\PasswordLess\Controllers;

use ClarkWinkelmann\PasswordLess\Token;
use Flarum\Foundation\Application;
use Flarum\Foundation\DispatchEventsTrait;
use Flarum\Http\AccessToken;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\Locale\Translator;
use Flarum\User\Event\LoggedIn;
use Flarum\User\User;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class LoginFromTokenController implements RequestHandlerInterface
{
    use DispatchEventsTrait;

    protected $app;
    protected $users;
    protected $authenticator;
    protected $rememberer;

    public function __construct(Dispatcher $events, Application $app, UserRepository $users, SessionAuthenticator $authenticator, Rememberer $rememberer)
    {
        $this->events = $events;
        $this->app = $app;
        $this->users = $users;
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();

        $url = Arr::get($params, 'return', $this->app->url());
        $response = new RedirectResponse($url);

        /**
         * @var User $actor
         */
        $actor = $request->getAttribute('actor');

        // If the user is already logged in, redirect as if login was successful
        if (!$actor->isGuest()) {
            return $response;
        }

        $userId = Arr::get($params, 'user');
        $passwordlessTokenValue = Arr::get($params, 'token');

        /**
         * @var Token $passwordlessToken
         */
        $passwordlessToken = Token::query()->where('user_id', $userId)->where('token', $passwordlessTokenValue)->first();

        if (!$passwordlessToken || $passwordlessToken->isExpired()) {
            /**
             * @var Factory $viewFactory
             */
            $viewFactory = app(Factory::class);

            /**
             * @var Translator $translator
             */
            $translator = app(Translator::class);

            // Show the Flarum error view, but with our custom message
            $view = $viewFactory->make('flarum.forum::error.not_found')
                ->with('message', $translator->trans('clarkwinkelmann-passwordless.api.' . ($passwordlessToken ? 'expired' : 'invalid') . '-token-error'));

            return new HtmlResponse($view->render(), 404);
        }

        Token::deleteOldTokens();

        /**
         * @var Session $session
         */
        $session = $request->getAttribute('session');

        $this->authenticator->logIn($session, $passwordlessToken->user_id);

        $accessToken = AccessToken::generate($passwordlessToken->user_id);

        $user = $this->users->findOrFail($accessToken->user_id);

        // Validate the user just like if they used an email confirmation token
        // We only do this when you use the link and not type the token as password
        // because it's too complicated to do it in the password check
        $user->activate();
        $user->save();
        $this->dispatchEventsFor($user);

        event(new LoggedIn($user, $accessToken));

        if ($passwordlessToken->remember) {
            $response = $this->rememberer->remember($response, $accessToken);
        }

        return $response;
    }
}
