<?php
/** @var string $content */
/** @var string|null $title */
/** @var string|null $activeNav */
/** @var string|null $crumb */
/** @var int|null $transactionsBadge */

$pageTitle = $title ?? 'Admin';
$active = $activeNav ?? '';
$crumbLabel = $crumb ?? $pageTitle;
$badge = isset($transactionsBadge) ? (int) $transactionsBadge : null;
$adminUsername = (string) ($_SESSION['admin_username'] ?? 'Admin');
$initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $adminUsername) ?: 'A', 0, 1));

$navItems = [
    'overview' => [
        ['key' => 'dashboard', 'href' => '/admin/dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
    ],
    'catalog' => [
        ['key' => 'products', 'href' => '/products', 'label' => 'Products', 'icon' => 'box'],
        ['key' => 'products-create', 'href' => '/products/create', 'label' => 'Add Product', 'icon' => 'plus-circle'],
    ],
    'access' => [
        ['key' => 'users', 'href' => '/users', 'label' => 'Users', 'icon' => 'users'],
        ['key' => 'users-create', 'href' => '/users/create', 'label' => 'Add User', 'icon' => 'user-plus'],
    ],
    'activity' => [
        ['key' => 'transactions', 'href' => '/transactions', 'label' => 'Transactions', 'icon' => 'chart', 'badge' => $badge],
        ['key' => 'transactions-create', 'href' => '/transactions/create', 'label' => 'Add Transaction', 'icon' => 'plus'],
    ],
];

$renderIcon = static function (string $name): string {
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'box' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'plus-circle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'user-plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg>',
        'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
        'plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
    ];
    return $icons[$name] ?? '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Vending Machine - <?php echo htmlspecialchars($pageTitle); ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

:root {
  --bg:        #0d0f14;
  --surface:   #151820;
  --surface2:  #1c2030;
  --border:    rgba(255,255,255,0.07);
  --teal:      #00c9b1;
  --teal-dim:  rgba(0,201,177,0.12);
  --teal-glow: rgba(0,201,177,0.25);
  --amber:     #f5a623;
  --amber-dim: rgba(245,166,35,0.12);
  --rose:      #f25c7a;
  --rose-dim:  rgba(242,92,122,0.12);
  --blue:      #4f8ef7;
  --blue-dim:  rgba(79,142,247,0.12);
  --violet:    #9b7ff4;
  --violet-dim:rgba(155,127,244,0.12);
  --text:      #e8ecf4;
  --text-mid:  #8a92a6;
  --text-dim:  #4a5168;
  --radius:    14px;
  --radius-lg: 20px;
}

html,body { height:100%; background:var(--bg); font-family:'DM Sans',sans-serif; color:var(--text); }
a { color: inherit; }

.layout { display:flex; min-height:100vh; }

.sidebar {
  width: 240px;
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 20;
  overflow-y: auto;
}
.sidebar-logo {
  padding: 24px 20px 20px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.logo-mark {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--teal) 0%, #00a896 100%);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 20px var(--teal-glow);
  flex-shrink: 0;
}
.logo-mark svg { width:18px; height:18px; fill:white; }
.logo-name { font-size:15px; font-weight:700; color:var(--text); letter-spacing:-0.3px; }
.logo-badge {
  display:inline-block;
  font-size:9px; font-weight:600;
  background: var(--teal-dim);
  color: var(--teal);
  border: 1px solid rgba(0,201,177,0.2);
  padding: 2px 6px; border-radius: 4px;
  text-transform: uppercase; letter-spacing: 0.5px;
  margin-top: 2px;
}
.sidebar-section { padding: 20px 12px 8px; }
.sidebar-label {
  font-size: 10px; font-weight: 600;
  color: var(--text-dim); letter-spacing: 1px;
  text-transform: uppercase;
  padding: 0 8px; margin-bottom: 6px;
}
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px;
  border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  color: var(--text-mid);
  cursor: pointer;
  transition: all 0.15s;
  text-decoration: none;
  margin-bottom: 2px;
  position: relative;
}
.nav-item:hover { background: var(--surface2); color: var(--text); }
.nav-item.active {
  background: var(--teal-dim);
  color: var(--teal);
  border: 1px solid rgba(0,201,177,0.18);
}
.nav-item.active::before {
  content:'';
  position: absolute; left: 0; top: 50%; transform: translateY(-50%);
  width: 3px; height: 20px;
  background: var(--teal);
  border-radius: 0 3px 3px 0;
}
.nav-item svg { width:16px; height:16px; flex-shrink:0; }
.nav-badge {
  margin-left: auto;
  font-size: 10px; font-weight: 700;
  background: var(--rose-dim);
  color: var(--rose);
  padding: 1px 6px; border-radius: 10px;
}
.sidebar-footer {
  margin-top: auto;
  padding: 16px 12px;
  border-top: 1px solid var(--border);
}
.admin-chip {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  background: var(--surface2);
  transition: background 0.15s;
  border: 0;
  width: 100%;
  text-align: left;
  cursor: pointer;
  color: inherit;
  font-family: inherit;
}
.admin-chip:hover { background: #222740; }
.admin-avatar {
  width: 30px; height: 30px;
  border-radius: 8px;
  background: linear-gradient(135deg, var(--violet) 0%, var(--blue) 100%);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; color: white; flex-shrink: 0;
}
.admin-info { flex: 1; overflow: hidden; }
.admin-name { font-size: 12px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.admin-role { font-size: 10px; color: var(--text-mid); }
.logout-icon { color: var(--text-dim); transition: color 0.15s; display:flex; }
.admin-chip:hover .logout-icon { color: var(--rose); }
.logout-form { margin: 0; }

.main {
  margin-left: 240px;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  min-width: 0;
}

.topbar {
  height: 60px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center;
  padding: 0 28px;
  gap: 16px;
  position: sticky; top: 0; z-index: 10;
  background: rgba(13,15,20,0.85);
  backdrop-filter: blur(12px);
}
.topbar-title { font-size: 14px; font-weight: 600; color: var(--text-mid); }
.topbar-title strong { color: var(--text); font-weight: 600; }
.topbar-spacer { flex: 1; }
.topbar-btn {
  width: 34px; height: 34px;
  border-radius: 8px;
  background: var(--surface);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.15s; position: relative;
  color: var(--text-mid);
  text-decoration: none;
}
.topbar-btn:hover { background: var(--surface2); border-color: rgba(255,255,255,0.12); color: var(--text); }
.topbar-btn svg { width:16px; height:16px; stroke: currentColor; fill:none; stroke-width:2; }

.content { padding: 28px; flex: 1; min-width: 0; }

.page-header {
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:16px;
  margin-bottom: 24px;
  animation: fadeUp 0.4s ease both;
}
.page-title {
  font-size: 24px; font-weight: 700;
  color: var(--text); letter-spacing: -0.5px;
  margin-bottom: 4px;
}
.page-subtitle { font-size: 13.5px; color: var(--text-mid); }

.panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  animation: fadeUp 0.4s ease both;
  margin-bottom: 16px;
  max-width: 100%;
  min-width: 0;
}
.panel-body { padding: 22px 24px; }

.data-toolbar {
  display:flex; align-items:center; justify-content:space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  gap: 16px;
}
.data-toolbar .text-muted { color: var(--text-mid) !important; font-size: 12px; }
.data-toolbar .form-control { max-width: 280px; }

.table-responsive { overflow-x: auto; max-width: 100%; -webkit-overflow-scrolling: touch; }
.panel table { width:100%; border-collapse:collapse; }
.panel thead th {
  font-size:10.5px; font-weight:600; color:var(--text-dim);
  text-transform:uppercase; letter-spacing:0.6px;
  padding:12px 20px; text-align:left;
  border-bottom:1px solid var(--border);
  background: rgba(255,255,255,0.02);
}
.panel thead th a { color: inherit; text-decoration: none; }
.panel thead th a:hover { color: var(--text-mid); }
.panel tbody td {
  font-size:13px; color:var(--text-mid);
  padding:14px 20px;
  border-bottom:1px solid rgba(255,255,255,0.04);
  vertical-align: middle;
}
.panel tbody tr:last-child td { border-bottom:none; }
.panel tbody tr:hover td { background:rgba(255,255,255,0.02); }
.panel tbody td.fw-semibold { color: var(--text); font-weight: 600; }
.panel .text-end { text-align: right; }

.metric-strip {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}
.metric {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 18px 20px;
  display: block;
  text-decoration: none;
  color: inherit;
  transition: transform 0.18s, border-color 0.18s;
  animation: fadeUp 0.4s ease both;
}
.metric:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.13); }
.metric-label { font-size:11.5px; font-weight:500; color:var(--text-mid); margin-bottom:6px; text-transform: uppercase; letter-spacing: 0.4px; }
.metric-value { font-size:24px; font-weight:700; color:var(--text); letter-spacing:-0.5px; line-height:1.1; }

