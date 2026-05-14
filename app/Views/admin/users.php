<?= $this->extend('shared/admin.layout.php') ?>
<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">

  <?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ── Form Panel ───────────────────────────────────── -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        <h2 class="text-base font-semibold text-gray-800 mb-5">
          <?= isset($editUser) && $editUser ? 'Edit User' : 'Create User' ?>
        </h2>

        <?php if (!empty($errors)): ?>
          <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
              <p><?= esc($error) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="/admin/users/save" method="post" enctype="multipart/form-data" class="space-y-4">
          <?= csrf_field() ?>

          <?php if (isset($editUser) && $editUser): ?>
            <input type="hidden" name="id" value="<?= esc($editUser['id']) ?>">
          <?php endif; ?>

          <!-- Full Name -->
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Full Name</label>
            <input
              type="text"
              name="full_name"
              value="<?= esc($editUser['full_name'] ?? '') ?>"
              placeholder="Jane Doe"
              class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
            >
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Email Address</label>
            <input
              type="email"
              name="email"
              value="<?= esc($editUser['email'] ?? '') ?>"
              placeholder="jane@example.com"
              class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
            >
          </div>

          <!-- Role -->
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
            <select
              name="role"
              class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
            >
              <option value="user"  <?= (($editUser['role'] ?? 'user') === 'user')  ? 'selected' : '' ?>>User</option>
              <option value="admin" <?= (($editUser['role'] ?? '')      === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Password
              <?php if (isset($editUser) && $editUser): ?>
                <span class="text-gray-400 font-normal">(leave blank to keep current)</span>
              <?php else: ?>
                <span class="text-red-400">*</span>
              <?php endif; ?>
            </label>
            <input
              type="password"
              name="password"
              placeholder="<?= isset($editUser) && $editUser ? '••••••••' : 'Enter password' ?>"
              class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
            >
          </div>

          <!-- Avatar -->
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Profile Image</label>
            <?php if (!empty($editUser['image'])): ?>
              <div class="flex items-center gap-3 mb-2">
                <img src="/uploads/users/<?= esc($editUser['image']) ?>"
                     class="h-10 w-10 rounded-full object-cover border border-gray-200" alt="">
                <span class="text-xs text-gray-400">Current image</span>
              </div>
            <?php endif; ?>
            <input
              type="file"
              name="image"
              accept="image/*"
              class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
            >
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-1">
            <button
              type="submit"
              class="flex-1 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 transition"
            >
              <?= isset($editUser) && $editUser ? 'Update User' : 'Create User' ?>
            </button>

            <?php if (isset($editUser) && $editUser): ?>
              <a href="/admin/users"
                 class="flex-1 text-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium py-2 transition">
                Cancel
              </a>
            <?php endif; ?>
          </div>

        </form>
      </div>
    </div>

    <!-- ── Users Table ───────────────────────────────────── -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800">All Users</h2>
          <span class="text-xs text-gray-400"><?= count($users) ?> total</span>
        </div>

        <?php if (empty($users)): ?>
          <div class="px-6 py-12 text-center text-sm text-gray-400">No users yet.</div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wide">
                  <th class="px-6 py-3 text-left">User</th>
                  <th class="px-6 py-3 text-left">Email</th>
                  <th class="px-6 py-3 text-left">Role</th>
                  <th class="px-6 py-3 text-left">Joined</th>
                  <th class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <?php foreach ($users as $user): ?>
                  <tr class="hover:bg-gray-50/50 transition <?= (isset($editUser) && $editUser && $editUser['id'] == $user['id']) ? 'bg-indigo-50/40' : '' ?>">

                    <!-- Avatar + Name -->
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-3">
                        <?php if (!empty($user['image'])): ?>
                          <img src="/uploads/users/<?= esc($user['image']) ?>"
                               class="h-8 w-8 rounded-full object-cover border border-gray-200 shrink-0" alt="">
                        <?php else: ?>
                          <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                          </div>
                        <?php endif; ?>
                        <span class="font-medium text-gray-800"><?= esc($user['full_name']) ?></span>
                      </div>
                    </td>

                    <td class="px-6 py-3 text-gray-500"><?= esc($user['email']) ?></td>

                    <!-- Role Badge -->
                    <td class="px-6 py-3">
                      <?php if ($user['role'] === 'admin'): ?>
                        <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                          Admin
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                          User
                        </span>
                      <?php endif; ?>
                    </td>

                    <td class="px-6 py-3 text-gray-400">
                      <?= date('M j, Y', strtotime($user['created_at'])) ?>
                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-3 text-right">
                      <div class="inline-flex items-center gap-1">
                        <a href="/admin/users/edit/<?= $user['id'] ?>"
                           class="inline-flex items-center rounded-md px-2.5 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                          Edit
                        </a>
                        <form action="/admin/users/delete/<?= $user['id'] ?>" method="post"
                              onsubmit="return confirm('Delete this user?')">
                          <?= csrf_field() ?>
                          <button type="submit"
                                  class="inline-flex items-center rounded-md px-2.5 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 transition">
                            Delete
                          </button>
                        </form>
                      </div>
                    </td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<?= $this->endSection() ?>