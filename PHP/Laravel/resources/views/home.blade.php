<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Home</title>
    </head>
    <body>
        {{--
            You should have access to the $user variable which is passed from the controller.
            This because the middleware already checks that the user is authenticated.
            You won't be able to access this page without being logged in, hence without $user
        --}}
        <h1>Welcome, {{ $user->name }}!</h1>
    </body>
</html>