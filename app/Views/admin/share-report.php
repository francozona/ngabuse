<?= $this->extend('shared/report.layout.php') ?>

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
        
        <a href="/" class="hover:text-gray-600 transition-colors">.ng Abuse Report</a>
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
    </div>
  </div>

  <!-- ── Main Grid ────────────────────────────────────────────── -->
  <div class="grid grid-cols-1 gap-6 py-4">

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
          <span class="ml-auto text-xs text-gray-400 font-medium"><?= count($evidenceFiles) ?> file(s)</span>
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
                  <img src="<?= base_url('uploads/avatars/' . $avatar) ?>"
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
                    <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                </head>
                <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f5f5;">
                        <tr>
                            <td style="padding: 40px 20px;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="margin: 0 auto; background-color: #ffffff;">
                                    
                                    <!-- Header -->
                        <tr>
                        <td style="padding: 32px 40px; vertical-align: middle;">
                            
                            <!-- Right-aligned logo -->
                            <img src="<?= base_url('logo.png') ?>" 
                                alt="NiRA" 
                                style="height: 48px; display: inline-block; float: right;">

                            <!-- Left-aligned logo -->
                            <img src="<?= base_url('nira-logo.png') ?>" 
                                alt="NiRA" 
                                style="height: 48px; display: inline-block;">

                        </td>
                    </tr>


                                    
                                    <!-- Content -->
                                    <tr>
                                        <td style="padding: 48px 40px;">
                                            <h1 style="color: #1a1a1a; font-size: 24px; font-weight: 600; margin: 0 0 24px 0; line-height: 1.3;">
                                                Certificate of Completion
                                            </h1>
                                            
                                            <p style="color: #4a4a4a; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;">
                                                Dear [Student Name],
                                            </p>
                                            
                                            <p style="color: #4a4a4a; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0;">
                                                This confirms your successful completion of the [Training Program Name] on [Date]. Your certificate is now available for download.
                                            </p>
                                            
                                            <!-- Certificate Info Box -->
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 0 0 32px 0; border: 1px solid #e5e5e5; background-color: #fafafa;">
                                                <tr>
                                                    <td style="padding: 24px;">
                                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                            <tr>
                                                                <td style="color: #6a6a6a; font-size: 13px; padding: 0 0 4px 0;">Participant</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #1a1a1a; font-size: 15px; font-weight: 500; padding: 0 0 16px 0;">[Student Name]</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #6a6a6a; font-size: 13px; padding: 0 0 4px 0;">Program</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #1a1a1a; font-size: 15px; font-weight: 500; padding: 0 0 16px 0;">[Training Program Name]</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #6a6a6a; font-size: 13px; padding: 0 0 4px 0;">Completed</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="color: #1a1a1a; font-size: 15px; font-weight: 500;">[Date]</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                            
                                            <!-- Download Button -->
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 0 32px 0;">
                                                <tr>
                                                    <td style="background-color: #1a5f3f; border-radius: 4px;">
                                                        <a href="[CERTIFICATE_DOWNLOAD_LINK]" download style="display: inline-block; color: #ffffff; text-decoration: none; padding: 14px 32px; font-size: 15px; font-weight: 500;">
                                                            Download Certificate
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                            
                                            <p style="color: #4a4a4a; font-size: 16px; line-height: 1.6; margin: 0 0 8px 0;">
                                                This certificate may be shared on professional networks and added to your credentials.
                                            </p>
                                            
                                            <p style="color: #4a4a4a; font-size: 16px; line-height: 1.6; margin: 0;">
                                                For questions regarding your certificate, please contact us at academy@nira.org.ng.
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    <!-- Footer -->
                                    <tr>
                                        <td style="padding: 32px 40px; background-color: #fafafa; border-top: 1px solid #e5e5e5;">
                                            <p style="color: #1a1a1a; font-size: 14px; font-weight: 500; margin: 0 0 8px 0;">
                                                Nigeria Internet Registration Association
                                            </p>
                                            
                                            <p style="color: #6a6a6a; font-size: 14px; line-height: 1.5; margin: 0 0 16px 0;">
                                                academy@nira.org.ng<br>
                                                www.nira.org.ng
                                            </p>
                                            
                                            <p style="color: #9a9a9a; font-size: 12px; line-height: 1.5; margin: 0;">
                                                © 2025 Nigeria Internet Registration Association. All rights reserved.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
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
   <!-- CKEditor Scripts -->
    <script src="//cdn.ckeditor.com/4.14.1/full-all/ckeditor.js"></script>
    <script src="https://cdn.ckeditor.com/4.14.1/full-all/plugins/codesnippet/lib/highlight/highlight.pack.js"></script>

    <script>
        document.querySelectorAll('textarea:not(.ignore-editor):not(.swal2-textarea)').forEach(function (textarea) {
            if (textarea.id && !textarea.closest('.swal2-container')) {
                CKEDITOR.replace(textarea.id, {
                    allowedContent: true,
                    extraPlugins: 'uploadimage,image2',
                    removePlugins: 'easyimage,cloudservices',
                    height: 900,
                    filebrowserUploadUrl: "{{ route('upload_image', ['_token' => csrf_token()]) }}",
                    filebrowserUploadMethod: 'form',
                });
            }
        });
    </script>
</div>

<?= $this->endSection() ?>