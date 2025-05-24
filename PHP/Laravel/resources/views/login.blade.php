<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Login</title>
    </head>
    <body>
        @php
        $faceit_url_connect = '';
        $code_challenge = hash('sha256', base64_encode( env('FACEIT_PKCE_CODE_VERIFIER')));

        $faceit_connect_url = "https://accounts.faceit.com?" . http_build_query([
            'client_id' => env('FACEIT_CLIENT_ID'),
            'response_type' => 'code',
            'code_challenge'=> $code_challenge,
            'redirect_popup' => true,
            'code_challenge_method' => 'plain',
        ]);

        $error_message = '';
        if ((isset($errors) && $errors != '' && $errors != '[]') || (is_array($errors) && count($errors) > 0)) {
            $error_message = $errors;
        }
    @endphp

        <h1>Login</h1>
        <p>Click the button below to login with Faceit.</p>
        <a href="{{ $faceit_connect_url }}" class="btn btn-primary">Login with Faceit</a>

        @if ($error_message)
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ $error_message }}
            </div>
        @endif
    </body>
</html>