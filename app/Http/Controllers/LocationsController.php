<?php

namespace App\Http\Controllers;

use App\Models\Clients\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class LocationsController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Locations/Index', [
            'users' => Location::all(),
        ]);
    }

    public function store()
    {
        Location::create(
            Request::validate([
                'name' => ['required', 'max:50'],
                'client_id' => ['required', 'max:50', 'email'],
            ])
        );

        return Redirect::route('locations.index');
    }

    /**
     * Update the authenticated user's current location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request)
    {
        $location = Location::findOrFail($request->location_id);
        if (! $request->user()->switchLocation($location)) {
            abort(403);
        }

        return redirect(config('fortify.home'), 303);
    }
}
