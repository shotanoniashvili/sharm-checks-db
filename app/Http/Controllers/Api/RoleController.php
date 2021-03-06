<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\VerifyEmailException;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Organization;
use App\Role;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index() {
        return new MessageResource('', true, Role::get());
    }
}
