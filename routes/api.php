<?php

// File ini ada karena bootstrap/app.php mereferensikannya dengan
// api: __DIR__ . '/../routes/api.php'. Semua route API publik dideklarasikan
// di routes/web.php (prefix '/api'). File ini sengaja kosong.
//
// Alasan: di Laravel 11/12 default tidak ada routes/api.php. Kita pakai prefix
// /api di web.php supaya CSRF + session middleware default ke route tsb.

return [];