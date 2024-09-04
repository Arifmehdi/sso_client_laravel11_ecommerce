<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class SSOController extends Controller
{
    public function login(Request $request)
    {
        $request->session()->put('state', $state =  Str::random(40));

        $query = http_build_query([
            // "client_id" => "9ced2b6a-50e8-4fcb-8524-c692199bfa4a",
            "client_id" => "9ced50fc-723c-440c-8577-bd00750dece5",
            "redirect_uri" => "http://localhost:8080/callback",
            "response_type" => "code",
            "scope" => "view-user",
            "state" => $state,
        ]);
    
        // return redirect()
        return redirect("http://localhost:8000/oauth/authorize?".$query);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull("state");

        throw_unless(strlen($state) > 0 && $state = $request->state,
        InvalidArgumentException::class);
    
        $response = Http::asForm()->post(
            "http://localhost:8000/oauth/token",
            [
                "grant_type" => "authorization_code",
                "client_id" => "9ced50fc-723c-440c-8577-bd00750dece5",
                "client_secret" => "oeWe35Gw1ZzM0SPygSJ4ixjba4GSwNvZemCfzrKf",
                "redirect_uri" => "http://localhost:8080/callback",
                "code" => $request->code,
            ]
        );
        $request->session()->put($response->json());
        return redirect(route('sso.authuser'));
        return $response->json();
    }

    public function authUser(Request $request)
    {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer ".$access_token
        ])->get("http://localhost:8000/api/user");
    
        $userArray = $response->json();
        try {
            $email =$userArray['email'];
        } catch (\Throwable $th) {
            return redirect('login')->withError("Failed to get login information! try again.");
        }
        $user = User::where('email',$email)->first();

        if(!$user){
            $user = new User;
            $user->name = $userArray['name'];
            $user->email = $userArray['email'];
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->save();
        }
        
        Auth::login($user);
        return redirect('home');
        // return $response->json();
    }
}
