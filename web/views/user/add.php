<?php
use App\Enums\UserRole;

$title = 'Add User';
$activeNav = 'users-create';
$crumb = 'Add User';
$roles = array_map(static fn (UserRole $role): array => ['value' => $role->value, 'label' => $role->label()], UserRole::cases());
ob_start();
?>

<section id="user-form-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Add User</h1>
            <p class="page-subtitle">Create an account and assign the correct role.</p>
        </div>
        <a href="/users" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/users/create" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input v-model.trim="form.username" type="text" class="form-control" id="username" name="username" required>
                    <div v-if="submitted && !form.username" class="field-error">Username is required.</div>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input v-model="form.password" type="password" class="form-control" id="password" name="password" required>
                    <div v-if="submitted && !form.password" class="field-error">Password is required.</div>
                </div>
                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select v-model="form.role" class="form-select" id="role" name="role" required>
                        <option value="" disabled>Select a role</option>
                        <option v-for="role in roles" :key="role.value" :value="role.value">[[ role.label ]]</option>
                    </select>
                    <div v-if="submitted && !form.role" class="field-error">Role is required.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</section>

<script>
(() => {
    const app = Vue.createApp({
        data() {
            return {
                submitted: false,
                roles: <?php echo json_encode($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                form: { username: '', password: '', role: '' }
            };
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#user-form-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
