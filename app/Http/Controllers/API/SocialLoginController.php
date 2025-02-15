<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class SocialLoginController extends Controller
{
    use ResponseTrait;
    public function SocialLogin(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'provider' => 'required|in:google',
        ]);

        try {
            $provider   = $request->provider;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);

            if ($socialUser) {
                $user      = User::where('email', $socialUser->email)->first();
                $isNewUser = false;

                if (!$user) {
                    $password = Str::random(16);
                    $user     = User::create([
                        'name'              => $socialUser->getName(),
                        'email'             => $socialUser->getEmail(),
                        'password'          => bcrypt($password),
                        'provider'          => $provider,
                        'provider_id'       => $socialUser->getId(),
                        'email_verified_at' => now(),
                    ]);
                    $isNewUser = true;
                }

                // Generate token
                $token = auth('api')->login($user);

                // Prepare success response
                $success = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ];

                // Evaluate the message based on the $isNewUser condition
                $message = $isNewUser ? 'User registered successfully' : 'User logged in successfully';

                // Call sendResponse from BaseController and pass the token
                return $this->sendResponse($success, $message, $token);

            } else {
                $error = 'Invalid credentials';
                $errorMessages = ['Invalid credentials'];
                $code = 404;
                return $this->sendError($error, $errorMessages, $code);
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 401);
        }
    }

    public function logout()
    {
        auth('api')->logout();

        // Return successful response for logout
        return $this->sendResponse([], 'Successfully logged out');
    }
}