/* Forms */
.form-label { font-size: 12px; font-weight: 600; color: var(--text-mid); margin-bottom: 6px; display:block; text-transform: uppercase; letter-spacing: 0.4px; }
.form-control, .form-select {
  width: 100%;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 10px 12px;
  color: var(--text);
  font-size: 13.5px;
  font-family: inherit;
  transition: border-color 0.15s, background 0.15s;
  outline: none;
}
.form-control:focus, .form-select:focus { border-color: rgba(0,201,177,0.45); background: var(--surface); }
.form-control::placeholder { color: var(--text-dim); }
.form-select { appearance: none; background-image: linear-gradient(45deg, transparent 50%, var(--text-mid) 50%), linear-gradient(135deg, var(--text-mid) 50%, transparent 50%); background-position: calc(100% - 16px) 50%, calc(100% - 11px) 50%; background-size: 5px 5px, 5px 5px; background-repeat: no-repeat; padding-right: 32px; }
.form-select option { background: var(--surface); color: var(--text); }

.field-error { color: var(--rose); font-size: 11.5px; margin-top: 6px; }
.form-hint { color: var(--text-mid); font-size: 12px; }

/* Buttons */
.btn {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 6px;
  font-family: inherit;
  font-size: 13px; font-weight: 600;
  padding: 9px 16px;
  border-radius: 10px;
  border: 1px solid transparent;
  background: var(--surface2);
  color: var(--text);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s;
  line-height: 1.2;
}
.btn:hover { transform: translateY(-1px); }
.btn-sm { font-size: 11.5px; padding: 6px 12px; border-radius: 8px; }
.btn-primary { background: var(--teal); color: #0b1a17; border-color: var(--teal); }
.btn-primary:hover { background: #00d6bd; box-shadow: 0 4px 14px var(--teal-glow); }
.btn-success { background: var(--teal-dim); color: var(--teal); border-color: rgba(0,201,177,0.25); }
.btn-success:hover { background: rgba(0,201,177,0.2); }
.btn-outline-secondary { background: transparent; color: var(--text-mid); border-color: var(--border); }
.btn-outline-secondary:hover { color: var(--text); border-color: rgba(255,255,255,0.18); background: var(--surface2); }
.btn-outline-danger { background: transparent; color: var(--rose); border-color: rgba(242,92,122,0.3); }
.btn-outline-danger:hover { background: var(--rose-dim); border-color: var(--rose); }

/* Badges + alerts */
.badge {
  display: inline-block;
  font-size: 10.5px; font-weight: 600;
  padding: 3px 9px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.4px;
}
.badge.text-bg-success { background: rgba(0,201,177,0.14); color: var(--teal); }
.badge.text-bg-danger { background: var(--rose-dim); color: var(--rose); }
.badge.text-bg-light { background: var(--surface2); color: var(--text-mid); }
.alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; margin-bottom: 18px; border: 1px solid transparent; }
.alert-success { background: var(--teal-dim); color: var(--teal); border-color: rgba(0,201,177,0.2); }

/* Bootstrap-grid subset */
.row { display: flex; flex-wrap: wrap; margin: -8px; }
.row > [class*="col-"] { padding: 8px; }
.row.g-3 { margin: -12px; }
.row.g-3 > [class*="col-"] { padding: 12px; }
.col-md-3, .col-md-4, .col-md-6 { flex: 0 0 100%; max-width: 100%; }
@media (min-width: 768px) {
  .col-md-3 { flex: 0 0 25%; max-width: 25%; }
  .col-md-4 { flex: 0 0 33.3333%; max-width: 33.3333%; }
  .col-md-6 { flex: 0 0 50%; max-width: 50%; }
}

/* Pagination */
.pagination {
  display: flex; gap: 6px; list-style: none;
  padding: 0; margin: 0;
}
.page-item .page-link {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 34px; height: 34px; padding: 0 10px;
  border-radius: 8px;
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text-mid);
  font-size: 12.5px; font-weight: 600;
  text-decoration: none;
  transition: all 0.15s;
}
.page-item .page-link:hover { color: var(--text); border-color: rgba(255,255,255,0.15); }
.page-item.active .page-link {
  background: var(--teal-dim);
  color: var(--teal);
  border-color: rgba(0,201,177,0.3);
}
.page-item.disabled .page-link { opacity: 0.4; pointer-events: none; }

