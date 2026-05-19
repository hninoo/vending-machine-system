<?php
$title = 'Users';
$activeNav = 'users';
$crumb = 'Users';
$userRows = array_map(static fn (array $user): array => [
    'id' => (int) $user['id'],
    'username' => (string) $user['username'],
    'role' => (string) $user['role'],
], $users);
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
ob_start();
?>

<section id="user-list-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Users</h1>
            <p class="page-subtitle">Manage access for admins and vending machine users.</p>
        </div>
        <a href="/users/create" class="btn btn-primary">Add User</a>
    </div>

    <div v-if="successMessage" class="alert alert-success">[[ successMessage ]]</div>

    <div class="panel">
        <div class="data-toolbar">
            <input v-model="query" type="search" class="form-control" placeholder="Search users" aria-label="Search users">
            <span class="text-muted">[[ filteredUsers.length ]] visible</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in filteredUsers" :key="user.id">
                        <td>[[ user.id ]]</td>
                        <td class="fw-semibold">[[ user.username ]]</td>
                        <td><span class="badge text-bg-light text-uppercase">[[ user.role ]]</span></td>
                        <td class="text-end">
                            <a :href="`/users/${user.id}/edit`" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="post" :action="`/users/${user.id}/delete`" class="d-inline">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr v-if="filteredUsers.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <nav class="mt-3" aria-label="User pagination">
        <ul class="pagination">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/users?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="/users?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/users?page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
</section>

<script>
(() => {
    const app = Vue.createApp({
        data() {
            return {
                query: '',
                successMessage: <?php echo json_encode($successMessage); ?>,
                users: <?php echo json_encode($userRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
            };
        },
        computed: {
            filteredUsers() {
                const keyword = this.query.trim().toLowerCase();
                if (!keyword) return this.users;
                return this.users.filter((user) => user.username.toLowerCase().includes(keyword) || user.role.toLowerCase().includes(keyword));
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#user-list-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
