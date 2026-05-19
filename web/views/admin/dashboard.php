<?php
/** @var array{products:int,users:int,transactions:int,revenueToday:float} $stats */
/** @var float|null $revenueDelta */
/** @var array<int,array{date:string,label:string,total:float}> $weeklySales */
/** @var array<int,array<string,mixed>> $recentProducts */
/** @var array<int,array<string,mixed>> $recentUsers */
/** @var array<int,array<string,mixed>> $recentTransactions */
/** @var array<int,array{tone:string,text:string,time:string}> $activity */
/** @var string $adminUsername */

$title = 'Dashboard';
$activeNav = 'dashboard';
$crumb = 'Dashboard';
$transactionsBadge = (int) $stats['transactions'];

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

$thumbStyles = [
    ['#e74c3c', '🥤'],
    ['#f5a623', '🍊'],
    ['#00c9b1', '⚡'],
    ['#4f8ef7', '💧'],
    ['#9b7ff4', '☕'],
];
$pickThumb = static function (string $name) use ($thumbStyles): array {
    $key = strtolower($name);
    if (str_contains($key, 'water')) return $thumbStyles[3];
    if (str_contains($key, 'coffee') || str_contains($key, 'nescafe')) return $thumbStyles[4];
    if (str_contains($key, 'fanta') || str_contains($key, 'orange')) return $thumbStyles[1];
    if (str_contains($key, 'mountain') || str_contains($key, 'energy')) return $thumbStyles[2];
    return $thumbStyles[0];
};
$avatarGradients = [
    ['#00c9b1', '#4f8ef7'],
    ['#f5a623', '#f25c7a'],
    ['#9b7ff4', '#f25c7a'],
    ['#4f8ef7', '#9b7ff4'],
    ['#f25c7a', '#f5a623'],
];
$stockPill = static function (int $qty): array {
    if ($qty <= 0) return ['red', 'Out of Stock'];
    if ($qty < 10) return ['yellow', 'Low Stock'];
    return ['green', 'In Stock'];
};
$deltaText = null;
$deltaClass = 'up';
if ($revenueDelta !== null) {
    $deltaText = sprintf('%s %.0f%% vs yesterday', $revenueDelta >= 0 ? '↑' : '↓', abs($revenueDelta));
    $deltaClass = $revenueDelta >= 0 ? 'up' : 'down';
}
ob_start();
?>
<style>
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}
.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 18px 20px;
  position: relative;
  overflow: hidden;
  animation: fadeUp 0.4s ease both;
  transition: transform 0.18s, border-color 0.18s;
}
.stat-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.13); }
.stat-card:nth-child(1) { animation-delay: 0.05s; }
.stat-card:nth-child(2) { animation-delay: 0.10s; }
.stat-card:nth-child(3) { animation-delay: 0.15s; }
.stat-card:nth-child(4) { animation-delay: 0.20s; }
.stat-card::after {
  content:'';
  position: absolute; top: 0; right: 0;
  width: 80px; height: 80px;
  border-radius: 50%;
  filter: blur(30px);
  opacity: 0.4;
}
.stat-card.teal::after  { background: var(--teal); }
.stat-card.amber::after { background: var(--amber); }
.stat-card.rose::after  { background: var(--rose); }
.stat-card.blue::after  { background: var(--blue); }
.stat-icon {
  width: 36px; height: 36px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px;
}
.stat-icon svg { width:18px; height:18px; }
.stat-card.teal  .stat-icon { background: var(--teal-dim);   }
.stat-card.amber .stat-icon { background: var(--amber-dim);  }
.stat-card.rose  .stat-icon { background: var(--rose-dim);   }
.stat-card.blue  .stat-icon { background: var(--blue-dim);   }
.stat-card.teal  .stat-icon svg { stroke: var(--teal);  fill:none; stroke-width:2; }
.stat-card.amber .stat-icon svg { stroke: var(--amber); fill:none; stroke-width:2; }
.stat-card.rose  .stat-icon svg { stroke: var(--rose);  fill:none; stroke-width:2; }
.stat-card.blue  .stat-icon svg { stroke: var(--blue);  fill:none; stroke-width:2; }
.stat-label { font-size:11.5px; font-weight:500; color:var(--text-mid); margin-bottom:4px; }
.stat-value { font-size:26px; font-weight:700; color:var(--text); letter-spacing:-1px; line-height:1; margin-bottom:8px; }
.stat-delta {
  display:inline-flex; align-items:center; gap:3px;
  font-size:11px; font-weight:600;
  padding: 2px 7px; border-radius:6px;
}
.stat-delta.up    { background:rgba(0,201,177,0.12); color:var(--teal); }
.stat-delta.down  { background:var(--rose-dim); color:var(--rose); }
.stat-delta.muted { background:var(--surface2); color:var(--text-mid); }

