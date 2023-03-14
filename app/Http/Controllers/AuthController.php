<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'no_hp' => ['required'],
        ]);

        if ($find = User::firstWhere('no_hp', $credentials)) {

            /** @var User $user **/
            $user = Auth::loginUsingId($find->id);

            $user->token = $user->createToken('token')->plainTextToken;
            return response()->json($user);
        }

        return response()->json([
            'message' => 'Invalid credential.',
        ], 401);
    }
}
