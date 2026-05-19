<?php
$title = 'Add Product';
$activeNav = 'products-create';
$crumb = 'Add Product';
ob_start();
?>

<section id="product-form-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Add Product</h1>
            <p class="page-subtitle">Create a product with price, stock, and storefront merchandising controls.</p>
        </div>
        <a href="/products" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="panel">
        <form class="panel-body" method="post" action="/products/create" @submit="submitted = true">
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
                <span class="form-hint text-muted">Preview value: $[[ inventoryValue ]]</span>
                <button type="submit" class="btn btn-primary">Save Product</button>
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
                form: { name: '', price: 0, quantity: 0, product_badge: 'none', old_price: null }
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
    app.mount('#product-form-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
