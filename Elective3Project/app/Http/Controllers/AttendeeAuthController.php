<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendeeAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                'max:100',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
            ],
        ], [
            'login.required' => 'Please enter your email or username.',
            'login.max' => 'Login field cannot exceed 100 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $field = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $validated['login'], 'password' => $validated['password'], 'role' => 'attendee'], true)) {
            throw ValidationException::withMessages([
                'login' => 'Invalid credentials. Please check your username/email and password.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('portal.home'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

        return view('portal.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:users,username',
            ],
            'full_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                'unique:users,email',
            ],
            'contact_number' => [
                'required',
                'string',
                'size:11',
                'regex:/^09[0-9]{9}$/',
                'unique:users,contact_number',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                'confirmed',
            ],
            'password_confirmation' => ['required', 'string'],
        ], [
            'username.regex' => 'Username must contain only letters, numbers, and underscores.',
            'username.min' => 'Username must be at least 3 characters.',
            'full_name.regex' => 'Full name must contain only letters and spaces.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'contact_number.regex' => 'Contact number must be 11 digits starting with 09.',
            'contact_number.size' => 'Contact number must be exactly 11 digits.',
            'contact_number.unique' => 'This contact number is already registered.',
            'password.regex' => 'Password must contain uppercase, lowercase, and numeric characters.',
            'password_confirmation.required' => 'Please confirm your password.',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'password' => $validated['password'],
            'role' => 'attendee',
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('portal.home')->with('success', 'Your attendee account has been created successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.home');
    }

    private function redirectAuthenticatedUser(): RedirectResponse
    {
        return Auth::user()?->role === 'admin'
            ? redirect()->route('dashboard')
            : redirect()->route('portal.home');
    }
}
