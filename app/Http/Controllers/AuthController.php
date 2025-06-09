<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Redirect;
use Exception;
class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle the login
    public function login(Request $request)
    {

        // Validation for email and password
        $validatedData = $request->validate([
            'username' => 'required|email',
            'password' => 'required|min:6',
        ]);
    
        try {
            // Get credentials from the request
            $credentials = $request->only('username', 'password');
    
                    
    
            if (Auth::attempt($credentials)) {
                // Check if the user's role_id is not in the excluded roles
                if (in_array(Auth::user()->role_id, [1, 7, 8])) {
                    // If role_id is not in the list, redirect to the intended page or home
                    return redirect()->intended('/');
                }
            
                // If role_id is in the excluded list, log out the user and redirect to the login page with a message
                Auth::logout();
                return redirect('/login')->with('error', 'Access denied for your role.');
            }
            // If the credentials do not match

           
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput(); // Keep input except for the password
    
        } catch (Exception $e) {
            // Handle any unexpected errors
            return back()->withErrors([
                'error' => 'Something went wrong. Please try again later.',
            ]);
        }
    }

    // Handle logout
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    // Show the registration form
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/home');
    }
}
