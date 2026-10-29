<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Local Google Calendar setup | IcyBreeze</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eaf8ff; color: #185079; font: 16px system-ui, sans-serif; }
        main { width: min(480px, calc(100% - 40px)); background: white; padding: 32px; border-radius: 18px; box-shadow: 0 18px 45px #18507920; }
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
    <h1>Google Calendar OAuth setup</h1>
    <p>Use a Google Cloud <strong>Web application</strong> OAuth client. Add this exact authorized redirect URI:</p>
    <p class="notice"><code>{{ route('admin.google-calendar.callback') }}</code></p>
    <p>The credentials are encrypted in local MySQL. Connecting a calendar still requires an administrator to approve access in Google.</p>
    @if ($configured)<p class="success">OAuth credentials are saved. Enter both fields again only to replace them.</p>@endif
    @if (session('status'))<p class="success">{{ session('status') }}</p>@endif
    @if ($errors->any())<p class="notice">Provide a valid OAuth Web client ID and secret.</p>@endif
    <form method="post" action="{{ request()->fullUrl() }}" autocomplete="off">
        @csrf
        <label for="client_id">OAuth Client ID</label>
        <input id="client_id" name="client_id" required autocomplete="off" spellcheck="false">
        <label for="client_secret">OAuth Client Secret</label>
        <input id="client_secret" name="client_secret" type="password" required autocomplete="new-password" spellcheck="false">
        <button type="submit">Save OAuth credentials</button>
    </form>
</main>
</body>
</html>
