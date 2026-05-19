<?php
use App\Enums\UserRole;

$title = 'Edit User';
$activeNav = 'users';
$crumb = 'Edit User';
$roles = array_map(static fn (UserRole $role): array => ['value' => $role->value, 'label' => $role->label()], UserRole::cases());
$userData = [
    'id' => (int) $user['id'],
    'username' => (string) $user['username'],
    'role' => (string) $user['role'],
];
ob_start();
?>

<section id="user-edit-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">Update account identity, password, or role.</p>
        </div>
        <a href="/users" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/users/<?php echo htmlspecialchars((string) $user['id']); ?>/update" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input v-model.trim="form.username" type="text" class="form-control" id="username" name="username" required>
                    <div v-if="submitted && !form.username" class="field-error">Username is required.</div>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select v-model="form.role" class="form-select" id="role" name="role" required>
                        <option v-for="role in roles" :key="role.value" :value="role.value">[[ role.label ]]</option>
                    </select>
                    <div v-if="submitted && !form.role" class="field-error">Role is required.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</section>

<script>
(() => {
    const user = <?php echo json_encode($userData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const app = Vue.createApp({
        data() {
            return {
                submitted: false,
                roles: <?php echo json_encode($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                form: { ...user }
            };
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#user-edit-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
