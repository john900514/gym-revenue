<?php

namespace App\Http\Controllers;

use App\Aggregates\Clients\ClientAggregate;
use App\Models\Clients\Client;
use App\Models\Clients\Location;
use App\Models\Clients\Security\SecurityRole;
use App\Models\Team;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Prologue\Alerts\Facades\Alert;

class UsersController extends Controller
{
    protected $rules = [
        'name' => ['required', 'max:50'],
        'email' => ['required', 'email'],
        'security_role' => ['required', 'exists:security_roles,id']
    ];

    public function index(Request $request)
    {
        $client_id = $request->user()->currentClientId();
        if ($client_id) {
            return Inertia::render('Users/Show', [
                'users' => User::whereHas('detail', function ($query) use ($client_id) {
                    return $query->whereName('associated_client')->whereValue($client_id);
                })->filter($request->only('search', 'club', 'team'))
                    ->paginate(10),
                'filters' => $request->all('search', 'club', 'team'),
                'clubs' => Location::whereClientId($client_id)->get(),
                'teams' => $client_id ? Team::findMany(Client::with('teams')->find($client_id)->teams->pluck('value')) : []
            ]);
        } else {
            //cb team selected
            return Inertia::render('Users/Show', [
                'users' => User::whereHas('teams', function ($query) use ($request) {
                    return $query->where('teams.id', '=', $request->user()->currentTeam()->first()->id);
                })->filter($request->only('search', 'club', 'team'))
                    ->paginate(10),
                'filters' => $request->all('search', 'club', 'team'),
                'clubs' => [],
                'teams' => []
            ]);
        }
    }

    public function create(Request $request)
    {
        if($request->user()->cannot('create', User::class)){
            abort(403);
        }
        return Inertia::render('Users/Create', [
            'securityRoles' => SecurityRole::whereActive(1)->whereClientId($request->user()->currentClientId())->get(['id', 'security_role'])
        ]);
    }

    public function edit($id)
    {
        if(request()->user()->cannot('update', User::class)){
            abort(403);
        }
        $user = User::with('details')->findOrFail($id);
        return Inertia::render('Users/Edit', [
            'selectedUser' => $user,
            'securityRoles' => SecurityRole::whereActive(1)->whereClientId(request()->user()->currentClientId())->get(['id', 'security_role'])
        ]);
    }

    public function store(Request $request)
    {
        $create_rules = array_merge($this->rules, ['password' => 'required', 'email' => ['required', 'email', 'unique:users,email']]);
        $data = $request->validate($create_rules);
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        $security_role = SecurityRole::with('role')->find($data['security_role']);
        UserDetails::create(['user_id' => $user->id, 'name' => 'security_role', 'value'=>$security_role->id]);

        $client_id = $request->user()->currentClientId();
        if ($client_id) {
            UserDetails::create(['user_id' => $user->id, 'name' => 'associated_client', 'value' => $client_id]);
        }
        $current_team = $request->user()->currentTeam()->first();
        UserDetails::create(['user_id' => $user->id, 'name' => 'default_team', 'value' => $current_team->id]);
        $role = $security_role->role->name;
        $current_team->users()->attach(
            $user, ['role' => $role]
        );
        if ($client_id) {
            $aggy = ClientAggregate::retrieve($client_id);
            $aggy->addUserToTeam($user->id, $current_team->id, $role);
            $aggy->persist();
        }
        Alert::success("User '{$user->name}' was created")->flash();

        return Redirect::route('users');
    }

    public function update(Request $request, $id)
    {
        if (!$id) {
            Alert::error("No User ID provided")->flash();
            return Redirect::route('users');
        }

        $data = $request->validate($this->rules);
        $current_user = $request->user();
        $user = User::findOrFail($id);
        $user->updateOrFail($data);
        $current_team = $current_user->currentTeam()->first();
        $old_role = $current_user->teams()->get()->keyBy('id')[$current_team->id]->pivot->role;
        $security_role = SecurityRole::with('role')->find($data['security_role']);
        UserDetails::firstOrCreate(['user_id' => $user->id, 'name' => 'security_role'])->updateOrFail(['value'=>$security_role->id]);
        $role = $security_role->role->name;
        $user->teams()->sync([$current_team->id => ['role' => $role]]);
        $client_id = $current_user->currentClientId();
        if ($client_id && $role !== $old_role) {
            $aggy = ClientAggregate::retrieve($client_id);
            $aggy->updateUserRoleOnTeam($user->id, $current_team->id, $old_role, $role);
            $aggy->persist();
        }
        Alert::success("User '{$user->name}' updated")->flash();

        return Redirect::route('users');
    }

    public function delete($id)
    {
        if (!$id) {
            Alert::error("No User ID provided")->flash();
            return Redirect::route('users');
        }

        $user = User::findOrFail($id);

        $success = $user->deleteOrFail();

        Alert::success("User '{$user->name}' deleted")->flash();

        return Redirect::back();
    }
}
