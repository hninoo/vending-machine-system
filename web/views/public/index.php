<?php
$title = 'Vending Machine';
$bagIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
$homeIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
$searchIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
$accountIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
$plusIcon = '<svg class="icon-plus" viewBox="0 0 10 10" aria-hidden="true"><line x1="5" y1="1" x2="5" y2="9"/><line x1="1" y1="5" x2="9" y2="5"/></svg>';
$logoIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="3"/><rect x="7" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="13" y="6" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="7" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="13" y="11" width="4" height="3" rx="1" fill="rgba(255,255,255,0.6)"/><rect x="9" y="17" width="6" height="3" rx="1" fill="rgba(255,255,255,0.4)"/></svg>';
$arrowLeft = '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://unpkg.com/vue-demi@0.14.10/lib/index.iife.js"></script>
    <script src="https://unpkg.com/pinia@2.1.7/dist/pinia.iife.prod.js"></script>
</head>
<body class="visible-one-shell">
    <main id="public-shop-app" class="visible-one-app" v-cloak>
        <div class="visible-toast" :class="{ 'is-visible': toast.visible, 'is-error': toast.error }">[[ toast.message ]]</div>

        <header class="visible-header">
            <div class="visible-header-top">
                <template v-if="view === 'shop'">
                    <button v-if="!auth.user" class="lang-badge as-signin" type="button" @click="openAccount" aria-label="Sign in">
                        <?php echo $accountIcon; ?>
                        <strong>Sign in</strong>
                    </button>
                    <button v-else class="visible-user-chip" type="button" @click="openAccount" :title="`Signed in as ${auth.user.username}`">
                        <span class="visible-user-avatar">[[ userInitial ]]</span>
                        <span>[[ auth.user.username ]]</span>
                    </button>
                </template>
                <button v-else class="lang-badge as-back" type="button" @click="view = 'shop'">
                    <?php echo $arrowLeft; ?>
                    <strong>Back</strong>
                </button>
                <a class="visible-logo" href="/" @click.prevent="view = 'shop'">
                    <span><?php echo $logoIcon; ?></span>
                    <strong>Vending Machine</strong>
                </a>
                <a class="visible-cart-btn" href="#" aria-label="Cart" @click.prevent="openCart">
                    <?php echo $bagIcon; ?>
                    <span v-if="cart.count > 0" :class="{ bump: cartBump }">[[ cart.count ]]</span>
                </a>
            </div>

            <template v-if="view === 'shop'">
                <label class="visible-search">
                    <?php echo $searchIcon; ?>
                    <input v-model.trim="search" type="search" placeholder="Search drinks, snacks..." aria-label="Search products" ref="searchInput">
                </label>

                <nav class="visible-cats" aria-label="Product categories">
                    <button
                        v-for="category in categories"
                        :key="category"
                        type="button"
                        class="visible-cat"
                        :class="{ active: activeCategory === category }"
                        @click="activeCategory = category"
                    >
                        [[ category ]]
                    </button>
                </nav>
            </template>
        </header>

        <section v-if="view === 'shop'" class="visible-product-grid" aria-label="Products">
            <article v-for="product in filteredProducts" :key="product.id" class="visible-product-card">
                <span v-if="product.badge === 'new'" class="visible-badge new">New</span>
                <span v-if="product.badge === 'sale'" class="visible-badge sale">Sale</span>
                <img class="visible-product-img" :src="product.image" :alt="`${product.name} product image`">
                <h2>[[ product.name ]]</h2>
                <div class="visible-product-footer">
                    <div>
                        <strong>$[[ formatPrice(product.price) ]]</strong>
                        <del v-if="product.old_price">$[[ formatPrice(product.old_price) ]]</del>
                    </div>
                    <button class="visible-add-btn" type="button" :disabled="product.quantity_available === 0" aria-label="Add to cart" @click="handleAdd(product)">
                        <span class="cart-plus-icon">
                            <?php echo $bagIcon; ?>
                            <?php echo $plusIcon; ?>
                        </span>
                    </button>
                </div>
            </article>

            <div v-if="!loadingProducts && filteredProducts.length === 0" class="visible-empty">
                No products found.
            </div>
            <div v-if="loadingProducts" class="visible-empty">Loading products...</div>
        </section>

        <section v-if="view === 'checkout'" class="visible-checkout-page">
            <article class="visible-drawer-card">
                <div class="visible-drawer-handle"></div>
                <div class="visible-drawer-title">Checkout</div>
                <p class="visible-muted">Review your cart and scan the QR code to pay.</p>

                <div v-if="cart.items.length === 0" class="visible-cart-empty">
                    <p>Your cart is empty</p>
                    <button class="visible-checkout-btn" type="button" @click="view = 'shop'">Browse Products</button>
                </div>

                <template v-else>
                    <div class="visible-checkout-list">
                        <article v-for="item in cart.items" :key="item.id" class="visible-cart-item">
                            <img class="visible-cart-item-img" :src="item.image" :alt="`${item.name} product image`">
                            <div class="visible-cart-item-info">
                                <h2>[[ item.name ]]</h2>
                                <strong>$[[ formatPrice(item.price * item.quantity) ]]</strong>
                                <small>[[ item.quantity ]] x $[[ formatPrice(item.price) ]]</small>
                            </div>
                        </article>
                    </div>

                    <dl class="visible-checkout-summary">
                        <div>
                            <dt>Products</dt>
                            <dd>[[ cart.items.length ]]</dd>
                        </div>
                        <div>
                            <dt>Quantity</dt>
                            <dd>[[ cart.count ]]</dd>
                        </div>
                        <div>
                            <dt>Total</dt>
                            <dd>$[[ formatPrice(cart.total) ]]</dd>
                        </div>
                    </dl>

                    <div class="visible-payment-card">
                        <div class="visible-payment-heading">
                            <span>QR Pay</span>
                            <strong>$[[ formatPrice(cart.total) ]]</strong>
                        </div>
                        <div class="qr-code" aria-label="QR payment code">
                            <span v-for="(cell, idx) in qrCells" :key="idx" :class="{ 'is-dark': cell === '1' }"></span>
                        </div>
                        <p class="visible-muted text-center">Scan this QR code with your mobile banking app.</p>
                    </div>

                    <button class="visible-checkout-btn" type="button" :disabled="processing" @click="completeCheckout">
                        <span v-if="!processing">Pay Completed</span>
                        <span v-else>Processing...</span>
                    </button>
                    <button class="visible-cancel-link" type="button" @click="view = 'shop'">Cancel</button>
                </template>
            </article>
        </section>

        <section v-if="view === 'account'" class="visible-account-page">
            <article class="visible-drawer-card">
                <template v-if="auth.user">
                    <div class="visible-drawer-title">Hi, [[ auth.user.username ]]</div>
                    <p class="visible-muted">You're signed in. Cart purchases will be linked to your account.</p>
                    <button class="visible-checkout-btn" type="button" @click="view = 'shop'">Browse Products</button>
                    <button class="visible-cancel-link" type="button" @click="signOut" :disabled="auth.processing">
                        <span v-if="!auth.processing">Sign out</span>
                        <span v-else>Signing out...</span>
                    </button>
                </template>
                <template v-else>
                    <div class="visible-drawer-title">[[ authMode === 'login' ? 'Sign in' : 'Create account' ]]</div>
                    <p class="visible-muted">Save your purchases, or skip ahead and check out as a guest.</p>

                    <div class="visible-auth-tabs">
                        <button type="button" :class="{ active: authMode === 'login' }" @click="setAuthMode('login')">Login</button>
                        <button type="button" :class="{ active: authMode === 'register' }" @click="setAuthMode('register')">Register</button>
                    </div>

                    <form class="visible-auth-form" @submit.prevent="submitAuth">
                        <label>
                            <span>Username</span>
                            <input v-model.trim="authForm.username" type="text" autocomplete="username" minlength="3" required>
                        </label>
                        <label>
                            <span>Password</span>
                            <input v-model="authForm.password" type="password" :autocomplete="authMode === 'login' ? 'current-password' : 'new-password'" :minlength="authMode === 'login' ? 1 : 6" required>
                            <small v-if="authMode === 'register'" class="visible-auth-hint">At least 6 characters.</small>
                        </label>
                        <label v-if="authMode === 'register'">
                            <span>Confirm password</span>
                            <input v-model="authForm.confirmPassword" type="password" autocomplete="new-password" minlength="6" required>
                            <small v-if="authForm.confirmPassword && !passwordsMatch" class="visible-auth-hint is-error">Passwords don't match.</small>
                        </label>
                        <p v-if="auth.error" class="visible-auth-error">[[ auth.error ]]</p>
                        <button class="visible-checkout-btn" type="submit" :disabled="auth.processing">
                            <span v-if="!auth.processing">[[ authMode === 'login' ? 'Sign in' : 'Create account' ]]</span>
                            <span v-else>Working...</span>
                        </button>
                    </form>

                    <button class="visible-cancel-link" type="button" @click="view = 'shop'">Continue as guest</button>
                    <a class="visible-admin-link" href="/admin/login">Admin? Sign in here.</a>
                </template>
            </article>
        </section>

        <nav class="visible-bottom-nav" aria-label="Public navigation">
            <a href="#" :class="{ active: view === 'shop' }" @click.prevent="view = 'shop'"><?php echo $homeIcon; ?><span>Home</span></a>
            <a href="#" @click.prevent="focusSearch"><?php echo $searchIcon; ?><span>Search</span></a>
            <a href="#" @click.prevent="openCart"><?php echo $bagIcon; ?><span>Cart</span></a>
            <a href="#" :class="{ active: view === 'account' }" @click.prevent="openAccount"><?php echo $accountIcon; ?><span>Account</span></a>
        </nav>

        <transition name="visible-overlay">
            <div v-if="cartOpen" class="visible-cart-overlay" @click.self="closeCart">
                <div class="visible-cart-drawer" role="dialog" aria-label="Cart">
                    <div class="visible-drawer-handle" @click="closeCart"></div>
                    <div class="visible-drawer-title">My Cart</div>

                    <div v-if="cart.items.length === 0" class="visible-cart-empty">
                        <p>Your cart is empty</p>
                    </div>

                    <div v-for="item in cart.items" :key="item.id" class="visible-cart-item drawer-row">
                        <img class="visible-cart-item-img" :src="item.image" :alt="`${item.name} product image`">
                        <div class="visible-cart-item-info">
                            <h2>[[ item.name ]]</h2>
                            <strong>$[[ formatPrice(item.price * item.quantity) ]]</strong>
                        </div>
                        <div class="visible-cart-qty">
                            <button type="button" @click="cart.decrement(item.id)" aria-label="Decrease quantity">-</button>
                            <span class="visible-qty-val">[[ item.quantity ]]</span>
                            <button type="button" @click="handleIncrement(item)" :disabled="item.quantity >= item.quantity_available" aria-label="Increase quantity">+</button>
                        </div>
                    </div>

                    <div v-if="cart.items.length > 0" class="visible-cart-total">
                        <span>Total</span>
                        <strong>$[[ formatPrice(cart.total) ]]</strong>
                    </div>

                    <button v-if="cart.items.length > 0" type="button" class="visible-checkout-btn" @click="goToCheckout">Proceed to Checkout</button>
                </div>
            </div>
        </transition>
    </main>

    <script>
    (() => {
        const { createApp, ref, computed, onMounted } = Vue;
        const { createPinia, defineStore } = Pinia;

        function productImage(name) {
            const key = String(name || '').toLowerCase();
            const palettes = [
                { match: /(coke|cola)/, top: '#8b0000', body: '#e31c16', label: 'Coke' },
                { match: /pepsi/, top: '#004b93', body: '#0065bd', label: 'Pepsi' },
                { match: /fanta/, top: '#cc4a00', body: '#f76c00', label: 'Fanta' },
                { match: /sprite/, top: '#007a2e', body: '#00b050', label: 'Sprite' },
                { match: /water/, top: '#1565c0', body: '#bbdefb', label: 'Water' },
                { match: /coffee/, top: '#5d2a06', body: '#8a4b1a', label: 'Coffee' },
            ];
            const found = palettes.find((p) => p.match.test(key));
            const palette = found || { top: '#009e8e', body: '#00b5a3', label: name };
            const label = String(palette.label).slice(0, 10);
            const svg = `
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="120" viewBox="0 0 80 120">
                    <ellipse cx="40" cy="14" rx="22" ry="9" fill="${palette.top}"/>
                    <rect x="18" y="14" width="44" height="92" fill="${palette.body}"/>
                    <ellipse cx="40" cy="106" rx="22" ry="9" fill="${palette.top}"/>
                    <rect x="18" y="14" width="44" height="46" fill="rgba(255,255,255,0.08)"/>
                    <text x="40" y="68" font-family="Arial Black, Arial, sans-serif" font-size="11" font-weight="900" fill="white" text-anchor="middle">${label}</text>
                    <ellipse cx="40" cy="14" rx="16" ry="5" fill="rgba(255,255,255,0.15)"/>
                </svg>`.trim();
            return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
        }

        function categoryFor(name) {
            const key = String(name || '').toLowerCase();
            if (/water/.test(key)) return 'Water';
            if (/coffee/.test(key)) return 'Coffee';
            if (/(red bull|monster|energy|mountain dew)/.test(key)) return 'Energy Drink';
            if (/(juice|apple|orange|tropicana)/.test(key)) return 'Fruit Drink';
            return 'Soft Drink';
        }

        function decorateProduct(raw) {
            const price = Number(raw.price);
            return {
                id: Number(raw.id),
                name: String(raw.name),
                price,
                old_price: raw.old_price === null || raw.old_price === undefined ? null : Number(raw.old_price),
                quantity_available: Number(raw.quantity_available),
                image: productImage(raw.name),
                category: categoryFor(raw.name),
                badge: raw.product_badge && raw.product_badge !== 'none' ? String(raw.product_badge) : null,
            };
        }

        const STORAGE_KEY = 'vending-machine-cart';

        const useCartStore = defineStore('cart', {
            state: () => ({
                items: [],
            }),
            getters: {
                count: (state) => state.items.reduce((sum, item) => sum + Number(item.quantity || 0), 0),
                total: (state) => state.items.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0),
            },
            actions: {
                hydrate() {
                    try {
                        const raw = localStorage.getItem(STORAGE_KEY);
                        if (raw) {
                            const parsed = JSON.parse(raw);
                            if (Array.isArray(parsed)) {
                                this.items = parsed;
                            }
                        }
                    } catch (_) { /* ignore */ }
                },
                persist() {
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items));
                    } catch (_) { /* ignore */ }
                },
                add(product, qty = 1) {
                    const available = Number(product.quantity_available);
                    if (available <= 0) return { ok: false, reason: 'out_of_stock' };
                    const existing = this.items.find((i) => i.id === product.id);
                    if (existing) {
                        if (existing.quantity >= available) {
                            return { ok: false, reason: 'max' };
                        }
                        existing.quantity = Math.min(existing.quantity + qty, available);
                        existing.quantity_available = available;
                        existing.price = Number(product.price);
                        existing.image = product.image;
                    } else {
                        this.items.push({
                            id: Number(product.id),
                            name: String(product.name),
                            price: Number(product.price),
                            quantity: Math.min(qty, available),
                            quantity_available: available,
                            image: product.image,
                        });
                    }
                    this.persist();
                    return { ok: true };
                },
                increment(id) {
                    const item = this.items.find((i) => i.id === id);
                    if (!item) return { ok: false, reason: 'missing' };
                    if (item.quantity >= item.quantity_available) {
                        return { ok: false, reason: 'max' };
                    }
                    item.quantity += 1;
                    this.persist();
                    return { ok: true };
                },
                decrement(id) {
                    const item = this.items.find((i) => i.id === id);
                    if (!item) return;
                    if (item.quantity <= 1) {
                        this.remove(id);
                        return;
                    }
                    item.quantity -= 1;
                    this.persist();
                },
                remove(id) {
                    this.items = this.items.filter((i) => i.id !== id);
                    this.persist();
                },
                clear() {
                    this.items = [];
                    this.persist();
                },
                reconcileStock(products) {
                    let changed = false;
                    const byId = new Map(products.map((p) => [p.id, p]));
                    this.items = this.items.flatMap((item) => {
                        const fresh = byId.get(item.id);
                        if (!fresh) { changed = true; return []; }
                        const nextQty = Math.min(item.quantity, fresh.quantity_available);
                        if (nextQty <= 0) { changed = true; return []; }
                        if (nextQty !== item.quantity || fresh.quantity_available !== item.quantity_available || fresh.price !== item.price) {
                            changed = true;
                            return [{
                                ...item,
                                quantity: nextQty,
                                quantity_available: fresh.quantity_available,
                                price: fresh.price,
                                image: fresh.image,
                            }];
                        }
                        return [item];
                    });
                    if (changed) this.persist();
                },
                async checkout() {
                    const items = this.items.map((i) => ({ product_id: i.id, quantity: i.quantity }));
                    const response = await fetch('/api/v1/cart/checkout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ items }),
                    });
                    const data = await response.json().catch(() => ({ ok: false, message: 'Unexpected server response.' }));
                    if (data.ok) {
                        this.clear();
                    }
                    return data;
                },
            },
        });

        const app = createApp({
            setup() {
                const cart = useCartStore();
                cart.hydrate();

                const products = ref([]);
                const loadingProducts = ref(true);
                const search = ref('');
                const activeCategory = ref('Best Seller');
                const categories = ['Best Seller', 'Soft Drink', 'Fruit Drink', 'Energy Drink', 'Coffee', 'Water'];
                const cartOpen = ref(false);
                const cartBump = ref(false);
                const view = ref('shop');
                const processing = ref(false);
                const searchInput = ref(null);
                const toast = ref({ visible: false, error: false, message: '' });
                const auth = ref({ user: null, processing: false, error: '' });
                const authMode = ref('login');
                const authForm = ref({ username: '', password: '', confirmPassword: '' });
                let bumpTimer = null;
                let toastTimer = null;

                async function loadProducts() {
                    loadingProducts.value = true;
                    try {
                        const response = await fetch('/api/v1/products', {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await response.json();
                        products.value = Array.isArray(data) ? data.map(decorateProduct) : [];
                        cart.reconcileStock(products.value);
                    } catch (e) {
                        showToast('Could not load products.', true);
                    } finally {
                        loadingProducts.value = false;
                    }
                }

                const filteredProducts = computed(() => {
                    const query = search.value.toLowerCase();
                    return products.value.filter((product) => {
                        const matchesCategory = activeCategory.value === 'Best Seller' || product.category === activeCategory.value;
                        const matchesSearch = !query || product.name.toLowerCase().includes(query);
                        return matchesCategory && matchesSearch;
                    });
                });

                const qrCells = (() => {
                    const rows = [
                        '111111101001111','100000101101001','101110101011101',
                        '101110100010101','101110101110101','100000101000001',
                        '111111101010101','000000001101000','110101111010111',
                        '010011000110010','111001101011101','001110010100011',
                        '101011111001101','100100010111001','111011101010111',
                    ];
                    return rows.join('').split('');
                })();

                function formatPrice(value) {
                    return Number(value).toFixed(2);
                }

                function triggerBump() {
                    if (bumpTimer) clearTimeout(bumpTimer);
                    cartBump.value = false;
                    requestAnimationFrame(() => {
                        cartBump.value = true;
                        bumpTimer = setTimeout(() => { cartBump.value = false; }, 320);
                    });
                }

                function showToast(message, error = false) {
                    toast.value = { visible: true, error, message };
                    if (toastTimer) clearTimeout(toastTimer);
                    toastTimer = setTimeout(() => { toast.value = { ...toast.value, visible: false }; }, 1800);
                }

                function handleAdd(product) {
                    const result = cart.add(product, 1);
                    if (!result.ok) {
                        showToast(result.reason === 'max' ? 'No more stock available.' : 'Out of stock.', true);
                        return;
                    }
                    triggerBump();
                    showToast(`${product.name} added to cart.`);
                }

                function handleIncrement(item) {
                    const result = cart.increment(item.id);
                    if (!result.ok && result.reason === 'max') {
                        showToast('No more stock available.', true);
                    }
                }

                function openCart() {
                    cartOpen.value = true;
                }

                function closeCart() {
                    cartOpen.value = false;
                }

                function goToCheckout() {
                    cartOpen.value = false;
                    view.value = 'checkout';
                }

                function focusSearch() {
                    view.value = 'shop';
                    Vue.nextTick(() => {
                        const el = searchInput.value;
                        if (el) {
                            el.focus();
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }

                async function loadUser() {
                    try {
                        const response = await fetch('/api/v1/customer/me', {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        const data = await response.json();
                        auth.value.user = data && data.user ? data.user : null;
                    } catch (e) { /* ignore */ }
                }

                function openAccount() {
                    auth.value.error = '';
                    view.value = 'account';
                }

                function setAuthMode(mode) {
                    authMode.value = mode;
                    auth.value.error = '';
                    authForm.value.confirmPassword = '';
                }

                const userInitial = computed(() => {
                    const name = auth.value.user?.username || '';
                    return name.slice(0, 1).toUpperCase() || '·';
                });

                const passwordsMatch = computed(() => {
                    return authForm.value.password === authForm.value.confirmPassword;
                });

                async function submitAuth() {
                    if (auth.value.processing) return;
                    if (authMode.value === 'register' && !passwordsMatch.value) {
                        auth.value.error = "Passwords don't match.";
                        return;
                    }
                    auth.value.processing = true;
                    auth.value.error = '';
                    try {
                        const endpoint = authMode.value === 'login'
                            ? '/api/v1/customer/login'
                            : '/api/v1/customer/register';
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                username: authForm.value.username,
                                password: authForm.value.password,
                            }),
                        });
                        const data = await response.json().catch(() => ({ ok: false, message: 'Unexpected server response.' }));
                        if (data.ok && data.user) {
                            auth.value.user = data.user;
                            authForm.value = { username: '', password: '', confirmPassword: '' };
                            showToast(data.message || (authMode.value === 'login' ? 'Welcome back.' : 'Account created.'));
                            view.value = 'shop';
                        } else {
                            auth.value.error = data.message || 'Could not complete request.';
                        }
                    } catch (e) {
                        auth.value.error = 'Network error. Please try again.';
                    } finally {
                        auth.value.processing = false;
                    }
                }

                async function signOut() {
                    if (auth.value.processing) return;
                    auth.value.processing = true;
                    try {
                        await fetch('/api/v1/customer/logout', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        auth.value.user = null;
                        authForm.value = { username: '', password: '', confirmPassword: '' };
                        authMode.value = 'login';
                        showToast('Signed out.');
                        view.value = 'shop';
                    } catch (e) {
                        showToast('Could not sign out.', true);
                    } finally {
                        auth.value.processing = false;
                    }
                }

                async function completeCheckout() {
                    if (processing.value || cart.items.length === 0) return;
                    processing.value = true;
                    try {
                        const data = await cart.checkout();
                        if (data.ok) {
                            showToast(data.message || 'Payment complete.');
                            view.value = 'shop';
                            await loadProducts();
                        } else {
                            showToast(data.message || 'Checkout failed.', true);
                            await loadProducts();
                        }
                    } catch (e) {
                        showToast('Network error. Please try again.', true);
                    } finally {
                        processing.value = false;
                    }
                }

                onMounted(() => {
                    loadProducts();
                    loadUser();
                });

                return {
                    cart,
                    products,
                    loadingProducts,
                    search,
                    activeCategory,
                    categories,
                    cartOpen,
                    cartBump,
                    view,
                    processing,
                    toast,
                    searchInput,
                    qrCells,
                    filteredProducts,
                    formatPrice,
                    handleAdd,
                    handleIncrement,
                    openCart,
                    closeCart,
                    goToCheckout,
                    focusSearch,
                    completeCheckout,
                    auth,
                    authMode,
                    authForm,
                    userInitial,
                    passwordsMatch,
                    openAccount,
                    setAuthMode,
                    submitAuth,
                    signOut,
                };
            },
        });
        app.config.compilerOptions.delimiters = ['[[', ']]'];
        app.use(createPinia());
        app.mount('#public-shop-app');
    })();
    </script>
</body>
</html>
