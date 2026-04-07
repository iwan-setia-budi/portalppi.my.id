<?php
include_once 'koneksi.php';
?>


<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Portal PPI — Sistem Pencegahan & Pengendalian Infeksi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;500;700;900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg1:#0f172a;
    --bg2:#0b6173;
    --accent:#00c2d8;
    --muted:#9aa7b2;
    --white:#fff;
    --radius:14px;
    --maxw:1200px;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0;
    font-family:'Inter',sans-serif;
    background:linear-gradient(180deg,var(--bg1) 0%,var(--bg2) 100%);
    color:var(--white);
    display:flex;flex-direction:column;align-items:center;
  }
  .wrap{width:100%;max-width:var(--maxw);padding:20px;}
  
  /* HEADER */
  header{
    position:sticky;top:0;z-index:40;
    display:flex;align-items:center;justify-content:space-between;
    background:rgba(255,255,255,0.04);backdrop-filter:blur(6px);
    padding:14px 22px;border-radius:12px;margin:12px 0;
  }
  .brand{display:flex;gap:12px;align-items:center}
  .logo{
    width:48px;height:48px;border-radius:10px;
    background:linear-gradient(135deg,var(--accent),#6ce1e9);
    display:flex;align-items:center;justify-content:center;
    font-weight:700;color:var(--bg1);font-family:'Poppins';
  }
  nav{display:flex;gap:18px;align-items:center;transition:all .3s ease;}
  nav a{color:var(--white);text-decoration:none;font-weight:600;opacity:0.95}
  nav a.cta{background:var(--accent);color:var(--bg1);padding:8px 14px;border-radius:10px}
  
  /* HAMBURGER MENU */
  .menu-toggle{
    display:none;flex-direction:column;cursor:pointer;
    width:26px;gap:5px;
  }
  .menu-toggle span{
    display:block;height:3px;background:var(--white);border-radius:2px;
    transition:0.3s;
  }
  .menu-open .bar1{transform:rotate(45deg) translate(5px,5px);}
  .menu-open .bar2{opacity:0;}
  .menu-open .bar3{transform:rotate(-45deg) translate(6px,-6px);}
  
  /* HERO SECTION */
  .hero{
    width:100%;display:grid;grid-template-columns:1fr 460px;gap:28px;align-items:center;
    margin:28px 0;padding:40px;border-radius:18px;
    background:rgba(255,255,255,0.03);
  }
  .hero-left{padding:10px 6%;}
  .eyebrow{display:inline-block;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,0.05);color:var(--muted);font-weight:600;margin-bottom:14px}
  .hero h2{font-size:2.05rem;line-height:1.2;margin:0 0 12px;font-weight:800}
  .typewrap{color:var(--accent)}
  .hero p{color:rgba(255,255,255,0.85);margin-bottom:18px;max-width:640px}
  .btn{padding:12px 18px;border-radius:12px;border:0;font-weight:700;cursor:pointer;transition:0.25s;}
  .btn-primary{background:var(--accent);color:var(--bg1)}
  .btn-ghost{background:transparent;border:1px solid rgba(255,255,255,0.1);color:var(--white)}
  .btn:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,0.3)}
  .stats{display:flex;gap:18px;margin-top:20px;flex-wrap:wrap}
  .stat{flex:1;background:rgba(255,255,255,0.05);padding:14px;border-radius:12px;text-align:center;min-width:100px}
  .stat .num{font-size:1.6rem;font-weight:800;color:var(--accent)}
  .stat .lbl{color:var(--muted);font-weight:600;margin-top:6px}
  .hero-right{display:flex;align-items:center;justify-content:center;padding:18px}
  .mock{
    width:100%;max-width:420px;border-radius:16px;padding:18px;
    background:rgba(255,255,255,0.03);box-shadow:0 8px 40px rgba(0,0,0,0.45)
  }
  .mock .screen{height:360px;background:#06303a;border-radius:10px;padding:14px;color:var(--muted);font-size:13px}
  
  /* FITUR */
  section{width:100%;margin:26px 0;}
  .features{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
  .feature{background:rgba(255,255,255,0.05);padding:20px;border-radius:12px;text-align:left}
  .feature h4{margin:8px 0 6px;color:var(--white)}
  .feature p{margin:0;color:var(--muted);font-size:14px}
  .integrate{display:flex;gap:18px;align-items:center;flex-wrap:wrap;padding:20px}
  .module{background:rgba(255,255,255,0.05);padding:12px 16px;border-radius:12px;min-width:160px;text-align:center}
  .module h5{margin:8px 0 6px;font-size:15px;color:var(--white)}
  .module p{margin:0;color:var(--muted);font-size:13px}

  /* TESTIMONI */
  #testimoni{background:linear-gradient(180deg,#0e2b46 0%,#081a2f 100%);padding:80px 10%;text-align:center;}
  #testimoni h3{font-size:1.8rem;color:var(--accent);margin-bottom:40px}
  .testimonial-card{
    background:rgba(255,255,255,0.06);border-radius:20px;
    max-width:800px;margin:0 auto;padding:50px 40px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    backdrop-filter:blur(10px);
  }
  .testimonial-card .quote{font-style:italic;font-size:1.2rem;color:#eaf6ff;margin-bottom:20px;line-height:1.7}
  .testimonial-card .author{color:#9bb6c9;font-weight:600}

  /* FOOTER */
  footer{
    background:linear-gradient(180deg,#081a2f 0%,#071322 100%);
    padding:80px 10% 20px;color:rgba(255,255,255,0.85);
  }
  .footer-content{
    display:grid;grid-template-columns:2fr 1fr 1fr;gap:50px;margin-bottom:40px;
  }
  .footer-logo{
    background:linear-gradient(135deg,var(--accent),#6ce1e9);
    width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;
    font-weight:800;color:var(--bg1);font-size:1.2rem;margin-bottom:12px;
  }
  .footer h4{color:var(--accent);margin:0 0 10px}
  .footer h5{color:var(--accent);margin-bottom:12px}
  .footer-desc{font-size:0.95rem;line-height:1.6;color:rgba(255,255,255,0.7)}
  .footer-column ul{list-style:none;padding:0;margin:0}
  .footer-column li{margin-bottom:8px;font-size:0.95rem}
  .footer-column a{color:rgba(255,255,255,0.85);text-decoration:none;transition:color .3s}
  .footer-column a:hover{color:var(--accent)}
  .footer-bottom{text-align:center;border-top:1px solid rgba(255,255,255,0.1);padding-top:15px;font-size:0.9rem;color:rgba(255,255,255,0.6)}

  /* RESPONSIVE */
  @media(max-width:900px){
    .features{grid-template-columns:repeat(2,1fr)}
    .footer-content{grid-template-columns:1fr 1fr}
  }
  @media(max-width:700px){
    .hero{grid-template-columns:1fr;padding:24px}
    .hero h2{font-size:1.6rem}
    .hero p{font-size:0.95rem}
    .features{grid-template-columns:1fr}
    .footer-content{grid-template-columns:1fr;text-align:center}
    .footer-logo{margin:0 auto}
    nav{display:none;flex-direction:column;gap:14px;background:rgba(0,0,0,0.7);padding:18px;border-radius:10px;position:absolute;top:70px;right:10px;width:200px;}
    nav.show{display:flex;}
    .menu-toggle{display:flex;}
  }
</style>
</head>
<body>
<div class="wrap">
<header>
  <div class="brand">
    <div class="logo">PPI</div>
    <div>
      <h1 style="margin:0;font-size:1.1rem;">Portal PPI</h1>
      <div style="font-size:12px;color:var(--muted)">Sistem Pencegahan & Pengendalian Infeksi</div>
    </div>
  </div>
  <nav>
    <a href="#fitur">Fitur</a>
    <a href="#integrasi">Integrasi</a>
    <a href="#testimoni">Testimoni</a>
    <a href="login.php" class="cta">Login</a>
  </nav>
  <div class="menu-toggle" onclick="toggleMenu(this)">
    <span class="bar1"></span>
    <span class="bar2"></span>
    <span class="bar3"></span>
  </div>
</header>

<section class="hero">
  <div class="hero-left">
    <div class="eyebrow">Sistem Terintegrasi PPI</div>
    <h2>Digitalisasi Program <span style="opacity:0.9">Pencegahan & Pengendalian Infeksi</span><br><span class="typewrap" id="typewriter"></span></h2>
    <p>Portal PPI membantu rumah sakit mengelola program PPI secara terintegrasi: audit, surveilans, pelaporan, dan manajemen tindakan korektif — dengan dashboard real-time dan pelaporan yang dapat dipercaya.</p>
    <div class="hero-actions">
      <button class="btn btn-primary" onclick="location.href='login.php'">Masuk ke Sistem</button>
      <button class="btn btn-ghost" onclick="document.getElementById('fitur').scrollIntoView({behavior:'smooth'})">Pelajari Lebih Lanjut</button>
    </div>
    <div class="stats">
      <div class="stat"><div class="num" data-target="124">0</div><div class="lbl">Audit/Tahun</div></div>
      <div class="stat"><div class="num" data-target="98">0</div><div class="lbl">Unit Terdokumentasi</div></div>
      <div class="stat"><div class="num" data-target="7">0</div><div class="lbl">Modul Terintegrasi</div></div>
    </div>
  </div>
  <div class="hero-right">
    <div class="mock">
      <div class="screen">Preview Dashboard</div>
    </div>
  </div>
</section>

<section id="fitur">
  <h3 style="font-size:20px;color:var(--accent)">Fitur Unggulan</h3>
  <div class="features">
    <div class="feature"><h4>Dashboard Real-time</h4><p>Visualisasi KPI, tren surveilans, dan notifikasi kejadian dalam satu tampilan interaktif.</p></div>
    <div class="feature"><h4>Manajemen Program</h4><p>Rencanakan, jadwalkan, dan dokumentasikan seluruh kegiatan PPI— audit, pelatihan, dan inspeksi.</p></div>
    <div class="feature"><h4>Pelaporan & Surveilans</h4><p>Form pelaporan cepat, notifikasi follow-up, dan integrasi data untuk pelaporan regulator.</p></div>
  </div>
</section>

<section id="integrasi">
  <h3 style="font-size:20px;color:var(--accent)">Integrasi Modul</h3>
  <div class="integrate">
    <div class="module"><h5>Audit</h5><p>Checklist, foto bukti, rekomendasi</p></div>
    <div style="color:var(--muted)">↔</div>
    <div class="module"><h5>Surveilans</h5><p>Tren infeksi, filter per-unit</p></div>
    <div style="color:var(--muted)">↔</div>
    <div class="module"><h5>Pelaporan</h5><p>Export PDF, Excel, API</p></div>
  </div>
</section>

<section id="testimoni">
  <h3>Apa kata pengguna</h3>
  <div class="testimonial-card">
    <p class="quote">“Portal PPI memudahkan audit internal kami, laporan jadi cepat dan terstruktur — tim kami lebih fokus pada tindakan korektif.”</p>
    <p class="author">— Ketua Komite, IPCD, IPCN, IPCLN, dan PJ PPI Unit</p>
  </div>
</section>

<footer>
  <div class="footer-content">
    <div class="footer-column">
      <div class="footer-logo">PPI</div>
      <h4>Portal PPI</h4>
      <p class="footer-desc">Sistem Pencegahan & Pengendalian Infeksi terpadu yang membantu rumah sakit mewujudkan pelayanan bermutu, aman, dan siap akreditasi.</p>
    </div>
    <div class="footer-column">
      <h5>Menu Cepat</h5>
      <ul>
        <li><a href="#fitur">Fitur</a></li>
        <li><a href="#integrasi">Integrasi</a></li>
        <li><a href="#testimoni">Testimoni</a></li>
        <li><a href="login.php">Login</a></li>
      </ul>
    </div>
    <div class="footer-column">
      <h5>Kontak Kami</h5>
      <ul>
        <li>🏥 Sistem Terintergrasi PPI</li>
        <li>📧 info@portalppi.my.id</li>
        <li>📞 (0717) 123-456</li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    © <span id="year"></span> Portal PPI — Sistem  PPI Rumah Sakit • All Rights Reserved
  </div>
</footer>
</div>

<script>
function toggleMenu(x){
  x.classList.toggle("menu-open");
  document.querySelector('nav').classList.toggle('show');
}

const words=["Terintegrasi.","Aman & Terkendali.","Data-Driven.","Siap Akreditasi."];
const tw=document.getElementById("typewriter");let idx=0,pos=0,forward=true;
function typeLoop(){const w=words[idx];if(forward){pos++;tw.textContent=w.slice(0,pos);
if(pos===w.length){forward=false;setTimeout(typeLoop,900);return;}}
else{pos--;tw.textContent=w.slice(0,pos);
if(pos===0){forward=true;idx=(idx+1)%words.length;}}
setTimeout(typeLoop,80);}typeLoop();

const counters=document.querySelectorAll('.num');
const ease=n=>1-Math.pow(1-n,3);
function runCounters(){counters.forEach(el=>{const t=+el.dataset.target;
const start=performance.now();function step(){
const p=Math.min((performance.now()-start)/1200,1);
el.textContent=Math.floor(ease(p)*t);if(p<1)requestAnimationFrame(step);}requestAnimationFrame(step);});}
function inView(el){const r=el.getBoundingClientRect();return r.top<innerHeight&&r.bottom>=0;}
let done=false;window.addEventListener('scroll',()=>{if(!done&&inView(document.querySelector('.stats'))){runCounters();done=true;}},{passive:true});
if(inView(document.querySelector('.stats'))){runCounters();done=true;}
document.getElementById('year').textContent=new Date().getFullYear();
</script>
</body>
</html>
