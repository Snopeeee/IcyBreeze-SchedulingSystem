<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Local OpenWeather setup | IcyBreeze</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eaf8ff; color: #185079; font: 16px system-ui, sans-serif; }
        main { width: min(440px, calc(100% - 40px)); background: white; padding: 32px; border-radius: 18px; box-shadow: 0 18px 45px #18507920; }
        h1 { margin: 0 0 8px; font-size: 1.7rem; }
        p { line-height: 1.5; }
        label { display: block; margin: 20px 0 6px; font-weight: 700; }
        input { box-sizing: border-box; width: 100%; padding: 12px; border: 1px solid #8bbdd4; border-radius: 8px; }
        button { margin-top: 24px; padding: 12px 18px; border: 0; border-radius: 8px; background: #185079; color: white; font-weight: 700; cursor: pointer; }
        .notice { padding: 12px; border-radius: 8px; background: #fff2b4; }
        .success { padding: 12px; border-radius: 8px; background: #dcf9eb; }
    </style>
</head>
<body>
<main>
    <h1>OpenWeather setup</h1>
    <p>Connect your existing OpenWeather key to the Iligan City service-planning forecast. This setup screen is available only on localhost.</p>
    <p class="notice">The key is encrypted in local MySQL, not placed in source code or Git.</p>
    @if ($configured)<p class="success">An OpenWeather key is saved. Enter a key only to replace it.</p>@endif
    @if (session('status'))<p class="success">{{ session('status') }}</p>@endif
    @if ($errors->any())<p class="notice">Enter a valid 32-character OpenWeather API key.</p>@endif
    <form method="post" action="{{ request()->fullUrl() }}" autocomplete="off">
        @csrf
        <label for="api_key">OpenWeather API key</label>
        <input id="api_key" name="api_key" type="password" required autocomplete="new-password" spellcheck="false">
        <button type="submit">Save OpenWeather key</button>
    </form>
</main>
</body>
</html>
