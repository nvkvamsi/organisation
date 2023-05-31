<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helper\JsonApiResponse;

class AuthController extends ApiController
{
    protected $client;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->client = DB::table('oauth_clients')
                          ->where(['password_client' => 1])
                          ->first();
    }

    function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials)) {
           
            $user_details = array();
            $user_details['first_name'] = Auth::user()->first_name;
            $user_details['last_name'] = Auth::user()->last_name;
            $user_details['email'] = Auth::user()->email;
            $user_details['username'] = Auth::user()->username;
            return JsonApiResponse::success('Successfully logged in.', [[
                'access_token' => Auth::user()->createToken(Str::random(50))->accessToken,
                'user_details' => $user_details
            ]]);
        } else {
            return JsonApiResponse::error('Please enter correct credentials.', 422);
        }
    }

    public function authenticate(Request $request)
    {
        $request->request->add([
            'username' => $request->get('email'),
            'password' => $request->get('password'),
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
        ]);
        
        $proxy = Request::create('oauth/token', 'POST');

        return Route::dispatch($proxy);
    }
}
