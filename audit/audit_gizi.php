<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Halaman Dalam Pengembangan</title>

<!-- Font Awesome (ICON AMAN) -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* =========================
   RESET & DASAR
========================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Poppins", "Segoe UI", sans-serif;
    min-height: 100vh;
    background: linear-gradient(135deg, #e8f1ff, #f6f9ff);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1f2937;
}

/* =========================
   CARD UTAMA
========================= */
.dev-card {
    background: #ffffff;
    max-width: 480px;
    width: 90%;
    padding: 40px 35px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 25px 60px rgba(0, 70, 160, 0.15);
    animation: fadeUp 0.8s ease;
}

/* =========================
   ICON
========================= */
.dev-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 36px;
}

/* =========================
   TEXT
========================= */
.dev-card h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
}

.dev-card p {
    font-size: 15px;
    color: #6b7280;
    line-height: 1.7;
    margin-bottom: 28px;
}

/* =========================
   BUTTON
========================= */
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: #fff;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 35px rgba(37, 99, 235, 0.45);
}

/* =========================
   ANIMASI
========================= */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 480px) {
    .dev-card {
        padding: 30px 22px;
    }

    .dev-card h1 {
        font-size: 20px;
    }
}
</style>
</head>

<body>

<div class="dev-card">
    <div class="dev-icon">
        <i class="fa-solid fa-screwdriver-wrench"></i>
    </div>

    <h1>Halaman Masih Dalam Pengembangan</h1>

    <p>
        Mohon maaf, halaman ini sedang kami kembangkan untuk memberikan
        pengalaman yang lebih baik.  
        Silakan kembali atau cek kembali nanti.
    </p>

    <a href="javascript:history.back()" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
</div>

</body>
</html>
