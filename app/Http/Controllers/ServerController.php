<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class ServerController extends Controller
{
    // this is the google token verification process
    // NOTE: this also saves the client informations to passport server
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
            } else {
                $userData = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make('Password@1234'),
                    'google_id' => $googleUser->id,
                ]);
                if ($userData) {
                    Auth::login($userData);
                }
            }

            // then we redirect them to dashboard
            // NOTE: This is required to ensure the is authorize to access the dashboard

            return redirect()->away('http://localhost:8080/dashboard');
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    // return the user and log the user on the application
    public function getUser()
    {
        try {
            $user = Auth::user(); 
    
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
    
            return response()->json($user);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Something went wrong', 'message' => $th->getMessage()], 500);
        }
    }
    
    // this return the passport oauth/authorize page to the client
    // // TEST: maybe i should remove this??
    // public function redirectClient()
    // {
    //     return view('vendor.passport.authorize');
    // }



    // testing purposes
    // public function test(Request $request)
    // {
    //     $googleToken = $request->input('google_token');
    //     return response()->json(['response' => "Hi  I am server, you said this is your token right: $googleToken"]);
    // }
}
