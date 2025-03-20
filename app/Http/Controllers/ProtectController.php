<?php

namespace App\Http\Controllers;

class ProtectController extends Controller
{
    /**
     * Show the password page.
     *
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('protect');
    }

    /**
     * Check the password page.
     */
    public function check()
    {
        $password = request()->input('password');
        if (config('youpi.password_protected') && in_array($password, config('youpi.passwords'))) {
            session()->put('protect_in', true);

            return redirect()->intended('/');
        }

        return redirect()->back()->withErrors(['message' => 'Mot de passe incorect']);
    }
}
