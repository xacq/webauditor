'use strict';

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'assets', 'pdf-report.js'), 'utf8');
const context = { window: {}, console };
vm.runInNewContext(source, context, { filename: 'pdf-report.js' });

if (!context.window.WebAuditorPdf || typeof context.window.WebAuditorPdf.buildDocument !== 'function') {
  throw new Error('PDF report builder was not exported');
}

const basePayload = {
  localeCode: 'es-EC',
  title: 'Informe de seguridad web',
  subtitle: 'Evaluación externa automatizada.',
  host: 'example.com',
  ip: '203.0.113.10',
  tlsProtocol: 'TLSv1.3',
  cipher: 'TLS_AES_256_GCM_SHA384',
  issuer: 'Example CA',
  response: '200 · 125 ms',
  duration: '2.4 s',
  coverage: 100,
  reportId: 'GPC-TEST-001',
  issued: '6 de noviembre de 2026, 20:00 UTC',
  grade: 'B',
  score: 84,
  risk: 'bajo',
  riskLabel: 'Bajo',
  counts: { pass: 20, warn: 4, fail: 2, unknown: 0 },
  labels: { score: 'de 100 puntos', site: 'Sitio', cipher: 'Cifrado', issuer: 'Emisor', response: 'Respuesta', reference: 'Referencia', issued: 'Emitido' },
  priorities: [{ title: 'Content-Security-Policy', detail: 'Definir una política restrictiva.' }],
  checks: [
    { id: 'cert', groupKey: 'transport', group: 'Transporte', title: 'Certificado válido', status: 'pass', statusLabel: 'Correcto', earned: 20, weight: 20, detail: 'El certificado es válido.', fix: '' },
    { id: 'csp', groupKey: 'headers', group: 'Cabeceras de seguridad', title: 'Content-Security-Policy', status: 'warn', statusLabel: 'Mejorable', earned: 3, weight: 10, detail: 'La política puede reforzarse.', fix: "default-src 'self';" },
  ],
  scope: 'Evaluación externa sin acceso interno ni revisión de código.',
  signature: 'Firma de emisión del informe',
};

for (const lang of ['es', 'en', 'fr', 'it', 'pt-PT', 'de']) {
  const definition = context.window.WebAuditorPdf.buildDocument({ ...basePayload, lang });
  if (definition.pageSize !== 'A4' || !Array.isArray(definition.content) || definition.content.length < 10) {
    throw new Error(`Invalid A4 PDF definition for ${lang}`);
  }
  if (definition.info.author !== 'Green PC' || typeof definition.header !== 'function' || typeof definition.footer !== 'function') {
    throw new Error(`Missing professional PDF metadata for ${lang}`);
  }
  const serialized = JSON.stringify(definition.content);
  for (const expected of ['example.com', 'GPC-TEST-001', 'Certificado válido', 'Content-Security-Policy', 'default-src']) {
    if (!serialized.includes(expected)) {
      throw new Error(`PDF content lost ${expected} for ${lang}`);
    }
  }
  const footer = definition.footer(2, 7);
  if (!JSON.stringify(footer).includes('2') || !JSON.stringify(footer).includes('7')) {
    throw new Error(`PDF pagination is missing for ${lang}`);
  }
}

console.log('PDF report definition checks passed');
