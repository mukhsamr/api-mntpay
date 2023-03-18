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
            $user->alias = $this->alias($user->nama);

            return response()->json($user);
        }

        return response()->json([
            'message' => 'Invalid credential.',
        ], 401);
    }

    private function alias(string $nama): string
    {
        $collection = collect(explode(' ', $nama))->take(2);

        $collection = $collection->map(function ($word) {
            return substr($word, 0, 1);
        });

        return $collection->implode('');
    }
}
