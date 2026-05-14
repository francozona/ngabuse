<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
         if (session()->get('admin_id')) {
            return redirect()->to('/dashboard');
        }

        return view('admin/login.php', ['errors' => []]);
    }

    public function attempt()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Basic presence check
        if (empty($email) || empty($password)) {
            return view('admin/login.php', [
                'errors' => ['Email and password are required.'],
            ]);
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        // Wrong email or wrong password — same message intentionally (no enumeration)
        if (!$user || !password_verify($password, $user['password'])) {
            return view('admin/login.php', [
                'errors' => ['Invalid email or password.'],
            ]);
        }

        // Only allow admin role
       if ($user['role'] != 'admin' && $user['role'] != 'user') {
            return redirect()->back()->with('errors', [
                'You do not have permission to access this area.'
            ]);
        }

        // Regenerate session ID to prevent fixation
        session()->regenerate(true);

        session()->set([
            'admin_id'    => $user['id'],
            'admin_name'  => $user['full_name'],
            'admin_email' => $user['email'],
            'admin_role'  => $user['role'],
            'admin_image' => $user['image'] ?? null,
        ]);

        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $user['full_name'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('success', 'You have been logged out.');
    }
}