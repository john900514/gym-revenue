<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Utility\AppState;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Prologue\Alerts\Facades\Alert;
use function Doctrine\Common\Cache\Psr6\get;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request)
    {

        $shared = [];
        $user = $request->user();
        $impersonateableUsers = User::with('default_team')->get();
        if ($request->user()) {
            $shared = [
                'impersonateableUsers' => $impersonateableUsers,
                'user.id' => $user->id,
                'user.all_locations' => $user->allLocations(),
                'user.current_client_id' => $user->currentClientId(),
                'user.permissions' => [
                    'users.create' => $user->can('create', User::class),
                    'users.read'=>$user->can('viewAny', User::class),
                    'users.update' => $user->can('update', User::class),
                    'users.delete'=>$user->can('delete', User::class),
                ],
                'app_state.is_simulation_mode' => AppState::isSimuationMode()
            ];
        }
        $previousUrl = url()->previous();

        if (!empty($previousUrl) && $previousUrl !== route('login') && $previousUrl !== url()->current()) {
            $shared['previousUrl'] = $previousUrl;
        }
        $alerts = Alert::getMessages();
        return array_merge(parent::share($request), [
            'flash' => function () use ($request, $alerts) {
                return [
                    'selectedLeadDetailIndex' => $request->session()->get('selectedLeadDetailIndex'),
                    'alerts' => $alerts
                ];
            },
        ], $shared);


//        $shared['flash'] = [];
//        if(!empty($request->session()->get('selectedLeadDetailIndex'))){
//            dd('works');
//            $shared['flash']['selectedLeadDetailIndex'] = $request->session()->get('selectedLeadDetailIndex');
//        }
////        $shared[ 'flash'] = ['selectedLeadDetailIndex'=>$request->session()->get('selectedLeadDetailIndex')];
//        $shared['flash']['foo'] = 'bar';
//        return array_merge(parent::share($request), $shared);
    }
}
