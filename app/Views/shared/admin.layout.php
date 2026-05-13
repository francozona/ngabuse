<!DOCTYPE html>
<html lang="en" x-data="dashboard()" x-init="init()">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>NiRA — Abuse Reports Dashboard</title>
<link rel="icon" href="logo.png" type="image/png" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        nira: { green:'#179e4f', dark:'#0d6b35', mid:'#12833f', light:'#e8f7ee', xlight:'#f3fbf6', muted:'#a8d5bc' },
        sidebar: { bg:'#ffffff', border:'#e8f0eb', hover:'#f0faf4', active:'#e4f5ec', text:'#5a7a65', heading:'#9dbda8' }
      },
      fontFamily: { display:['Syne','sans-serif'], body:['DM Sans','sans-serif'] },
      boxShadow: {
        'card': '0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.05)',
        'card-hover': '0 4px 24px rgba(0,0,0,0.1)',
        'green': '0 8px 32px rgba(23,158,79,0.25)',
      }
    }
  }
}
</script>
<style>
[x-cloak]{display:none!important}
*{font-family:'DM Sans',sans-serif}
.font-display{font-family:'Syne',sans-serif}

/* Scrollbar */
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:#d1fae5;border-radius:99px}

/* Sidebar */
.sidebar-item{
  display:flex;align-items:center;gap:12px;padding:10px 14px;
  border-radius:10px;cursor:pointer;transition:all .18s ease;
  font-size:0.875rem;font-weight:500;color:rgba(255,255,255,0.7);
  position:relative;
}
.sidebar-item:hover{background:rgba(255,255,255,0.15);color:#fff}
.sidebar-item.active{background:rgba(255,255,255,0.2);color:#fff}
.sidebar-item.active::before{
  content:'';position:absolute;left:0;top:20%;bottom:20%;
  width:3px;background:#fff;border-radius:0 3px 3px 0;
}
.sidebar-item .icon{width:18px;height:18px;flex-shrink:0;opacity:.65}
.sidebar-item.active .icon,.sidebar-item:hover .icon{opacity:1}

/* Status badges */
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;font-family:'Syne',sans-serif;letter-spacing:.02em}
.badge-open{background:#fef3c7;color:#92400e}
.badge-review{background:#dbeafe;color:#1e40af}
.badge-actioned{background:#d1fae5;color:#065f46}
.badge-closed{background:#f3f4f6;color:#6b7280}

/* Category badge */
.cat-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600}
.cat-phishing{background:#fef9c3;color:#854d0e}
.cat-malware{background:#fee2e2;color:#991b1b}
.cat-botnet{background:#ffedd5;color:#9a3412}
.cat-pharming{background:#ede9fe;color:#5b21b6}
.cat-spam{background:#dbeafe;color:#1e3a8a}
.cat-other{background:#f3f4f6;color:#374151}

/* Table row hover */
.report-row{cursor:pointer;transition:background .12s}
.report-row:hover{background:#f0fdf4}
.report-row.selected{background:#e8f7ee}

/* Stats card */
.stat-card{
  background:#fff;border-radius:16px;padding:20px 22px;
  box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
  border:1px solid rgba(0,0,0,.045);
  position:relative;overflow:hidden;
}
.stat-card::after{
  content:'';position:absolute;top:-20px;right:-20px;
  width:80px;height:80px;border-radius:50%;
  opacity:.07;
}
.stat-card.green::after{background:#179e4f}
.stat-card.amber::after{background:#f59e0b}
.stat-card.blue::after{background:#3b82f6}
.stat-card.red::after{background:#ef4444}

/* Detail panel */
.detail-panel{
  position:fixed;top:0;right:0;bottom:0;width:420px;z-index:50;
  background:#fff;box-shadow:-8px 0 40px rgba(0,0,0,.12);
  display:flex;flex-direction:column;
  transition:transform .3s cubic-bezier(.4,0,.2,1);
}
.detail-panel.closed{transform:translateX(100%)}

/* Chart bar */
.chart-bar{
  background:#179e4f;border-radius:4px 4px 0 0;
  transition:height .6s cubic-bezier(.4,0,.2,1);
  min-height:4px;
}

/* Animations */
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
.fade-in{animation:fadeIn .3s ease both}

@keyframes slideIn{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
.slide-in{animation:slideIn .25s ease both}

/* Mini sparkline */
.spark-dot{width:6px;height:6px;border-radius:50%;background:#179e4f;flex-shrink:0}

/* Toggle switch */
.toggle-track{width:36px;height:20px;border-radius:10px;background:#e5e7eb;cursor:pointer;position:relative;transition:background .2s}
.toggle-track.on{background:#179e4f}
.toggle-thumb{width:16px;height:16px;border-radius:50%;background:#fff;position:absolute;top:2px;left:2px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-track.on .toggle-thumb{left:18px}

/* Sidebar logo glow */
.logo-glow{box-shadow:0 0 16px rgba(23,158,79,.2)}
</style>
</head>
<body class="bg-slate-100 h-screen overflow-hidden flex">

<!-- ════════ SIDEBAR ════════ -->
<aside class="w-64 flex-shrink-0 flex flex-col h-screen overflow-hidden" style="background:#179e4f">
  <!-- Logo -->
  <div class="px-5 py-6 border-b" style="border-color:rgba(255,255,255,0.2)">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center flex-shrink-0" style="box-shadow:0 2px 12px rgba(0,0,0,0.12)">
        <!-- NiRA "N" monogram -->
          <img class="w-6 h-6" src="logo.png"/>
      </div>
      <div>
        <p class="font-display font-900 text-white leading-tight">NiRA .ng</p>
        <p class="text-[14px] leading-tight" style="color:rgba(255,255,255,0.65)">Abuse Portal Admin</p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
    <p class="text-[9px] font-display font-700 uppercase tracking-widest px-3 mb-2" style="color:rgba(255,255,255,0.5)">Main</p>

    <div class="sidebar-item active" @click="activePage='dashboard'">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Overview
    </div>

    <div class="sidebar-item" @click="activePage='reports'; activeSubmenu='all'">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      All Reports
      <span class="ml-auto text-[10px] font-display font-700 px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,0.2);color:#fff" x-text="reports.length"></span>
    </div>

    <p class="text-[9px] font-display font-700 uppercase tracking-widest px-3 mt-4 mb-2" style="color:rgba(255,255,255,0.5)">Status</p>

    <template x-for="s in statusNav" :key="s.key">
      <div class="sidebar-item" @click="filterStatus(s.key)">
        <span class="w-2 h-2 rounded-full flex-shrink-0" :style="`background:${s.color}`"></span>
        <span x-text="s.label"></span>
        <span class="ml-auto text-[10px] font-display font-700 px-1.5 py-0.5 rounded-full" style="background:#e4f5ec;color:#0d6b35" x-text="reports.filter(r=>r.status===s.key).length"></span>
      </div>
    </template>

    <p class="text-[9px] font-display font-700 uppercase tracking-widest px-3 mt-4 mb-2" style="color:#9dbda8">Admin</p>
    <div class="sidebar-item">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Users
    </div>
    <div class="sidebar-item">
   <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
    Logout
    </div>
  </nav>

  <!-- User card -->
  <div class="px-3 py-4" style="border-color:#e8f0eb">
    <div class="flex items-center gap-3 px-2 py-2 rounded-xl cursor-pointer transition-all bg-slate-50">
      <div class="w-8 h-8 rounded-full bg-nira-green flex items-center justify-center text-white font-display font-700 text-sm flex-shrink-0">A</div>
      <div class="flex-1 min-w-0">
        <p class="text-gray-800 text-xs font-display font-600 truncate">Admin User</p>
        <p class="text-[10px] truncate" style="color:#5a7a65">admin@nira.org.ng</p>
      </div>
      <svg class="w-4 h-4 flex-shrink-0" style="color:#9dbda8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
    </div>
  </div>
</aside>

<!-- ════════ MAIN CONTENT ════════ -->
<div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

  <!-- Top bar -->
  <header class="bg-white border-b border-slate-200 flex items-center gap-4 px-6 py-3.5 flex-shrink-0">
    <!-- Title -->
    <div class="flex-1 min-w-0">
      <h1 class="font-display font-800 text-gray-900 text-lg leading-tight" x-text="pageTitle"></h1>
      <p class="text-xs text-gray-400" x-text="pageSubtitle"></p>
    </div>

    

    <!-- Export -->
    <button class="hidden md:flex items-center gap-2 text-sm font-display font-700 px-4 py-2 rounded-xl border border-slate-200 text-gray-600 hover:bg-slate-50 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export
    </button>

  </header>

  <?= $this->renderSection('content') ?>
  
</div>
<!-- end main -->

<!-- ════════ DETAIL PANEL ════════ -->
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
              <svg class="w-5 h-5 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3"/></svg>
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
              <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-nira-green/25 transition-all cursor-pointer group">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="f.type === 'PDF' ? 'bg-red-50' : 'bg-blue-50'">
                  <svg x-show="f.type === 'PDF'" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  <svg x-show="f.type !== 'PDF'" class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-display font-700 text-gray-700 truncate" x-text="f.name"></p>
                  <p class="text-[10px] text-gray-400" x-text="f.size"></p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-nira-green transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
              </div>
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
          <button @click="updateStatus('UNDER_REVIEW')"
            class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all border border-blue-200/50">
            Mark In Review
          </button>
          <button @click="updateStatus('ACTIONED')"
            class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-nira-light text-nira-dark hover:bg-green-100 transition-all border border-nira-green/20">
            Mark Actioned
          </button>
          <button @click="updateStatus('CLOSED')"
            class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-slate-100 text-gray-600 hover:bg-slate-200 transition-all">
            Close Report
          </button>
          <button class="text-xs font-display font-700 px-3 py-2.5 rounded-xl bg-red-50 text-red-700 hover:bg-red-100 transition-all border border-red-200/50">
            Escalate
          </button>
        </div>
        <!-- Investigator assign -->
        <div class="flex items-center gap-2 p-3 rounded-xl bg-white border border-slate-200">
          <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <select class="flex-1 text-xs font-display bg-transparent text-gray-700 focus:outline-none">
            <option>Assign investigator…</option>
            <option>Chidi Okonkwo</option>
            <option>Amaka Eze</option>
            <option>Tunde Adeyemi</option>
          </select>
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
    activePage: 'dashboard',
    searchQuery: '',
    activeFilter: '',
    tableStatusFilter: 'all',
    currentPage: 1,
    pageSize: 8,
    selectedReport: null,

    pageTitle: 'Overview',
    pageSubtitle: 'NiRA Abuse Portal — Admin Dashboard',

    statusNav: [
      { key:'OPEN',         label:'Open',       color:'#f59e0b' },
      { key:'UNDER_REVIEW', label:'In Review',  color:'#3b82f6' },
      { key:'ACTIONED',     label:'Actioned',   color:'#179e4f' },
      { key:'CLOSED',       label:'Closed',     color:'#9ca3af' },
    ],
    categoryNav: [
      { key:'Phishing',  label:'Phishing',  icon:'🎣' },
      { key:'Malware',   label:'Malware',   icon:'🦠' },
      { key:'Botnets',   label:'Botnets',   icon:'🤖' },
      { key:'Pharming',  label:'Pharming',  icon:'🔀' },
      { key:'Spam',      label:'Spam',      icon:'📨' },
    ],

    stats: [
      { label:'Total Reports',  value:0, trend:14, color:'green', iconBg:'bg-nira-light',    icon:'📋', spark:[40,55,45,70,60,80,65,90] },
      { label:'Open Cases',     value:0, trend:-3, color:'amber', iconBg:'bg-amber-50',     icon:'🔓', spark:[50,60,40,55,70,45,65,50] },
      { label:'In Review',      value:0, trend:8,  color:'blue',  iconBg:'bg-blue-50',      icon:'🔍', spark:[30,45,60,40,70,55,65,75] },
      { label:'Actioned Today', value:0, trend:22, color:'red',   iconBg:'bg-green-50',     icon:'✅', spark:[20,40,35,55,45,70,60,80] },
    ],

    weeklyData: [
      { label:'W1', count:8  }, { label:'W2', count:14 }, { label:'W3', count:11 },
      { label:'W4', count:19 }, { label:'W5', count:16 }, { label:'W6', count:23 },
      { label:'W7', count:18 }, { label:'W8', count:27 },
    ],
    maxWeekly: 27,

    categoryBreakdown: [
      { name:'Phishing', icon:'🎣', count:0, color:'#f59e0b' },
      { name:'Malware',  icon:'🦠', count:0, color:'#ef4444' },
      { name:'Botnets',  icon:'🤖', count:0, color:'#f97316' },
      { name:'Pharming', icon:'🔀', count:0, color:'#8b5cf6' },
      { name:'Spam',     icon:'📨', count:0, color:'#3b82f6' },
    ],
    maxCategory: 1,

    tldDist: [
      { tld:'.ng', count:18 }, { tld:'.com.ng', count:12 }, { tld:'.org.ng', count:7 },
      { tld:'.gov.ng', count:4 }, { tld:'.edu.ng', count:3 },
    ],

    reports: [],

    init() {
      this.reports = this.generateReports();
      this.updateStats();
    },

    generateReports() {
      const categories = ['Phishing','Malware','Botnets','Pharming','Spam','Phishing','Malware','Phishing'];
      const statuses = ['OPEN','OPEN','UNDER_REVIEW','ACTIONED','CLOSED','OPEN','UNDER_REVIEW','ACTIONED'];
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
      this.stats[2].value = this.reports.filter(r => r.status === 'UNDER_REVIEW').length;
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
      return { OPEN:'badge-open', UNDER_REVIEW:'badge-review', ACTIONED:'badge-actioned', CLOSED:'badge-closed' }[s] || 'badge-closed';
    },
    statusLabel(s) {
      return { OPEN:'Open', UNDER_REVIEW:'In Review', ACTIONED:'Actioned', CLOSED:'Closed' }[s] || s;
    },
    catClass(c) {
      return { Phishing:'cat-phishing', Malware:'cat-malware', Botnets:'cat-botnet', Pharming:'cat-pharming', Spam:'cat-spam' }[c] || 'cat-other';
    },
  }
}
</script>
</body>
</html>