.section-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}
.section-grid .panel { margin-bottom: 0; }
.section-grid .panel:nth-child(1) { animation-delay: 0.25s; }
.section-grid .panel:nth-child(2) { animation-delay: 0.30s; }

.panel-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}
.panel-title { font-size:13.5px; font-weight:700; color:var(--text); }
.panel-sub   { font-size:11px; color:var(--text-mid); margin-top:1px; }
.panel-action {
  font-size:12px; font-weight:600; color:var(--teal);
  padding:5px 10px;
  border-radius:7px; border:1px solid rgba(0,201,177,0.2);
  background:var(--teal-dim);
  transition:all 0.15s;
  text-decoration: none;
}
.panel-action:hover { background:rgba(0,201,177,0.2); }

.table-wrap { overflow-x:auto; }
.prod-cell { display:flex; align-items:center; gap:10px; }
.prod-thumb {
  width:32px; height:32px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  font-size:14px; flex-shrink:0;
}
.prod-name { font-size:13px; font-weight:600; color:var(--text); }
.prod-sku  { font-size:10.5px; color:var(--text-dim); font-family:'DM Mono',monospace; }
.pill {
  display:inline-block;
  font-size:10.5px; font-weight:600;
  padding:3px 9px; border-radius:20px;
}
.pill.green  { background:rgba(0,201,177,0.12); color:var(--teal); }
.pill.yellow { background:var(--amber-dim); color:var(--amber); }
.pill.red    { background:var(--rose-dim); color:var(--rose); }
.role-tag { font-size:11.5px; font-weight:600; }
.role-tag.admin { color: var(--amber); }
.role-tag.user  { color: var(--text-mid); }

.quick-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  padding: 16px;
}
.quick-card {
  border-radius: var(--radius);
  padding: 16px;
  cursor: pointer;
  transition: all 0.18s;
  border: 1px solid transparent;
  position: relative;
  overflow: hidden;
  text-decoration: none;
  display: block;
}
.quick-card:hover { transform: translateY(-2px); }
.quick-card.teal   { background:var(--teal-dim);   border-color:rgba(0,201,177,0.18); }
.quick-card.amber  { background:var(--amber-dim);  border-color:rgba(245,166,35,0.18); }
.quick-card.rose   { background:var(--rose-dim);   border-color:rgba(242,92,122,0.18); }
.quick-card.violet { background:var(--violet-dim); border-color:rgba(155,127,244,0.18); }
.quick-card.blue   { background:var(--blue-dim);   border-color:rgba(79,142,247,0.18); }
.qc-icon { font-size:20px; margin-bottom:8px; }
.qc-label { font-size:12px; font-weight:700; }
.qc-desc  { font-size:10.5px; margin-top:2px; color:var(--text-mid); }
.quick-card.teal   .qc-label { color:var(--teal); }
.quick-card.amber  .qc-label { color:var(--amber); }
.quick-card.rose   .qc-label { color:var(--rose); }
.quick-card.violet .qc-label { color:var(--violet); }
.quick-card.blue   .qc-label { color:var(--blue); }

.bottom-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}
.bottom-row .panel { margin-bottom: 0; }
.bottom-row .panel:nth-child(1) { animation-delay: 0.35s; }
.bottom-row .panel:nth-child(2) { animation-delay: 0.40s; }
.bottom-row .panel:nth-child(3) { animation-delay: 0.45s; }

.feed-list { padding: 4px 0; }
.feed-item {
  display:flex; align-items:flex-start; gap:12px;
  padding:12px 20px;
  border-bottom:1px solid rgba(255,255,255,0.04);
  transition: background 0.12s;
}
.feed-item:hover { background:rgba(255,255,255,0.02); }
.feed-item:last-child { border-bottom:none; }
.feed-dot {
  width:8px; height:8px; border-radius:50%;
  margin-top:5px; flex-shrink:0;
}
.feed-dot.teal  { background:var(--teal); box-shadow:0 0 6px var(--teal-glow); }
.feed-dot.amber { background:var(--amber); }
.feed-dot.rose  { background:var(--rose); }
.feed-dot.blue  { background:var(--blue); }
.feed-text { font-size:12.5px; color:var(--text-mid); line-height:1.4; }
.feed-text strong { color:var(--text); font-weight:600; }
.feed-time { margin-left:auto; font-size:10.5px; color:var(--text-dim); flex-shrink:0; font-family:'DM Mono',monospace; }

