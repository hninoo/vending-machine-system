<?php
$title = 'Products';
$activeNav = 'products';
$crumb = 'Products';
$sort = $sortBy ?? 'id';
$currentDirection = $direction ?? 'ASC';
$nextDirection = ($currentDirection === 'ASC') ? 'DESC' : 'ASC';
$productRows = array_map(static fn (array $product): array => [
    'id' => (int) $product['id'],
    'name' => (string) $product['name'],
    'price' => (float) $product['price'],
    'quantity_available' => (int) $product['quantity_available'],
    'product_badge' => (string) ($product['product_badge'] ?? 'none'),
    'old_price' => isset($product['old_price']) ? (float) $product['old_price'] : null,
], $products);
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
ob_start();
?>

<section id="product-list-app" v-cloak>
    <div class="page-header">
        <div>
            <h1 class="page-title">Products</h1>
            <p class="page-subtitle">Inventory, pricing, and purchase actions for vending machine products.</p>
        </div>
        <a href="/products/create" class="btn btn-primary">Add Product</a>
    </div>

    <div class="metric-strip">
        <div class="metric">
            <div class="metric-label">Products</div>
            <div class="metric-value">[[ products.length ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Units Available</div>
            <div class="metric-value">[[ totalUnits ]]</div>
        </div>
        <div class="metric">
            <div class="metric-label">Inventory Value</div>
            <div class="metric-value">$[[ inventoryValue ]]</div>
        </div>
    </div>

    <div v-if="successMessage" class="alert alert-success">[[ successMessage ]]</div>

    <div class="panel">
        <div class="data-toolbar">
            <input v-model="query" type="search" class="form-control" placeholder="Search products" aria-label="Search products">
            <span class="text-muted">Sorted by <?php echo htmlspecialchars($sort); ?> <?php echo htmlspecialchars($currentDirection); ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th><a href="/products?sort=id&direction=<?php echo $nextDirection; ?>">ID</a></th>
                        <th><a href="/products?sort=name&direction=<?php echo $nextDirection; ?>">Name</a></th>
                        <th><a href="/products?sort=price&direction=<?php echo $nextDirection; ?>">Price</a></th>
                        <th><a href="/products?sort=product_badge&direction=<?php echo $nextDirection; ?>">Badge</a></th>
                        <th><a href="/products?sort=quantity_available&direction=<?php echo $nextDirection; ?>">Quantity</a></th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in filteredProducts" :key="product.id">
                        <td>[[ product.id ]]</td>
                        <td class="fw-semibold">[[ product.name ]]</td>
                        <td>$[[ formatPrice(product.price) ]]</td>
                        <td>
                            <span v-if="product.product_badge !== 'none'" class="badge" :class="product.product_badge === 'sale' ? 'text-bg-danger' : 'text-bg-success'">
                                [[ product.product_badge ]]
                            </span>
                            <span v-else class="text-muted">-</span>
                            <span v-if="product.old_price" class="text-muted"> $[[ formatPrice(product.old_price) ]]</span>
                        </td>
                        <td>
                            <span class="badge" :class="product.quantity_available > 0 ? 'text-bg-success' : 'text-bg-danger'">
                                [[ product.quantity_available ]]
                            </span>
                        </td>
                        <td class="text-end">
                            <a :href="`/products/${product.id}/purchase`" class="btn btn-success btn-sm">Purchase</a>
                            <a :href="`/products/${product.id}/edit`" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="post" :action="`/products/${product.id}/delete`" class="d-inline">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <tr v-if="filteredProducts.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">No products found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <nav class="mt-3" aria-label="Product pagination">
        <ul class="pagination">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/products?page=<?php echo $page - 1; ?>&sort=<?php echo htmlspecialchars($sort); ?>&direction=<?php echo htmlspecialchars($currentDirection); ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="/products?page=<?php echo $i; ?>&sort=<?php echo htmlspecialchars($sort); ?>&direction=<?php echo htmlspecialchars($currentDirection); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="/products?page=<?php echo $page + 1; ?>&sort=<?php echo htmlspecialchars($sort); ?>&direction=<?php echo htmlspecialchars($currentDirection); ?>">Next</a>
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
                products: <?php echo json_encode($productRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
            };
        },
        computed: {
            filteredProducts() {
                const keyword = this.query.trim().toLowerCase();
                if (!keyword) return this.products;
                return this.products.filter((product) => product.name.toLowerCase().includes(keyword));
            },
            totalUnits() {
                return this.products.reduce((total, product) => total + product.quantity_available, 0);
            },
            inventoryValue() {
                const value = this.products.reduce((total, product) => total + product.price * product.quantity_available, 0);
                return value.toFixed(2);
            }
        },
        methods: {
            formatPrice(value) {
                return Number(value).toFixed(3);
            }
        }
    });
    app.config.compilerOptions.delimiters = ['[[', ']]'];
    app.mount('#product-list-app');
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../admin/layout.php';
