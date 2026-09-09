<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Abuse Reports by Attack Category</h1>
            <p class="text-sm text-gray-500 mt-1">
                Total reports: <span class="font-semibold text-gray-700"><?= esc($total) ?></span>
                <?php if ($from && $to): ?>
                    <span class="text-gray-400">· <?= esc(date('M d, Y', strtotime($from))) ?> – <?= esc(date('M d, Y', strtotime($to))) ?></span>
                <?php else: ?>
                    <span class="text-gray-400">· All time</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Range filter -->
        <form method="get" class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="<?= esc($from ? date('Y-m-d', strtotime($from)) : '') ?>"
                   class="text-sm rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <span class="text-gray-400 text-sm">to</span>
            <input type="date" name="to" value="<?= esc($to ? date('Y-m-d', strtotime($to)) : '') ?>"
                   class="text-sm rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
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
            <a href="?range=<?= $val ?>"
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
                No abuse category data available for this range.
            </div>
        <?php else: ?>
            <div class="relative w-full h-96">
                <canvas id="attackChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($labels)): ?>
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($labels as $i => $label): ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 font-medium truncate"><?= esc($label) ?></p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= esc($values[$i]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;

    const ctx = document.getElementById('attackChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        '#6366f1', '#f43f5e', '#f59e0b', '#10b981',
                        '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6',
                        '#eab308', '#ef4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, padding: 16 } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.formattedValue} report(s)`
                        }
                    }
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>