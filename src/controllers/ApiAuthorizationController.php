<?php


namespace crocodicstudio\crudbooster\controllers;


use crocodicstudio\crudbooster\helpers\CB;
use crocodicstudio\crudbooster\models\CmsApikey;
use Illuminate\Support\Facades\Cache;

class ApiAuthorizationController extends Controller
{
    private $ttl = 2880;
    private $token_length = 16;

    public function postGetToken() {
        CB::valid(['secret'=>'required'],'json');

        $exists = CmsApikey::query()->withSecret(g('secret'))->active()->exists();
        if($exists) {
            $accessToken = str_random($this->token_length);;
            // $ttl is in minutes; Cache::put() takes seconds since Laravel 5.8.
            Cache::put("api_token_".$accessToken,[
                "ip"=> request()->ip(),
                "user_agent"=> request()->userAgent()
            ], now()->addMinutes($this->ttl));

            return response()->json([
                'api_status'=>1,
                'api_message'=>'success',
                'data'=>[
                    'access_token'=>$accessToken,
                    'expiry'=> strtotime("+".$this->ttl." minutes")
                ]
            ]);
        } else {
            return response()->json([
                'api_status'=>0,
                'api_message'=>'Credential invalid!'
            ], 400);
        }
    }
}