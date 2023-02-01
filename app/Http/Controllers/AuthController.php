<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class AuthController extends Controller
{
    function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string',
                'account_name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($this->_checkUnique('account_name', $data['account_name']))
                return response()->json('すでに使用されているアカウント名です', 422);

            if ($this->_checkUnique('email', $data['email']))
                return response()->json('すでに使用されているメールアドレスです', 422);

            $data['password'] = Hash::make($data['password']);
            $data['email_verified_at'] = date('Y-m-d H:i:s');

            $user = User::create($data);
            $response = $user->toArray();
            $response['token'] =  $user->createToken($user['account_name'])->plainTextToken;

            return response()->json($response);
        } catch (\Exception $e) {
            Logger($e);
            abort(404);
        }
    }

    function login(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'required|string',
            'password' => 'required'
        ]);

        if (Auth::attempt($data)) {
            $user = auth()->user();
            $user->load('tweets');

            $response = $user->toArray();
            $response['token'] = $user->createToken($data['account_name'])->plainTextToken;

            return response()->json($response);
        }

        return response()->json(false, 200);
    }

    private function _checkUnique($column, $data)
    {
        return User::where($column, $data)->exists();
    }
}
