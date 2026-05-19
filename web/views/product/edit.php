<?php
$title = 'Edit Product';
$activeNav = 'products';
$crumb = 'Edit Product';
$productData = [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'price' => (float) $product['price'],
    'quantity' => (int) $product['quantity_available'],
    'product_badge' => (string) ($product['product_badge'] ?? 'none'),
    'old_price' => isset($product['old_price']) ? (float) $product['old_price'] : null,
];
ob_start();
?>

<section id="product-edit-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Product</h1>
            <p class="page-subtitle">Update inventory and pricing details.</p>
        </div>
        <a href="/products" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/products/<?php echo htmlspecialchars((string) $product['id']); ?>/update" @submit="submitted = true">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Product Name</label>
                    <input v-model.trim="form.name" type="text" class="form-control" id="name" name="name" required>
                    <div v-if="submitted && !form.name" class="field-error">Product name is required.</div>
                </div>
                <div class="col-md-3">
                    <label for="price" class="form-label">Price</label>
                    <input v-model.number="form.price" type="number" class="form-control" id="price" name="price" step="0.001" min="0.001" required>
                    <div v-if="submitted && form.price <= 0" class="field-error">Price must be positive.</div>
                </div>
                <div class="col-md-3">
                    <label for="quantity" class="form-label">Quantity Available</label>
                    <input v-model.number="form.quantity" type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                    <div v-if="submitted && form.quantity < 0" class="field-error">Quantity cannot be negative.</div>
                </div>
                <div class="col-md-3">
                    <label for="product_badge" class="form-label">Storefront Badge</label>
                    <select v-model="form.product_badge" class="form-select" id="product_badge" name="product_badge">
                        <option value="none">None</option>
                        <option value="new">New</option>
                        <option value="sale">Sale</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="old_price" class="form-label">Compare Price</label>
                    <input v-model.number="form.old_price" type="number" class="form-control" id="old_price" name="old_price" step="0.001" min="0.001" :required="form.product_badge === 'sale'" :disabled="form.product_badge !== 'sale'">
                    <div v-if="submitted && form.product_badge === 'sale' && !form.old_price" class="field-error">Compare price is required for sale products.</div>
                    <div v-if="submitted && form.product_badge === 'sale' && form.old_price && form.old_price <= form.price" class="field-error">Compare price must be greater than price.</div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="form-hint text-muted">Inventory value: $[[ inventoryValue ]]</span>
                <button type="submit" class="btn btn-primary">Update Product</button>
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
                submitted: false,
                form: { ...product }
            };
        },
        watch: {
            'form.product_badge'(value) {
                if (value !== 'sale') {
                    this.form.old_price = null;
                }
            }
        },
        computed: {
            inventoryValue() {
                return (Number(this.form.price || 0) * Number(this.form.quantity || 0)).toFixed(2);
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#product-edit-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
