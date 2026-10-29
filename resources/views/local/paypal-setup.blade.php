<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Local PayPal Sandbox setup | IcyBreeze</title>
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
    <h1>PayPal Sandbox setup</h1>
    <p>This screen works only on localhost. Sandbox payments are test transactions and do not collect real money.</p>
    <p class="notice">Do not use live PayPal credentials here. The credentials are encrypted in the local database and are never committed to Git.</p>
    @if ($configured)<p class="success">Sandbox credentials are saved. Enter both fields again only to replace them.</p>@endif
    @if (session('status'))<p class="success">{{ session('status') }}</p>@endif
    @if ($errors->any())<p class="notice">Please provide a valid Sandbox Client ID and Secret.</p>@endif
    <form method="post" action="{{ request()->fullUrl() }}" autocomplete="off">
        @csrf
        <label for="client_id">Sandbox Client ID</label>
        <input id="client_id" name="client_id" required autocomplete="off" spellcheck="false">
        <label for="client_secret">Sandbox Secret</label>
        <input id="client_secret" name="client_secret" type="password" required autocomplete="new-password" spellcheck="false">
        <button type="submit">Save sandbox credentials</button>
    </form>
</main>
</body>
</html>
