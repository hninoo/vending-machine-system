<?php
$title = 'Purchase Product';
$activeNav = 'products';
$crumb = 'Purchase';
$productData = [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'price' => (float) $product['price'],
    'quantity_available' => (int) $product['quantity_available'],
];
ob_start();
?>

<section id="purchase-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Purchase [[ product.name ]]</h1>
            <p class="page-subtitle">Record a purchase and update inventory immediately.</p>
        </div>
        <a href="/products" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="metric-strip">
        <div class="metric">
            <div class="metric-label">Unit Price</div>
            <div class="metric-value">$[[ formatPrice(product.price) ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Available</div>
            <div class="metric-value">[[ product.quantity_available ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Purchase Total</div>
            <div class="metric-value">$[[ total ]]</div>
        </div>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" :action="`/products/${product.id}/purchase`" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="user_id" class="form-label">User ID</label>
                    <input v-model.number="form.user_id" type="number" class="form-control" id="user_id" name="user_id" min="1" required>
                    <div v-if="submitted && form.user_id <= 0" class="field-error">Valid user ID is required.</div>
                </div>
                <div class="col-md-6">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input v-model.number="form.quantity" type="number" class="form-control" id="quantity" name="quantity" min="1" :max="product.quantity_available" required>
                    <div v-if="submitted && quantityInvalid" class="field-error">Quantity must be within available stock.</div>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/products" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Purchase</button>
            </div>
        </form>
    </div>
</section>

<script>
(() => {
    const product = <?php echo json_encode($productData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    const app = Vue.createApp({
        data() {
            return {
                product,
                submitted: false,
                form: { user_id: 0, quantity: 1 }
            };
        },
        computed: {
            quantityInvalid() {
                return this.form.quantity <= 0 || this.form.quantity > this.product.quantity_available;
            },
            total() {
                return (Number(this.product.price) * Number(this.form.quantity || 0)).toFixed(2);
            }
        },
        methods: {
            formatPrice(value) {
                return Number(value).toFixed(3);
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#purchase-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
