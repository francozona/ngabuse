<?= $this->extend('shared/admin.layout.php') ?>

<?= $this->section('content') ?>

<div class="flex-1 overflow-y-auto px-6 py-5">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Domain Abuse Reports by Registrar</h1>
            <p class="text-sm text-gray-500 mt-1">
                Total reports analyzed: <span class="font-semibold text-gray-700"><?= esc($total) ?></span>
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
                No registrar email data available for this range.
            </div>
        <?php else: ?>
            <div class="relative w-full h-96">
                <canvas id="registrarChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($labels)): ?>
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach (array_slice($labels, 0, 8) as $i => $label): ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-400 font-medium truncate"><?= esc($label) ?></p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= esc($values[$i]) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Datatable below the cards: full registrar breakdown -->
    <div class="mt-6 px-4 py-4 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table id="registrarSummaryTable" class="display w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Registrar</th>
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

<!-- jQuery + DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const labels = <?= json_encode($labels) ?>;
    const values = <?= json_encode($values) ?>;

    const ctx = document.getElementById('registrarChart');
    if (ctx) {
        // Destroy any existing chart bound to this canvas before drawing a new one.
        // Prevents duplicate/overlapping bars if this script ever re-runs on the same canvas.
        const existingChart = Chart.getChart(ctx);
        if (existingChart) {
            existingChart.destroy();
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Abuse Reports',
                    data: values,
                    backgroundColor: 'rgba(121, 223, 38, 0.7)',
                    borderColor: 'rgba(96, 148, 50, 0.7)',
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: labels.length > 10 ? 'y' : 'x',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.formattedValue} report(s)`
                        }
                    }
                },
                scales: {
                    x: { ticks: { autoSkip: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    $(document).ready(function () {
        if ($('#registrarSummaryTable').length) {
            $('#registrarSummaryTable').DataTable({
                order: [[2, 'desc']], // matches arsort() ordering from the controller
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                columnDefs: [
                    { orderable: false, targets: 0 } // "#" is a display index, not sortable
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ registrars"
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>