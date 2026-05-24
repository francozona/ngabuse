<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>
<link rel="stylesheet"
        href="https://cdn.ckeditor.com/4.14.1/full-all/plugins/codesnippet/lib/highlight/styles/monokai_sublime.css">
  <style>
      .cke_notifications_area {
          display: none;
      }
  </style>
<div class="flex-1 overflow-y-auto px-6 py-5 bg-white min-h-screen">
<style>
  #responseEditor + .ck-editor .ck-editor__editable_inline {
    min-height: 500px;
}
  </style>
  <!-- ── Page Header ─────────────────────────────────────────── -->
  <div class="mb-6 flex items-center justify-between">
    <div>
      <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
        <a href="/admin/reports" class="hover:text-gray-600 transition-colors">Abuse Reports</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 font-medium"><?= esc($report['ticket_id'] ?? 'N/A') ?></span>
      </div>
      <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Abuse Report Detail</h1>
    </div>

    <!-- Status Badge + Actions -->
    <div class="flex items-center gap-3">
      <?php
        $status = $report['status'] ?? 'pending';
        $statusConfig = [
          'pending'      => ['bg-amber-50 text-amber-700 ring-amber-200',        'bg-amber-400',   'Open'],
          'under_review' => ['bg-blue-50 text-blue-700 ring-blue-200',           'bg-blue-400',    'Under Review'],
          'resolved'     => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-400', 'Closed'],
          'rejected'     => ['bg-red-50 text-red-700 ring-red-200',              'bg-red-400',     'Rejected'],
        ];
        [$statusClass, $dotClass, $statusLabel] = $statusConfig[$status] ?? $statusConfig['pending'];
      ?>
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold ring-1 <?= $statusClass ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= $dotClass ?>"></span>
        <?= $statusLabel ?>
      </span>

      <!-- Update Status Dropdown (Alpine.js) -->
      <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
          Update Status
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" @click.outside="open = false"
             class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-10 overflow-hidden">
          <?php foreach (['pending' => 'Open', 'under_review' => 'Under Review', 'resolved' => 'Actioned', 'rejected' => 'Rejected'] as $val => $label): ?>
          <form method="POST" action="/admin/reports/<?= esc($report['id'] ?? '') ?>/status">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="<?= $val ?>">
            <button type="submit"
                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors <?= $status === $val ? 'font-semibold bg-gray-50' : '' ?>">
              <?= $label ?>
            </button>
          </form>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Main Grid ────────────────────────────────────────────── -->
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 py-4">

    <!-- Left Column (spans 2) -->
    <div class="xl:col-span-2 space-y-5">

      <!-- Ticket Overview Card -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Report Overview</h2>
        </div>
        <div class="p-5 grid grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Ticket ID</p>
            <p class="text-sm font-mono font-semibold text-gray-800"><?= esc($report['ticket_id'] ?? '—') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Abuse Category</p>
            <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-md bg-rose-50 text-rose-700 ring-1 ring-rose-200">
              <?= esc($report['abuse_category'] ?? '—') ?>
            </span>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Domain Name</p>
            <p class="text-sm font-medium text-gray-800"><?= esc($report['domain_name'] ?? '—') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">TLD</p>
            <p class="text-sm font-mono text-gray-800"><?= esc($report['tld'] ?? '—') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Full Domain</p>
            <p class="text-sm font-medium text-gray-800"><?= esc($report['full_domain'] ?? '—') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Date First Observed</p>
            <p class="text-sm text-gray-800"><?= esc($report['date_first_observed'] ?? '—') ?></p>
          </div>
          <div class="col-span-2">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Abusive URL</p>
            <a href="<?= esc($report['abusive_url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-mono break-all underline underline-offset-2">
              <?= esc($report['abusive_url'] ?? '—') ?>
              <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Date Submitted</p>
            <p class="text-sm text-gray-800"><?= esc($report['created_at'] ? date('M d, Y H:i', strtotime($report['created_at'])) : '—') ?></p>
          </div>
        </div>
      </div>

      <!-- Description Card -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Description</h2>
        </div>
        <div class="p-5">
          <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap"><?= esc($report['description'] ?? 'No description provided.') ?></p>
        </div>
      </div>

      <!-- Registrar Notification Card -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Registrar Notification</h2>
        </div>
        <div class="p-5 grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Registrar Notified?</p>
            <?php if (!empty($report['registrar_notified'])): ?>
              <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Yes
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                No
              </span>
            <?php endif; ?>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Notification Date</p>
            <p class="text-sm text-gray-800"><?= esc($report['registrar_notification_date'] ? date('M d, Y', strtotime($report['registrar_notification_date'])) : '—') ?></p>
          </div>
        </div>
      </div>

      <!-- Evidence Files Card -->
    <?php
        $evidenceFiles = $report['evidence_files'] ?? [];

        if (is_string($evidenceFiles)) {
            $decoded = json_decode($evidenceFiles, true);

            $evidenceFiles = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($evidenceFiles)) {
            $evidenceFiles = [];
        }

        
        ?>
      <?php if (!empty($evidenceFiles)): ?>
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Evidence Files</h2>
          <span class="ml-auto text-xs text-gray-400 font-medium"><?= $user['raw_password'] ?> </span>
        </div>
        <div class="p-5 space-y-2">
          <?php foreach ($evidenceFiles as $i => $file): ?>
          <a href="<?= esc(is_array($file) ? ($file['url'] ?? '#') : $file) ?>" target="_blank" rel="noopener noreferrer"
             class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all group">
            <div class="w-9 h-9 rounded-lg bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
              <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm text-gray-700 group-hover:text-blue-700 font-medium transition-colors">
              <?= esc(is_array($file) ? ($file['name'] ?? 'Evidence File ' . ($i + 1)) : 'Evidence File ' . ($i + 1)) ?>
            </span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 ml-auto transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

     
      

    </div><!-- end left col -->

    <!-- Right Column (sidebar) -->
    <div class="space-y-5">

      <!-- Reporter Info -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Reporter</h2>
        </div>
        <div class="p-5">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-bold shrink-0">
              <?= strtoupper(substr($report['reporter_name'] ?? ($report['user_id'] ?? 'U'), 0, 1)) ?>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-800 truncate"><?= esc($report['reporter_name'] ?? 'User #' . ($report['user_id'] ?? '—')) ?></p>
              <p class="text-xs text-gray-400 truncate"><?= esc($report['reporter_email'] ?? '—') ?></p>
               <p class="text-xs text-gray-400">Password : <?= esc($user['raw_password'] ?? '—') ?></p>
            </div>
          </div>
          <?php if (!empty($report['reporter_email'])): ?>
          <a href="mailto:<?= esc($report['reporter_email']) ?>"
             class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Email Reporter
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Domain Stats -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Domain Stats</h2>
        </div>
        <div class="p-5 space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-xs text-gray-400 font-medium">Reports for this domain</span>
            <span class="text-sm font-bold text-gray-800"><?= esc($domainReportCount ?? '—') ?></span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-xs text-gray-400 font-medium">Last report</span>
            <span class="text-xs text-gray-600"><?= esc($lastDomainReport ?? '—') ?></span>
          </div>
          <div class="pt-2 border-t border-gray-100">
            <a href="/reports?domain=<?= urlencode($report['full_domain'] ?? '') ?>"
               class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
              View all reports for this domain →
            </a>
          </div>
        </div>
      </div>

      <!-- Timestamps -->
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Timestamps</h2>
        </div>
        <div class="p-5 space-y-3">
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Created</p>
            <p class="text-xs text-gray-700"><?= esc($report['created_at'] ? date('M d, Y H:i:s', strtotime($report['created_at'])) : '—') ?></p>
          </div>
          <div>
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5">Last Updated</p>
            <p class="text-xs text-gray-700"><?= esc($report['updated_at'] ? date('M d, Y H:i:s', strtotime($report['updated_at'])) : '—') ?></p>
          </div>
          <?php if (!empty($report['deleted_at'])): ?>
          <div>
            <p class="text-xs text-red-400 font-medium uppercase tracking-wide mb-0.5">Deleted</p>
            <p class="text-xs text-red-600"><?= esc(date('M d, Y H:i:s', strtotime($report['deleted_at']))) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Danger Zone -->
      <!-- <div class="bg-white border border-red-100 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-red-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <h2 class="text-sm font-semibold text-red-500 uppercase tracking-wide">Danger Zone</h2>
        </div>
        <div class="p-5">
          <form method="POST" action="/admin/reports/<?= esc($report['id'] ?? '') ?>/delete"
                onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone.')">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-semibold text-red-600 border border-red-200 rounded-xl
                           hover:bg-red-50 hover:border-red-300 active:scale-[0.97] transition-all">
              Delete Report
            </button>
          </form>
        </div>
      </div> -->

    </div><!-- end right col -->

     

  </div><!-- end grid -->
            <!-- ── Responses / Timeline ─────────────────────────────── -->
      <div class="bg-white w- border border-gray-200 rounded-2xl overflow-hidden shadow-sm mb-8">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Responses & Activity</h2>
          </div>
          <span class="text-xs bg-gray-100 text-gray-500 font-semibold px-2.5 py-0.5 rounded-full"><?= count($responses ?? []) ?></span>
        </div>

        <!-- Timeline -->
        <div class="p-5 mb-8">
          <?php if (!empty($responses)): ?>

          <?php $lastIndex = array_key_last($responses); ?>

          <div class="relative space-y-6">

            <!-- Full timeline line -->
            <div class="absolute left-4 top-0 bottom-0 w-px bg-gray-100"></div>

            <?php foreach ($responses as $idx => $response): ?>

            <?php
             $userModel = new \App\Models\UserModel();
              $responder = $userModel->find($response['user_id']);
              $isAdmin = ($responder['role'] ?? '') === 'admin';

              $name = $responder['full_name'] ?? 'Unknown';
              $initial = strtoupper(substr($name, 0, 1));

              $date = !empty($response['created_at'])
                ? date('M d, Y · H:i', strtotime($response['created_at']))
                : '—';
            ?>

            <div class="relative flex gap-4">

              <?php
                $avatar = $responder['image'] ?? null;
                $hasImage = !empty($avatar);
              ?>

              <div class="relative z-10 w-8 h-8 rounded-full overflow-hidden flex items-center justify-center text-xs font-bold
                <?= $isAdmin ? 'bg-gray-900 text-white' : 'bg-blue-100 text-blue-700' ?>">

                <?php if ($hasImage): ?>
                  <img src="<?= base_url($avatar) ?>"
                      class="w-full h-full object-cover"
                      alt="avatar">
                <?php else: ?>
                  <?= $initial ?>
                <?php endif; ?>

              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">

                <!-- Header -->
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="text-sm font-semibold text-gray-800">
                    <?= esc($name) ?>
                  </span>

                  <?php if ($isAdmin): ?>
                  <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 font-medium">
                    Admin
                  </span>
                  <?php endif; ?>

                  <span class="text-xs text-gray-400 ml-auto">
                    <?= esc($date) ?>
                  </span>
                </div>

                <!-- Message -->
                <div class="rounded-xl px-4 py-3 text-sm leading-relaxed
                  <?= $isAdmin ? 'bg-gray-50 border border-gray-200 text-gray-700'
                              : 'bg-blue-50 border border-blue-100 text-gray-700' ?>">
               <?= html_entity_decode($response['message'] ?? '') ?>
                </div>

              </div>
            </div>

            <?php endforeach; ?>

          </div>

          <?php else: ?>
          <div class="py-8 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
              <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <p class="text-sm text-gray-400 font-medium">No responses yet</p>
            <p class="text-xs text-gray-300 mt-0.5">Be the first to respond to this report</p>
          </div>
          <?php endif; ?>

          <!-- ── Reply Box ───────────────────────────────── -->
         <div class="mt-6 pt-5 border-t border-gray-100">
           <form method="POST" action="/admin/reports/<?= esc($report['id'] ?? '') ?>/respond">
              <?= csrf_field() ?>

            <p class="text-sm font-semibold text-gray-700 mb-3">Add CC</p>

            <textarea
              name="cc_emails"
              class="rounded border mb-4 border-gray-300 text-gray-900 focus:ring-gray-900 focus:border-gray-900 w-full p-3 text-sm"
              placeholder="Add additional email addresses to notify (comma-separated)"
            ></textarea>
              
              <p class="text-sm font-semibold text-gray-700 mb-3">Add a Response</p>
            
              <!-- CKEditor replaces this textarea -->
              <textarea name="message" id="responseEditor">
                   
              </textarea>

              <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer select-none">
                  <input type="checkbox" name="notify_reporter" value="1"
                        class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 cursor-pointer">
                  Notify reporter via email
                </label>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl
                              hover:bg-gray-700 active:scale-[0.97] transition-all">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
                  Send Response
                </button>
              </div>
            </form>
          </div>

          <!-- CKEditor 5 with SimpleUploadAdapter via importmap -->
        <script type="importmap">
        {
          "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.3.1/"
          }
        }
        </script>
 
        </div>
      </div>
       <!-- CKEditor 5 -->
      <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

      <script>
      document.querySelectorAll('textarea:not(.ignore-editor):not(.swal2-textarea)').forEach(function (textarea) {

          if (textarea.id && !textarea.closest('.swal2-container')) {

              ClassicEditor
                  .create(textarea, {
                      ckfinder: {
                          uploadUrl: "<?= base_url('upload-image') ?>"
                      },
                      toolbar: [
                          'heading',
                          '|',
                          'bold',
                          'italic',
                          'link',
                          'bulletedList',
                          'numberedList',
                          '|',
                          'outdent',
                          'indent',
                          '|',
                          'uploadImage',
                          'blockQuote',
                          'insertTable',
                          'mediaEmbed',
                          'undo',
                          'redo',
                          'codeBlock'
                      ]
                  })
                  .then(editor => {
                      console.log('Editor initialized:', textarea.id);
                  })
                  .catch(error => {
                      console.error(error);
                  });
          }
      });
      </script>
</div>

<?= $this->endSection() ?>