.chart-bars {
  display:flex; align-items:flex-end; gap:4px;
  height:60px;
  padding:12px 20px;
}
.bar {
  flex:1; border-radius:4px 4px 0 0;
  background:var(--surface2);
  min-height:4px;
  transition:background 0.2s;
  cursor:pointer;
  position:relative;
}
.bar:hover { background: var(--teal-dim); }
.bar.active { background: linear-gradient(to top, var(--teal), rgba(0,201,177,0.4)); }
.bar-labels {
  display:flex; justify-content:space-between;
  padding:0 20px 12px;
  font-size:9.5px; color:var(--text-dim); font-family:'DM Mono',monospace;
}
.tx-id { font-family:'DM Mono',monospace; font-size:11px; color:var(--teal); }
.tx-amount { color:var(--text); font-weight:600; }
.tx-time { font-size:11px; }
.panel-empty { padding: 24px 20px; color: var(--text-dim); font-size: 12.5px; text-align: center; }

.dash-header h1 {
  font-size: 24px; font-weight: 700;
  color: var(--text); letter-spacing: -0.5px;
  margin-bottom: 4px;
}
.dash-header p { font-size: 13.5px; color: var(--text-mid); }
.dash-header { margin-bottom: 28px; animation: fadeUp 0.4s ease both; }

@media (max-width: 1100px) {
  .stats-row { grid-template-columns: repeat(2, 1fr); }
  .section-grid { grid-template-columns: 1fr; }
  .bottom-row { grid-template-columns: 1fr; }
}
@media (max-width: 720px) {
  .stats-row { grid-template-columns: 1fr; }
}
</style>

<div class="dash-header">
  <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($adminUsername); ?></h1>
  <p>Here's what's happening with your vending machines today.</p>
</div>

<div class="stats-row">
  <div class="stat-card teal">
    <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
    <div class="stat-label">Total Products</div>
    <div class="stat-value"><?php echo number_format((int) $stats['products']); ?></div>
    <span class="stat-delta muted">Catalog size</span>
  </div>
  <div class="stat-card amber">
    <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
    <div class="stat-label">Registered Users</div>
    <div class="stat-value"><?php echo number_format((int) $stats['users']); ?></div>
    <span class="stat-delta muted">Accounts on record</span>
  </div>
  <div class="stat-card rose">
    <div class="stat-icon"><svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
    <div class="stat-label">Transactions</div>
    <div class="stat-value"><?php echo number_format((int) $stats['transactions']); ?></div>
    <span class="stat-delta muted">All-time</span>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
    <div class="stat-label">Revenue Today</div>
    <div class="stat-value">$<?php echo number_format((float) $stats['revenueToday'], 2); ?></div>
    <?php if ($deltaText !== null): ?>
      <span class="stat-delta <?php echo $deltaClass; ?>"><?php echo htmlspecialchars($deltaText); ?></span>
    <?php else: ?>
      <span class="stat-delta muted">No data yesterday</span>
    <?php endif; ?>
  </div>
</div>

<div class="section-grid">

  <div class="panel">
    <div class="panel-head">
      <div>
        <div class="panel-title">Recent Products</div>
        <div class="panel-sub">Last updated inventory</div>
      </div>
      <a class="panel-action" href="/products">View all</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Product</th><th>Price</th><th>Stock</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($recentProducts)): ?>
            <tr><td colspan="4" class="panel-empty">No products yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentProducts as $product):
              [$thumbColor, $thumbEmoji] = $pickThumb((string) $product['name']);
              [$pillTone, $pillLabel] = $stockPill((int) $product['quantity_available']);
            ?>
            <tr>
              <td>
                <div class="prod-cell">
                  <div class="prod-thumb" style="background: <?php echo $thumbColor; ?>26;"><?php echo $thumbEmoji; ?></div>
                  <div>
                    <div class="prod-name"><?php echo htmlspecialchars((string) $product['name']); ?></div>
                    <div class="prod-sku">SKU-<?php echo str_pad((string) $product['id'], 3, '0', STR_PAD_LEFT); ?></div>
                  </div>
                </div>
              </td>
              <td>$<?php echo number_format((float) $product['price'], 2); ?></td>
              <td><?php echo (int) $product['quantity_available']; ?></td>
              <td><span class="pill <?php echo $pillTone; ?>"><?php echo $pillLabel; ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div>
        <div class="panel-title">Quick Actions</div>
        <div class="panel-sub">Common tasks</div>
      </div>
    </div>
    <div class="quick-grid">
      <a class="quick-card teal" href="/products/create">
        <div class="qc-icon">📦</div>
        <div class="qc-label">Add Product</div>
        <div class="qc-desc">New inventory item</div>
      </a>
      <a class="quick-card amber" href="/users/create">
        <div class="qc-icon">👤</div>
        <div class="qc-label">Add User</div>
        <div class="qc-desc">New account</div>
      </a>
      <a class="quick-card rose" href="/transactions/create">
        <div class="qc-icon">🧾</div>
        <div class="qc-label">Transaction</div>
        <div class="qc-desc">Log a sale</div>
      </a>
      <a class="quick-card violet" href="/transactions">
        <div class="qc-icon">📊</div>
        <div class="qc-label">Reports</div>
        <div class="qc-desc">View analytics</div>
      </a>
    </div>

    <div class="panel-head" style="border-top:1px solid var(--border); border-bottom:none; margin-top:4px;">
      <div class="panel-title">Sales This Week</div>
      <div class="panel-sub">$<?php echo number_format(array_sum(array_column($weeklySales, 'total')), 2); ?> total</div>
    </div>
    <div class="chart-bars" id="chartBars"></div>
    <div class="bar-labels" id="chartLabels"></div>
  </div>
