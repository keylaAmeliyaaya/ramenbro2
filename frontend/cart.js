/**
 * cart.js
 * Logic keranjang belanja bersama (shared) untuk homepage, detailpage, dan checkoutpage.
 * Disimpan di localStorage supaya datanya tetap ada walau pindah antar halaman (karena
 * ini multi-page site, bukan single page app, jadi butuh persistence di browser).
 */

const CART_KEY = 'cc_cart';
const API_BASE = 'http://127.0.0.1:8000/api';
const CUSTOMER_KEY = 'cc_customer';

// Format angka jadi format Rupiah, contoh: 88000 -> "Rp 88.000"
function formatRupiah(number) {
    return `Rp ${Math.round(number).toLocaleString('id-ID').replace(/,/g, '.')}`;
}

// Ambil isi keranjang dari localStorage
function getCart() {
    try {
        const raw = localStorage.getItem(CART_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        console.error('Gagal membaca keranjang:', e);
        return [];
    }
}

// Simpan array keranjang ke localStorage
function saveCart(cart) {
    try {
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
    } catch (e) {
        console.error('Gagal menyimpan keranjang:', e);
    }
}

/**
 * Tambah item ke keranjang.
 * item: { productId, name, image, price, variant, qty }
 * Kalau produk + variant (level pedas) yang sama sudah ada, qty-nya digabung (ditambah),
 * bukan bikin baris baru.
 */
function addToCart(item) {
    const cart = getCart();
    const cartId = `${item.productId}__${item.variant}`.replace(/\s+/g, '_');
    const existing = cart.find(c => c.cartId === cartId);

    if (existing) {
        existing.qty += item.qty;
    } else {
        cart.push({ ...item, cartId });
    }

    saveCart(cart);
    return cart;
}

/**
 * Update qty item di keranjang berdasarkan cartId.
 * Kalau qty baru <= 0, item otomatis dihapus dari keranjang.
 */
function updateCartQty(cartId, newQty) {
    let cart = getCart();

    if (newQty <= 0) {
        cart = cart.filter(c => c.cartId !== cartId);
    } else {
        const it = cart.find(c => c.cartId === cartId);
        if (it) it.qty = newQty;
    }

    saveCart(cart);
    return cart;
}

// Total jumlah item (semua qty dijumlah) di keranjang
function getCartTotalQty() {
    return getCart().reduce((sum, i) => sum + i.qty, 0);
}

// Total harga (subtotal) di keranjang
function getCartSubtotal() {
    return getCart().reduce((sum, i) => sum + i.qty * i.price, 0);
}

// Kosongkan keranjang (dipakai nanti setelah pembayaran berhasil, misalnya)
function clearCart() {
    localStorage.removeItem(CART_KEY);
}

function getCustomer(){ try { return JSON.parse(localStorage.getItem(CUSTOMER_KEY) || '{}'); } catch(e){ return {}; } }
function saveCustomer(data){ localStorage.setItem(CUSTOMER_KEY, JSON.stringify(data || {})); }
function orderCodeFromId(id){ return 'RB-' + String(id).padStart(5,'0'); }
function apiJson(path, options={}){ return fetch(API_BASE + path, {headers:{'Accept':'application/json','Content-Type':'application/json',...(options.headers||{})}, ...options}).then(async r=>{ const d=await r.json().catch(()=>({})); if(!r.ok){ throw new Error(d.message || 'Request API gagal'); } return d; }); }
function normalizeOrders(payload){ const d=payload?.data; if(Array.isArray(d)) return d; if(Array.isArray(d?.data)) return d.data; return []; }
