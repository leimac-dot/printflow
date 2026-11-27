<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\PrintModelController;
use App\Models\PrintModel;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$resolveApiUser = function (Request $request): ?User {
    $header = (string) $request->header('Authorization', '');
    if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        $token = trim($matches[1]);
        if ($token !== '') {
            $user = User::where('remember_token', $token)->first();
            if ($user) {
                return $user;
            }
        }
    }

    $email = $request->input('email') ?? $request->input('client_email');
    if (is_string($email) && trim($email) !== '') {
        return User::where('email', strtolower(trim($email)))->first();
    }

    return null;
};

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::get('/models', function () {
    return response()->json(PrintModel::query()->latest()->get());
});

Route::post('/models', function (Request $request) use ($resolveApiUser) {
    $user = $resolveApiUser($request);
    if ($user) {
        Auth::setUser($user);
    }

    return app(PrintModelController::class)->store($request);
});

Route::put('/models/{model}', function (Request $request, PrintModel $model) use ($resolveApiUser) {
    $user = $resolveApiUser($request);
    if ($user) {
        Auth::setUser($user);
    }

    return app(PrintModelController::class)->update($request, $model);
});

Route::delete('/models/{model}', function (Request $request, PrintModel $model) use ($resolveApiUser) {
    $user = $resolveApiUser($request);
    if ($user) {
        Auth::setUser($user);
    }

    return app(PrintModelController::class)->destroy($model);
});

Route::get('/quotes', function (Request $request) use ($resolveApiUser) {
    $user = $resolveApiUser($request);

    $query = Quote::with(['user', 'printModel', 'histories'])->latest();

    if (! $user) {
        return response()->json($query->whereNull('user_id')->get());
    }

    if ($user->role === 'admin') {
        return response()->json($query->get());
    }

    $quotes = $query->where(function ($q) use ($user) {
        $q->where('user_id', $user->id)
            ->orWhere('client_email', $user->email);
    })->get();

    return response()->json($quotes);
});

Route::post('/quotes', function (Request $request) use ($resolveApiUser) {
    $user = $resolveApiUser($request);
    if ($user) {
        Auth::setUser($user);
    }

    return app(CalculatorController::class)->store($request);
});

Route::patch('/quotes/{quote}/status', function (Request $request, Quote $quote) use ($resolveApiUser) {
    $user = $resolveApiUser($request);
    if ($user) {
        Auth::setUser($user);
    }

    return app(CalculatorController::class)->updateStatus($request, $quote);
});