</div>

<div class="bottom-row">

  <div class="panel">
    <div class="panel-head">
      <div>
        <div class="panel-title">Recent Activity</div>
        <div class="panel-sub">Live updates</div>
      </div>
      <a class="panel-action" href="/transactions">All logs</a>
    </div>
    <div class="feed-list">
      <?php if (empty($activity)): ?>
        <div class="panel-empty">No activity yet.</div>
      <?php else: ?>
        <?php foreach ($activity as $entry): ?>
          <div class="feed-item">
            <div class="feed-dot <?php echo htmlspecialchars($entry['tone']); ?>"></div>
            <div class="feed-text"><?php echo $entry['text']; ?></div>
            <div class="feed-time"><?php echo htmlspecialchars($entry['time']); ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div>
        <div class="panel-title">Recent Users</div>
        <div class="panel-sub">Registered accounts</div>
      </div>
      <a class="panel-action" href="/users">View all</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($recentUsers)): ?>
            <tr><td colspan="3" class="panel-empty">No users yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentUsers as $i => $user):
              $gradient = $avatarGradients[$i % count($avatarGradients)];
              $initial = strtoupper(substr((string) $user['username'], 0, 2));
              $role = (string) $user['role'];
            ?>
            <tr>
              <td>
                <div class="prod-cell">
                  <div class="admin-avatar" style="background:linear-gradient(135deg,<?php echo $gradient[0]; ?>,<?php echo $gradient[1]; ?>);width:28px;height:28px;border-radius:7px;font-size:11px;"><?php echo htmlspecialchars($initial); ?></div>
                  <div>
                    <div class="prod-name"><?php echo htmlspecialchars((string) $user['username']); ?></div>
                    <div class="prod-sku">UID-<?php echo str_pad((string) $user['id'], 3, '0', STR_PAD_LEFT); ?></div>
                  </div>
                </div>
              </td>
              <td><span class="role-tag <?php echo $role === 'admin' ? 'admin' : 'user'; ?>"><?php echo htmlspecialchars(ucfirst($role)); ?></span></td>
              <td><span class="pill green">Active</span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <div>
        <div class="panel-title">Transactions</div>
        <div class="panel-sub">Latest purchases</div>
      </div>
      <a class="panel-action" href="/transactions">View all</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Amount</th><th>Time</th></tr></thead>
        <tbody>
          <?php if (empty($recentTransactions)): ?>
            <tr><td colspan="3" class="panel-empty">No transactions yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentTransactions as $tx): ?>
            <tr>
              <td class="tx-id">#<?php echo (int) $tx['id']; ?></td>
              <td class="tx-amount">$<?php echo number_format((float) $tx['total_price'], 2); ?></td>
              <td class="tx-time"><?php echo htmlspecialchars(date('h:i A', strtotime((string) $tx['transaction_date']))); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
(() => {
    const weekly = <?php echo json_encode(array_map(static fn (array $row): array => [
        'label' => $row['label'],
        'total' => (float) $row['total'],
    ], $weeklySales), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const values = weekly.map(d => d.total);
    const labels = weekly.map(d => d.label);
    const max = Math.max(1, ...values);
    const peakIdx = values.indexOf(Math.max(...values));
    const bars = document.getElementById('chartBars');
    const lbls = document.getElementById('chartLabels');

    weekly.forEach((d, i) => {
        const b = document.createElement('div');
        b.className = 'bar' + (i === peakIdx && d.total > 0 ? ' active' : '');
        b.style.height = (Math.max(d.total, 0) / max * 100) + '%';
        b.title = `${labels[i]}: $${d.total.toFixed(2)}`;
        bars.appendChild(b);
    });
    labels.forEach(d => {
        const s = document.createElement('span');
        s.textContent = d;
        lbls.appendChild(s);
    });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
