<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Departments\Department;
use App\Domain\Locations\Projections\Location;
use App\Domain\Roles\Role;
use App\Domain\Teams\Models\Team;
use App\Domain\Users\Models\Employee;
use App\Enums\SecurityGroupEnum;
use App\Models\File;
use App\Models\Folder;
use App\Models\Position;
use App\Support\CurrentInfoRetriever;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Prologue\Alerts\Facades\Alert;

class FilesController extends Controller
{
    public function index(Request $request)
    {
        $client_id = request()->user()->client_id;

        if ($client_id === null) {
            return Redirect::route('dashboard');
        }

        $isSearching = $request->has('search');

        $page_count     = 10;
        $roles          = request()->user()->getRoles();
        $security_group = request()->user()->securityGroup();

        if ($isSearching) {
            $files = File::with('client')
                    ->whereClientId($client_id)
                    //->whereUserId(null)
                    ->whereIsHidden(false)
                    ->whereFileableId($client_id)
                    ->filter($request->only('search', 'trashed'))
                    ->sort()
                    ->paginate($page_count)
                    ->appends(request()->except('page'));
        } else {
            if ($security_group === SecurityGroupEnum::ADMIN || $security_group === SecurityGroupEnum::ACCOUNT_OWNER) {
                $files = File::with('client')
                    ->whereClientId($client_id)
                    //->whereUserId(null)
                    ->whereIsHidden(false)
                    ->whereFolder(null)
                    ->whereFileableId($client_id)
                    ->filter($request->only('search', 'trashed'))
                    ->sort()
                    ->paginate($page_count)
                    ->appends(request()->except('page'));
            } else {
                $files = File::with('client')
                    ->whereClientId($client_id)
                    //->whereUserId(null)
                    ->whereIsHidden(false)
                    ->whereFolder(null)
                    ->whereFileableId($client_id)
                    ->where('permissions', 'like', '%' . strtolower(str_replace(' ', '_', $roles[0])) . '%')
                    ->filter($request->only('search', 'trashed'))
                    ->sort()
                    ->paginate($page_count)
                    ->appends(request()->except('page'));
            }
        }


        $folders = Folder::with('files')->filter($request->only('search', 'trashed'))
            ->get();



        $user = Employee::whereId($request->user()->id)->with('teams', 'positions', 'departments')->first();

        /**
        * Folder permissions to determine if you can see the folder
         * based on team, location, user_id, position, or department
        */
        foreach ($folders as $key => $folder) {
            $shouldForgetFolder = true;
            $hasPermissionsSet  = false;
            if ($folder->user_ids) {
                if (in_array($user->id, $folder->user_ids)) {
                    $shouldForgetFolder = false;
                }
                $hasPermissionsSet = true;
            }

            if ($folder->team_ids) {
                foreach ($user->teams as $team) {
                    if (in_array($team->id, $folder->team_ids)) {
                        $shouldForgetFolder = false;
                    }
                }
                $hasPermissionsSet = true;
            }

            if ($folder->position_ids) {
                foreach ($user->positions as $position) {
                    if (in_array($position->id, $folder->position_ids)) {
                        $shouldForgetFolder = false;
                    }
                }
                $hasPermissionsSet = true;
            }

            if ($folder->department_ids) {
                foreach ($user->departments as $department) {
                    if (in_array($department->id, $folder->department_ids)) {
                        $shouldForgetFolder = false;
                    }
                }
                $hasPermissionsSet = true;
            }

            if ($folder->role_ids) {
                if (in_array($user->role()->id, $folder->role_ids)) {
                    $shouldForgetFolder = false;
                }
                $hasPermissionsSet = true;
            }

            if ($folder->location_ids) {
                if (in_array(CurrentInfoRetriever::getCurrentLocationID(), $folder->location_ids)) {
                    $shouldForgetFolder = false;
                }
                $hasPermissionsSet = true;
            }

            if ($hasPermissionsSet && $shouldForgetFolder) {
                $folders->forget($key);
            }
        }

        return Inertia::render('Files/Show', [
            'files' => $files,
            'folders' => $folders,
            'departments' => Department::all(),
            'positions' => Position::all(),
            'roles' => Role::all(),
            'locations' => Location::all(),
            'users' => Employee::all(),
            'teams' => Team::all(),
            'uploadFileRoute' => 'files.store',
        ]);
    }

    public function edit($id)
    {
        if (! $id) {
            Alert::error("No file ID provided")->flash();

            return Redirect::route('files');
        }

        return Inertia::render('Files/Edit', [
            'file' => File::find($id),
        ]);
    }

    public function upload(Request $request)
    {
        return Inertia::render('Files/Upload', [
        ]);
    }

    //TODO:we could do a ton of cleanup here between shared codes with index. just ran out of time.
    public function export(Request $request)
    {
        $client_id = request()->user()->client_id;

        if ($client_id === null) {
            abort(403);
        }
        $roles          = request()->user()->getRoles();
        $security_group = request()->user()->securityGroup();

        if ($security_group === SecurityGroupEnum::ADMIN || $security_group === SecurityGroupEnum::ACCOUNT_OWNER) {
            $files = File::with('client')
                ->whereClientId($client_id)
                ->whereUserId(null)
                ->filter($request->only('search', 'trashed'))
                ->get();
        } else {
            $files = File::with('client')
                ->whereClientId($client_id)
                ->whereUserId(null)
                ->where('permissions', 'like', '%' . strtolower(str_replace(' ', '_', $roles[0])) . '%')
                ->filter($request->only('search', 'trashed'))
                ->get();
        }

        return $files;
    }
}
