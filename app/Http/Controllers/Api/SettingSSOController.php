<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SettingSSOController extends Controller
{
    public function show()
    {
        // $this->authorize('setting.view');

        $user = Auth::user();

        $isSocialite = $user && $user->is_socialite;

        return response()->json([
            'is_socialite' => (bool) $isSocialite,
        ]);
    }
}
