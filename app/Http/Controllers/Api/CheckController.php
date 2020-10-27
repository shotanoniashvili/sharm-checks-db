<?php

namespace App\Http\Controllers\Api;

use App\Check;
use App\CheckItem;
use App\CheckItemStatus;
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

class CheckController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();

        $query = Check::with(['user' => function($user) {
            $user->with('managers');
        }])->with(['items' => function($items) {
            $items->with(['statuses', 'finishedBy']);
        }])->where('is_archive', false);

        if($user->hasRole('manager')) $query->where('is_visible', true);

        if(!$user->hasRole('admin')) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        $checks = $query->get();

        return new MessageResource('', true, $checks);
    }

    public function indexArchive(Request $request) {
        $user = $request->user();

        $perPage = 50;
        $page = $request->page ? $request->page : 1;

        $query = Check::with(['user' => function($user) {
            $user->with('managers');
        }])->with(['items' => function($items) {
            $items->with(['statuses', 'finishedBy']);
        }])->where('is_archive', true);

        if(!$user->hasRole('admin')) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        if($request->input('date-from')) $query->where('created_at', '>=', $request->input('date-from'));
        if($request->input('date-to')) $query->where('created_at', '<=', $request->input('date-to'));
        if($request->input('operators')) $query->whereIn('user_id', $request->input('operators'));

        $total = $query->count();

        $checks = $query->limit($perPage)->offset($perPage * ($page - 1))->get();

        return new MessageResource('', true, $checks, $total);
    }

    public function items(Check $check) {
        $checkItems = $check->items()->with(['statuses', 'finishedBy'])->get();
        return new MessageResource('', true, $checkItems);
    }

    public function managers(Check $check) {
        $managers = $check->user->managers()->get();
        return new MessageResource('', true, $managers);
    }

    public function toggleStatus(Check $check) {
        $check->update(['is_visible' => !$check->is_visible]);

        return new MessageResource('', true);
    }

    public function toggleArchive(Check $check) {
        $check->update(['is_archive' => !$check->is_archive]);

        return new MessageResource('', true);
    }

    /*public function copyFromArchive(Check $check) {
        $check->update(['is_archive' => !$check->is_archive]);

        return new MessageResource('', true);
    }*/

    public function store(Request $request) {
        $data = [
            'name' => $request->name,
            'user_id' => $request->user()->id,
        ];

        $check = Check::create($data);

        return new MessageResource('', true, $check);
    }

    public function update(Request $request, Check $check) {
        $check->update(['name' => $request->name]);

        return new MessageResource('', true);
    }

    public function storeItem(Request $request, Check $check) {
        $check->items()->save(new CheckItem($request->toArray()));

        return new MessageResource('', true);
    }

    public function updateItem(Request $request, Check $check, CheckItem $item) {
        $item->update($request->toArray());

        return new MessageResource('', true);
    }

    public function deleteItem(Request $request, Check $check, CheckItem $item) {
        $item->delete();

        return new MessageResource('', true);
    }

    public function getItemStatus(Check $check, CheckItem $item, User $manager) {
        return new MessageResource('',true, CheckItemStatus::where('user_id', $manager->id)->where('check_item_id', $item->id)->first());
    }

    public function approveItem(Request $request, Check $check, CheckItem $item, User $manager) {
        $itemStatus = CheckItemStatus::firstOrNew([
            'check_item_id' => $item->id,
            'user_id'       => $manager->id
        ]);

        $itemStatus->is_accepted = true;
        $itemStatus->comment = $request->comment;
        $itemStatus->save();

        return new MessageResource('', true);
    }

    public function rejectItem(Request $request, Check $check, CheckItem $item, User $manager) {
        $itemStatus = CheckItemStatus::firstOrNew([
            'check_item_id' => $item->id,
            'user_id'       => $manager->id
        ]);

        $itemStatus->is_accepted = false;
        $itemStatus->comment = $request->comment;
        $itemStatus->save();

        return new MessageResource('', true);
    }

    public function finish(Request $request, Check $check, CheckItem $item) {
        $item->finished_by = $request->user()->id;
        $item->save();

        return new MessageResource('', true);
    }
}
