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
              
            </span>
          </div>
          <p class="font-display font-800 text-3xl text-gray-900 leading-none mb-1" x-text="s.value"></p>
          <p class="text-xs text-gray-500 font-display font-600" x-text="s.label"></p>
           
        </div>
      </template>
    </div>

    <!-- ─── CHART + CATEGORY BREAKDOWN ─── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
       <!-- PIE -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-card">
  <h3 class="font-display font-700 text-gray-900 text-sm mb-1">Category Breakdown</h3>
  <div class="legend-row" id="pie-legend"></div>
  <div style="position:relative;height:260px;">
    <canvas id="pieChart"></canvas>
  </div>
</div>

<!-- BAR -->
<div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-card">
  <h3 class="font-display font-700 text-gray-900 text-sm mb-1">Reports by Status</h3>
  <div style="position:relative;height:240px;">
    <canvas id="barChart"></canvas>
  </div>
</div>

<!-- LINE -->
<div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 shadow-card">
  <h3 class="font-display font-700 text-gray-900 text-sm mb-1">Submissions Over Time</h3>
  <div style="position:relative;height:240px;">
    <canvas id="lineChart"></canvas>
  </div>
</div>
    </div>

    
  </div> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
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
                                    'under_review' => 'UNDER_REVIEW',
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
            'timeline'      => array_filter([
                                    ['event' => 'Report submitted',   'time' => date('d M Y, H:i', strtotime($r['created_at'])),  'color' => '#16a34a'],
                                    $r['status'] !== 'pending' ? ['event' => 'Assigned to investigator', 'time' => date('d M Y, H:i', strtotime($r['updated_at'])), 'color' => '#2563eb'] : null,
                                    in_array($r['status'], ['resolved','rejected']) ? ['event' => 'Action taken — domain suspended', 'time' => date('d M Y, H:i', strtotime($r['updated_at'])), 'color' => '#dc2626'] : null,
                               ]),
        ];
    }, $reports ?? [])) ?>,

    // ── stats from PHP ─────────────────────────────────────────
    stats: [
      { label:'Total Reports',  value: <?= $stats['total'] ?>,    trend:14, color:'green', iconBg:'bg-nira-light', icon:'📋', spark:[40,55,45,70,60,80,65,90] },
      { label:'Open Cases',     value: <?= $stats['open'] ?>,     trend:-3, color:'amber', iconBg:'bg-amber-50',  icon:'🔓', spark:[50,60,40,55,70,45,65,50] },
      { label:'In Review',      value: <?= $stats['in_review'] ?>,trend: 8, color:'blue',  iconBg:'bg-blue-50',   icon:'🔍', spark:[30,45,60,40,70,55,65,75] },
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
  this.updateStats();
  this.$nextTick(() => {
    this.initCharts();
  });
},

initCharts() {
  const NIR  = '#1f771f';
  const NIR2 = '#2ea02e';
  const NIR3 = '#4ec94e';
  const NIR4 = '#7fda7f';
  const NIR5 = '#afeaaf';

  // ── PIE — category breakdown ──────────────────────────────
  const catLabels = this.categoryBreakdown.map(c => c.name);
  const catCounts = this.categoryBreakdown.map(c => c.count);
  const pieColors = [NIR, NIR2, NIR3, NIR4, NIR5];

  new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
      labels: catLabels,
      datasets: [{
        data: catCounts,
        backgroundColor: pieColors,
        borderColor: '#fff',
        borderWidth: 2,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.parsed} reports`
          }
        }
      }
    }
  });

  // ── BAR — reports by status ───────────────────────────────
  new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: ['Open', 'Under Review', 'Actioned', 'Closed'],
      datasets: [{
        label: 'Reports',
        data: [
          this.stats[1].value,  // Open
          this.stats[2].value,  // In Review
          this.stats[3].value,  // Actioned
          this.reports.filter(r => r.status === 'CLOSED').length,
        ],
        backgroundColor: [NIR, NIR2, NIR3, NIR4],
        borderColor:     ['#155a15', NIR, NIR2, NIR3],
        borderWidth: 1,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 12 }, color: '#888' },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,.06)' },
          ticks: { stepSize: 2, font: { size: 11 }, color: '#888' }
        }
      }
    }
  });

  // ── LINE — weekly submissions ─────────────────────────────
  const weekLabels = this.weeklyData.map(w => w.label);
  const weekCounts = this.weeklyData.map(w => w.count);

  new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
      labels: weekLabels,
      datasets: [{
        label: 'Submissions',
        data: weekCounts,
        borderColor: NIR,
        backgroundColor: 'rgba(31,119,31,.10)',
        pointBackgroundColor: NIR,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        borderWidth: 2.5,
        fill: true,
        tension: 0.38,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 12 }, color: '#888', autoSkip: false }
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,.06)' },
          ticks: { stepSize: 3, font: { size: 11 }, color: '#888' }
        }
      }
    }
  });
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
  <?= $this->endSection() ?>