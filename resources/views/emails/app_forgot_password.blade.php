<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Password Reset' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <p>{{ __('api.hello') }}{{ !empty($name) ? ' ' . $name : '' }},</p>
    <p>{{ __('api.forgot_password_mail_intro') }}</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px;">{{ $code }}</p>
    <p>{{ __('api.forgot_password_mail_expiry', ['minutes' => $minutes ?? 10]) }}</p>
    <p>{{ __('api.forgot_password_mail_ignore') }}</p>
</body>
</html>
