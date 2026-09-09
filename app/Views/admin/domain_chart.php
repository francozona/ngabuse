<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Most Reported Domains</h1>
            <p class="text-sm text-gray-500 mt-1">
                Total reports: <span class="font-semibold text-gray-700"><?= esc($total) ?></span>
                <?php if ($from && $to): ?>
                    <span class="text-gray-400">· <?= esc(date('M d, Y', strtotime($from))) ?> – <?= esc(date('M d, Y', strtotime($to))) ?></span>
                <?php else: ?>
                    <span class="text-gray-400">· All time</span>
                <?php endif; ?>
                <span class="text-gray-400">· Top <?= esc($limit) ?></span>
            </p>
        </div>

        <!-- Range + limit filter -->
        <form method="get" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="<?= esc($from ? date('Y-m-d', strtotime($from)) : '') ?>"
                   class="text-sm rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <span class="text-gray-400 text-sm">to</span>
            <input type="date" name="to" value="<?= esc($to ? date('Y-m-d', strtotime($to)) : '') ?>"
                   class="text-sm rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select name="limit"
                    class="text-sm rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <?php foreach ([10, 15, 25, 50] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>>Top <?= $opt ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                    class="text-sm font-medium bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Apply
            </button>
        </form>
    </div>

    <!-- Quick presets -->
    <div class="mb-6 flex flex-wrap gap-2">
        <?php
        $presets = ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', 'all' => 'All time'];
        $currentRange = service('request')->getGet('range') ?: (!$from ? 'all' : '');
        ?>
        <?php foreach ($presets as $val => $label): ?>
            <a href="?range=<?= $val ?>&limit=<?= $limit ?>"
               class="text-xs font-medium px-3 py-1.5 rounded-full border transition
                      <?= $currentRange === $val
                            ? 'bg-green-600 text-white border-indigo-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' ?>">
                <?= esc($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <?php if (empty($labels)): ?>
            <div class="flex items-center justify-center h-64 text-gray-400 text-sm">
                No domain data available for this range.
            </div>
        <?php else: ?>
            <div class="relative w-full" style="height: <?= max(320, count($labels) * 32) ?>px">
                <canvas id="domainChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($labels)): ?>
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Domain</th>
                    <th class="px-4 py-3 text-right">Reports</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($labels as $i => $label): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-400"><?= $i + 1 ?></td>
                        <td class="px-4 py-2.5 font-medium text-gray-700"><?= esc($label) ?></td>
                        <td class="px-4 py-2.5 text-right font-semibold text-gray-800"><?= esc($values[$i]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;

    const ctx = document.getElementById('domainChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Reports',
                    data: values,
                    backgroundColor: 'rgba(126, 244, 63, 0.7)',
                    borderColor: 'rgb(63, 244, 81)',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.formattedValue} report(s)`
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>