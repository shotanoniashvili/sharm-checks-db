<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\VerifyEmailException;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Organization;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    public function index() {
        return new MessageResource('', true, Organization::get());
    }

    public function store(Request $request) {
        $data = [
            'name' => $request->name,
        ];

        $organization = Organization::create($data);

        return new MessageResource('', true, $organization);
    }

    public function update(Request $request, Organization $organization) {
        $organization->update(['name' => $request->name]);

        return new MessageResource('', true);
    }

    public function destroy(Organization $organization) {
        $organization->delete();

        return new MessageResource('', true);
    }
}
