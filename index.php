<?php
declare(strict_types=1);

// La propia herramienta aplica lo que audita: CSP con nonce, HSTS, etc.
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'none'; script-src 'nonce-$nonce'; style-src 'nonce-$nonce' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; connect-src 'self'; img-src 'self' data:; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
if (!empty($_SERVER['HTTPS'])) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Auditor de seguridad web · Green PC</title>
<meta name="description" content="Revisa HTTPS, certificado, cabeceras de seguridad y cookies de cualquier sitio público. Gratis y sin guardar resultados.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400&family=Schibsted+Grotesk:wght@400;600;800&display=swap" rel="stylesheet">
<style nonce="<?= $nonce ?>">
:root{
  --bg:#f8fafc;
  --panel:#ffffff;
  --ink:#0f172a;
  --muted:#64748b;
  --line:#e2e8f0;
  --brand:#2563eb;
  --brand-dark:#1d4ed8;
  --accent:#ea580c;
  --accent-hover:#c2410c;
  --ok:#0284c7;
  --warn:#f59e0b;
  --bad:#ea580c;
  --sans:'Schibsted Grotesk',system-ui,-apple-system,'Segoe UI',sans-serif;
  --mono:'JetBrains Mono',ui-monospace,Menlo,Consolas,monospace;
  --radius:10px;
  --shadow:0 4px 16px rgba(15, 23, 42, 0.05);
}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{
  margin:0;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  background-color:var(--bg);
  background-image:
    radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.08) 0px, transparent 50%),
    radial-gradient(at 100% 0%, rgba(234, 88, 12, 0.07) 0px, transparent 45%),
    linear-gradient(180deg, #f8fafc 0%, #edf4fc 100%);
  background-attachment:fixed;
  color:var(--ink);
  font:400 1rem/1.55 var(--sans);
}
a{color:var(--brand);transition:color .2s ease}
a:hover{color:var(--accent)}
:focus-visible{outline:3px solid var(--brand);outline-offset:2px}
.wrap{max-width:1080px;width:100%;margin:0 auto;padding:0 1.5rem}
header.top{padding:1.25rem 0;border-bottom:1px solid rgba(226, 232, 240, 0.8);background:rgba(255, 255, 255, 0.7);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
header.top .wrap{display:flex;align-items:center;gap:.7rem;min-height:48px}.product-brand-link{display:inline-flex;align-items:center;text-decoration:none}.product-logo{width:min(400px,54vw);height:auto;display:block}.brand-endorsement{display:inline-flex;align-items:center;gap:.38rem;color:var(--muted);font-size:.73rem;white-space:nowrap}.brand-endorsement img{height:18px;width:auto;display:block}.brand-endorsement strong{font-weight:600}
.footer-brand{display:flex;align-items:center;gap:.7rem;flex-wrap:wrap}.footer-product-logo{width:225px;height:auto}.footer-greenpc{height:10px;width:auto}.footer-by{font-size:.82rem;color:var(--muted)}
main.wrap{flex:1;padding-top:1rem}
.hero{padding:3rem 0 2rem}
h1{font-size:clamp(2.2rem,5vw,3.4rem);line-height:1.1;letter-spacing:-.03em;font-weight:800;margin:0 0 1.1rem;max-width:18ch;color:#0f172a}
.lead{color:var(--muted);font-size:1.125rem;line-height:1.6;max-width:58ch;margin:0 0 2rem}
form.scan{display:flex;gap:.75rem;flex-wrap:wrap;max-width:680px}

.language-field{display:flex;align-items:center;gap:.45rem;flex:0 1 auto}.language-field label{color:var(--muted);font-size:.82rem;font-weight:600}.language-field select{min-height:48px;padding:.7rem 2rem .7rem .75rem;border:1.5px solid var(--line);border-radius:var(--radius);background:var(--panel);color:var(--ink);font:600 .9rem var(--sans);cursor:pointer}
@media (max-width:620px){.language-field{width:100%}.language-field select{flex:1}}

form.scan input{
  flex:1 1 280px;
  min-width:0;
  padding:.9rem 1.1rem;
  font:400 1.05rem var(--mono);
  background:var(--panel);
  color:var(--ink);
  border:1.5px solid var(--line);
  border-radius:var(--radius);
  box-shadow:0 1px 3px rgba(0,0,0,0.04);
  transition:border-color .2s ease, box-shadow .2s ease;
}
form.scan input:focus{
  outline:none;
  border-color:var(--brand);
  box-shadow:0 0 0 3px rgba(37, 99, 235, 0.18);
}
form.scan button{
  padding:.9rem 1.6rem;
  font:600 1rem var(--sans);
  background:linear-gradient(180deg, var(--brand) 0%, var(--brand-dark) 100%);
  color:#fff;
  border:none;
  border-radius:var(--radius);
  cursor:pointer;
  box-shadow:0 2px 6px rgba(37, 99, 235, 0.28);
  transition:transform .15s ease, box-shadow .2s ease, background .2s ease;
}
form.scan button:hover{
  background:linear-gradient(180deg, var(--brand-dark) 0%, #1e40af 100%);
  box-shadow:0 4px 14px rgba(37, 99, 235, 0.38);
  transform:translateY(-1px);
}
form.scan button:active{
  transform:scale(0.98);
}
form.scan button[disabled]{opacity:.6;cursor:progress;transform:none}
.service-status{display:inline-flex;align-items:center;gap:.5rem;margin:0 0 1rem;padding:.38rem .7rem;border:1px solid rgba(2,132,199,.25);border-radius:999px;background:rgba(2,132,199,.08);color:#075985;font-size:.86rem;font-weight:600}
.service-light{width:.62rem;height:.62rem;border-radius:50%;background:var(--ok);box-shadow:0 0 0 .2rem rgba(2,132,199,.14);animation:pulse 2s ease-in-out infinite}
.service-status[data-state="busy"] .service-light{background:var(--warn);box-shadow:0 0 0 .2rem rgba(245,158,11,.16)}
.service-status[data-state="unavailable"] .service-light{background:var(--bad);box-shadow:0 0 0 .2rem rgba(234,88,12,.16)}
.limit-note{max-width:680px;color:var(--muted);font-size:.84rem;margin:.65rem 0 0}.limit-note strong{color:var(--ink)}
.hint{color:var(--muted);font-size:.875rem;margin:.85rem 0 0}
#out{padding-bottom:3rem}
.risk{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .65rem;border-radius:999px;font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.risk-alto{background:rgba(234,88,12,.13);color:var(--bad)} .risk-medio{background:rgba(245,158,11,.16);color:#a16207} .risk-bajo{background:rgba(2,132,199,.13);color:var(--ok)}
.summary-note{font-size:.83rem;color:var(--muted);margin:.8rem 0 0}
.report-actions{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px solid var(--line)}
.report-actions button{font:600 .88rem var(--sans);border:1px solid var(--line);border-radius:6px;padding:.55rem .7rem;background:var(--panel);color:var(--ink);cursor:pointer}
.report-actions button:hover{border-color:var(--brand);color:var(--brand)}
.priorities{background:linear-gradient(135deg,rgba(37,99,235,.09),rgba(255,255,255,.4));border:1px solid rgba(37,99,235,.22);border-radius:var(--radius);padding:1.15rem 1.2rem;margin:0 0 2rem}
.priorities h2{margin:0 0 .25rem}.priorities>p{color:var(--muted);font-size:.92rem;margin:.2rem 0 .8rem}
.priorities ol{margin:.4rem 0 0;padding-left:1.35rem}.priorities li{padding:.45rem 0}.priorities strong{display:block}.priorities span{font-size:.9rem;color:var(--muted)}
.progress{max-width:680px;background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:1rem 1.2rem;box-shadow:var(--shadow)}
.progress b{display:block;margin-bottom:.25rem}.progress small{color:var(--muted)}
.err{background:var(--panel);border-left:5px solid var(--bad);border-radius:var(--radius);padding:1rem 1.25rem;max-width:680px;margin:0 0 2rem;box-shadow:var(--shadow)}
.report{display:grid;grid-template-columns:minmax(0,300px) minmax(0,1fr);gap:2rem;align-items:start;width:100%;min-width:0;animation:fadeIn .4s ease-out}
.report-cover,.report-content,.summary{min-width:0}
.report-cover{grid-column:1/-1;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.5rem 1.65rem;margin:0;border:1px solid var(--line);border-top:6px solid var(--brand);border-radius:var(--radius);background:linear-gradient(135deg,rgba(37,99,235,.09),var(--panel) 52%);box-shadow:var(--shadow);break-inside:avoid}
.report-brand{display:flex;align-items:center;gap:1.1rem}.report-product-logo{width:168px;height:auto;max-width:42vw}.report-corporate{display:flex;align-items:center;gap:.4rem;margin-top:.35rem;color:var(--muted);font-size:.75rem}.report-corporate img{height:17px;width:auto}.report-cover h2{margin:0;font-size:1.55rem}.report-cover p{margin:.18rem 0;color:var(--muted);font-size:.92rem}.report-ref{text-align:right;font:400 .78rem/1.55 var(--mono);color:var(--muted)}.report-ref strong,.report-ref span{display:block}.report-ref strong{color:var(--ink);font-size:.9rem}
.report-section-label{margin:0 0 .75rem;color:var(--brand);font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.report-signature{margin:1.5rem 0 1rem;padding:1.25rem;border:1px solid var(--line);border-radius:var(--radius);background:linear-gradient(135deg,rgba(37,99,235,.06),var(--panel));display:flex;justify-content:space-between;gap:1.2rem;align-items:end;break-inside:avoid}.report-signature p{margin:0;color:var(--muted);font-size:.84rem;max-width:58ch}.signature-brand{border-bottom:1px solid var(--ink);padding:0 1.5rem .35rem 0}.signature-logo{display:block;width:150px;max-width:42vw;height:auto}.signature-role{font-size:.78rem;color:var(--muted);margin-top:.3rem}.print-footer{display:none}.print-note{flex:1 1 100%;margin:0;color:var(--muted);font-size:.78rem}
.summary{
  position:sticky;
  top:1.5rem;
  background:rgba(255,255,255,0.88);
  backdrop-filter:blur(10px);
  -webkit-backdrop-filter:blur(10px);
  border:1px solid var(--line);
  border-radius:var(--radius);
  padding:1.5rem;
  box-shadow:var(--shadow);
}
.grade{font:800 8.5rem/0.9 var(--sans);letter-spacing:-.05em;margin:0;text-shadow:0 2px 8px rgba(0,0,0,0.08)}
.g-A,.g-B{color:var(--ok)} .g-C,.g-D{color:var(--warn)} .g-F{color:var(--bad)}
.score{font-size:1.15rem;font-weight:600;margin:.25rem 0 1rem}
.summary dl{margin:0;display:grid;grid-template-columns:auto 1fr;gap:.4rem .75rem;font-size:.9rem}
.summary dt{color:var(--muted)} .summary dd{margin:0;font-family:var(--mono);font-size:.85rem;overflow-wrap:anywhere}
.tally{margin:1rem 0 0;padding-top:1rem;border-top:1px solid var(--line);font-size:.95rem}
.cta{margin-top:1rem;padding-top:1rem;border-top:1px solid var(--line)}
.cta a{
  display:inline-block;
  margin-top:.4rem;
  padding:.65rem 1.1rem;
  background:linear-gradient(180deg, #f97316 0%, var(--accent) 100%);
  color:#fff;
  text-decoration:none;
  border-radius:6px;
  font-weight:600;
  box-shadow:0 2px 6px rgba(234, 88, 12, 0.25);
  transition:transform .15s ease, box-shadow .2s ease, background .2s ease;
}
.cta a:hover{
  background:linear-gradient(180deg, var(--accent) 0%, var(--accent-hover) 100%);
  box-shadow:0 4px 12px rgba(234, 88, 12, 0.35);
  transform:translateY(-1px);
}
.group{margin:0 0 2rem}
.group h2{font-size:1.15rem;margin:0 0 .75rem;color:var(--ink)}
details.chk{
  background:var(--panel);
  border:1px solid var(--line);
  border-left-width:5px;
  border-radius:var(--radius);
  margin:0 0 .65rem;
  box-shadow:0 2px 6px rgba(15, 23, 42, 0.04);
  transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
details.chk:hover{
  transform:translateY(-1px);
  box-shadow:0 6px 14px rgba(15, 23, 42, 0.08);
}
details.chk.pass{border-left-color:var(--ok)} details.chk.warn{border-left-color:var(--warn)} details.chk.fail{border-left-color:var(--bad)} details.chk.unknown{border-left-color:var(--muted)}
details.chk summary{cursor:pointer;list-style:none;display:flex;gap:.75rem;align-items:baseline;padding:.85rem 1.1rem}
details.chk summary::-webkit-details-marker{display:none}
.badge{font-size:.8rem;font-weight:600;min-width:5.5rem}
.pass .badge{color:var(--ok)} .warn .badge{color:var(--warn)} .fail .badge{color:var(--bad)} .unknown .badge{color:var(--muted)}
.ttl{font-weight:600;flex:1}
.pts{font-family:var(--mono);font-size:.85rem;color:var(--muted)}
.body{padding:0 1.1rem 1.1rem 7.25rem}
.body p{margin:0 0 .6rem;max-width:70ch}
.body pre{margin:0;padding:.8rem 1rem;background:#0f172a;color:#f1f5f9;border-radius:6px;font:400 .82rem/1.5 var(--mono);overflow-x:auto;white-space:pre}
.body .fixlbl{font-weight:600;font-size:.9rem;margin:.6rem 0 .3rem}
footer{border-top:1px solid var(--line);padding:1.5rem 0 2rem;color:var(--muted);font-size:.9rem;background:rgba(255,255,255,0.5);backdrop-filter:blur(8px)}
@keyframes pulse{50%{opacity:.48;transform:scale(.86)}}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
@media (max-width:820px){
  .report{grid-template-columns:1fr}
  .report-cover,.report-signature{flex-direction:column}.report-ref{text-align:left}
  .summary{position:static}
  .grade{font-size:6.5rem}
  .body{padding-left:1.1rem}
  details.chk summary{flex-wrap:wrap}
}
@page{size:A4 portrait;margin:13mm 13mm 18mm}
@media print{
  header.top,.hero,footer,.cta,.report-actions{display:none!important}
  :root{--panel:#fff;--ink:#000;--muted:#334155;--line:#cbd5e1}
  html,body{background:#fff!important;color:#000!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  main.wrap{max-width:none;padding:0}.report{display:block}.report-cover{box-shadow:none;margin:0 0 8mm;padding:10mm 11mm;border-radius:0}.report-brand img{width:72px}.report-cover h2{font-size:1.45rem}.report-ref{text-align:right}
  .summary{position:static;box-shadow:none;margin:0 0 8mm;padding:8mm;border:1px solid #aaa;break-inside:avoid}.summary .grade{font-size:5.5rem}.summary .report-section-label{display:block}
  .report-signature{margin:8mm 0 0;box-shadow:none}.report-signature p,.report-ref,.report-cover p{color:#333}.report-signature{break-inside:avoid}
  details.chk{break-inside:avoid;box-shadow:none}.priorities{break-inside:avoid}.body pre{white-space:pre-wrap}.print-footer{display:flex;position:fixed;left:0;right:0;bottom:-11mm;justify-content:space-between;border-top:1px solid #9ca3af;padding-top:2mm;color:#475569;font:400 7.5pt/1.3 var(--sans)}
}
@media (prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;animation:none!important;transition:none!important}}
@media (prefers-color-scheme: dark){
  :root{
    --bg:#0b1320;
    --panel:#131d2e;
    --ink:#f1f5f9;
    --muted:#94a3b8;
    --line:#1e2e47;
    --brand:#3b82f6;
    --brand-dark:#2563eb;
    --accent:#f97316;
    --accent-hover:#ea580c;
    --ok:#38bdf8;
    --warn:#fbbf24;
    --bad:#fb923c;
    --shadow:0 4px 20px rgba(0,0,0,0.4);
  }
  body{
    background-color:var(--bg);
    background-image:
      radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.14) 0px, transparent 50%),
      radial-gradient(at 100% 0%, rgba(249, 115, 22, 0.10) 0px, transparent 45%),
      linear-gradient(180deg, #0b1320 0%, #0f172a 100%);
  }
  header.top{background:rgba(19, 29, 46, 0.75);border-bottom-color:var(--line)}
  h1{color:#f8fafc}
  .summary{background:rgba(19, 29, 46, 0.85);border-color:var(--line)}
  footer{background:rgba(11, 19, 32, 0.65);border-top-color:var(--line)}
  .body pre{background:#070c14;color:#e2e8f0}
}
</style>
</head>
<body>
<header class="top"><div class="wrap"><a class="product-brand-link" href="./" aria-label="Web Auditor"><img src="assets/img/web-auditor-logo.webp" alt="Web Auditor" class="product-logo"></a><span class="brand-endorsement">by <a href="https://greenpc.dev" aria-label="Green PC"><img src="assets/img/GPC-logo-pequeño.png" alt="Green PC"></a></span></div></header>

<main class="wrap">
  <section class="hero">
    <h1>¿Qué tan bien protegido está tu sitio web?</h1>
    <p class="lead">Analiza HTTPS, certificado, cabeceras de seguridad y cookies de cualquier sitio público. Recibes una nota de A a F y cómo corregir cada punto.</p>
    <p class="service-status" id="service-status" data-state="ready"><span class="service-light" aria-hidden="true"></span><span id="service-text">Análisis disponible</span></p>
    <form class="scan" id="f" method="get" action="./">
      <span class="language-field"><label for="lang">Idioma del informe</label><select id="lang" name="lang"><option value="es">Español</option><option value="en">English</option><option value="fr">Français</option><option value="it">Italiano</option><option value="pt-PT">Português</option><option value="de">Deutsch</option></select></span>
      <label for="u" hidden>Dominio o URL</label>
      <input id="u" name="u" type="text" inputmode="url" autocomplete="off" autocapitalize="off" spellcheck="false" aria-describedby="scan-hint" placeholder="ejemplo.com" required maxlength="2048">
      <button type="submit" id="b">Analizar sitio</button>
    </form>
    <p class="hint" id="scan-hint">Solo lectura: el análisis suele tardar entre 15 y 60 segundos. No guardamos el dominio ni los resultados.</p>
    <p class="limit-note" id="limit-note" role="status"><strong>Servidor listo.</strong> Puedes enviar una nueva solicitud. Límite: 10 análisis por hora y 4 por sitio por minuto.</p>
  </section>

  <div id="out" role="status" aria-live="polite" aria-busy="false" tabindex="-1"></div>
</main>

<footer><div class="wrap"><div class="footer-brand"><img src="assets/img/web-auditor-logo.webp" alt="Web Auditor" class="footer-product-logo"><span class="footer-by">by</span><a href="https://greenpc.dev"><img src="assets/img/GPC-logo-pequeño.png" alt="Green PC" class="footer-greenpc"></a><span>· La nota es orientativa: no reemplaza una auditoría completa.</span></div></div></footer>

<script nonce="<?= $nonce ?>" src="assets/app.js"></script>
</body>
</html>
