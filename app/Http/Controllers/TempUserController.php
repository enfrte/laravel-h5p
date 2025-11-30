<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TempUserController extends Controller
{
    public function index()
    {
        return view('temp-user.index');
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|alpha_num|max:20'
        ]);

        session(['temp_username' => $validated['username']]);
        $_SESSION['foo'] = 'bar';

        return redirect()->route('home')->with('success', 'Temporary user created successfully!');
    }

    public function logout()
    {
        session()->forget('temp_username');
        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}
