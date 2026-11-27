<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function throttleKey(Request $request, string $email, string $scope = 'web'): string
    {
        return $scope . ':' . Str::transliterate(Str::lower(trim($email)) . '|' . $request->ip());
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'client',
        ]);

        Auth::login($user);

        return redirect('/')->with('success', '¡Cuenta gratuita creada! Ya puedes seleccionar modelos y cotizar.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));
        $key = $this->throttleKey($request, $credentials['email'], 'web');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Demasiados intentos fallidos. Intenta de nuevo en {$seconds} segundos.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            return redirect('/')->with('success', 'Sesión iniciada correctamente.');
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada.');
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($credentials['email']));
        $key = $this->throttleKey($request, $email, 'api');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Demasiados intentos fallidos. Intenta de nuevo en {$seconds} segundos.",
            ], 429);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        RateLimiter::clear($key);

        $token = Str::random(60);
        $user->forceFill([
            'remember_token' => $token,
        ])->save();

        return response()->json([
            'message' => 'Sesión iniciada correctamente.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'client',
            ],
        ], 200);
    }
}