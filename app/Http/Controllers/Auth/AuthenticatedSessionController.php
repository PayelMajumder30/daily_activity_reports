<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     $request->session()->regenerate();

    //     if(auth()->user()->role == 0)
    //         {
    //             return redirect()->route('dashboard');
    //         }

    //     if(auth()->user()->role == 1){
    //         return redirect()->route('uploader.index');
    //     }

    //     return redirect()->route('dashboard');

    //     // return redirect()->intended(route('dashboard', absolute: false));
    // }



    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if user exists
        $user = User::where('email', $request->email)->first();

        // Check account status
        if ($user && $user->status == 0) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.',
                ]);
        }

        // Authenticate user
        $request->authenticate();

        $request->session()->regenerate();

        if (auth()->user()->role == 0) {
            return redirect()->route('dashboard');
        }

        if (auth()->user()->role == 1) {
            return redirect()->route('uploader.index');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
