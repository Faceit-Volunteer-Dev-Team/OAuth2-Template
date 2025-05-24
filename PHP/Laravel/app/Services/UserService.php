<?php

namespace App\Services;

class UserService {

    private const DEBUG = false;

    public static function getToken($code)
    {
        $class_return = new \App\Classes\ClassReturn();

        $http = new \App\Classes\Curl([
            'url' => 'https://api.faceit.com/auth/v1/oauth/token',
            'protocol' => "POST",
            'header' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                'Authorization: Basic ' . base64_encode(env('FACEIT_CLIENT_ID') . ':' . env('FACEIT_CLIENT_SECRET'))
            ],
            'post_data' => http_build_query([
                'code' => $code,
                'grant_type' => 'authorization_code',
                'code_verifier' => hash('sha256', base64_encode( env('FACEIT_PKCE_CODE_VERIFIER')))
            ])
        ]);

        if (self::DEBUG) {
            $http->setDebug(true);
        }

        try {
            $result = $http->execute();
            if ($result->errcode != 0) {
                return $result;
            }

            $data = $http->getData();

            $class_return->data = json_decode($data);

            $response = self::validateJWT($class_return->data->id_token);
            if ($response->errcode != 0) {
                return $response;
            }

            $class_return->data->player = self::extractJWTBody($class_return->data->id_token);
        } catch (\Exception $e) {
            $class_return->message = $e->getMessage();
            $class_return->errcode = 1;
        }

        return $class_return;
    }

    public static function refreshToken($refresh_token)
    {
        $class_return = new \App\Classes\ClassReturn();

        $http = new \App\Classes\Curl([
            'url' => 'https://api.faceit.com/auth/v1/oauth/token',
            'protocol' => "POST",
            'header' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                'Authorization: Basic ' . base64_encode(env('FACEIT_CLIENT_ID') . ':' . env('FACEIT_CLIENT_SECRET'))
            ],
            'post_data' => http_build_query([
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token',
            ]),
        ]);

        if (self::DEBUG) {
            $http->setDebug(true);
        }

        try {
            $result = $http->execute();
            if ($result->errcode != 0) {
                return $result;
            }

            $data = $http->getData();

            $class_return->data = json_decode($data);

            $class_return->data->player = self::extractJWTBody($class_return->data->id_token);
        } catch (\Exception $e) {
            $class_return->message = $e->getMessage();
            $class_return->errcode = 1;
        }

        return $class_return;
    }

    protected static function validateJWT($token_id)
    {
        $class_return = new \App\Classes\ClassReturn();

        // Issues is statically inserted but should be taken from the URL - https://api.faceit.com/auth/v1/openid_configuration
        $issuer_url = "https://api.faceit.com/auth";
        $audience_client_id = env('FACEIT_CLIENT_ID');

        // Saving the keys that will help with the validation of the token
        // Keys are found at https://api.faceit.com/auth/v1/oauth/certs
        // That is https://developers.faceit.com/api/auth/v1/openid_configuration -> jwks_uri
        $keys = [
            "kty" => "RSA",
            "alg" => "RS256",
            "use" => "sig",
            "n" => "AL60AMMiRduMp2OrcAMXXQEnr2OBIdbwOaOhJEaBsJqG0WmecbPBGM_WaCyz91ooSP7B1OfXKZb7YmSuE4REpv9GUBNyV3DNwD5z2nr9o0B6etme3Vt1xiD5_e68H9Jvv2SAAQyEI1zHOdFQwCAkVA6Byeh5ziyKX30TNegCM_ix",
            "e" => "AQAB",
            "kid" => "4aca2e8c-9a35-441e-9ceb-1f30bebaada0"
        ];

        // We split the token in 3 parts
        $token_parts = explode(".", $token_id);

        // We decode the header and the payload and its signature
        $header = json_decode(base64_decode($token_parts[0]));
        $payload = json_decode(base64_decode($token_parts[1]));

        if ($header->alg != $keys["alg"]) {
            $class_return->errcode = 1;
            $class_return->message = 'Invalid algorithm';
            return $class_return;
        }

        if ($header->kid != $keys["kid"]) {
            $class_return->errcode = 1;
            $class_return->message = 'Invalid key';
            return $class_return;
        }

        if ($payload->iss != $issuer_url) {
            $class_return->errcode = 1;
            $class_return->message = 'Invalid issuer';
            return $class_return;
        }

        if ($payload->aud != $audience_client_id) {
            $class_return->errcode = 1;
            $class_return->message = 'Invalid audience';
            return $class_return;
        }

        if ($payload->exp < time()) {
            $class_return->errcode = 1;
            $class_return->message = 'Token expired';
            return $class_return;
        }

        $class_return->data = $payload;

        return $class_return;
    }

    protected static function extractJWTBody($token_id)
    {
        $token_parts = explode(".", $token_id);
        $payload = json_decode(base64_decode($token_parts[1]));

        return $payload;
    }
}
