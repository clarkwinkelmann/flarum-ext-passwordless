<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body {
            font-family: 'Open Sans', sans-serif;
            background: white;
            color: #426799;
            margin: 0;
            padding: 0;
        }

        .content {
            box-sizing: border-box;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 10px 20px;
        }

        .header {
            border-bottom: 1px solid #e8ecf3;
        }

        .header a {
            color: {{ $settings->get('theme_primary_color') }};
            text-decoration: none;
        }

        .footer {
            background: #e8ecf3;
        }

        dt {
            font-weight: bold;
        }

        .Button, .Button[href] { {{-- [href] necessary to override Gmail link color --}}
            display: block;
            width: 300px;
            max-width: 100%;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            line-height: 20px;
            border-radius: 4px;
            border: 0;
            color: #fff;
            background: {{ $settings->get('theme_primary_color') }};
            font-weight: bold;
            margin: 40px auto;
            padding: 8px 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="content">
        <a href="{{ app()->url() }}">{{ $settings->get('forum_title') }}</a>
    </div>
</div>
<div class="content">
    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.message', ['{domain}' => parse_url(app()->url(), PHP_URL_HOST)]) }}</p>

    <a href="{{ $link }}" class="Button">{{ $translator->trans('clarkwinkelmann-passwordless.mail.link') }}</a>

    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.expires', ['{minutes}' => $expireMinutes]) }}</p>

    <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.alternative', ['{token}' => $token]) }}</p>
</div>
<div class="footer">
    <div class="content">
        <p>{{ $translator->trans('clarkwinkelmann-passwordless.mail.footer', ['{title}' => $settings->get('forum_title')]) }}</p>
    </div>
</div>
</body>
</html>
