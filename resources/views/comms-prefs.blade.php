<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="gr-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $asset_name = ((date('H') >= 23) || (date('H') < 16 )) ? '/img/gr-dark-logo.png' : '/img/gr-light-logo.png' @endphp
    <link rel="icon" type="image/png" href="{{ asset($asset_name) }}">

    <title inertia>{{ env('APP_NAME', 'Laravel') }} - Communication Preferences</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    @vite(['resources/css/app.css'])

</head>
<body class="font-sans antialiased flex flex-col min-h-screen">
<div class="flex flex-1">
    <div class="flex-1">
        <div class="container m-auto min-h-full flex items-center justify-center p-4">
            <div class="border-[1px] border-secondary rounded flex flex-col p-8 w-full max-w-[34rem]">
                <h1 class="text-secondary text-3xl font-bold">Communication Preferences</h1>

                @if ($success ?? false)
                    <div class="alert alert-success mt-8">
                        Your emails and SMS preferences have been updated for {{$client->name}}
                    </div>
                @endif

                <form class="space-y-8 mt-8" method="POST"
                      action="{{route("comms-prefs.{$entity_type}.update", $entity->id)}}">
                    @csrf

                    <div class="form-control flex-row">
                        <label for="subscribe_sms" class="label space-x-2">
                            <input type="checkbox" name="subscribe_sms" id="subscribe_sms" @checked(!$entity->unsubscribed_sms) />

                            <span
                                class="label-text">Subscribe me to SMS updates from {{$client->name}} {{$client->name}}</span>
                        </label>
                    </div>


                    {{--LEAVING THIS CODE BLOCK BELOW JUST IN CASE MATCHING CODE--}}
                    {{--FROM ABOVE WILL NOT SUFFICE--}}

                    <div class="form-control flex-row">
                        <label for="subscribe_email" class="label space-x-2">
                            <input type="checkbox" name="subscribe_email" id="subscribe_email"  @checked(!$entity->unsubscribed_email) />

                            <span class="label-text">Subscribe me to Email updates from {{$client->name}}</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-full xl:w-auto">Save</button>
                </form>
            </div>

        </div>
    </div>
</div>

<footer class="bg-base-200 p-2 text-gray-400 w-full">

    <div class="text-muted ml-auto mr-auto text-center">
        <div class="inner-footer">
            <small><b>CONCEPTUAL PROTOTYPE</b> | v.<b>{!! env('APP_VERSION') !!}</b> | Build
                <b>{!! env('APP_BUILD') !!}</b></small> | <b><small>© 2022 GymRevenue.</small></b>
            <br/>
            <b><small>GymRevenue is a Registered Trademark of <a href="https://capeandbay.com" target="_blank">Cape &
                        Bay, LLC</a>. All Rights Reserved. </small></b>
        </div>
    </div>
</footer>
</body>
</html>
