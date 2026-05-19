<?php
$title = 'Add Transaction';
$activeNav = 'transactions-create';
$crumb = 'Add Transaction';
$userOptions = array_map(static fn (array $user): array => ['id' => (int) $user['id'], 'username' => (string) $user['username']], $users);
$productOptions = array_map(static fn (array $product): array => [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'price' => (float) $product['price'],
    'quantity_available' => (int) $product['quantity_available'],
], $products);
ob_start();
?>

<section id="transaction-form-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Transaction</h1>
            <p class="page-subtitle">Record a product purchase for a selected user.</p>
        </div>
        <a href="/transactions" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/transactions/create" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="user_id" class="form-label">User</label>
                    <select v-model.number="form.user_id" class="form-select" id="user_id" name="user_id" required>
                        <option value="">Select User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">[[ user.username ]]</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="product_id" class="form-label">Product</label>
                    <select v-model.number="form.product_id" class="form-select" id="product_id" name="product_id" required>
                        <option value="">Select Product</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">
                            [[ product.name ]] - $[[ formatPrice(product.price) ]]
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input v-model.number="form.quantity" type="number" class="form-control" id="quantity" name="quantity" min="1" :max="selectedProduct?.quantity_available || null" required>
                    <div v-if="submitted && quantityInvalid" class="field-error">Quantity must be within available stock.</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="form-hint text-muted">Total: $[[ total ]]</span>
                <button type="submit" class="btn btn-primary">Save Transaction</button>
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
                users: <?php echo json_encode($userOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                products: <?php echo json_encode($productOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
                form: { user_id: '', product_id: '', quantity: 1 }
            };
        },
        computed: {
            selectedProduct() {
                return this.products.find((product) => product.id === this.form.product_id);
            },
            quantityInvalid() {
                return !this.selectedProduct || this.form.quantity <= 0 || this.form.quantity > this.selectedProduct.quantity_available;
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
    app.mount('#transaction-form-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
