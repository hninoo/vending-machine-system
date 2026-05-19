<?php
$title = 'Transactions';
$activeNav = 'transactions';
$crumb = 'Transactions';
$transactionRows = array_map(static fn (array $transaction): array => [
    'id' => (int) $transaction['id'],
    'username' => (string) $transaction['username'],
    'product_name' => (string) $transaction['product_name'],
    'quantity' => (int) $transaction['quantity'],
    'total_price' => (float) $transaction['total_price'],
], $transactions);
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
ob_start();
?>

<section id="transaction-list-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Transactions</h1>
            <p class="page-subtitle">Review purchase activity and totals.</p>
        </div>
        <a href="/transactions/create" class="btn btn-primary">Add Transaction</a>
    </div>

    <div class="metric-strip">
        <div class="metric">
            <div class="metric-label">Transactions</div>
            <div class="metric-value">[[ transactions.length ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Units Sold</div>
            <div class="metric-value">[[ unitsSold ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Revenue</div>
            <div class="metric-value">$[[ revenue ]]</div>
        </div>
    </div>

    <div v-if="successMessage" class="alert alert-success">[[ successMessage ]]</div>

    <div class="panel">
        <div class="data-toolbar">
            <input v-model="query" type="search" class="form-control" placeholder="Search transactions" aria-label="Search transactions">
            <span class="text-muted">[[ filteredTransactions.length ]] visible</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                        <td>[[ transaction.id ]]</td>
                        <td class="fw-semibold">[[ transaction.username ]]</td>
                        <td>[[ transaction.product_name ]]</td>
                        <td>[[ transaction.quantity ]]</td>
                        <td>$[[ formatMoney(transaction.total_price) ]]</td>
                        <td class="text-end">
                            <a :href="`/transactions/${transaction.id}/edit`" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="post" :action="`/transactions/${transaction.id}/delete`" class="d-inline">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr v-if="filteredTransactions.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">No transactions found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <nav class="mt-3" aria-label="Transaction pagination">
        <ul class="pagination">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/transactions?page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="/transactions?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/transactions?page=<?php echo $page + 1; ?>">Next</a>
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
                transactions: <?php echo json_encode($transactionRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
            };
        },
        computed: {
            filteredTransactions() {
                const keyword = this.query.trim().toLowerCase();
                if (!keyword) return this.transactions;
                return this.transactions.filter((transaction) =>
                    transaction.username.toLowerCase().includes(keyword) ||
                    transaction.product_name.toLowerCase().includes(keyword)
                );
            },
            unitsSold() {
                return this.transactions.reduce((total, transaction) => total + transaction.quantity, 0);
            },
            revenue() {
                const value = this.transactions.reduce((total, transaction) => total + transaction.total_price, 0);
                return value.toFixed(2);
            }
        },
        methods: {
            formatMoney(value) {
                return Number(value).toFixed(2);
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#transaction-list-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
