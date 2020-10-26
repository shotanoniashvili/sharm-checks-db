<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\VerifyEmailException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\MessageResource;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index() {
        return new MessageResource('', true, User::with('roles')->get());
    }

    public function store(UserRequest $request) {
        DB::beginTransaction();
        try {
            $user = User::create($request->toArray());

            $user->update(['password' => Hash::make($request->password)]);

            $user->roles()->sync($request->roles);

            DB::commit();
            new MessageResource('', true, $user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function update(Request $request, User $user) {
        DB::beginTransaction();
        try {
            $user->update($request->toArray());

            $user->roles()->sync($request->roles);

            $user->managers()->sync($request->managers);

            if($request->password) $user->update(['password' => Hash::make($request->password)]);

            DB::commit();
            new MessageResource('', true, $user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e);
        }
    }

    public function destroy(Request $request, User $user) {
        $user->delete();

        new MessageResource('', true);
    }
}