/* Utility shims */
.d-flex { display: flex; }
.d-inline { display: inline; }
.justify-content-between { justify-content: space-between; }
.justify-content-end { justify-content: flex-end; }
.align-items-center { align-items: center; }
.gap-2 { gap: 8px; }
.mt-3 { margin-top: 16px; }
.mt-4 { margin-top: 24px; }
.ms-auto { margin-left: auto; }
.py-4 { padding-top: 18px; padding-bottom: 18px; }
.text-center { text-align: center; }
.text-end { text-align: right; }
.text-muted { color: var(--text-mid); }
.text-uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
.fw-semibold { font-weight: 600; }

[v-cloak] { display: none; }

@keyframes fadeUp {
  from { opacity:0; transform:translateY(14px); }
  to   { opacity:1; transform:translateY(0); }
}
::-webkit-scrollbar { width:5px; height:5px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--surface2); border-radius:10px; }

.sidebar-toggle {
  display: none;
  width: 36px; height: 36px;
  border-radius: 9px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-mid);
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.sidebar-toggle:hover { background: var(--surface2); color: var(--text); }
.sidebar-toggle svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }

.sidebar-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(2px);
  z-index: 15;
  animation: fadeIn 0.18s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

@media (max-width: 1100px) {
  .sidebar { width: 220px; }
  .main { margin-left: 220px; }
}

