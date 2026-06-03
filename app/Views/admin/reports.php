<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">

    <!-- ─── REPORTS TABLE ─── -->
    <div x-data="{ tableStatusFilter: '<?= esc($status ?? 'all') ?>' }" class="bg-white rounded-2xl border border-slate-100 shadow-card fade-in overflow-hidden" style="animation-delay:.25s">
      <!-- Table header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <h3 class="font-display font-700 text-gray-900 text-sm">Abuse Reports</h3>
          <span class="text-[11px] font-display font-700 px-2.5 py-0.5 rounded-full bg-nira-light text-nira-dark" x-text="filteredReports.length + ' records'"></span>
        </div>
        <div class="flex items-center gap-2">
          <!-- Search -->
        <div class="relative hidden md:block">
          <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" x-model="searchQuery" placeholder="Search reports, domains…"
            class="pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm text-gray-700 focus:outline-none focus:border-nira-green focus:ring-2 focus:ring-nira-green/20 w-64 transition-all">
        </div>
          <!-- Status filter tabs -->
          <div class="hidden sm:flex items-center gap-1 bg-slate-50 rounded-xl p-1">
            <button @click="tableStatusFilter='all'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='all' ? 'bg-white text-nira-green shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              All
            </button>
            <button @click="tableStatusFilter='OPEN'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='OPEN' ? 'bg-white text-amber-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Open
            </button>
            <button @click="tableStatusFilter='ASSIGNED_TO_REGISTRAR'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='ASSIGNED_TO_REGISTRAR' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Assigned
            </button>
            <button @click="tableStatusFilter='ACTIONED'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='ACTIONED' ? 'bg-white text-nira-green shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Actioned
            </button>
             <button @click="tableStatusFilter='CLOSED'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='CLOSED' ? 'bg-white text-nira-green shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Rejected
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-5 py-3 w-8">
                 
              </th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Ticket ID</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Domain</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Category</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Reporter</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Status</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Submitted</th>
              <th class="text-left text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider px-3 py-3">Evidence</th>
              <th class="px-3 py-3 w-8"></th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(r, i) in paginatedReports" :key="r.id">
              <tr class="report-row border-b border-slate-50 fade-in"
                  :class="selectedReport?.id === r.id ? 'selected' : ''"
                  :style="`animation-delay:${i*0.04}s`"
                  @click="selectReport(r)">
                <td class="px-5 py-3.5" @click.stop>
                   
                </td>
                <td class="px-3 py-3.5">
                  <span class="font-display font-700 text-xs text-nira-dark" x-text="r.ticket"></span>
                </td>
                <td class="px-3 py-3.5">
                  <div>
                    <p class="text-sm font-display font-700 text-gray-800" x-text="r.domain + '.' + r.tld"></p>
                    <p class="text-[10px] text-gray-400 truncate max-w-[160px]" x-text="r.url"></p>
                  </div>
                </td>
                <td class="px-3 py-3.5">
                  <span class="cat-badge" :class="catClass(r.category)" x-text="r.category"></span>
                </td>
                <td class="px-3 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-display font-700 text-gray-600 flex-shrink-0" x-text="r.reporter[0].toUpperCase()"></div>
                    <div class="min-w-0">
                      <p class="text-xs font-display font-600 text-gray-700 truncate max-w-[100px]" x-text="r.reporter"></p>
                    </div>
                  </div>
                </td>
                <td class="px-3 py-3.5">
                  <span class="badge" :class="statusClass(r.status)" x-text="statusLabel(r.status)"></span>
                </td>
                <td class="px-3 py-3.5">
                  <p class="text-xs text-gray-600 font-display" x-text="r.date"></p>
                </td>
                <td class="px-3 py-3.5">
                  <div class="flex items-center gap-1">
                    <template x-for="f in r.files" :key="f">
                      <span class="w-5 h-5 rounded bg-slate-100 flex items-center justify-center">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                      </span>
                    </template>
                    <span class="text-[10px] text-gray-400 font-display ml-0.5" x-text="r.files.length + ' file' + (r.files.length>1?'s':'')"></span>
                  </div>
                </td>
                <td class="px-3 py-3.5">
                  <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </td>
              </tr>
            </template>
            <tr x-show="filteredReports.length === 0">
              <td colspan="9" class="text-center py-16 text-sm text-gray-400 font-display">
                <div class="flex flex-col items-center gap-2">
                  <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  No reports match your filters
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 bg-slate-50/30">
        <p class="text-xs text-gray-400 font-display">
          Showing <span class="font-700 text-gray-700" x-text="(currentPage-1)*pageSize+1"></span>–<span class="font-700 text-gray-700" x-text="Math.min(currentPage*pageSize, filteredReports.length)"></span> of <span class="font-700 text-gray-700" x-text="filteredReports.length"></span>
        </p>
        <div class="flex items-center gap-1">
          <button @click="currentPage = Math.max(1, currentPage - 1)"
            class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-gray-400 hover:text-nira-green hover:border-nira-green/30 transition-all disabled:opacity-40"
            :disabled="currentPage === 1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <template x-for="p in totalPages" :key="p">
            <button @click="currentPage = p"
              class="w-8 h-8 rounded-lg text-xs font-display font-700 transition-all"
              :class="p === currentPage ? 'bg-nira-green text-white' : 'text-gray-500 hover:bg-slate-100'">
              <span x-text="p"></span>
            </button>
          </template>
          <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
            class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-gray-400 hover:text-nira-green hover:border-nira-green/30 transition-all"
            :disabled="currentPage === totalPages">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div> 

  <!-- ════════ DETAIL PANEL ════════ -->
   <div x-show="selectedReport"
     class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
     @click="selectedReport = null">
