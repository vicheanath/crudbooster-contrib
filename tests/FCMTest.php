<?php

namespace crocodicstudio\crudbooster\tests;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use crocodicstudio\crudbooster\helpers\FCM;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FCMTest extends TestCase
{
    private $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        $this->publicKey = openssl_pkey_get_details($key)['key'];

        DB::table('cms_settings')->where('name', 'google_fcm_service_account')->update(['content' => json_encode([
            'project_id' => 'demo-project',
            'client_email' => 'push@demo-project.iam.gserviceaccount.com',
            'private_key' => $privateKey,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ])]);
    }

    public function test_sends_v1_messages_per_token_with_cached_oauth_token()
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.test', 'expires_in' => 3600]),
            'fcm.googleapis.com/v1/projects/demo-project/messages:send' => Http::sequence()
                ->push(['name' => 'projects/demo-project/messages/1'])
                ->push(['error' => ['status' => 'UNREGISTERED']], 404)
                ->push(['name' => 'projects/demo-project/messages/3']),
        ]);

        $result = json_decode(CRUDBooster::sendFCM(['token-a', 'token-b'], ['title' => '<b>Hello</b>', 'content' => 'World', 'id' => 7]), true);
        $this->assertSame(1, $result['success']);
        $this->assertSame(1, $result['failure']);
        $this->assertSame('UNREGISTERED', $result['results'][1]['error']);

        CRUDBooster::sendFCM(['token-c'], ['title' => 'Again', 'content' => 'Cached']);

        Http::assertSentCount(4); // one token request, three messages
        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), 'oauth2')) {
                return false;
            }
            [$header, $claims, $signature] = explode('.', $request['assertion']);
            $decoded = base64_decode(strtr($signature, '-_', '+/'));
            $payload = json_decode(base64_decode(strtr($claims, '-_', '+/')), true);

            return openssl_verify("$header.$claims", $decoded, $this->publicKey, OPENSSL_ALGO_SHA256) === 1
                && $payload['scope'] === FCM::SCOPE
                && $payload['iss'] === 'push@demo-project.iam.gserviceaccount.com';
        });
        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer ya29.test')
                && $request['message']['token'] === 'token-a'
                && $request['message']['notification'] === ['title' => 'Hello', 'body' => 'World']
                && $request['message']['data']['id'] === '7';
        });
    }

    public function test_reports_missing_credentials_without_calling_google()
    {
        DB::table('cms_settings')->where('name', 'google_fcm_service_account')->update(['content' => '']);
        \Cache::flush();
        Http::fake();

        $result = json_decode(CRUDBooster::sendFCM(['token-a'], ['title' => 'Hi', 'content' => 'There']), true);

        $this->assertSame(0, $result['success']);
        Http::assertNothingSent();
    }
}
