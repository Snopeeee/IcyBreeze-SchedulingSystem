<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Technician Sign In | IcyBreeze</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icybreeze-favicon.png') }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<main class="login-page">
    <div class="login-card">
        <a href="{{ route('home') }}"><img class="login-logo" src="{{ asset('images/icybreeze-logo-brand.png') }}" alt="IcyBreeze Aircon Cleaning — Cleaner Air, Cooler Life"></a>
        <h1>Technician mobile sign in</h1>
        <p>View assigned Iligan jobs, customer-approved locations, and today’s optimized route.</p>
        @if($errors->any())<div class="flash" style="margin:0 0 18px;color:#9b3a3a;background:#fff0f0;border:1px solid #f2caca"><i class="ph-fill ph-warning-circle"></i>{{ $errors->first() }}</div>@endif
        <form class="admin-form" method="POST" action="{{ route('technician.login.store') }}">
            @csrf
            <div><label for="email">Technician email</label><input id="email" type="email" name="email" value="{{ old('email','rene@icybreeze.test') }}" required autofocus></div>
            <div><label for="password">Password</label><input id="password" type="password" name="password" value="password" required></div>
            <label style="display:flex;align-items:center;gap:8px"><input style="width:auto;min-height:auto" type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button class="button button-navy">Open my jobs <i class="ph ph-arrow-right"></i></button>
        </form>
        <div class="login-demo"><strong>Local technician demo</strong><br>rene@icybreeze.test · password</div>
    </div>
</main>
</body>
</html>
