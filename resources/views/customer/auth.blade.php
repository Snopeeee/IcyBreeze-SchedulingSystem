@extends('layouts.site')
@php($title = $register ? 'Create Customer Account' : 'Customer Sign In')
@section('content')
<section class="account-section"><div class="account-card">
    <span class="eyebrow">IcyBreeze customer portal</span>
    <h1>{{ $register ? 'Create your account' : 'Welcome back' }}</h1>
    <p>{{ $register ? 'Keep your future bookings in one secure place.' : 'Sign in to view appointments made with your account.' }}</p>
    @if($errors->any())<div class="flash account-error">{{ $errors->first() }}</div>@endif
    <form class="admin-form" method="POST" action="{{ route($register ? 'customer.register.store' : 'customer.login.store') }}">@csrf
        @if($register)
            <div><label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
            <div><label for="phone">Mobile number</label><input id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel"></div>
        @endif
        <div><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div>
        <div><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="{{ $register ? 'new-password' : 'current-password' }}"></div>
        @if($register)<div><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>@endif
        @unless($register)<label class="login-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>@endunless
        <button class="button button-navy" type="submit">{{ $register ? 'Create Account' : 'Sign In' }} <i class="ph ph-arrow-right"></i></button>
    </form>
    <p class="account-switch">{{ $register ? 'Already have an account?' : 'New to IcyBreeze?' }} <a href="{{ route($register ? 'customer.login' : 'customer.register') }}">{{ $register ? 'Sign in' : 'Create an account' }}</a></p>
    <p class="account-note">Earlier guest bookings remain accessible through their private booking links. They are not automatically attached to a new account.</p>
</div></section>
@endsection
