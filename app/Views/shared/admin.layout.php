<?php
$image = session()->get('admin_image');
$name  = session()->get('admin_name') ?? 'Admin';
$email = session()->get('admin_email') ?? '';
 $uri = service('uri')->getSegment(1); 
function active($path, $uri) {
    return $path === $uri ? 'active' : '';
}

$reportModel = new \App\Models\AbuseReportModel();

// Get all status counts in one query
$statusCounts = $reportModel
    ->select('status, COUNT(*) as total')
    ->groupBy('status')
    ->findAll();

// Convert to easy array
$counts = [
    'pending' => 0,
    'under_review' => 0,
    'resolved' => 0,
    'rejected' => 0,
];

foreach ($statusCounts as $row) {
    $counts[$row['status']] = (int)$row['total'];
}
?>

<!DOCTYPE html>
<html lang="en" x-data="dashboard()" x-init="init()">
<head>
<meta charset="UTF-8"/>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>NiRA — Abuse Reports Dashboard</title>
<link rel="icon" href="/logo.png" type="image/png" />
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
  position:fixed;top:0;right:0;bottom:0;width:820px;z-index:50;
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
<aside x-data="appData()" class="w-64 flex-shrink-0 flex flex-col h-screen overflow-hidden" style="background:#179e4f">
  <!-- Logo -->
  <div class="px-5 py-6 border-b" style="border-color:rgba(255,255,255,0.2)">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center flex-shrink-0" style="box-shadow:0 2px 12px rgba(0,0,0,0.12)">
        <!-- NiRA "N" monogram -->
          <img class="w-6 h-6" src="/logo.png"/>
      </div>
      <div>
        <p class="font-display font-900 text-white leading-tight">NiRA .ng</p>
        <p class="text-[14px] leading-tight" style="color:rgba(255,255,255,0.65)">.NG DNS Abuse Admin</p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
      <p class="text-[9px] font-display font-700 uppercase tracking-widest px-3 mb-2" style="color:rgba(255,255,255,0.5)">Main</p>

    <a href="/dashboard" class="sidebar-item  <?= active('dashboard', $uri) ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Overview
    </a>

    <a href="/reports" class="sidebar-item mb-3 <?= active('reports', $uri) ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      All Reports
     </a>

    <p  class="text-[9px]  font-display font-700 uppercase tracking-widest py-2 mt-3 mb-2" style="color:rgba(255,255,255,0.5)">Status</p>

        <a href="/reports?status=pending" class="sidebar-item cursor-pointer flex items-center gap-2">

            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#f59e0b"></span>

            <span>Open</span>

            <?php if ($counts['pending'] > 0): ?>
                <span class="ml-auto text-[10px] font-display font-700 px-1.5 py-0.5 rounded-full"
                      style="background:#e4f5ec;color:#0d6b35">
                    <?= $counts['pending'] ?>
                </span>
            <?php endif; ?>

        </a>

        <a href="/reports?status=under_review" class="sidebar-item cursor-pointer flex items-center gap-2">

            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#3b82f6"></span>

            <span>In Review</span>

            <?php if ($counts['under_review'] > 0): ?>
                <span class="ml-auto text-[10px] font-display font-700 px-1.5 py-0.5 rounded-full"
                      style="background:#e4f5ec;color:#0d6b35">
                    <?= $counts['under_review'] ?>
                </span>
            <?php endif; ?>

        </a>

        <a href="/reports?status=resolved" class="sidebar-item cursor-pointer flex items-center gap-2">

            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#10b981"></span>

            <span>Actioned</span>

            <?php if ($counts['resolved'] > 0): ?>
                <span class="ml-auto text-[10px] font-display font-700 px-1.5 py-0.5 rounded-full"
                      style="background:#e4f5ec;color:#0d6b35">
                    <?= $counts['resolved'] ?>
                </span>
            <?php endif; ?>

        </a>

        <a href="/reports?status=rejected" class="sidebar-item cursor-pointer flex items-center gap-2">

            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#6b7280"></span>

            <span>Rejected</span>

            <?php if ($counts['rejected'] > 0): ?>
                <span class="ml-auto text-[10px] font-display font-700 px-1.5 py-0.5 rounded-full"
                      style="background:#e4f5ec;color:#0d6b35">
                    <?= $counts['rejected'] ?>
                </span>
            <?php endif; ?>

        </a>
    
   

    <p class="text-[9px] font-display font-700 uppercase tracking-widest px-3 mt-4 mb-2" style="color:#9dbda8">Admin</p>
    <a href="/admin/users" class="sidebar-item  <?= active('admin', $uri) ?>">
      <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Users
    </a>
    <a href="/admin/logout" class="sidebar-item">
   <svg class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
    Logout
</a>
  </nav>

  <!-- User card -->
  <div class="px-3 py-4">
    <div class="flex items-center gap-3 px-2 py-2 rounded-xl cursor-pointer transition-all bg-slate-50">

      <!-- Avatar -->
      <div class="w-8 h-8 rounded-full overflow-hidden bg-nira-green flex items-center justify-center flex-shrink-0">

        <?php if (!empty($image)): ?>
          <img src="<?= esc($image) ?>" class="w-full h-full object-cover" />
        <?php else: ?>
          <span class="text-white font-bold text-sm">
            <?= strtoupper(substr($name, 0, 1)) ?>
          </span>
        <?php endif; ?>

      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <p class="text-gray-800 text-xs font-display font-semibold truncate">
          <?= esc($name) ?>
        </p>
        <p class="text-[10px] truncate" style="color:#5a7a65">
          <?= esc($email) ?>
        </p>
      </div>

    </div>
  </div>
</aside>

<!-- ════════ MAIN CONTENT ════════ -->
<div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

  <!-- Top bar -->
  <header class="bg-white border-b border-slate-200 flex items-center gap-4 px-6 py-3.5 flex-shrink-0">
    <!-- Title -->
    <div class="flex-1 min-w-0">
      <h1 class="font-display font-800 text-gray-900 text-lg leading-tight">Abuse Management</h1>
      <p class="text-xs text-gray-400">Manage and control reports</p>
    </div>

    

    <!-- Export -->
    <!-- <button class="hidden md:flex items-center gap-2 text-sm font-display font-700 px-4 py-2 rounded-xl border border-slate-200 text-gray-600 hover:bg-slate-50 transition-all">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export
    </button> -->

  </header>

  <?= $this->renderSection('content') ?>
  
</div>
<!-- end main -->
<script>
function appData() {
  return {
    reports: [],

    statusNav: [
      {
        key: 'open',
        label: 'Open',
        color: '#f59e0b'
      },
      {
        key: 'in_review',
        label: 'In Review',
        color: '#3b82f6'
      },
      {
        key: 'actioned',
        label: 'Actioned',
        color: '#10b981'
      },
      {
        key: 'closed',
        label: 'Closed',
        color: '#6b7280'
      }
    ],

    filterStatus(status) {
      console.log(status)
    }
  }
}
</script>

</body>
</html>
