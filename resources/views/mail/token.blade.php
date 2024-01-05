@extends('passwordless::mail.layout')

@section('content')
    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.token.message', ['{domain}' => parse_url($url->to('forum')->base(), PHP_URL_HOST)]) }}</p>

    <p><span class="password">{{ $token }}</span></p>

    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.token.expires', ['{minutes}' => $expireMinutes]) }}</p>
@endsection
