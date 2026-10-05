RAMEN BRO FRONTEND
==================

Struktur project akhirnya cukup 2 folder utama:
  backend/
  frontend/

FRONTEND
1. Extract folder frontend ini ke folder project yang sama dengan backend.
2. Jalankan:
   cd C:\laragon\www\RAMEN-BRO-FULLSTACK-CODED\ramen_bro_final\frontend
   npm install
   npm run dev
3. User: http://localhost:5173/ atau /homepage.html
4. Admin: http://localhost:5173/admin.html

BACKEND ORDER PATCH (WAJIB SEKALI)
---------------------------------
Karena katalog user memakai harga/menu dari desain HTML, sedangkan Product seeder Laravel lama memakai katalog berbeda, copy file:
  frontend\_BACKEND_PATCH\OrderController.php
ke:
  backend\app\Http\Controllers\Api\OrderController.php

Lalu di terminal backend:
  php artisan optimize:clear
  php artisan serve --port=8000

ALUR
User -> Detail -> Keranjang/Checkout -> Payment QRIS/Kasir -> POST /api/orders -> Database Laravel -> Admin Transaksi.
Admin melakukan refresh otomatis setiap 4 detik dan dapat konfirmasi pembayaran + ubah status.

Catatan: QRIS adalah dummy/demo sesuai desain asli.
