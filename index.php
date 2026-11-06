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
.report{display:grid;grid-template-columns:300px 1fr;gap:2rem;align-items:start;animation:fadeIn .4s ease-out}
.report-cover{grid-column:1/-1;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.5rem 1.65rem;margin:0;border:1px solid var(--line);border-top:6px solid var(--brand);border-radius:var(--radius);background:linear-gradient(135deg,rgba(37,99,235,.09),var(--panel) 52%);box-shadow:var(--shadow);break-inside:avoid}
.report-brand{display:flex;align-items:center;gap:1.1rem}.report-product-logo{width:168px;height:auto;max-width:42vw}.report-corporate{display:flex;align-items:center;gap:.4rem;margin-top:.35rem;color:var(--muted);font-size:.75rem}.report-corporate img{height:17px;width:auto}.report-cover h2{margin:0;font-size:1.55rem}.report-cover p{margin:.18rem 0;color:var(--muted);font-size:.92rem}.report-ref{text-align:right;font:400 .78rem/1.55 var(--mono);color:var(--muted)}.report-ref strong,.report-ref span{display:block}.report-ref strong{color:var(--ink);font-size:.9rem}
.report-section-label{margin:0 0 .75rem;color:var(--brand);font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.report-signature{margin:1.5rem 0 1rem;padding:1.25rem;border:1px solid var(--line);border-radius:var(--radius);background:linear-gradient(135deg,rgba(37,99,235,.06),var(--panel));display:flex;justify-content:space-between;gap:1.2rem;align-items:end;break-inside:avoid}.report-signature p{margin:0;color:var(--muted);font-size:.84rem;max-width:58ch}.signature-name{font:600 1.45rem/1.1 Georgia,serif;color:var(--ink);border-bottom:1px solid var(--ink);padding:0 1.5rem .3rem 0}.signature-role{font-size:.78rem;color:var(--muted);margin-top:.3rem}.print-footer{display:none}.print-note{flex:1 1 100%;margin:0;color:var(--muted);font-size:.78rem}
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
details.chk.pass{border-left-color:var(--ok)} details.chk.warn{border-left-color:var(--warn)} details.chk.fail{border-left-color:var(--bad)}
details.chk summary{cursor:pointer;list-style:none;display:flex;gap:.75rem;align-items:baseline;padding:.85rem 1.1rem}
details.chk summary::-webkit-details-marker{display:none}
.badge{font-size:.8rem;font-weight:600;min-width:5.5rem}
.pass .badge{color:var(--ok)} .warn .badge{color:var(--warn)} .fail .badge{color:var(--bad)}
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

<script nonce="<?= $nonce ?>">
(() => {
  const $ = id => document.getElementById(id);
  const form = $('f'), input = $('u'), lang = $('lang'), btn = $('b'), out = $('out');
  const serviceStatus = $('service-status'), serviceText = $('service-text'), limitNote = $('limit-note');
  const LOCALES = {
    es:{code:'es-EC',ready:'Listo para un nuevo análisis',available:'Análisis disponible',scan:'Analizar sitio',busy:'Análisis en curso',waiting:'Esperando para una nueva solicitud',wait:'Espera ',server:'Servidor listo. ',protection:'Protección activa. ',request:'Solicitud en curso. ',report:'Informe de seguridad web',automated:'Evaluación externa automatizada de la postura básica de seguridad.',executive:'Resumen ejecutivo',score:'de 100 puntos',risk:'Riesgo ',site:'Sitio',cipher:'Cifrado',issuer:'Emisor',response:'Respuesta',redirects:'Redirecciones',correct:'Correcto',improvable:'Mejorable',failure:'Falla',print:'Imprimir informe / Guardar PDF',printNote:'Para un PDF profesional: A4, una página por hoja y encabezados/pies del navegador desactivados.',priorities:'Corrige primero esto',priorityIntro:'Estas acciones producen la mayor mejora de seguridad.',fix:'Cómo corregirlo',issued:'Emitido',dateUnavailable:'Fecha no disponible',reference:'Referencia de informe',footer:'La nota es orientativa: no reemplaza una auditoría completa.',external:'Evaluación externa orientativa; no reemplaza un pentest.',reportFooter:'Green PC · Informe de seguridad web',signature:'Firma de emisión del informe',scope:'Alcance: evaluación externa automatizada del sitio indicado, sin acceso interno ni revisión de código. Para priorizar una corrección o realizar una auditoría profunda, contacta a Green PC.'},
    en:{code:'en-GB',ready:'Ready for a new analysis',available:'Analysis available',scan:'Analyse site',busy:'Analysis in progress',waiting:'Waiting for a new request',wait:'Wait ',server:'Server ready. ',protection:'Protection active. ',request:'Request in progress. ',report:'Web security report',automated:'Automated external assessment of the basic security posture.',executive:'Executive summary',score:'out of 100 points',risk:'Risk ',site:'Site',cipher:'Cipher',issuer:'Issuer',response:'Response',redirects:'Redirects',correct:'Compliant',improvable:'Needs improvement',failure:'Failure',print:'Print report / Save PDF',printNote:'For a professional PDF: A4, one page per sheet, with browser headers and footers disabled.',priorities:'Fix these first',priorityIntro:'These actions deliver the greatest security improvement.',fix:'How to fix it',issued:'Issued',dateUnavailable:'Date unavailable',reference:'Report reference',footer:'The score is guidance only; it does not replace a complete audit.',external:'External guidance only; it does not replace a penetration test.',reportFooter:'Green PC · Web security report',signature:'Report issuance signature',scope:'Scope: automated external assessment of the indicated site, with no internal access or code review. Contact Green PC to prioritize remediation or conduct an in-depth audit.'},
    fr:{code:'fr-FR',ready:'Prêt pour une nouvelle analyse',available:'Analyse disponible',scan:'Analyser le site',busy:'Analyse en cours',waiting:'En attente d’une nouvelle demande',wait:'Attendez ',server:'Serveur prêt. ',protection:'Protection active. ',request:'Demande en cours. ',report:'Rapport de sécurité web',automated:'Évaluation externe automatisée de la posture de sécurité de base.',executive:'Résumé exécutif',score:'sur 100 points',risk:'Risque ',site:'Site',cipher:'Chiffrement',issuer:'Émetteur',response:'Réponse',redirects:'Redirections',correct:'Conforme',improvable:'À améliorer',failure:'Défaillance',print:'Imprimer / Enregistrer en PDF',printNote:'Pour un PDF professionnel : A4, une page par feuille et en-têtes/pieds du navigateur désactivés.',priorities:'À corriger en priorité',priorityIntro:'Ces actions apportent la plus grande amélioration de sécurité.',fix:'Comment corriger',issued:'Émis',dateUnavailable:'Date indisponible',reference:'Référence du rapport',footer:'La note est indicative et ne remplace pas un audit complet.',external:'Évaluation externe indicative ; elle ne remplace pas un test d’intrusion.',reportFooter:'Green PC · Rapport de sécurité web',signature:'Signature d’émission du rapport',scope:'Portée : évaluation externe automatisée du site indiqué, sans accès interne ni revue de code. Contactez Green PC pour prioriser les corrections ou réaliser un audit approfondi.'},
    it:{code:'it-IT',ready:'Pronto per una nuova analisi',available:'Analisi disponibile',scan:'Analizza il sito',busy:'Analisi in corso',waiting:'In attesa di una nuova richiesta',wait:'Attendi ',server:'Server pronto. ',protection:'Protezione attiva. ',request:'Richiesta in corso. ',report:'Rapporto di sicurezza web',automated:'Valutazione esterna automatizzata della postura di sicurezza di base.',executive:'Riepilogo esecutivo',score:'su 100 punti',risk:'Rischio ',site:'Sito',cipher:'Cifratura',issuer:'Emittente',response:'Risposta',redirects:'Reindirizzamenti',correct:'Conforme',improvable:'Da migliorare',failure:'Errore',print:'Stampa rapporto / Salva PDF',printNote:'Per un PDF professionale: A4, una pagina per foglio e intestazioni/piè di pagina del browser disattivati.',priorities:'Correggi prima questi punti',priorityIntro:'Queste azioni producono il maggiore miglioramento della sicurezza.',fix:'Come correggere',issued:'Emesso',dateUnavailable:'Data non disponibile',reference:'Riferimento rapporto',footer:'Il punteggio è solo indicativo e non sostituisce un audit completo.',external:'Valutazione esterna indicativa; non sostituisce un penetration test.',reportFooter:'Green PC · Rapporto di sicurezza web',signature:'Firma di emissione del rapporto',scope:'Ambito: valutazione esterna automatizzata del sito indicato, senza accesso interno né revisione del codice. Contatta Green PC per definire le priorità o svolgere un audit approfondito.'},
    'pt-PT':{code:'pt-PT',ready:'Pronto para uma nova análise',available:'Análise disponível',scan:'Analisar site',busy:'Análise em curso',waiting:'A aguardar um novo pedido',wait:'Aguarde ',server:'Servidor pronto. ',protection:'Proteção ativa. ',request:'Pedido em curso. ',report:'Relatório de segurança web',automated:'Avaliação externa automatizada da postura básica de segurança.',executive:'Resumo executivo',score:'em 100 pontos',risk:'Risco ',site:'Site',cipher:'Cifra',issuer:'Emissor',response:'Resposta',redirects:'Redirecionamentos',correct:'Conforme',improvable:'A melhorar',failure:'Falha',print:'Imprimir relatório / Guardar PDF',printNote:'Para um PDF profissional: A4, uma página por folha e cabeçalhos/rodapés do navegador desativados.',priorities:'Corrija primeiro estes pontos',priorityIntro:'Estas ações proporcionam a maior melhoria de segurança.',fix:'Como corrigir',issued:'Emitido',dateUnavailable:'Data indisponível',reference:'Referência do relatório',footer:'A classificação é apenas indicativa e não substitui uma auditoria completa.',external:'Avaliação externa indicativa; não substitui um teste de intrusão.',reportFooter:'Green PC · Relatório de segurança web',signature:'Assinatura de emissão do relatório',scope:'Âmbito: avaliação externa automatizada do site indicado, sem acesso interno ou revisão de código. Contacte a Green PC para priorizar correções ou realizar uma auditoria aprofundada.'},
    de:{code:'de-DE',ready:'Bereit für eine neue Analyse',available:'Analyse verfügbar',scan:'Website analysieren',busy:'Analyse läuft',waiting:'Warten auf eine neue Anfrage',wait:'Warten ',server:'Server bereit. ',protection:'Schutz aktiv. ',request:'Anfrage läuft. ',report:'Web-Sicherheitsbericht',automated:'Automatisierte externe Bewertung der grundlegenden Sicherheitslage.',executive:'Management-Zusammenfassung',score:'von 100 Punkten',risk:'Risiko ',site:'Website',cipher:'Verschlüsselung',issuer:'Aussteller',response:'Antwort',redirects:'Weiterleitungen',correct:'Konform',improvable:'Verbesserbar',failure:'Fehler',print:'Bericht drucken / PDF speichern',printNote:'Für ein professionelles PDF: A4, eine Seite pro Blatt sowie Browser-Kopf- und Fußzeilen deaktivieren.',priorities:'Zuerst beheben',priorityIntro:'Diese Maßnahmen bringen die größte Sicherheitsverbesserung.',fix:'So beheben Sie es',issued:'Ausgestellt',dateUnavailable:'Datum nicht verfügbar',reference:'Berichtsreferenz',footer:'Die Bewertung dient nur als Orientierung und ersetzt kein vollständiges Audit.',external:'Orientierende externe Bewertung; sie ersetzt keinen Penetrationstest.',reportFooter:'Green PC · Web-Sicherheitsbericht',signature:'Ausstellungsbestätigung des Berichts',scope:'Umfang: automatisierte externe Bewertung der angegebenen Website, ohne internen Zugang oder Code-Review. Kontaktieren Sie Green PC, um Korrekturen zu priorisieren oder ein vertieftes Audit durchzuführen.'}
  };
  const currentLocale = () => LOCALES[lang.value] || LOCALES.es;
  let cooldownTimer = null;
  const setServiceState = (state, text) => { serviceStatus.dataset.state = state; serviceText.textContent = text; };
  const setLimitNote = (html) => { limitNote.replaceChildren(); limitNote.append(...html); };
  const textNode = text => document.createTextNode(text);
  function setReady() {
    window.clearInterval(cooldownTimer);
    btn.disabled = false;
    btn.textContent = currentLocale().scan;
    setServiceState('ready', currentLocale().ready);
    setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().server }), textNode('Puedes enviar una nueva solicitud. Límite: 10 análisis por hora y 4 por sitio por minuto.')]);
  }
  function startCooldown(seconds) {
    window.clearInterval(cooldownTimer);
    const endsAt = Date.now() + seconds * 1000;
    const tick = () => {
      const remaining = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
      if (remaining === 0) { setReady(); return; }
      const minutes = Math.floor(remaining / 60);
      const time = String(minutes).padStart(2, '0') + ':' + String(remaining % 60).padStart(2, '0');
      btn.disabled = true;
      btn.textContent = currentLocale().wait + time;
      setServiceState('busy', currentLocale().waiting);
      setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().protection }), textNode('Podrás solicitar otro análisis en ' + time + '.')]);
    };
    tick();
    cooldownTimer = window.setInterval(tick, 1000);
  }
  const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };
  const LABEL = status => ({ pass: currentLocale().correct, warn: currentLocale().improvable, fail: currentLocale().failure }[status]);

  function render(d) {
    const L = LOCALES[d.lang] || currentLocale();
    document.documentElement.lang = d.lang || lang.value;
    out.replaceChildren();
    const wrap = el('div', 'report');

    const s = el('aside', 'summary');
    s.append(el('p', 'report-section-label', L.executive));
    const risk = d.risk || (d.grade === 'A' || d.grade === 'B' ? 'bajo' : (d.grade === 'C' ? 'medio' : 'alto'));
    s.append(el('p', 'grade g-' + d.grade, d.grade), el('p', 'score', d.score + ' ' + L.score), el('span', 'risk risk-' + risk, L.risk + risk));
    const dl = el('dl');
    const row = (k, v) => { if (v) { dl.append(el('dt', '', k), el('dd', '', v)); } };
    row(L.site, d.host);
    row('IP', d.ip);
    if (d.tls && d.tls.protocol) row('TLS', d.tls.protocol);
    if (d.tls && d.tls.cipher) row(L.cipher, d.tls.cipher);
    if (d.tls && d.tls.issuer) row(L.issuer, d.tls.issuer);
    row(L.response, d.status + ' · ' + d.ms + ' ms');
    if (d.chain.length > 1) row(L.redirects, String(d.chain.length - 1));
    s.append(dl);

    const n = { pass: 0, warn: 0, fail: 0 };
    d.checks.forEach(c => n[c.status]++);
    s.append(el('p', 'tally', n.fail + ' fallas, ' + n.warn + ' mejorables, ' + n.pass + ' correctos.'), el('p', 'summary-note', 'Evaluación externa orientativa; no reemplaza un pentest.'));
    const actions = el('div', 'report-actions');
    const printBtn = el('button', '', L.print);
    printBtn.type = 'button';
    printBtn.addEventListener('click', () => window.print());
    const printNote = el('p', 'print-note', L.printNote);
    actions.append(printBtn, printNote);
    s.append(actions);
    if (d.grade !== 'A') {
      const cta = el('div', 'cta', 'Podemos corregir esto por ti, sin tocar tu diseño ni tu contenido.');
      const a = el('a', '', 'Hablar con Green PC');
      a.href = 'https://greenpc.dev';
      cta.append(document.createElement('br'), a);
      s.append(cta);
    }

    const list = el('div', 'report-content');
    const cover = el('section', 'report-cover');
    const brand = el('div', 'report-brand');
    const productLogo = document.createElement('img');
    productLogo.src = 'assets/img/web-auditor-logo.webp'; productLogo.alt = 'Web Auditor'; productLogo.className = 'report-product-logo';
    const title = el('div');
    title.append(el('h2', '', L.report), el('p', '', L.automated));
    const corporate = el('div', 'report-corporate');
    corporate.append(el('span', '', 'by'), (() => { const logo = document.createElement('img'); logo.src = 'assets/img/GPC-logo-pequeño.png'; logo.alt = 'Green PC'; return logo; })());
    title.append(corporate);
    brand.append(productLogo, title);
    const issuedAt = new Date(d.at || Date.now());
    const issued = Number.isNaN(issuedAt.getTime()) ? 'Fecha no disponible' : new Intl.DateTimeFormat(L.code, { dateStyle: 'long', timeStyle: 'short', timeZone: 'UTC' }).format(issuedAt) + ' UTC';
    const reference = el('div', 'report-ref');
    reference.append(el('strong', '', d.report_id || L.reference), el('span', '', L.issued + ': ' + issued), el('span', '', 'Sitio: ' + d.host));
    cover.append(brand, reference);
    if (Array.isArray(d.priorities) && d.priorities.length) {
      const priorities = el('section', 'priorities');
      priorities.append(el('h2', '', L.priorities), el('p', '', L.priorityIntro));
      const ordered = el('ol');
      d.priorities.forEach(c => {
        const item = el('li');
        item.append(el('strong', '', c.title), el('span', '', c.detail));
        ordered.append(item);
      });
      priorities.append(ordered);
      list.append(priorities);
    }
    const groups = new Map();
    d.checks.forEach(c => { if (!groups.has(c.group)) groups.set(c.group, []); groups.get(c.group).push(c); });
    groups.forEach((items, name) => {
      const g = el('section', 'group');
      g.append(el('h2', '', name));
      items.forEach(c => {
        const det = el('details', 'chk ' + c.status);
        if (c.status !== 'pass') det.open = true;
        const sum = el('summary');
        sum.append(el('span', 'badge', LABEL(c.status)), el('span', 'ttl', c.title), el('span', 'pts', c.earned + '/' + c.weight));
        const body = el('div', 'body');
        body.append(el('p', '', c.detail));
        if (c.fix) { body.append(el('p', 'fixlbl', L.fix), el('pre', '', c.fix)); }
        det.append(sum, body);
        g.append(det);
      });
      list.append(g);
    });

    const signature = el('section', 'report-signature');
    const signatureText = el('p', '', L.scope);
    const signatureBlock = el('div');
    signatureBlock.append(el('div', 'signature-name', 'Green PC'), el('div', 'signature-role', L.signature));
    signature.append(signatureText, signatureBlock);
    list.append(signature);
    const printFooter = el('div', 'print-footer');
    printFooter.append(el('span', '', L.reportFooter), el('span', '', d.report_id || L.reference));
    wrap.append(cover, s, list, printFooter);
    out.append(wrap);
    out.setAttribute('aria-busy', 'false');
    out.focus({ preventScroll: true });
    wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  async function run(url) {
    window.clearInterval(cooldownTimer);
    btn.disabled = true; btn.textContent = currentLocale().busy + '…';
    setServiceState('busy', currentLocale().busy);
    setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().request }), textNode('No cierres esta página; puede tardar hasta un minuto.')]);
    out.setAttribute('aria-busy', 'true');
    const progress = el('div', 'progress');
    const title = el('b', '', 'Preparando análisis…');
    const detail = el('small', '', 'Validando el destino de forma segura.');
    progress.append(title, detail);
    out.replaceChildren(progress);
    const stages = [
      ['Verificando transporte…', 'Certificado, protocolos TLS y redirecciones.'],
      ['Revisando cabeceras…', 'CSP, cookies, CORS y controles del navegador.'],
      ['Buscando exposiciones…', 'Archivos de configuración, copias y repositorios.'],
      ['Analizando DNS y correo…', 'SPF, DMARC y CAA.']
    ];
    let stage = 0;
    const stageTimer = window.setInterval(() => {
      const current = stages[Math.min(stage++, stages.length - 1)];
      title.textContent = current[0]; detail.textContent = current[1];
    }, 1700);
    let completed = false;
    try {
      const res = await fetch('api.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url, lang: lang.value })
      });
      const data = await res.json().catch(() => ({ error: 'Respuesta inesperada del servidor.' }));
      if (!res.ok || data.error) {
        const error = new Error(data.error || 'No se pudo completar el análisis.');
        error.retryAfter = Number(data.retry_after) || Number(res.headers.get('Retry-After')) || 0;
        throw error;
      }
      render(data);
      completed = true;
    } catch (e) {
      const error = el('div', 'err', e.message);
      error.setAttribute('role', 'alert');
      out.replaceChildren(error);
      out.setAttribute('aria-busy', 'false');
      if (e.retryAfter > 0) {
        startCooldown(e.retryAfter);
      } else {
        setServiceState('unavailable', 'Servicio temporalmente no disponible');
        setLimitNote([Object.assign(document.createElement('strong'), { textContent: 'No se completó la solicitud. ' }), textNode('Revisa el dominio e inténtalo de nuevo en unos segundos.')]);
      }
      out.focus();
    } finally {
      window.clearInterval(stageTimer);
      if (completed) setReady();
    }
  }

  form.addEventListener('submit', e => {
    e.preventDefault();
    const v = input.value.trim();
    document.documentElement.lang = lang.value;
    if (!v) return;
    history.replaceState(null, '', location.pathname);
    run(v);
  });

})();
</script>
</body>
</html>
