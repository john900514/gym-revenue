<?php

namespace App\Http\Controllers;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Classification;
use Bouncer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Prologue\Alerts\Facades\Alert;
use Silber\Bouncer\Database\Role;

class RolesController extends Controller
{
    protected $rules = [
        'name' => ['string', 'required'],
        'id' => ['integer', 'sometimes', 'nullable'],
        'ability_ids' => ['array', 'sometimes'],
        'ability_ids.*' => ['array', 'sometimes'],
    ];

    public function index(Request $request)
    {
        $client_id = $request->user()->currentClientId();
        if (!$client_id) {
            return Redirect::route('dashboard');
        }

        if($request->user()->cannot('roles.read',Role::class))
        {
            Alert::error("Oops! You dont have permissions to do that.")->flash();
            return Redirect::back();
        }

        $roles = Role::whereClientId($client_id)->paginate(10);

        return Inertia::render('Roles/Show', [
            'roles' => $roles,
            'filters' => $request->all('search', 'trashed', 'state')
        ]);
    }

    public function create()
    {
        $client_id = request()->user()->currentClientId();
        if (!$client_id) {
            return Redirect::route('dashboard');
        }

        if(request()->user()->cannot('roles.create',Role::class))
        {
            Alert::error("Oops! You dont have permissions to do that.")->flash();
            return Redirect::back();
        }

        return Inertia::render('Roles/Create', [
            'availableAbilities' => Bouncer::ability()->whereEntityId(null)->get(['name', 'title', 'id'])
        ]);
    }

    public function edit($id)
    {
        $client_id = request()->user()->currentClientId();
        if (!$client_id) {
            return Redirect::route('dashboard');
        }
        if (!$id) {
            Alert::error("No Security Role ID provided")->flash();
            return Redirect::back();
        }
        if(request()->user()->cannot('roles.update',Role::class))
        {
            Alert::error("Oops! You dont have permissions to do that.")->flash();
            return Redirect::back();
        }

        return Inertia::render('Roles/Edit', [
            'availableAbilities' => Bouncer::ability()->whereEntityId(null)->get(['name', 'title', 'id']),
            'role' => Role::with('abilities')->findOrFail($id),
        ]);
    }


}