</div>
<div class="detail-panel" :class="selectedReport ? '' : 'closed'" style="font-family:'DM Sans',sans-serif">
  <template x-if="selectedReport">
    <div class="flex flex-col h-full slide-in">
      <!-- Panel header -->
      <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 flex-shrink-0">
        <button @click="selectedReport = null" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-gray-500 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="flex-1 min-w-0">
          <p class="font-display font-800 text-sm text-gray-900 leading-tight" x-text="selectedReport.ticket"></p>
          <p class="text-[11px] text-gray-400" x-text="selectedReport.domain + selectedReport.tld"></p>
        </div>
        <span class="badge" :class="statusClass(selectedReport.status)" x-text="statusLabel(selectedReport.status)"></span>
      </div>

      <!-- Scrollable panel body -->
      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">

        <!-- Domain block -->
        <div class="bg-gradient-to-br from-nira-xlight to-white rounded-xl p-4 border border-nira-green/15">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-nira-green/10 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-6 h-6 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/></svg>
          </div>
            <div>
              <p class="font-display font-800 text-gray-900" x-text="selectedReport.domain + selectedReport.tld"></p>
              <a class="text-xs text-nira-green hover:underline break-all" x-text="selectedReport.url" :href="selectedReport.url" target="_blank"></a>
              <div class="flex items-center gap-2 mt-2">
                <span class="cat-badge" :class="catClass(selectedReport.category)" x-text="selectedReport.category"></span>
                <span class="text-[10px] text-gray-400 font-display">First seen: <span class="font-700 text-gray-700" x-text="selectedReport.firstSeen"></span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Reporter -->
        <div>
          <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-2.5">Reporter</p>
          <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
            <div class="w-9 h-9 rounded-full bg-nira-green flex items-center justify-center text-white font-display font-700 flex-shrink-0" x-text="selectedReport.reporter[0].toUpperCase()"></div>
            <div>
              <p class="text-sm font-display font-700 text-gray-800" x-text="selectedReport.reporter"></p>
              <p class="text-xs text-gray-400" x-text="selectedReport.reporterEmail"></p>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-2.5">Description</p>
          <p class="text-sm text-gray-700 leading-relaxed bg-slate-50 rounded-xl p-3.5 border border-slate-100" x-text="selectedReport.description"></p>
        </div>

        <!-- Evidence files -->
        <div>
          <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-2.5">Evidence Files</p>
          <div class="space-y-2">
            <template x-for="(f, i) in selectedReport.fileDetails" :key="i">
              <a :href="f.url"
                target="_blank"
                class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-nira-green/25 transition-all cursor-pointer group">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="f.type === 'PDF' ? 'bg-red-50' : 'bg-blue-50'">
                  <svg x-show="f.type === 'PDF'" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <svg x-show="f.type !== 'PDF'" class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-display font-700 text-gray-700 truncate" x-text="f.name"></p>
                  <p class="text-[10px] text-gray-400" x-text="f.size"></p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-nira-green transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
              </a>
            </template>
          </div>
        </div>

        <!-- Registrar -->
        <div x-show="selectedReport.registrar">
          <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-2.5">Registrar Notified</p>
          <div class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-100">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span class="text-sm font-display font-700 text-gray-700" x-text="selectedReport.registrar"></span>
            <span class="ml-auto text-xs text-gray-400" x-text="selectedReport.registrarDate"></span>
          </div>
        </div>

        <!-- Timeline -->
        <div>
          <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-3">Activity Timeline</p>
          <div class="relative pl-5">
            <div class="absolute left-[7px] top-2 bottom-2 w-px bg-slate-200"></div>
            <template x-for="(e, i) in selectedReport.timeline" :key="i">
              <div class="flex gap-3 mb-4 relative">
                <div class="w-3.5 h-3.5 rounded-full flex-shrink-0 mt-0.5 -ml-5 border-2 border-white z-10"
                     :style="`background:${e.color}`"></div>
                <div>
                  <p class="text-xs font-display font-700 text-gray-800" x-text="e.event"></p>
                  <p class="text-[10px] text-gray-400" x-text="e.time"></p>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Panel actions -->
      <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50 flex-shrink-0 space-y-2.5">
        <p class="text-[10px] font-display font-700 text-gray-400 uppercase tracking-wider mb-3">Update Status</p>
        <div class="grid grid-cols-2 gap-2">
  
        <a :href="`/report/OPEN/${selectedReport.id}`"  
          class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all border border-blue-200/50">
          See More
        </a>

        <button @click="selectedReport = null" 
          class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-nira-light text-nira-dark hover:bg-green-100 transition-all border border-nira-green/20">
          Cancel
        </button>

       

      </div>
         
      </div>
    </div>
  </template>

  <!-- Empty state -->
  <template x-if="!selectedReport">
    <div class="flex flex-col items-center justify-center h-full text-center px-8 gap-3">
      <div class="w-16 h-16 rounded-2xl bg-nira-light flex items-center justify-center">
        <svg class="w-8 h-8 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <p class="font-display font-700 text-gray-700 text-sm">Select a report</p>
      <p class="text-xs text-gray-400">Click any row in the table to view full details and take action</p>
    </div>
  </template>
