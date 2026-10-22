<?php

namespace crocodicstudio\crudbooster\helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Firebase Cloud Messaging HTTP v1 client. Replaces the legacy
 * https://fcm.googleapis.com/fcm/send API, which Google shut down in 2024.
 *
 * Credentials: a service account JSON, from the `google_fcm_service_account`
 * setting or a file path in config('crudbooster.FCM_SERVICE_ACCOUNT').
 */
class FCM
{
    const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Sends one notification to each token and returns a JSON string shaped
     * like the legacy multicast response: {success, failure, results[]}.
     */
    public static function send(array $tokens, array $data)
    {
        $account = self::serviceAccount();
        if (! $account) {
            return json_encode(['success' => 0, 'failure' => count($tokens), 'error' => 'FCM service account is not configured']);
        }

        $accessToken = self::accessToken($account);
        $url = 'https://fcm.googleapis.com/v1/projects/'.$account['project_id'].'/messages:send';
        $title = trim(strip_tags($data['title']));
        $body = trim(strip_tags($data['content']));

        $results = [];
        $success = 0;
        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)->post($url, ['message' => [
                'token' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                // HTTP v1 only accepts string values in `data`.
                'data' => array_map(function ($value) {
                    return is_scalar($value) || $value === null ? (string) $value : json_encode($value);
                }, $data),
                'android' => ['priority' => 'high', 'notification' => ['sound' => 'default']],
                'apns' => ['payload' => ['aps' => ['sound' => 'default', 'badge' => 0, 'content-available' => 1]]],
            ]]);

            if ($response->successful()) {
                $success++;
                $results[] = ['message_id' => $response->json('name')];
            } else {
                $results[] = ['error' => $response->json('error.status') ?: $response->status()];
            }
        }

        return json_encode(['success' => $success, 'failure' => count($tokens) - $success, 'results' => $results]);
    }

    public static function serviceAccount()
    {
        $json = CRUDBooster::getSetting('google_fcm_service_account');
        $path = config('crudbooster.FCM_SERVICE_ACCOUNT');
        if (! $json && $path && is_file($path)) {
            $json = file_get_contents($path);
        }

        $account = json_decode((string) $json, true);
        if (! is_array($account) || empty($account['project_id']) || empty($account['private_key']) || empty($account['client_email'])) {
            return null;
        }

        return $account;
    }

    public static function accessToken(array $account)
    {
        $cacheKey = 'cb_fcm_token_'.md5($account['client_email'].$account['project_id']);

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $tokenUri = $account['token_uri'] ?? 'https://oauth2.googleapis.com/token';
        $response = Http::asForm()->post($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => self::jwt($account, $tokenUri),
        ])->throw();

        $token = $response->json('access_token');
        Cache::put($cacheKey, $token, max(60, (int) $response->json('expires_in', 3600) - 60));

        return $token;
    }

    public static function jwt(array $account, $audience)
    {
        $now = time();
        $segments = [
            self::base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
            self::base64url(json_encode([
                'iss' => $account['client_email'],
                'scope' => self::SCOPE,
                'aud' => $audience,
                'iat' => $now,
                'exp' => $now + 3600,
            ])),
        ];

        openssl_sign(implode('.', $segments), $signature, $account['private_key'], OPENSSL_ALGO_SHA256);
        $segments[] = self::base64url($signature);

        return implode('.', $segments);
    }

    private static function base64url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
