<?php
$title = 'Admin Sign in';
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vending Machine - <?php echo htmlspecialchars($title); ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<style>
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --bg:        #0d0f14;
  --surface:   #151820;
  --surface2:  #1c2030;
  --border:    rgba(255,255,255,0.07);
  --teal:      #00c9b1;
  --teal-dim:  rgba(0,201,177,0.12);
  --teal-glow: rgba(0,201,177,0.25);
  --rose:      #f25c7a;
  --rose-dim:  rgba(242,92,122,0.12);
  --text:      #e8ecf4;
  --text-mid:  #8a92a6;
  --text-dim:  #4a5168;
}

html, body {
  height: 100%;
  background: var(--bg);
  font-family: 'DM Sans', sans-serif;
  color: var(--text);
  -webkit-font-smoothing: antialiased;
}

body.auth-shell {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 32px 18px;
  background:
    radial-gradient(circle at 12% 18%, rgba(0,201,177,0.10), transparent 45%),
    radial-gradient(circle at 88% 82%, rgba(155,127,244,0.08), transparent 45%),
    var(--bg);
  position: relative;
  overflow: hidden;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 22px;
  padding: 36px 32px 32px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.35);
  animation: fadeUp 0.4s ease both;
  position: relative;
  z-index: 1;
}

.auth-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 22px;
}
.auth-brand .logo-mark {
  width: 40px; height: 40px;
  border-radius: 11px;
  background: linear-gradient(135deg, var(--teal) 0%, #00a896 100%);
  display: grid; place-items: center;
  box-shadow: 0 0 22px var(--teal-glow);
  flex-shrink: 0;
}
.auth-brand .logo-mark svg { width: 20px; height: 20px; fill: white; }
.auth-brand-text { display: grid; gap: 2px; }
.auth-brand-name { font-size: 16px; font-weight: 700; letter-spacing: -0.3px; color: var(--text); }
.auth-brand-badge {
  display: inline-block;
  font-size: 9px; font-weight: 600;
  background: var(--teal-dim);
  color: var(--teal);
  border: 1px solid rgba(0,201,177,0.2);
  padding: 2px 6px; border-radius: 4px;
  text-transform: uppercase; letter-spacing: 0.5px;
  width: max-content;
}

.auth-title {
  font-size: 22px; font-weight: 700;
  color: var(--text);
  letter-spacing: -0.4px;
  margin-bottom: 4px;
}
.auth-subtitle {
  font-size: 13px; color: var(--text-mid);
  margin-bottom: 22px;
}

.auth-field { display: grid; gap: 6px; margin-bottom: 16px; }
.auth-field label {
  font-size: 11.5px;
  font-weight: 600;
  color: var(--text-mid);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.auth-field input {
  width: 100%;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px 14px;
  font-family: inherit;
  font-size: 14px;
  color: var(--text);
  transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
  outline: none;
}
.auth-field input::placeholder { color: var(--text-dim); }
.auth-field input:focus {
  border-color: rgba(0,201,177,0.45);
  background: var(--surface2);
  box-shadow: 0 0 0 3px rgba(0,201,177,0.15);
}
.auth-field .field-error {
  color: var(--rose);
  font-size: 11.5px;
  font-weight: 600;
  margin-top: 2px;
}

.auth-alert {
  display: flex; align-items: center; gap: 10px;
  padding: 11px 14px;
  border-radius: 12px;
  background: var(--rose-dim);
  border: 1px solid rgba(242,92,122,0.28);
  color: var(--rose);
  font-size: 12.5px;
  font-weight: 600;
  margin-bottom: 18px;
}
.auth-alert svg { width: 16px; height: 16px; flex-shrink: 0; }

.auth-submit {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: var(--teal);
  color: #0b1a17;
  border: 0;
  border-radius: 12px;
  padding: 13px 20px;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 8px;
  transition: transform 0.15s, box-shadow 0.15s, background 0.15s;
}
.auth-submit:hover {
  background: #00d6bd;
  box-shadow: 0 6px 18px var(--teal-glow);
  transform: translateY(-1px);
}
.auth-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.auth-foot {
  margin-top: 20px;
  padding-top: 18px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: var(--text-mid);
}
.auth-foot a {
  color: var(--teal);
  text-decoration: none;
  font-weight: 600;
}
.auth-foot a:hover { text-decoration: underline; }

[v-cloak] { display: none; }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body class="auth-shell">
<main id="login-app" class="auth-card" v-cloak>
    <div class="auth-brand">
        <div class="logo-mark">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="4" y="2" width="16" height="20" rx="3"/>
                <rect x="7" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/>
                <rect x="13" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/>
                <rect x="7" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/>
                <rect x="13" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/>
                <rect x="9" y="17" width="6" height="3" rx="1" fill="rgba(255,255,255,0.4)"/>
            </svg>
        </div>
        <div class="auth-brand-text">
            <span class="auth-brand-name">Vending Machine</span>
            <span class="auth-brand-badge">Admin</span>
        </div>
    </div>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to manage products, users, and transactions.</p>

    <div v-if="errorMessage" class="auth-alert" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>[[ errorMessage ]]</span>
    </div>

    <form method="post" action="/admin/login" @submit="submitted = true">
        <div class="auth-field">
            <label for="username">Username</label>
            <input v-model.trim="form.username" type="text" id="username" name="username" autocomplete="username" required autofocus>
            <span v-if="submitted && !form.username" class="field-error">Username is required.</span>
        </div>
        <div class="auth-field">
            <label for="password">Password</label>
            <input v-model="form.password" type="password" id="password" name="password" autocomplete="current-password" required>
            <span v-if="submitted && !form.password" class="field-error">Password is required.</span>
        </div>

        <button type="submit" class="auth-submit">
            Sign in
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
        </button>
    </form>

    <div class="auth-foot">
        <span>Not an admin?</span>
        <a href="/">Go to storefront</a>
    </div>
</main>

<script>
(() => {
    const app = Vue.createApp({
        data() {
            return {
                submitted: false,
                errorMessage: <?php echo json_encode($errorMessage); ?>,
                form: { username: '', password: '' }
            };
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#login-app');
})();
</script>
</body>
</html>