</div>

<!-- Detail overlay (mobile) -->
<div x-show="selectedReport" x-cloak @click="selectedReport=null"
  class="fixed inset-0 bg-black/20 z-40 lg:hidden" style="backdrop-filter:blur(2px)"></div>



<script>
function dashboard() {
  return {
    // ── inject PHP data directly into Alpine ──────────────────
    reports: <?= json_encode(array_map(function($r) {
       $files = json_decode($r['evidence_files'], true);

    if (is_string($files)) {
        $files = json_decode($files, true);
    }

    if (!is_array($files)) {
        $files = [];
    }
        return [
            'id'            => $r['id'],
            'ticket'        => $r['ticket_id'],
            'domain'        => $r['domain_name'],
            'tld'           => $r['tld'],
            'url'           => $r['abusive_url'],
            'category'      => $r['abuse_category'],
            'reporter'      => $r['reporter_name'],
            'reporterEmail' => $r['reporter_email'],
            'status'        => strtoupper(str_replace('_', '_', match($r['status']) {
                                    'pending'      => 'OPEN',
                                    'assigned_to_registrar' => 'ASSIGNED_TO_REGISTRAR',
                                    'resolved'     => 'ACTIONED',
                                    'rejected'     => 'CLOSED',
                                    default        => 'CLOSED',
                               })),
            'date'          => date('d M Y', strtotime($r['created_at'])),
            'firstSeen'     => date('d M Y', strtotime($r['date_first_observed'])),
            'description'   => $r['description'],
            'registrar'     => $r['registrar_notified'],
            'registrarDate' => $r['registrar_notification_date']
                                ? date('d M Y', strtotime($r['registrar_notification_date']))
                                : null,
            'files' => array_map(
                fn($f) => base_url($f),
                $files
            ),

            'fileDetails' => array_map(fn($path) => [
              'name' => basename($path),
              'type' => strtoupper(pathinfo($path, PATHINFO_EXTENSION)),
              'size' => '—',
              'url'  => base_url($path),
          ], $files),
            'timeline'      => $r['timeline'] ?? [],
        ];
    }, $reports ?? [])) ?>,

    // ── stats from PHP ─────────────────────────────────────────
    stats: [
      { label:'Total Reports',  value: <?= $stats['total'] ?>,    trend:14, color:'green', iconBg:'bg-nira-light', icon:'📋', spark:[40,55,45,70,60,80,65,90] },
      { label:'Open Cases',     value: <?= $stats['open'] ?>,     trend:-3, color:'amber', iconBg:'bg-amber-50',  icon:'🔓', spark:[50,60,40,55,70,45,65,50] },
      { label:'Assigned',      value: <?= $stats['in_review'] ?>,trend: 8, color:'blue',  iconBg:'bg-blue-50',   icon:'🔍', spark:[30,45,60,40,70,55,65,75] },
      { label:'Actioned Today', value: <?= $stats['actioned'] ?>, trend:22, color:'red',   iconBg:'bg-green-50',  icon:'✅', spark:[20,40,35,55,45,70,60,80] },
    ],

    // ── weekly chart from PHP ──────────────────────────────────
    weeklyData: <?= json_encode(array_map(fn($w) => [
        'label' => $w['label'],
        'count' => (int)$w['count'],
    ], $weekly ?? [])) ?>,

    get maxWeekly() {
      return Math.max(...this.weeklyData.map(w => w.count), 1);
    },

    // ── category breakdown from PHP ────────────────────────────
    categoryBreakdown: <?= json_encode(array_map(fn($c) => [
        'name'  => $c['name'],
        'count' => (int)$c['count'],
        'color' => match($c['name']) {
            'Phishing' => '#f59e0b',
            'Malware'  => '#ef4444',
            'Botnets'  => '#f97316',
            'Pharming' => '#8b5cf6',
            'Spam'     => '#3b82f6',
            default    => '#94a3b8',
        },
    ], $categories ?? [])) ?>,

    get maxCategory() {
      return Math.max(...this.categoryBreakdown.map(c => c.count), 1);
    },

    // ── keep the rest of your Alpine methods unchanged ─────────
    activePage: 'dashboard',
    searchQuery: '',
    activeFilter: '',
    tableStatusFilter: 'all',
    currentPage: 1,
    pageSize: 8,
    selectedReport: null,

    init() {
      this.updateStats(); // no longer needed for counts but kept for categoryBreakdown maxes
    },

    generateReports() {
      const categories = ['Phishing','Malware','Botnets','Pharming','Spam','Phishing','Malware','Phishing'];
      const statuses = ['OPEN','OPEN','ASSIGNED_TO_REGISTRAR','ACTIONED','CLOSED','OPEN','ASSIGNED_TO_REGISTRAR','ACTIONED'];
      const tlds = ['.ng','.com.ng','.org.ng','.gov.ng','.edu.ng','.net.ng'];
      const domains = ['fakebank','securelogin','ng-verify','paymentng','updateaccount','myprofile-ng','nigeriaforms','quickloan-ng','verifycard','loginportal','officialforms','taxrefund-ng','govportal-ng','bankverify','id-update'];
      const reporters = [
        {name:'Emeka Okafor',    email:'emeka.o@gmail.com'},
        {name:'Chisom Adaeze',   email:'c.adaeze@yahoo.com'},
        {name:'Tunde Fashola',   email:'tfashola@corp.ng'},
        {name:'Ngozi Iweala',    email:'n.iweala@ngo.org'},
        {name:'Babatunde Raji',  email:'braji@tech.ng'},
        {name:'Aisha Buhari',    email:'aisha.b@edu.ng'},
        {name:'Chukwuemeka S',   email:'c.sani@law.ng'},
        {name:'Funmilayo Okon',  email:'fokon@bank.ng'},
      ];
      const descs = [
        'Website impersonates First Bank login page, collecting user credentials via a fake form. Users are redirected after submitting.',
        'Domain distributes ransomware disguised as tax documents. PDF downloads trigger executable payloads upon opening.',
        'Observed botnet C2 activity, coordinating DDoS attacks against e-commerce targets. High traffic volume noted.',
        'DNS poisoning detected — users querying legitimate domains are being silently redirected to this fraudulent IP.',
        'Bulk unsolicited promotional emails sent from this domain with embedded phishing links targeting banking customers.',
        'Clone of EFCC official portal designed to harvest login credentials from government employees.',
        'Malicious JavaScript injected, silently mining cryptocurrency on visitor machines without consent.',
        'Spoofed INEC voting portal collecting personal data ahead of the election cycle.',
      ];
      const registrars = ['Web4Africa','Qservers','Whogohost','DomainKing','WhoIsNg', null, null, null];

      return Array.from({length: 32}, (_, i) => {
        const ri = i % reporters.length;
        const cat = categories[i % categories.length];
        const d = domains[i % domains.length];
        const tld = tlds[i % tlds.length];
        const day = String(Math.max(1, 28 - i)).padStart(2,'0');
        const month = i < 15 ? '05' : '04';
        const reg = registrars[i % registrars.length];

        return {
          id: i + 1,
          ticket: `NiRA-ABUSE-2025${month}${day}-${String(i+1).padStart(4,'0')}`,
          domain: d,
          tld: tld,
          url: `https://${d}${tld}/login`,
          category: cat,
          reporter: reporters[ri].name,
          reporterEmail: reporters[ri].email,
          status: statuses[i % statuses.length],
          date: `${day} ${month === '05' ? 'May' : 'Apr'} 2025`,
          firstSeen: `${String(Math.max(1, parseInt(day)-3)).padStart(2,'0')} ${month === '05' ? 'May' : 'Apr'} 2025`,
          files: Array.from({length: (i % 3) + 1}, (_, j) => `file_${j+1}`),
          fileDetails: [
            { name: `screenshot_${i+1}.png`, type: 'PNG', size: `${(Math.random()*2+0.5).toFixed(1)} MB` },
            ...(i % 2 === 0 ? [{ name: `evidence_${i+1}.pdf`, type: 'PDF', size: `${(Math.random()*1.5+0.3).toFixed(1)} MB` }] : []),
          ],
          description: descs[i % descs.length],
          registrar: reg,
          registrarDate: reg ? `${day} ${month === '05' ? 'May' : 'Apr'} 2025` : null,
          timeline: [
            { event: 'Report submitted', time: `${day} ${month === '05' ? 'May' : 'Apr'} 2025, 09:14`, color: '#179e4f' },
            ...(statuses[i % statuses.length] !== 'OPEN' ? [{ event: 'Assigned to investigator', time: `${day} ${month === '05' ? 'May' : 'Apr'} 2025, 11:02`, color: '#3b82f6' }] : []),
            ...(statuses[i % statuses.length] === 'ACTIONED' || statuses[i % statuses.length] === 'CLOSED' ? [{ event: 'Action taken — domain suspended', time: `${day} ${month === '05' ? 'May' : 'Apr'} 2025, 15:30`, color: '#ef4444' }] : []),
          ],
        };
      });
    },

    updateStats() {
      this.stats[0].value = this.reports.length;
      this.stats[1].value = this.reports.filter(r => r.status === 'OPEN').length;
      this.stats[2].value = this.reports.filter(r => r.status === 'ASSIGNED_TO_REGISTRAR').length;
      this.stats[3].value = this.reports.filter(r => r.status === 'ACTIONED').length;

      const cats = ['Phishing','Malware','Botnets','Pharming','Spam'];
      cats.forEach((c, i) => {
        if (this.categoryBreakdown[i]) {
          this.categoryBreakdown[i].count = this.reports.filter(r => r.category === c).length;
        }
      });
      this.maxCategory = Math.max(...this.categoryBreakdown.map(c => c.count), 1);
    },

    get filteredReports() {
      let r = this.reports;
      if (this.searchQuery) {
        const q = this.searchQuery.toLowerCase();
        r = r.filter(x => x.domain.toLowerCase().includes(q) || x.ticket.toLowerCase().includes(q) || x.reporter.toLowerCase().includes(q) || x.category.toLowerCase().includes(q));
      }
      if (this.tableStatusFilter !== 'all') r = r.filter(x => x.status === this.tableStatusFilter);
      if (this.activeFilter) r = r.filter(x => x.status === this.activeFilter || x.category === this.activeFilter);
      return r;
    },

    get paginatedReports() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredReports.slice(start, start + this.pageSize);
    },

    get totalPages() {
      return Math.max(1, Math.ceil(this.filteredReports.length / this.pageSize));
    },

    selectReport(r) { this.selectedReport = r; },

    filterStatus(key) {
      this.activeFilter = key;
      this.tableStatusFilter = key;
      this.currentPage = 1;
    },

    filterCategory(key) {
      this.activeFilter = key;
      this.tableStatusFilter = 'all';
      this.currentPage = 1;
    },

    clearFilter() {
      this.activeFilter = '';
      this.tableStatusFilter = 'all';
    },

    updateStatus(newStatus) {
      if (this.selectedReport) {
        const idx = this.reports.findIndex(r => r.id === this.selectedReport.id);
        if (idx > -1) {
          this.reports[idx].status = newStatus;
          this.selectedReport = { ...this.reports[idx] };
        }
        this.updateStats();
      }
    },

    statusClass(s) {
      return { OPEN:'badge-open', ASSIGNED_TO_REGISTRAR:'badge-review', ACTIONED:'badge-actioned', CLOSED:'badge-closed' }[s] || 'badge-closed';
    },
    statusLabel(s) {
      return { OPEN:'Open', ASSIGNED_TO_REGISTRAR:'Assigned', ACTIONED:'Actioned', CLOSED:'Closed' }[s] || s;
    },
    catClass(c) {
      return { Phishing:'cat-phishing', Malware:'cat-malware', Botnets:'cat-botnet', Pharming:'cat-pharming', Spam:'cat-spam' }[c] || 'cat-other';
    },
  }
}
</script>
  <?= $this->endSection() ?>