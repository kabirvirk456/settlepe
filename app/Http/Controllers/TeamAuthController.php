<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeamAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isTeamMember()) {
            return redirect()->route('team.home');
        }

        return view('team.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided team credentials are incorrect.',
            ]);
        }

        if (! Auth::user()->isTeamMember()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This login is only for Sales, RM, and Admin users.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('team.home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('team.login')->with('status', 'Team user logged out.');
    }
}
