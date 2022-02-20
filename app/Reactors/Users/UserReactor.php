<?php

namespace App\Reactors\Users;

use App\Mail\Users\NewUserWelcomeEmail;
use App\Models\User;
use App\StorableEvents\Users\WelcomeEmailSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class UserReactor extends Reactor implements ShouldQueue
{
    public function onWelcomeEmailSent(WelcomeEmailSent $event)
    {
        $user = User::find($event->user);

        Mail::to($user->email)->send(new NewUserWelcomeEmail($user));
    }
}