<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">

    <!-- ─── STAT CARDS ─── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <template x-for="(s, i) in stats" :key="i">
        <div class="stat-card fade-in" :class="s.color" :style="`animation-delay:${i*0.06}s`">
          <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" :class="s.iconBg">
              <span x-html="s.icon" class="text-lg leading-none"></span>
            </div>
            <span class="text-xs font-display font-700 flex items-center gap-1" :class="s.trend > 0 ? 'text-nira-green' : 'text-red-500'">
              <span x-text="s.trend > 0 ? '↑' : '↓'"></span>
              <span x-text="Math.abs(s.trend) + '%'"></span>
            </span>
          </div>
          <p class="font-display font-800 text-3xl text-gray-900 leading-none mb-1" x-text="s.value"></p>
          <p class="text-xs text-gray-500 font-display font-600" x-text="s.label"></p>
          <!-- Mini spark -->
          <div class="flex items-end gap-0.5 mt-3 h-6">
            <template x-for="(v, j) in s.spark" :key="j">
              <div class="flex-1 rounded-sm opacity-60 transition-all" :style="`height:${v}%;background:#179e4f`"></div>
            </template>
          </div>
        </div>
      </template>
    </div>

    <!-- ─── CHART + CATEGORY BREAKDOWN ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
      <!-- Weekly chart -->
      <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 shadow-card fade-in" style="animation-delay:.15s">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h3 class="font-display font-700 text-gray-900 text-sm">Submissions Over Time</h3>
            <p class="text-xs text-gray-400 mt-0.5">Last 8 weeks</p>
          </div>
          <div class="flex gap-2">
            <button class="text-[11px] font-display font-700 px-3 py-1 rounded-full bg-nira-light text-nira-dark">Weekly</button>
            <button class="text-[11px] font-display font-700 px-3 py-1 rounded-full text-gray-400 hover:bg-slate-50">Monthly</button>
          </div>
        </div>
        <div class="flex items-end gap-3 h-32 px-2">
          <template x-for="(w, i) in weeklyData" :key="i">
            <div class="flex-1 flex flex-col items-center gap-1.5">
              <span class="text-[10px] text-gray-400 font-display font-600" x-text="w.count"></span>
              <div class="w-full chart-bar" :style="`height:${(w.count/maxWeekly*100)}%`"></div>
              <span class="text-[10px] text-gray-400 font-display" x-text="w.label"></span>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ─── REPORTS TABLE ─── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-card fade-in overflow-hidden" style="animation-delay:.25s">
      <!-- Table header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <h3 class="font-display font-700 text-gray-900 text-sm">Abuse Reports</h3>
          <span class="text-[11px] font-display font-700 px-2.5 py-0.5 rounded-full bg-nira-light text-nira-dark" x-text="filteredReports.length + ' records'"></span>
        </div>
        <div class="flex items-center gap-2">
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
            <button @click="tableStatusFilter='UNDER_REVIEW'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='UNDER_REVIEW' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              In Review
            </button>
            <button @click="tableStatusFilter='ACTIONED'"
              class="text-[11px] font-display font-700 px-3 py-1.5 rounded-lg transition-all"
              :class="tableStatusFilter==='ACTIONED' ? 'bg-white text-nira-green shadow-sm' : 'text-gray-400 hover:text-gray-600'">
              Actioned
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
                <input type="checkbox" class="rounded">
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
                  <input type="checkbox" class="rounded">
                </td>
                <td class="px-3 py-3.5">
                  <span class="font-display font-700 text-xs text-nira-dark" x-text="r.ticket"></span>
                </td>
                <td class="px-3 py-3.5">
                  <div>
                    <p class="text-sm font-display font-700 text-gray-800" x-text="r.domain + r.tld"></p>
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
  <?= $this->endSection() ?>