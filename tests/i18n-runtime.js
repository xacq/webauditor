'use strict';

const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(path.join(__dirname, '..', 'assets', 'app.js'), 'utf8');
const start = source.indexOf('  const CSP_ISSUE_LABELS = {');
const end = source.indexOf('  const RISK_LABELS = {', start);
if (start < 0 || end < 0) {
  throw new Error('Runtime message catalog was not found');
}

const fragment = source.slice(start, end)
  .replace('const CSP_ISSUE_LABELS =', 'globalThis.CSP_ISSUE_LABELS =')
  .replace('const CHECK_MESSAGES =', 'globalThis.CHECK_MESSAGES =')
  .replace('const catalogLabel =', 'globalThis.catalogLabel =')
  .replace('const localizeCheckMessage =', 'globalThis.localizeCheckMessage =');
new Function(fragment)();

const germanSafe = globalThis.localizeCheckMessage({
  message_code: 'env.safe', message_params: { status: 404 }, detail: 'fallback', fix: ''
}, 'de');
if (!germanSafe.detail.includes('HTTP 404') || germanSafe.detail.includes('accesible públicamente')) {
  throw new Error('German .env safe message was not rendered correctly');
}

const englishBackup = globalThis.localizeCheckMessage({
  message_code: 'backups.exposed', message_params: { files: ['/backup.sql'] }, detail: 'fallback', fix: 'fallback'
}, 'en');
if (!englishBackup.detail.includes('/backup.sql') || !englishBackup.fix.includes('Apache:')) {
  throw new Error('English backup exposure message lost evidence or remediation');
}

const frenchUnknown = globalThis.localizeCheckMessage({
  message_code: 'backups.unknown', message_params: { unknown_files: ['/db.sql'] }, detail: 'fallback', fix: ''
}, 'fr');
if (!frenchUnknown.detail.includes('/db.sql')) {
  throw new Error('French unknown backup message lost dynamic evidence');
}

const germanCertificate = globalThis.localizeCheckMessage({
  message_code: 'cert.invalid', message_params: { host: 'example.com', error: 'hostname mismatch' }, detail: 'fallback', fix: 'fallback'
}, 'de');
if (!germanCertificate.detail.includes('hostname mismatch') || !germanCertificate.fix.includes('certbot --apache -d example.com')) {
  throw new Error('German certificate message lost error evidence or remediation parameters');
}

const frenchTls = globalThis.localizeCheckMessage({
  message_code: 'tls.modern', message_params: { protocol: 'TLSv1.3', cipher_suffix: ' (TLS_AES_128_GCM_SHA256)' }, detail: 'fallback', fix: ''
}, 'fr');
if (!frenchTls.detail.includes('TLSv1.3') || !frenchTls.detail.includes('TLS_AES_128_GCM_SHA256')) {
  throw new Error('French TLS message lost negotiated protocol evidence');
}

const portugueseCors = globalThis.localizeCheckMessage({
  message_code: 'cors.restricted', message_params: { origin: 'https://app.example' }, detail: 'fallback', fix: ''
}, 'pt-PT');
if (!portugueseCors.detail.includes('https://app.example')) {
  throw new Error('Portuguese CORS message lost the allowed origin');
}

const italianHsts = globalThis.localizeCheckMessage({
  message_code: 'hsts.short', message_params: { max_age: 3600 }, detail: 'fallback', fix: 'fallback'
}, 'it');
if (!italianHsts.detail.includes('3600') || !italianHsts.fix.includes('31536000')) {
  throw new Error('Italian HSTS message lost max-age evidence or remediation');
}

const englishCsp = globalThis.localizeCheckMessage({
  message_code: 'csp.weak', message_params: { issue_codes: ['unsafe_inline', 'wildcard'] }, detail: 'fallback', fix: 'fallback'
}, 'en');
if (!englishCsp.detail.includes("unsafe-inline") || !englishCsp.detail.includes('wildcards')) {
  throw new Error('English CSP message did not translate structured issue codes');
}

const germanCookies = globalThis.localizeCheckMessage({
  message_code: 'cookies.unsafe',
  message_params: { no_secure: ['session'], no_http_only: [], no_same_site: ['session', 'prefs'] },
  detail: 'fallback', fix: 'fallback'
}, 'de');
if (!germanCookies.detail.includes('session') || !germanCookies.detail.includes('prefs') || !germanCookies.fix.includes('session.cookie_secure')) {
  throw new Error('German cookie message lost names or remediation');
}

const frenchCoop = globalThis.localizeCheckMessage({
  message_code: 'coop.weak', message_params: { value: 'unsafe-none' }, detail: 'fallback', fix: 'fallback'
}, 'fr');
if (!frenchCoop.detail.includes('unsafe-none') || !frenchCoop.fix.includes('same-origin')) {
  throw new Error('French COOP message lost its value or remediation');
}

const portugueseLeak = globalThis.localizeCheckMessage({
  message_code: 'leak.exposed', message_params: { values: ['Server: nginx/1.2'] }, detail: 'fallback', fix: 'fallback'
}, 'pt-PT');
if (!portugueseLeak.detail.includes('nginx/1.2') || !portugueseLeak.fix.includes('ServerTokens')) {
  throw new Error('Portuguese technology disclosure message lost evidence or remediation');
}

const frenchExpiry = globalThis.localizeCheckMessage({
  message_code: 'expiry.expired', message_params: { days: 3 }, detail: 'fallback', fix: 'fallback'
}, 'fr');
if (!frenchExpiry.detail.includes('3') || !frenchExpiry.fix.includes('certbot renew')) {
  throw new Error('French certificate expiry message lost days or remediation');
}

const germanSecurityTxt = globalThis.localizeCheckMessage({
  message_code: 'sectxt.invalid', message_params: { host: 'example.com', expires: '2027-01-01T00:00:00Z' }, detail: 'fallback', fix: 'fallback'
}, 'de');
if (!germanSecurityTxt.fix.includes('seguridad@example.com') || !germanSecurityTxt.fix.includes('2027-01-01')) {
  throw new Error('German security.txt remediation lost dynamic values');
}

const italianSpf = globalThis.localizeCheckMessage({
  message_code: 'spf.softfail', message_params: { record: 'v=spf1 mx ~all' }, detail: 'fallback', fix: 'fallback'
}, 'it');
if (!italianSpf.detail.includes('v=spf1 mx ~all') || !italianSpf.fix.includes('-all')) {
  throw new Error('Italian SPF message lost the record or remediation');
}

const englishDmarc = globalThis.localizeCheckMessage({
  message_code: 'dmarc.monitoring', message_params: { policy: 'none', percent: 100, host: 'example.com' }, detail: 'fallback', fix: 'fallback'
}, 'en');
if (!englishDmarc.detail.includes('none') || !englishDmarc.fix.includes('example.com')) {
  throw new Error('English DMARC message lost policy or reporting address');
}

const portugueseCaa = globalThis.localizeCheckMessage({
  message_code: 'caa.configured', message_params: { records: ['0 issue "letsencrypt.org"'] }, detail: 'fallback', fix: ''
}, 'pt-PT');
if (!portugueseCaa.detail.includes('letsencrypt.org')) {
  throw new Error('Portuguese CAA message lost record evidence');
}

console.log('I18n runtime checks passed');
