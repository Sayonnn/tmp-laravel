<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function googleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    // NOTE: This route is not associated with passport
    // this route logs the user using google account directly using socialite
    public function googleAuthentication()
    {

        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();
            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard');
            } else {
                $userData = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make('Password@1234'),
                    'google_id' => $googleUser->id,
                ]);
                if ($userData) {
                    Auth::login($userData);
                    return redirect()->route('dashboard');
                }
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    // NOTE: this method accepts the verified datas from the passport server
    // NOTE: this will redirect the client to the passport oauth/authorize page
    public function passportAuthorization()
    {
        try {
            $client_id = config('services.socialite.client_id');
            $callbackURI = config('services.socialite.callback');

            $url = url(config('services.passport.authorize')) . '?' . http_build_query([
                'client_id' => $client_id,
                'redirect_uri' => $callbackURI,
                'response_type' => 'code',
                'scope' => '',
                'state' => Hash::make(Str::random(20)),
            ]);

            // dd($url);

            return redirect($url);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    // NOTE: this receives and retrieve the passport authorization token 
    public function passportCallback(Request $request)
    {

        if ($request->has('error')) {
            return response()->json(['error' => $request->get('error')], 400);
        }

        // 🔹 Get the authorization code from the URL
        $code = $request->get('code');


        if (!$code) {
            return response()->json(['error' => 'Authorization code not found'], 400);
        }

        // 🔹 Exchange authorization code for an access token
        $response = Http::asForm()->post(config('services.passport.token'), [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('services.socialite.client_id'),
            'client_secret' => config('services.socialite.client_secret'),
            'redirect_uri'  => config('services.socialite.callback'),
            'code'          => $code,
        ]);

        $tokenData = $response->json();
        // save the tokens in sessions
        session('access_token', $tokenData['access_token']);
        session('refresh_token', $tokenData['refresh_token']);
        // dd($tokenData['access_token']);

        dd($tokenData);
        // automate login here
        // NOTE: we send the google token to the server for validation here
        $users = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $tokenData['access_token']
        ])->get('http://127.0.0.1:8000/api/user');

        if (!$users->successful()) {
            return response()->json([
                'error' => 'Failed to get user data',
                'details' => $users->json()
            ], 400);
        }
        $user = $users->json();

        // auto login the user
        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid user data received.');
        }
        
        // Check if user exists in the database
        $existingUser = User::where('google_id', $user['google_id'])->first();
        
        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            $newUser = User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make(Str::random(16)), // Secure random password
                'google_id' => $user['google_id'],
            ]);
        
            Auth::login($newUser);
        }
        
        // Redirect to the dashboard
        return redirect()->route('dashboard');
        // Success: Return the response from Passport Server

        // 🔹 Return or store the access token for future API requests
        // return response()->json(['response' => $users->json()]);
    }


    // NOTE: This route send the google token to passport server for verification
    public function goAuthorize()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            // dd($googleUser);

            $user = User::where('google_id', $googleUser->id)->first();
            // dd($user);

            if (!$user) {
                User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make('Password@1234'),
                    'google_id' => $googleUser->id,
                ]);
            }
            Auth::login($user);


            // NOTE: We also pass the client_id and client_secret so that passport server can save it
            // NOTE: We should also pass the login infos so passport can recognize it
            $secrets = [
                'clientID' => config('services.google.client_id'),
                'clientSecret' => config('services.google.client_secret'),
                'clientName' => config('services.google.client_name'),
                'callback' => config('services.passport.callback')
            ];

            // dd($secrets);

            // NOTE: we send the google token to the server for validation here
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(config('services.passport.verify'), ['token' => $googleUser->token, 'user' => $googleUser, 'secrets' => $secrets]);

            // NOTE: we call the passportRedirect method here passsing the verified datas
            if ($response->successful()) {
                // TEST
                dd($response->json());
                // return $this->passportRedirect($response->json());
            } else {
                dd("failed");
            }
            // return redirect('/dashboard');
        } catch (\Throwable $th) {
            dd($th);
        }
    }



    public function requestToken(Request $request)
    {
        try {
            // METHOD: POST
            // NOTE: we use the authtoken and client credentials to make a request to passport server oauth/token api
            // NOTE: This should be use only to exchange authToken with accessToken
            $response = $request->json();
            dd($response);
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