@media (max-width: 880px) {
  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.24s ease;
    width: 260px;
    box-shadow: 8px 0 32px rgba(0,0,0,0.4);
  }
  .main { margin-left: 0; }
  .sidebar-toggle { display: inline-flex; }
  body.sidebar-open .sidebar { transform: translateX(0); }
  body.sidebar-open .sidebar-backdrop { display: block; }
  .topbar { padding: 0 18px; gap: 10px; }
  .content { padding: 20px 16px; }
  html, body { overflow-x: hidden; }
  .layout, .main, .content { max-width: 100vw; }
}

@media (max-width: 560px) {
  .topbar-title { font-size: 13px; }
  .topbar-title strong { display: inline; }
  .panel thead th { padding: 10px 14px; font-size: 9.5px; letter-spacing: 0.4px; }
  .panel tbody td { padding: 12px 14px; font-size: 12.5px; }
  .panel .text-end { white-space: nowrap; }
  .table-responsive table { min-width: 470px; }
  .data-toolbar { flex-direction: column; align-items: stretch; gap: 8px; padding: 14px 16px; }
  .data-toolbar .form-control { max-width: 100%; }
  .page-header { flex-direction: column; align-items: stretch; gap: 12px; }
  .page-header .btn { align-self: flex-start; }
  .page-title { font-size: 22px; }
}
</style>
</head>
<body>
<div class="layout">

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="3"/><rect x="7" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="13" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="7" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="13" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="9" y="17" width="6" height="3" rx="1" fill="rgba(255,255,255,0.4)"/></svg>
      </div>
      <div>
        <div class="logo-name">Vending Machine</div>
        <span class="logo-badge">Admin</span>
      </div>
    </div>

    <?php foreach ($navItems as $sectionKey => $items): ?>
      <div class="sidebar-section">
        <div class="sidebar-label"><?php echo htmlspecialchars(ucfirst($sectionKey)); ?></div>
        <?php foreach ($items as $item):
          $isActive = $active === $item['key'];
          $itemBadge = $item['badge'] ?? null;
        ?>
          <a class="nav-item<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($item['href']); ?>">
            <?php echo $renderIcon((string) $item['icon']); ?>
            <?php echo htmlspecialchars($item['label']); ?>
            <?php if ($itemBadge !== null && $itemBadge > 0): ?>
              <span class="nav-badge"><?php echo (int) $itemBadge; ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <div class="sidebar-footer">
      <form method="post" action="/admin/logout" class="logout-form">
        <button type="submit" class="admin-chip" title="Logout">
          <div class="admin-avatar"><?php echo htmlspecialchars($initials); ?></div>
          <div class="admin-info">
            <div class="admin-name"><?php echo htmlspecialchars($adminUsername); ?></div>
            <div class="admin-role">Super Admin</div>
          </div>
          <div class="logout-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </div>
        </button>
      </form>
    </div>
  </aside>

  <div class="main">

    <div class="topbar">
      <button type="button" class="sidebar-toggle" aria-label="Open menu" aria-expanded="false" data-sidebar-toggle>
        <svg viewBox="0 0 24 24" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="topbar-title">Admin / <strong><?php echo htmlspecialchars($crumbLabel); ?></strong></span>
      <div class="topbar-spacer"></div>
      <a class="topbar-btn" href="/" aria-label="View storefront" title="View storefront">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      </a>
    </div>

    <main class="content"><?php echo $content; ?></main>
  </div>

  <div class="sidebar-backdrop" data-sidebar-close></div>
</div>

<script>
(() => {
  const body = document.body;
  const toggles = document.querySelectorAll('[data-sidebar-toggle]');
  const setOpen = (isOpen) => {
    body.classList.toggle('sidebar-open', isOpen);
    toggles.forEach(el => el.setAttribute('aria-expanded', String(isOpen)));
  };
  const close = () => setOpen(false);
  toggles.forEach(el => el.addEventListener('click', () => setOpen(!body.classList.contains('sidebar-open'))));
  document.querySelectorAll('[data-sidebar-close]').forEach(el => el.addEventListener('click', close));
  document.querySelectorAll('.sidebar .nav-item').forEach(el => el.addEventListener('click', close));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
