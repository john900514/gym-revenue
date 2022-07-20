<?php

namespace App\Providers;

use App\Domain\Teams\Actions\AddTeamMember;
use App\Domain\Teams\Actions\CreateTeam;
use App\Domain\Teams\Actions\DeleteTeam;
use App\Domain\Teams\Actions\InviteTeamMember;
use App\Domain\Teams\Actions\RemoveTeamMember;
use App\Domain\Teams\Actions\UpdateTeamName;
use App\Domain\Teams\Models\Team;
use App\Domain\Teams\Models\TeamInvitation;
use App\Domain\Users\Actions\DeleteUser;
use App\Domain\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::useUserModel(User::class);
        Jetstream::useTeamModel(Team::class);
        Jetstream::useTeamInvitationModel(TeamInvitation::class);

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
        Fortify::authenticateUsing(function (Request $request) {
            //Call withoutGlobalScopes so we don't try to apply client_id on login
            $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {
                //Successfull CRM Auth -
                //Let's set some info in our cookie session so we
                // can use it in middleware / global scopes
                // without having to hit the db
                session(['user_id' => $user->id]);
                session(['client_id' => $user->client_id]);

                return $user;
            }
        });
    }

    /**
     * Configure the roles and permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        // @todo - utilize bouncer to get the roles (possibly using the session)
        //TODO:pull from roles table
        Jetstream::role('Admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Administrator users can perform any action.');

        Jetstream::role('Account Owner', 'Account Owner', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Account owners can perform any action.');

        Jetstream::role('Regional Admin', 'Regional Admin', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Regional Admins can perform any action for locations in their region.');

        Jetstream::role('Location Manager', 'Location Manager', [
            'create',
            'read',
            'update',
            'delete',
        ])->description('Location managers can perform any action for their location.');

        Jetstream::role('Sales Rep', 'Sales Rep', [
            'read',
            'create',
            'update',
        ])->description('Sales Reps have the ability to read, create, and update.');

        Jetstream::role('Employee', 'Employee', [
            'read',
        ])->description('Employees have the ability to read.');
    }
}
