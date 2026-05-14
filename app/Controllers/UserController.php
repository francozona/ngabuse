<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function users(): string
    {
        return view('admin/users.php', [
            'users'    => $this->userModel->where('role !=', 'visitor')->orderBy('created_at', 'DESC')->findAll(),
            'editUser' => null,
            'errors'   => [],
        ]);
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        return view('admin/users.php', [
            'users'    => $this->userModel->orderBy('created_at', 'DESC')->findAll(),
            'editUser' => $user,
            'errors'   => [],
        ]);
    }

    public function save()
    {
        $id       = $this->request->getPost('id');
        $isUpdate = !empty($id);

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'role'      => $this->request->getPost('role'),
        ];

        // Only hash + save password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(FCPATH . 'uploads/users', $newName);
            $data['image'] = $newName;
        }

        if ($isUpdate) {
            $this->userModel->update($id, $data);
        } else {
            // Password required on create
            if (empty($password)) {
                return view('admin/users.php', [
                    'users'    => $this->userModel->orderBy('created_at', 'DESC')->findAll(),
                    'editUser' => null,
                    'errors'   => ['password' => 'Password is required when creating a user.'],
                ]);
            }
            $this->userModel->insert($data);
        }

        if (!$this->userModel->errors()) {
            return redirect()->to('/admin/users')
                             ->with('success', $isUpdate ? 'User updated.' : 'User created.');
        }

        return view('admin/users.php', [
            'users'    => $this->userModel->orderBy('created_at', 'DESC')->findAll(),
            'editUser' => $isUpdate ? array_merge(['id' => $id], $data) : null,
            'errors'   => $this->userModel->errors(),
        ]);
    }

    public function delete(int $id)
    {
        $this->userModel->delete($id);
        return redirect()->to('/admin/users')->with('success', 'User deleted.');
    }
}