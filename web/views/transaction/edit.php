<?php
$title = 'Edit Transaction';
$activeNav = 'transactions';
$crumb = 'Edit Transaction';
$userOptions = array_map(static fn (array $user): array => ['id' => (int) $user['id'], 'username' => (string) $user['username']], $users);
$productOptions = array_map(static fn (array $product): array => [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'price' => (float) $product['price'],
    'quantity_available' => (int) $product['quantity_available'],
], $products);
$transactionData = [
    'id' => (int) $transaction['id'],
    'user_id' => (int) $transaction['user_id'],
    'product_id' => (int) $transaction['product_id'],
    'quantity' => (int) $transaction['quantity'],
];
ob_start();
?>

<section id="transaction-edit-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Transaction</h1>
            <p class="page-subtitle">Adjust transaction ownership, product, or quantity.</p>
        </div>
        <a href="/transactions" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/transactions/<?php echo htmlspecialchars((string) $transaction['id']); ?>/update" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="user_id" class="form-label">User</label>
                    <select v-model.number="form.user_id" class="form-select" id="user_id" name="user_id" required>
                        <option v-for="user in users" :key="user.id" :value="user.id">[[ user.username ]]</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="product_id" class="form-label">Product</label>
                    <select v-model.number="form.product_id" class="form-select" id="product_id" name="product_id" required>
                        <option v-for="product in products" :key="product.id" :value="product.id">
                            [[ product.name ]] - $[[ formatPrice(product.price) ]]
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input v-model.number="form.quantity" type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                    <div v-if="submitted && form.quantity <= 0" class="field-error">Quantity must be positive.</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="form-hint text-muted">Total: $[[ total ]]</span>
                <button type="submit" class="btn btn-primary">Update Transaction</button>
            </div>
        </form>
    </div>
</section>

<script>
(() => {
    const transaction = <?php echo json_encode($transactionData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const app = Vue.createApp({
        data() {
            return {
                submitted: false,
                users: <?php echo json_encode($userOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                products: <?php echo json_encode($productOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                form: { ...transaction }
            };
        },
        computed: {
            selectedProduct() {
                return this.products.find((product) => product.id === this.form.product_id);
            },
            total() {
                return this.selectedProduct ? (this.selectedProduct.price * Number(this.form.quantity || 0)).toFixed(2) : '0.00';
            }
        },
        methods: {
            formatPrice(value) {
                return Number(value).toFixed(3);
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#transaction-edit-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
