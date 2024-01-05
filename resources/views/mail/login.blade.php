@extends('passwordless::mail.layout')

@section('content')
    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.login.message', ['{domain}' => parse_url($url->to('forum')->base(), PHP_URL_HOST)]) }}</p>

    <div class="ButtonBlock">
        <a href="{{ $link }}" class="Button">{{ $translator->trans('clarkwinkelmann-passwordless.mail.login.link') }}</a>
    </div>

    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.login.expires', ['{minutes}' => $expireMinutes]) }}</p>

    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.login.alternative', ['{token}' => $token]) }}</p>
@endsection
