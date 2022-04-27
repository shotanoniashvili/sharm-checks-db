<?php

namespace App\Http\Controllers\Api;

use App\Check;
use App\CheckItem;
use App\CheckItemStatus;
use App\Exceptions\VerifyEmailException;
use App\Exports\CheckExport;
use App\Exports\ChecksExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Organization;
use App\Role;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class CheckController extends Controller
{
    private $sadzaglishvilisID = 3;

    public function index(Request $request) {
        $user = $request->user();

        $query = Check::with(['user' => function($user) {
            $user->with('managers');
        }])->with(['items' => function($items) {
            $items->with(['statuses', 'finishedBy']);
        }])->where('is_archive', false);

        if($user->hasRole('manager')) $query->where('is_visible', true);

        $query->whereHas('user', function($q) use ($user) {
            $q->whereHas('organizations', function($q2) use ($user) {
                $q2->whereIn('id', $user->organizations->pluck('id')->toArray());
            });
        });

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

        $query->whereHas('user', function($q) use ($user) {
            $q->whereHas('organizations', function($q2) use ($user) {
                $q2->whereIn('id', $user->organizations->pluck('id')->toArray());
            });
        });

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

    public function copyFromArchive(Request $request, Check $check) {
        $clonedCheck = Check::create([
            'name' => $check->name,
            'user_id' => $request->user()->id,
            'status' => 'in_progress'
        ]);

        foreach ($check->items as $item) {
            $clonedCheck->items()->save(new CheckItem([
                'op_name'   => $item->op_name,
                'gel'       => $item->gel,
                'eur'       => $item->eur,
                'rur'       => $item->rur,
                'usd'       => $item->usd,
                'source'    => $item->source,
                'destination'    => $item->destination,
                'brand'     => $item->brand,
                'doc_type'  => $item->doc_type,
                'comment'   => $item->comment,
                'finished_by'    => null,
                'file'      => null,
            ]));
        }

        return new MessageResource('', true);
    }

    public function moveToArchive(Check $check) {
        $check->update(['is_archive' => true]);

        return new MessageResource('', true);
    }

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
        $checkItem = new CheckItem($request->toArray());

        if ($request->hasFile('file')) {
            $name = (new \DateTime())->format('Y-m-d H-i-s').'.'.$request->file('file')->getClientOriginalExtension();
            $link = '/storage/uploads/'.$name;
            $path = $request->file('file')->storeAs('/public/uploads', $name);
            $checkItem->file = $link;
        }

        $check->items()->save($checkItem);

        return new MessageResource('', true);
    }

    public function updateItem(Request $request, Check $check, CheckItem $item) {
        if($item->statuses()->count() !== 0 && !$request->user()->hasRole('admin')) abort(403);

        $item->update($request->toArray());

        if ($request->hasFile('file')) {
            $name = (new \DateTime())->format('Y-m-d H-i-s').'.'.$request->file('file')->getClientOriginalExtension();
            $link = '/storage/uploads/'.$name;
            $path = $request->file('file')->storeAs('/public/uploads', $name);
            $item->file = $link;
        }

        $item->save();

        return new MessageResource('', true);
    }

    public function deleteItem(Request $request, Check $check, CheckItem $item) {
        if($item->statuses()->count() !== 0 && !$request->user()->hasRole('admin')) abort(403);

        $item->delete();

        return new MessageResource('', true);
    }

    public function getItemStatus(Check $check, CheckItem $item, User $manager) {
        return new MessageResource('',true, CheckItemStatus::where('user_id', $manager->id)->where('check_item_id', $item->id)->first());
    }

    public function approveItem(Request $request, Check $check, CheckItem $item, User $manager) {
        if($manager->id !== $request->user()->id && !$request->user()->hasRole('admin')) abort(403);

        $itemStatus = CheckItemStatus::firstOrNew([
            'check_item_id' => $item->id,
            'user_id'       => $manager->id
        ]);

        $itemStatus->is_accepted = true;
        $itemStatus->comment = $request->comment;
        $itemStatus->save();

        $managerCheck = $manager->id === $this->sadzaglishvilisID;
        if($check->user->managers->count() === CheckItemStatus::whereIn('check_item_id', $check->items->pluck('id')->toArray())->count() || $managerCheck)
            $check->update(['status' => 'waiting_payment']);

        return new MessageResource('', true);
    }

    public function rejectItem(Request $request, Check $check, CheckItem $item, User $manager) {
        if($manager->id !== $request->user()->id && !$request->user()->hasRole('admin')) abort(403);

        $itemStatus = CheckItemStatus::firstOrNew([
            'check_item_id' => $item->id,
            'user_id'       => $manager->id
        ]);

        $itemStatus->is_accepted = false;
        $itemStatus->comment = $request->comment;
        $itemStatus->save();

        $managerCheck = $manager->id === $this->sadzaglishvilisID;
        if($check->user->managers->count() === CheckItemStatus::whereIn('check_item_id', $check->items->pluck('id')->toArray())->count() || $managerCheck)
            $check->update(['status' => 'rejected']);

        return new MessageResource('', true);
    }

    public function finish(Request $request, Check $check, CheckItem $item) {
        $item->finished_by = $request->user()->id;
        $item->save();

        if($check->user->managers->count() === CheckItemStatus::whereIn('check_item_id', $check->items->pluck('id')->toArray())->count() ||
            $check->items()->whereNull('finished_by')->count() === 0)
            $check->update(['status' => 'completed']);

        return new MessageResource('', true);
    }

    public function exportCheck(Check $check) {
        return Excel::download(new CheckExport($check), 'check-export.xlsx');
    }

    public function exportChecks(Request $request) {
        $user = $request->user();

        $query = Check::where('is_archive', true)->with(['user' => function($user) {
            $user->with('managers');
        }])->with(['items' => function($items) {
            $items->with(['statuses', 'finishedBy']);
        }])->where('is_archive', true);

        $query->whereHas('user', function($q) use ($user) {
            $q->whereHas('organizations', function($q2) use ($user) {
                $q2->whereIn('id', $user->organizations->pluck('id')->toArray());
            });
        });

        if($request->input('date-from')) $query->where('created_at', '>=', $request->input('date-from'));
        if($request->input('date-to')) $query->where('created_at', '<=', $request->input('date-to'));
        if($request->input('operators')) $query->whereIn('user_id', $request->input('operators'));

        return Excel::download(new ChecksExport($query->get()), 'checks-export.xlsx');
    }

    public function destroy(Check $check) {
        $check->delete();

        return new MessageResource('', true);
    }
}
