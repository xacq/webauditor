(() => {
  'use strict';

  const COLORS = {
    navy: '#0b1730',
    blue: '#2563eb',
    cyan: '#0284c7',
    orange: '#ea580c',
    amber: '#d97706',
    red: '#dc2626',
    slate: '#475569',
    muted: '#64748b',
    line: '#dbe4f0',
    paleBlue: '#eff6ff',
    paleCyan: '#ecfeff',
    paleAmber: '#fffbeb',
    paleRed: '#fef2f2',
    paleSlate: '#f1f5f9',
    white: '#ffffff',
  };

  const PDF_COPY = {
    es: {
      assessment: 'Evaluación profesional de seguridad web', overview: 'Resumen ejecutivo', risk: 'Nivel de riesgo',
      coverage: 'Cobertura verificada', duration: 'Duración del análisis', controls: 'Estado de controles',
      passed: 'Correctos', warnings: 'Mejorables', failed: 'Fallidos', unknown: 'No verificados',
      technical: 'Perfil técnico observado', priorities: 'Plan de remediación prioritario',
      prioritiesIntro: 'Estas acciones concentran la mayor reducción de riesgo y deben atenderse primero.',
      findings: 'Resultados detallados', recommendation: 'Recomendación', detail: 'Observación', points: 'Puntos',
      scope: 'Alcance y limitaciones', issuedBy: 'Emitido por Green PC', page: 'Página', of: 'de',
      informational: 'Documento técnico de uso informativo', notAvailable: 'No disponible',
      disclaimer: 'Evaluación externa automatizada. No sustituye una prueba de penetración, una revisión de código ni una auditoría de cumplimiento.',
    },
    en: {
      assessment: 'Professional web security assessment', overview: 'Executive overview', risk: 'Risk level',
      coverage: 'Verified coverage', duration: 'Analysis duration', controls: 'Control status',
      passed: 'Compliant', warnings: 'Needs improvement', failed: 'Failed', unknown: 'Not verified',
      technical: 'Observed technical profile', priorities: 'Priority remediation plan',
      prioritiesIntro: 'These actions provide the greatest risk reduction and should be addressed first.',
      findings: 'Detailed findings', recommendation: 'Recommendation', detail: 'Observation', points: 'Points',
      scope: 'Scope and limitations', issuedBy: 'Issued by Green PC', page: 'Page', of: 'of',
      informational: 'Technical document for informational use', notAvailable: 'Not available',
      disclaimer: 'Automated external assessment. It does not replace a penetration test, code review, or compliance audit.',
    },
    fr: {
      assessment: 'Évaluation professionnelle de la sécurité web', overview: 'Synthèse exécutive', risk: 'Niveau de risque',
      coverage: 'Couverture vérifiée', duration: 'Durée de l’analyse', controls: 'État des contrôles',
      passed: 'Conformes', warnings: 'À améliorer', failed: 'Échecs', unknown: 'Non vérifiés',
      technical: 'Profil technique observé', priorities: 'Plan de remédiation prioritaire',
      prioritiesIntro: 'Ces actions offrent la plus forte réduction du risque et doivent être traitées en priorité.',
      findings: 'Résultats détaillés', recommendation: 'Recommandation', detail: 'Observation', points: 'Points',
      scope: 'Portée et limites', issuedBy: 'Émis par Green PC', page: 'Page', of: 'sur',
      informational: 'Document technique à usage informatif', notAvailable: 'Non disponible',
      disclaimer: 'Évaluation externe automatisée. Elle ne remplace ni un test d’intrusion, ni une revue de code, ni un audit de conformité.',
    },
    it: {
      assessment: 'Valutazione professionale della sicurezza web', overview: 'Riepilogo esecutivo', risk: 'Livello di rischio',
      coverage: 'Copertura verificata', duration: 'Durata dell’analisi', controls: 'Stato dei controlli',
      passed: 'Conformi', warnings: 'Da migliorare', failed: 'Errori', unknown: 'Non verificati',
      technical: 'Profilo tecnico osservato', priorities: 'Piano di correzione prioritario',
      prioritiesIntro: 'Queste azioni offrono la maggiore riduzione del rischio e devono essere affrontate per prime.',
      findings: 'Risultati dettagliati', recommendation: 'Raccomandazione', detail: 'Osservazione', points: 'Punti',
      scope: 'Ambito e limitazioni', issuedBy: 'Emesso da Green PC', page: 'Pagina', of: 'di',
      informational: 'Documento tecnico a uso informativo', notAvailable: 'Non disponibile',
      disclaimer: 'Valutazione esterna automatizzata. Non sostituisce un penetration test, una revisione del codice o un audit di conformità.',
    },
    'pt-PT': {
      assessment: 'Avaliação profissional de segurança web', overview: 'Resumo executivo', risk: 'Nível de risco',
      coverage: 'Cobertura verificada', duration: 'Duração da análise', controls: 'Estado dos controlos',
      passed: 'Conformes', warnings: 'A melhorar', failed: 'Falhas', unknown: 'Não verificados',
      technical: 'Perfil técnico observado', priorities: 'Plano de correção prioritário',
      prioritiesIntro: 'Estas ações proporcionam a maior redução de risco e devem ser tratadas primeiro.',
      findings: 'Resultados detalhados', recommendation: 'Recomendação', detail: 'Observação', points: 'Pontos',
      scope: 'Âmbito e limitações', issuedBy: 'Emitido pela Green PC', page: 'Página', of: 'de',
      informational: 'Documento técnico para uso informativo', notAvailable: 'Não disponível',
      disclaimer: 'Avaliação externa automatizada. Não substitui um teste de intrusão, revisão de código ou auditoria de conformidade.',
    },
    de: {
      assessment: 'Professionelle Web-Sicherheitsbewertung', overview: 'Management-Zusammenfassung', risk: 'Risikostufe',
      coverage: 'Geprüfte Abdeckung', duration: 'Analysedauer', controls: 'Kontrollstatus',
      passed: 'Konform', warnings: 'Verbesserbar', failed: 'Fehler', unknown: 'Nicht geprüft',
      technical: 'Beobachtetes technisches Profil', priorities: 'Priorisierter Maßnahmenplan',
      prioritiesIntro: 'Diese Maßnahmen senken das Risiko am stärksten und sollten zuerst umgesetzt werden.',
      findings: 'Detaillierte Ergebnisse', recommendation: 'Empfehlung', detail: 'Beobachtung', points: 'Punkte',
      scope: 'Umfang und Einschränkungen', issuedBy: 'Ausgestellt von Green PC', page: 'Seite', of: 'von',
      informational: 'Technisches Dokument zu Informationszwecken', notAvailable: 'Nicht verfügbar',
      disclaimer: 'Automatisierte externe Bewertung. Sie ersetzt weder einen Penetrationstest noch eine Codeprüfung oder Compliance-Auditierung.',
    },
  };

  const clean = value => value === null || value === undefined || value === '' ? '' : String(value)
    .replace(/→/g, '->')
    .replace(/[‐‑‒–—−]/g, '-');
  const safeFilename = value => clean(value).toLowerCase().replace(/[^a-z0-9.-]+/g, '-').replace(/^-+|-+$/g, '') || 'site';
  const statusColor = status => ({ pass: COLORS.cyan, warn: COLORS.amber, fail: COLORS.red, unknown: COLORS.slate }[status] || COLORS.slate);
  const statusFill = status => ({ pass: COLORS.paleCyan, warn: COLORS.paleAmber, fail: COLORS.paleRed, unknown: COLORS.paleSlate }[status] || COLORS.paleSlate);
  const gradeColor = grade => grade === 'A' || grade === 'B' ? COLORS.cyan : grade === 'C' || grade === 'D' ? COLORS.amber : COLORS.red;
  const riskColor = risk => risk === 'bajo' ? COLORS.cyan : risk === 'medio' ? COLORS.amber : COLORS.red;

  function sectionHeading(text, options = {}) {
    return {
      stack: [
        { text, fontSize: 16, bold: true, color: COLORS.navy },
        { canvas: [{ type: 'line', x1: 0, y1: 4, x2: 511, y2: 4, lineWidth: 1.5, lineColor: options.color || COLORS.blue }] },
      ],
      margin: options.margin || [0, 4, 0, 14],
      pageBreak: options.pageBreak,
    };
  }

  function metricCell(value, label, color, fill) {
    return {
      stack: [
        { text: clean(value), fontSize: 20, bold: true, color, alignment: 'center' },
        { text: label, fontSize: 8, color: COLORS.slate, alignment: 'center', margin: [0, 3, 0, 0] },
      ],
      fillColor: fill,
      margin: [5, 9, 5, 9],
    };
  }

  function technicalTable(payload, copy) {
    const labels = payload.labels || {};
    const rows = [
      [labels.site || 'Site', payload.host],
      ['IP', payload.ip],
      ['TLS', payload.tlsProtocol],
      [labels.cipher || 'Cipher', payload.cipher],
      [labels.issuer || 'Issuer', payload.issuer],
      [labels.response || 'Response', payload.response],
      [copy.coverage, Number.isFinite(payload.coverage) ? payload.coverage + '%' : ''],
      [copy.duration, payload.duration],
      [labels.reference || 'Reference', payload.reportId],
      [labels.issued || 'Issued', payload.issued],
    ].filter(row => clean(row[1]));

    return {
      table: {
        widths: [124, '*'],
        body: rows.map((row, index) => [
          { text: clean(row[0]), bold: true, color: COLORS.slate, fillColor: index % 2 ? COLORS.white : '#f8fafc', margin: [7, 6, 7, 6] },
          { text: clean(row[1]), color: COLORS.navy, fillColor: index % 2 ? COLORS.white : '#f8fafc', margin: [7, 6, 7, 6] },
        ]),
      },
      layout: {
        hLineColor: () => COLORS.line,
        vLineColor: () => COLORS.line,
        hLineWidth: () => 0.6,
        vLineWidth: () => 0.6,
      },
      margin: [0, 0, 0, 18],
    };
  }

  function priorityCard(priority, index) {
    return {
      table: {
        widths: [28, '*'],
        body: [[
          { text: String(index + 1), fontSize: 15, bold: true, color: COLORS.white, fillColor: COLORS.orange, alignment: 'center', margin: [0, 9, 0, 9] },
          {
            stack: [
              { text: clean(priority.title), fontSize: 10.5, bold: true, color: COLORS.navy },
              { text: clean(priority.detail), fontSize: 8.5, color: COLORS.slate, lineHeight: 1.25, margin: [0, 3, 0, 0] },
            ],
            fillColor: '#fff7ed', margin: [10, 7, 10, 7],
          },
        ]],
      },
      layout: 'noBorders',
      margin: [0, 0, 0, 7],
      unbreakable: true,
    };
  }

  function findingCard(check, copy) {
    const color = statusColor(check.status);
    const fill = statusFill(check.status);
    const points = check.status === 'unknown' ? 'N/V' : clean(check.earned) + '/' + clean(check.weight);
    const detailStack = [
      { text: copy.detail.toUpperCase(), fontSize: 6.8, bold: true, color: COLORS.muted, characterSpacing: 0.6 },
      { text: clean(check.detail), fontSize: 8.3, color: COLORS.slate, lineHeight: 1.28, margin: [0, 2, 0, 0] },
    ];
    if (clean(check.fix)) {
      detailStack.push(
        { text: copy.recommendation.toUpperCase(), fontSize: 6.8, bold: true, color: COLORS.muted, characterSpacing: 0.6, margin: [0, 8, 0, 0] },
        { text: clean(check.fix), fontSize: 7.3, color: COLORS.navy, lineHeight: 1.22, fillColor: '#f8fafc', margin: [0, 3, 0, 0] },
      );
    }

    return {
      table: {
        widths: [72, '*', 44],
        dontBreakRows: true,
        body: [
          [
            { text: clean(check.statusLabel), bold: true, fontSize: 7.5, color, fillColor: fill, alignment: 'center', margin: [4, 7, 4, 7] },
            { text: clean(check.title), bold: true, fontSize: 9.5, color: COLORS.navy, margin: [8, 7, 8, 7] },
            { text: points, bold: true, fontSize: 8, color: COLORS.slate, alignment: 'right', margin: [4, 7, 7, 7] },
          ],
          [
            { text: '', fillColor: fill },
            { stack: detailStack, colSpan: 2, margin: [8, 7, 8, 8] },
            {},
          ],
        ],
      },
      layout: {
        hLineColor: () => COLORS.line,
        vLineColor: columnIndex => columnIndex === 0 ? color : COLORS.line,
        hLineWidth: rowIndex => rowIndex === 0 || rowIndex === 2 ? 0.8 : 0.45,
        vLineWidth: columnIndex => columnIndex === 0 ? 2.2 : 0.45,
      },
      margin: [0, 0, 0, 8],
      unbreakable: true,
    };
  }

  function signatureBlock(payload, copy) {
    const logo = payload.images && payload.images.corporateLogo;
    const brand = logo
      ? { image: logo, width: 122, alignment: 'right', margin: [0, 0, 0, 4] }
      : { text: 'GREEN PC', bold: true, color: COLORS.blue, fontSize: 15, alignment: 'right' };
    return {
      stack: [
        sectionHeading(copy.scope, { margin: [0, 18, 0, 12], color: COLORS.orange }),
        {
          table: {
            widths: ['*', 158],
            body: [[
              {
                stack: [
                  { text: clean(payload.scope), fontSize: 8.4, color: COLORS.slate, lineHeight: 1.3 },
                  { text: copy.disclaimer, fontSize: 7.5, italics: true, color: COLORS.muted, margin: [0, 8, 0, 0] },
                ],
                margin: [12, 10, 12, 10],
              },
              {
                stack: [brand, { canvas: [{ type: 'line', x1: 8, y1: 2, x2: 140, y2: 2, lineWidth: 0.8, lineColor: COLORS.slate }] },
                  { text: clean(payload.signature || copy.issuedBy), fontSize: 7, color: COLORS.muted, alignment: 'right', margin: [0, 5, 0, 0] }],
                fillColor: COLORS.paleBlue, margin: [10, 10, 10, 10],
              },
            ]],
          },
          layout: {
            hLineColor: () => COLORS.line, vLineColor: () => COLORS.line,
            hLineWidth: () => 0.7, vLineWidth: () => 0.7,
          },
          unbreakable: true,
        },
      ],
      unbreakable: true,
    };
  }

  function buildDocument(payload) {
    const copy = PDF_COPY[payload.lang] || PDF_COPY.es;
    const labels = payload.labels || {};
    const productLogo = payload.images && payload.images.productLogo;
    const corporateLogo = payload.images && payload.images.corporateLogo;
    const counts = payload.counts || { pass: 0, warn: 0, fail: 0, unknown: 0 };
    const riskTone = riskColor(payload.risk);
    const content = [];

    content.push({
      columns: [
        productLogo ? { image: productLogo, width: 178 } : { text: 'WEBAUDITOR', fontSize: 18, bold: true, color: COLORS.blue },
        corporateLogo ? { stack: [{ text: 'by', fontSize: 7, color: COLORS.muted, alignment: 'right' }, { image: corporateLogo, width: 112, alignment: 'right', margin: [0, 3, 0, 0] }] }
          : { text: 'GREEN PC', fontSize: 13, bold: true, color: COLORS.blue, alignment: 'right' },
      ],
      columnGap: 20,
      margin: [0, 0, 0, 22],
    });

    content.push({
      table: {
        widths: ['*'],
        body: [[{
          stack: [
            { text: copy.assessment.toUpperCase(), fontSize: 8, bold: true, color: '#93c5fd', characterSpacing: 1.1 },
            { text: clean(payload.title), fontSize: 27, bold: true, color: COLORS.white, margin: [0, 9, 0, 5] },
            { text: clean(payload.subtitle), fontSize: 10, color: '#cbd5e1', lineHeight: 1.25 },
            { text: clean(payload.host), fontSize: 17, bold: true, color: COLORS.white, margin: [0, 24, 0, 0] },
            { text: clean(payload.reportId), fontSize: 8, color: '#94a3b8', margin: [0, 5, 0, 0] },
          ],
          fillColor: COLORS.navy,
          margin: [22, 24, 22, 24],
        }]],
      },
      layout: 'noBorders',
      margin: [0, 0, 0, 18],
    });

    content.push({
      table: {
        widths: [120, '*'],
        body: [[
          {
            stack: [
              { text: clean(payload.grade), fontSize: 72, bold: true, color: gradeColor(payload.grade), alignment: 'center' },
              { text: clean(payload.score) + ' ' + clean(labels.score), fontSize: 9, bold: true, color: COLORS.navy, alignment: 'center', margin: [0, -4, 0, 0] },
            ],
            fillColor: '#f8fafc', margin: [8, 8, 8, 11],
          },
          {
            stack: [
              { text: copy.overview, fontSize: 16, bold: true, color: COLORS.navy },
              { text: copy.risk.toUpperCase(), fontSize: 7, bold: true, color: COLORS.muted, characterSpacing: 0.8, margin: [0, 14, 0, 2] },
              { text: clean(payload.riskLabel), fontSize: 17, bold: true, color: riskTone },
              {
                columns: [
                  { stack: [{ text: copy.coverage, fontSize: 7, color: COLORS.muted }, { text: Number.isFinite(payload.coverage) ? payload.coverage + '%' : copy.notAvailable, fontSize: 11, bold: true, color: COLORS.navy, margin: [0, 2, 0, 0] }] },
                  { stack: [{ text: copy.duration, fontSize: 7, color: COLORS.muted }, { text: clean(payload.duration) || copy.notAvailable, fontSize: 11, bold: true, color: COLORS.navy, margin: [0, 2, 0, 0] }] },
                ],
                columnGap: 15, margin: [0, 15, 0, 0],
              },
            ],
            margin: [18, 13, 15, 10],
          },
        ]],
      },
      layout: {
        hLineColor: () => COLORS.line, vLineColor: () => COLORS.line,
        hLineWidth: () => 0.7, vLineWidth: () => 0.7,
      },
      margin: [0, 0, 0, 18],
    });

    content.push({
      table: {
        widths: ['*', '*', '*', '*'],
        body: [[
          metricCell(counts.pass, copy.passed, COLORS.cyan, COLORS.paleCyan),
          metricCell(counts.warn, copy.warnings, COLORS.amber, COLORS.paleAmber),
          metricCell(counts.fail, copy.failed, COLORS.red, COLORS.paleRed),
          metricCell(counts.unknown, copy.unknown, COLORS.slate, COLORS.paleSlate),
        ]],
      },
      layout: { hLineWidth: () => 0, vLineWidth: () => 2, vLineColor: () => COLORS.white },
      margin: [0, 0, 0, 18],
    });

    const firstPriorities = (payload.priorities || []).slice(0, 3);
    if (firstPriorities.length) {
      content.push({ text: copy.priorities, fontSize: 12, bold: true, color: COLORS.navy, margin: [0, 0, 0, 8] });
      firstPriorities.forEach((priority, index) => content.push(priorityCard(priority, index)));
    }

    content[content.length - 1].pageBreak = 'after';

    content.push(sectionHeading(copy.overview));
    content.push(technicalTable(payload, copy));

    if (firstPriorities.length) {
      content.push(sectionHeading(copy.priorities, { color: COLORS.orange }));
      content.push({ text: copy.prioritiesIntro, fontSize: 8.5, color: COLORS.slate, margin: [0, -5, 0, 10] });
      firstPriorities.forEach((priority, index) => content.push(priorityCard(priority, index)));
    }

    content.push(sectionHeading(copy.findings, { pageBreak: 'before' }));
    const groups = new Map();
    (payload.checks || []).forEach(check => {
      const key = check.groupKey || check.group || 'other';
      if (!groups.has(key)) groups.set(key, { name: check.group || key, checks: [] });
      groups.get(key).checks.push(check);
    });
    groups.forEach(group => {
      content.push({
        text: clean(group.name),
        fontSize: 12.5,
        bold: true,
        color: COLORS.blue,
        margin: [0, 10, 0, 8],
      });
      group.checks.forEach(check => content.push(findingCard(check, copy)));
    });

    content.push(signatureBlock(payload, copy));

    return {
      pageSize: 'A4',
      pageMargins: [42, 55, 42, 48],
      info: {
        title: clean(payload.title) + ' - ' + clean(payload.host),
        author: 'Green PC',
        subject: copy.assessment,
        keywords: 'Green PC, WebAuditor, seguridad web, TLS, cabeceras HTTP',
        creator: 'WebAuditor by Green PC',
        producer: 'WebAuditor by Green PC',
      },
      background: (currentPage, pageSize) => ({
        canvas: [
          { type: 'rect', x: 0, y: 0, w: pageSize.width, h: 7, color: currentPage === 1 ? COLORS.orange : COLORS.blue },
        ],
      }),
      header: currentPage => currentPage === 1 ? null : ({
        columns: [
          { text: clean(payload.title), fontSize: 7.5, bold: true, color: COLORS.navy },
          { text: clean(payload.reportId), fontSize: 7, color: COLORS.muted, alignment: 'right' },
        ],
        margin: [42, 22, 42, 0],
      }),
      footer: (currentPage, pageCount) => ({
        stack: [
          { canvas: [{ type: 'line', x1: 0, y1: 0, x2: 511, y2: 0, lineWidth: 0.6, lineColor: COLORS.line }] },
          {
            columns: [
              { text: clean(payload.host) + ' · ' + copy.informational, fontSize: 6.8, color: COLORS.muted },
              { text: copy.page + ' ' + currentPage + ' ' + copy.of + ' ' + pageCount, fontSize: 6.8, color: COLORS.muted, alignment: 'right' },
            ],
            margin: [0, 6, 0, 0],
          },
        ],
        margin: [42, 0, 42, 18],
      }),
      defaultStyle: { font: 'Roboto', fontSize: 9, color: COLORS.navy, lineHeight: 1.18 },
      content,
    };
  }

  function imageToPng(url) {
    return new Promise((resolve, reject) => {
      const image = new Image();
      image.decoding = 'async';
      image.onload = () => {
        try {
          const canvas = document.createElement('canvas');
          canvas.width = image.naturalWidth;
          canvas.height = image.naturalHeight;
          const context = canvas.getContext('2d');
          context.drawImage(image, 0, 0);
          resolve(canvas.toDataURL('image/png'));
        } catch (error) {
          reject(error);
        }
      };
      image.onerror = () => reject(new Error('No se pudo cargar el recurso gráfico del informe.'));
      image.src = url;
    });
  }

  async function download(payload) {
    if (!window.pdfMake || typeof window.pdfMake.createPdf !== 'function') {
      throw new Error('El generador de PDF no está disponible.');
    }
    const urls = payload.assetUrls || {};
    const [productLogo, corporateLogo] = await Promise.all([
      imageToPng(urls.productLogo || 'assets/img/web-auditor-logo.webp'),
      imageToPng(urls.corporateLogo || 'assets/img/GPC-logo-pequeño.png'),
    ]);
    const enriched = { ...payload, images: { productLogo, corporateLogo } };
    const definition = buildDocument(enriched);
    const filename = 'Green-PC-WebAuditor-' + safeFilename(payload.host) + '-' + safeFilename(payload.reportId) + '.pdf';
    await window.pdfMake.createPdf(definition).download(filename);
    return filename;
  }

  window.WebAuditorPdf = { buildDocument, download };
})();
