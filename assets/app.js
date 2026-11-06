(() => {
  const $ = id => document.getElementById(id);
  const form = $('f'), input = $('u'), lang = $('lang'), btn = $('b'), out = $('out');
  const serviceStatus = $('service-status'), serviceText = $('service-text'), limitNote = $('limit-note');
  const LOCALES = {
    es:{code:'es-EC',ready:'Listo para un nuevo análisis',available:'Análisis disponible',scan:'Analizar sitio',busy:'Análisis en curso',waiting:'Esperando para una nueva solicitud',wait:'Espera ',server:'Servidor listo. ',protection:'Protección activa. ',request:'Solicitud en curso. ',report:'Informe de seguridad web',automated:'Evaluación externa automatizada de la postura básica de seguridad.',executive:'Resumen ejecutivo',score:'de 100 puntos',risk:'Riesgo ',site:'Sitio',cipher:'Cifrado',issuer:'Emisor',response:'Respuesta',redirects:'Redirecciones',correct:'Correcto',improvable:'Mejorable',failure:'Falla',unknown:'No verificado',print:'Imprimir informe / Guardar PDF',printNote:'Para un PDF profesional: A4, una página por hoja y encabezados/pies del navegador desactivados.',priorities:'Corrige primero esto',priorityIntro:'Estas acciones producen la mayor mejora de seguridad.',fix:'Cómo corregirlo',issued:'Emitido',dateUnavailable:'Fecha no disponible',reference:'Referencia de informe',footer:'La nota es orientativa: no reemplaza una auditoría completa.',external:'Evaluación externa orientativa; no reemplaza un pentest.',reportFooter:'Green PC · Informe de seguridad web',signature:'Firma de emisión del informe',scope:'Alcance: evaluación externa automatizada del sitio indicado, sin acceso interno ni revisión de código. Para priorizar una corrección o realizar una auditoría profunda, contacta a Green PC.'},
    en:{code:'en-GB',ready:'Ready for a new analysis',available:'Analysis available',scan:'Analyse site',busy:'Analysis in progress',waiting:'Waiting for a new request',wait:'Wait ',server:'Server ready. ',protection:'Protection active. ',request:'Request in progress. ',report:'Web security report',automated:'Automated external assessment of the basic security posture.',executive:'Executive summary',score:'out of 100 points',risk:'Risk ',site:'Site',cipher:'Cipher',issuer:'Issuer',response:'Response',redirects:'Redirects',correct:'Compliant',improvable:'Needs improvement',failure:'Failure',unknown:'Not verified',print:'Print report / Save PDF',printNote:'For a professional PDF: A4, one page per sheet, with browser headers and footers disabled.',priorities:'Fix these first',priorityIntro:'These actions deliver the greatest security improvement.',fix:'How to fix it',issued:'Issued',dateUnavailable:'Date unavailable',reference:'Report reference',footer:'The score is guidance only; it does not replace a complete audit.',external:'External guidance only; it does not replace a penetration test.',reportFooter:'Green PC · Web security report',signature:'Report issuance signature',scope:'Scope: automated external assessment of the indicated site, with no internal access or code review. Contact Green PC to prioritize remediation or conduct an in-depth audit.'},
    fr:{code:'fr-FR',ready:'Prêt pour une nouvelle analyse',available:'Analyse disponible',scan:'Analyser le site',busy:'Analyse en cours',waiting:'En attente d’une nouvelle demande',wait:'Attendez ',server:'Serveur prêt. ',protection:'Protection active. ',request:'Demande en cours. ',report:'Rapport de sécurité web',automated:'Évaluation externe automatisée de la posture de sécurité de base.',executive:'Résumé exécutif',score:'sur 100 points',risk:'Risque ',site:'Site',cipher:'Chiffrement',issuer:'Émetteur',response:'Réponse',redirects:'Redirections',correct:'Conforme',improvable:'À améliorer',failure:'Défaillance',print:'Imprimer / Enregistrer en PDF',printNote:'Pour un PDF professionnel : A4, une page par feuille et en-têtes/pieds du navigateur désactivés.',priorities:'À corriger en priorité',priorityIntro:'Ces actions apportent la plus grande amélioration de sécurité.',fix:'Comment corriger',issued:'Émis',dateUnavailable:'Date indisponible',reference:'Référence du rapport',footer:'La note est indicative et ne remplace pas un audit complet.',external:'Évaluation externe indicative ; elle ne remplace pas un test d’intrusion.',reportFooter:'Green PC · Rapport de sécurité web',signature:'Signature d’émission du rapport',scope:'Portée : évaluation externe automatisée du site indiqué, sans accès interne ni revue de code. Contactez Green PC pour prioriser les corrections ou réaliser un audit approfondi.'},
    it:{code:'it-IT',ready:'Pronto per una nuova analisi',available:'Analisi disponibile',scan:'Analizza il sito',busy:'Analisi in corso',waiting:'In attesa di una nuova richiesta',wait:'Attendi ',server:'Server pronto. ',protection:'Protezione attiva. ',request:'Richiesta in corso. ',report:'Rapporto di sicurezza web',automated:'Valutazione esterna automatizzata della postura di sicurezza di base.',executive:'Riepilogo esecutivo',score:'su 100 punti',risk:'Rischio ',site:'Sito',cipher:'Cifratura',issuer:'Emittente',response:'Risposta',redirects:'Reindirizzamenti',correct:'Conforme',improvable:'Da migliorare',failure:'Errore',print:'Stampa rapporto / Salva PDF',printNote:'Per un PDF professionale: A4, una pagina per foglio e intestazioni/piè di pagina del browser disattivati.',priorities:'Correggi prima questi punti',priorityIntro:'Queste azioni producono il maggiore miglioramento della sicurezza.',fix:'Come correggere',issued:'Emesso',dateUnavailable:'Data non disponibile',reference:'Riferimento rapporto',footer:'Il punteggio è solo indicativo e non sostituisce un audit completo.',external:'Valutazione esterna indicativa; non sostituisce un penetration test.',reportFooter:'Green PC · Rapporto di sicurezza web',signature:'Firma di emissione del rapporto',scope:'Ambito: valutazione esterna automatizzata del sito indicato, senza accesso interno né revisione del codice. Contatta Green PC per definire le priorità o svolgere un audit approfondito.'},
    'pt-PT':{code:'pt-PT',ready:'Pronto para uma nova análise',available:'Análise disponível',scan:'Analisar site',busy:'Análise em curso',waiting:'A aguardar um novo pedido',wait:'Aguarde ',server:'Servidor pronto. ',protection:'Proteção ativa. ',request:'Pedido em curso. ',report:'Relatório de segurança web',automated:'Avaliação externa automatizada da postura básica de segurança.',executive:'Resumo executivo',score:'em 100 pontos',risk:'Risco ',site:'Site',cipher:'Cifra',issuer:'Emissor',response:'Resposta',redirects:'Redirecionamentos',correct:'Conforme',improvable:'A melhorar',failure:'Falha',print:'Imprimir relatório / Guardar PDF',printNote:'Para um PDF profissional: A4, uma página por folha e cabeçalhos/rodapés do navegador desativados.',priorities:'Corrija primeiro estes pontos',priorityIntro:'Estas ações proporcionam a maior melhoria de segurança.',fix:'Como corrigir',issued:'Emitido',dateUnavailable:'Data indisponível',reference:'Referência do relatório',footer:'A classificação é apenas indicativa e não substitui uma auditoria completa.',external:'Avaliação externa indicativa; não substitui um teste de intrusão.',reportFooter:'Green PC · Relatório de segurança web',signature:'Assinatura de emissão do relatório',scope:'Âmbito: avaliação externa automatizada do site indicado, sem acesso interno ou revisão de código. Contacte a Green PC para priorizar correções ou realizar uma auditoria aprofundada.'},
    de:{code:'de-DE',ready:'Bereit für eine neue Analyse',available:'Analyse verfügbar',scan:'Website analysieren',busy:'Analyse läuft',waiting:'Warten auf eine neue Anfrage',wait:'Warten ',server:'Server bereit. ',protection:'Schutz aktiv. ',request:'Anfrage läuft. ',report:'Web-Sicherheitsbericht',automated:'Automatisierte externe Bewertung der grundlegenden Sicherheitslage.',executive:'Management-Zusammenfassung',score:'von 100 Punkten',risk:'Risiko ',site:'Website',cipher:'Verschlüsselung',issuer:'Aussteller',response:'Antwort',redirects:'Weiterleitungen',correct:'Konform',improvable:'Verbesserbar',failure:'Fehler',print:'Bericht drucken / PDF speichern',printNote:'Für ein professionelles PDF: A4, eine Seite pro Blatt sowie Browser-Kopf- und Fußzeilen deaktivieren.',priorities:'Zuerst beheben',priorityIntro:'Diese Maßnahmen bringen die größte Sicherheitsverbesserung.',fix:'So beheben Sie es',issued:'Ausgestellt',dateUnavailable:'Datum nicht verfügbar',reference:'Berichtsreferenz',footer:'Die Bewertung dient nur als Orientierung und ersetzt kein vollständiges Audit.',external:'Orientierende externe Bewertung; sie ersetzt keinen Penetrationstest.',reportFooter:'Green PC · Web-Sicherheitsbericht',signature:'Ausstellungsbestätigung des Berichts',scope:'Umfang: automatisierte externe Bewertung der angegebenen Website, ohne internen Zugang oder Code-Review. Kontaktieren Sie Green PC, um Korrekturen zu priorisieren oder ein vertieftes Audit durchzuführen.'}
  };
  const PDF_ACTION_COPY = {
    es:{download:'Descargar PDF',generating:'Generando PDF…',ready:'PDF descargado',error:'No se pudo generar el PDF. Inténtalo de nuevo.',print:'Imprimir',note:'PDF A4 profesional con portada, resumen, hallazgos, numeración y firma Green PC.'},
    en:{download:'Download PDF',generating:'Generating PDF…',ready:'PDF downloaded',error:'The PDF could not be generated. Please try again.',print:'Print',note:'Professional A4 PDF with cover, overview, findings, page numbers and Green PC signature.'},
    fr:{download:'Télécharger le PDF',generating:'Génération du PDF…',ready:'PDF téléchargé',error:'Impossible de générer le PDF. Veuillez réessayer.',print:'Imprimer',note:'PDF A4 professionnel avec couverture, synthèse, résultats, pagination et signature Green PC.'},
    it:{download:'Scarica PDF',generating:'Generazione PDF…',ready:'PDF scaricato',error:'Impossibile generare il PDF. Riprova.',print:'Stampa',note:'PDF A4 professionale con copertina, riepilogo, risultati, numerazione e firma Green PC.'},
    'pt-PT':{download:'Descarregar PDF',generating:'A gerar PDF…',ready:'PDF descarregado',error:'Não foi possível gerar o PDF. Tente novamente.',print:'Imprimir',note:'PDF A4 profissional com capa, resumo, resultados, numeração e assinatura Green PC.'},
    de:{download:'PDF herunterladen',generating:'PDF wird erstellt…',ready:'PDF heruntergeladen',error:'Die PDF-Datei konnte nicht erstellt werden. Bitte erneut versuchen.',print:'Drucken',note:'Professionelles A4-PDF mit Deckblatt, Übersicht, Ergebnissen, Seitenzahlen und Green-PC-Signatur.'},
  };
  const UNKNOWN_LABELS = { es:'No verificado', en:'Not verified', fr:'Non vérifié', it:'Non verificato', 'pt-PT':'Não verificado', de:'Nicht geprüft' };
  const COVERAGE_LABELS = { es:'Cobertura', en:'Coverage', fr:'Couverture', it:'Copertura', 'pt-PT':'Cobertura', de:'Abdeckung' };
  const DURATION_LABELS = { es:'Duración total', en:'Total duration', fr:'Durée totale', it:'Durata totale', 'pt-PT':'Duração total', de:'Gesamtdauer' };
  const GROUP_LABELS = {
    transport:{es:'Transporte',en:'Transport',fr:'Transport',it:'Trasporto','pt-PT':'Transporte',de:'Transport'},
    headers:{es:'Cabeceras de seguridad',en:'Security headers',fr:'En-têtes de sécurité',it:'Header di sicurezza','pt-PT':'Cabeçalhos de segurança',de:'Sicherheits-Header'},
    exposure:{es:'Cookies y archivos sensibles',en:'Cookies and sensitive files',fr:'Cookies et fichiers sensibles',it:'Cookie e file sensibili','pt-PT':'Cookies e ficheiros sensíveis',de:'Cookies und sensible Dateien'},
    practices:{es:'Buenas prácticas',en:'Best practices',fr:'Bonnes pratiques',it:'Buone pratiche','pt-PT':'Boas práticas',de:'Bewährte Verfahren'},
    dns_mail:{es:'Seguridad DNS y Correo',en:'DNS and email security',fr:'Sécurité DNS et messagerie',it:'Sicurezza DNS ed email','pt-PT':'Segurança de DNS e correio',de:'DNS- und E-Mail-Sicherheit'}
  };
  const CHECK_LABELS = {
    cert:{es:'Certificado válido',en:'Valid certificate',fr:'Certificat valide',it:'Certificato valido','pt-PT':'Certificado válido',de:'Gültiges Zertifikat'},
    expiry:{es:'Vigencia del certificado',en:'Certificate validity',fr:'Validité du certificat',it:'Validità del certificato','pt-PT':'Validade do certificado',de:'Zertifikatsgültigkeit'},
    tls:{es:'Versión de TLS',en:'TLS version',fr:'Version TLS',it:'Versione TLS','pt-PT':'Versão TLS',de:'TLS-Version'},
    legacy_tls:{es:'Protocolos TLS heredados',en:'Legacy TLS protocols',fr:'Protocoles TLS obsolètes',it:'Protocolli TLS obsoleti','pt-PT':'Protocolos TLS obsoletos',de:'Veraltete TLS-Protokolle'},
    redirect:{es:'Redirección HTTP → HTTPS',en:'HTTP → HTTPS redirect',fr:'Redirection HTTP → HTTPS',it:'Reindirizzamento HTTP → HTTPS','pt-PT':'Redirecionamento HTTP → HTTPS',de:'HTTP → HTTPS-Weiterleitung'},
    hsts_max_age:{es:'HSTS: vigencia',en:'HSTS: validity',fr:'HSTS : durée de validité',it:'HSTS: validità','pt-PT':'HSTS: validade',de:'HSTS: Gültigkeitsdauer'},
    hsts_subdomains:{es:'HSTS: subdominios',en:'HSTS: subdomains',fr:'HSTS : sous-domaines',it:'HSTS: sottodomini','pt-PT':'HSTS: subdomínios',de:'HSTS: Subdomains'},
    hsts_preload:{es:'HSTS: preparación para preload',en:'HSTS: preload readiness',fr:'HSTS : préparation au préchargement',it:'HSTS: preparazione al preload','pt-PT':'HSTS: preparação para preload',de:'HSTS: Preload-Bereitschaft'},
    csp:{es:'Content-Security-Policy',en:'Content-Security-Policy',fr:'Content-Security-Policy',it:'Content-Security-Policy','pt-PT':'Content-Security-Policy',de:'Content-Security-Policy'},
    framing:{es:'Protección contra clickjacking',en:'Clickjacking protection',fr:'Protection contre le détournement de clic',it:'Protezione dal clickjacking','pt-PT':'Proteção contra clickjacking',de:'Clickjacking-Schutz'},
    nosniff:{es:'X-Content-Type-Options',en:'X-Content-Type-Options',fr:'X-Content-Type-Options',it:'X-Content-Type-Options','pt-PT':'X-Content-Type-Options',de:'X-Content-Type-Options'},
    referrer:{es:'Referrer-Policy',en:'Referrer-Policy',fr:'Referrer-Policy',it:'Referrer-Policy','pt-PT':'Referrer-Policy',de:'Referrer-Policy'},
    permissions:{es:'Permissions-Policy',en:'Permissions-Policy',fr:'Permissions-Policy',it:'Permissions-Policy','pt-PT':'Permissions-Policy',de:'Permissions-Policy'},
    coop:{es:'Cross-Origin-Opener-Policy (COOP)',en:'Cross-Origin-Opener-Policy (COOP)',fr:'Cross-Origin-Opener-Policy (COOP)',it:'Cross-Origin-Opener-Policy (COOP)','pt-PT':'Cross-Origin-Opener-Policy (COOP)',de:'Cross-Origin-Opener-Policy (COOP)'},
    corp:{es:'Cross-Origin-Resource-Policy (CORP)',en:'Cross-Origin-Resource-Policy (CORP)',fr:'Cross-Origin-Resource-Policy (CORP)',it:'Cross-Origin-Resource-Policy (CORP)','pt-PT':'Cross-Origin-Resource-Policy (CORP)',de:'Cross-Origin-Resource-Policy (CORP)'},
    cookies:{es:'Cookies',en:'Cookies',fr:'Cookies',it:'Cookie','pt-PT':'Cookies',de:'Cookies'},
    leak:{es:'Divulgación de tecnología',en:'Technology disclosure',fr:'Divulgation technologique',it:'Divulgazione della tecnologia','pt-PT':'Divulgação de tecnologia',de:'Offenlegung von Technologien'},
    env:{es:'Protección de archivo .env',en:'.env file protection',fr:'Protection du fichier .env',it:'Protezione del file .env','pt-PT':'Proteção do ficheiro .env',de:'Schutz der .env-Datei'},
    git:{es:'Protección de repositorio .git',en:'.git repository protection',fr:'Protection du dépôt .git',it:'Protezione del repository .git','pt-PT':'Proteção do repositório .git',de:'Schutz des .git-Repositorys'},
    backups:{es:'Archivos de respaldo expuestos',en:'Exposed backup files',fr:'Fichiers de sauvegarde exposés',it:'File di backup esposti','pt-PT':'Ficheiros de cópia expostos',de:'Offengelegte Sicherungsdateien'},
    sectxt:{es:'security.txt (RFC 9116)',en:'security.txt (RFC 9116)',fr:'security.txt (RFC 9116)',it:'security.txt (RFC 9116)','pt-PT':'security.txt (RFC 9116)',de:'security.txt (RFC 9116)'},
    cors:{es:'Configuración CORS',en:'CORS configuration',fr:'Configuration CORS',it:'Configurazione CORS','pt-PT':'Configuração CORS',de:'CORS-Konfiguration'},
    cipher:{es:'Fortaleza del cifrado TLS',en:'TLS cipher strength',fr:'Robustesse du chiffrement TLS',it:'Robustezza della cifratura TLS','pt-PT':'Robustez da cifra TLS',de:'Stärke der TLS-Verschlüsselung'},
    spf:{es:'Registro SPF (Antisuplantación)',en:'SPF record (Anti-spoofing)',fr:'Enregistrement SPF (anti-usurpation)',it:'Record SPF (anti-spoofing)','pt-PT':'Registo SPF (antifalsificação)',de:'SPF-Eintrag (Schutz vor Spoofing)'},
    dmarc:{es:'Política DMARC (Protección de identidad)',en:'DMARC policy (Identity protection)',fr:'Politique DMARC (protection de l’identité)',it:'Policy DMARC (protezione dell’identità)','pt-PT':'Política DMARC (proteção de identidade)',de:'DMARC-Richtlinie (Identitätsschutz)'},
    caa:{es:'Registros CAA (Autoridad de Certificación)',en:'CAA records (Certification authority)',fr:'Enregistrements CAA (autorité de certification)',it:'Record CAA (autorità di certificazione)','pt-PT':'Registos CAA (autoridade de certificação)',de:'CAA-Einträge (Zertifizierungsstelle)'}
  };
  const CSP_ISSUE_LABELS = {
    missing_script_source:{es:'no restringe scripts porque falta script-src o default-src',en:'scripts are unrestricted because script-src or default-src is missing',fr:'les scripts ne sont pas limités car script-src ou default-src manque',it:'gli script non sono limitati perché manca script-src o default-src','pt-PT':'os scripts não são restringidos porque falta script-src ou default-src',de:'Skripte sind nicht eingeschränkt, da script-src oder default-src fehlt'},
    unsafe_inline:{es:"permite 'unsafe-inline' en scripts",en:"allows 'unsafe-inline' in scripts",fr:"autorise 'unsafe-inline' dans les scripts",it:"consente 'unsafe-inline' negli script",'pt-PT':"permite 'unsafe-inline' nos scripts",de:"erlaubt 'unsafe-inline' in Skripten"},
    unsafe_eval:{es:"permite 'unsafe-eval' en scripts",en:"allows 'unsafe-eval' in scripts",fr:"autorise 'unsafe-eval' dans les scripts",it:"consente 'unsafe-eval' negli script",'pt-PT':"permite 'unsafe-eval' nos scripts",de:"erlaubt 'unsafe-eval' in Skripten"},
    wildcard:{es:'permite comodines en scripts',en:'allows wildcards in scripts',fr:'autorise les caractères génériques dans les scripts',it:'consente caratteri jolly negli script','pt-PT':'permite caracteres universais nos scripts',de:'erlaubt Platzhalter in Skripten'},
    data_scheme:{es:'permite data: en scripts',en:'allows data: in scripts',fr:'autorise data: dans les scripts',it:'consente data: negli script','pt-PT':'permite data: nos scripts',de:'erlaubt data: in Skripten'},
    http_scheme:{es:'permite http: en scripts',en:'allows http: in scripts',fr:'autorise http: dans les scripts',it:'consente http: negli script','pt-PT':'permite http: nos scripts',de:'erlaubt http: in Skripten'}
  };
  const COOKIE_ISSUE_LABELS = {
    no_secure:{es:'sin Secure: {names}',en:'without Secure: {names}',fr:'sans Secure : {names}',it:'senza Secure: {names}','pt-PT':'sem Secure: {names}',de:'ohne Secure: {names}'},
    no_http_only:{es:'sin HttpOnly: {names}',en:'without HttpOnly: {names}',fr:'sans HttpOnly : {names}',it:'senza HttpOnly: {names}','pt-PT':'sem HttpOnly: {names}',de:'ohne HttpOnly: {names}'},
    no_same_site:{es:'sin SameSite: {names}',en:'without SameSite: {names}',fr:'sans SameSite : {names}',it:'senza SameSite: {names}','pt-PT':'sem SameSite: {names}',de:'ohne SameSite: {names}'}
  };
  const CHECK_MESSAGES = {
    'env.unknown':{detail:{es:'No se pudo verificar el archivo .env por un error de conexión.',en:'The .env file could not be verified because of a connection error.',fr:'Le fichier .env n’a pas pu être vérifié en raison d’une erreur de connexion.',it:'Non è stato possibile verificare il file .env a causa di un errore di connessione.','pt-PT':'Não foi possível verificar o ficheiro .env devido a um erro de ligação.',de:'Die .env-Datei konnte wegen eines Verbindungsfehlers nicht geprüft werden.'}},
    'env.exposed':{
      detail:{es:'¡PELIGRO CRÍTICO! El archivo .env está expuesto públicamente y filtra credenciales y claves de la aplicación.',en:'CRITICAL DANGER! The .env file is publicly exposed and leaks application credentials and keys.',fr:'DANGER CRITIQUE ! Le fichier .env est publiquement accessible et divulgue des identifiants et des clés.',it:'PERICOLO CRITICO! Il file .env è esposto pubblicamente e rivela credenziali e chiavi dell’applicazione.','pt-PT':'PERIGO CRÍTICO! O ficheiro .env está exposto publicamente e divulga credenciais e chaves da aplicação.',de:'KRITISCHE GEFAHR! Die .env-Datei ist öffentlich zugänglich und legt Zugangsdaten sowie Anwendungsschlüssel offen.'},
      fix:{es:'Bloquea el acceso a los archivos ocultos en el servidor web:',en:'Block access to hidden files on the web server:',fr:'Bloquez l’accès aux fichiers cachés sur le serveur web :',it:'Blocca l’accesso ai file nascosti sul server web:','pt-PT':'Bloqueie o acesso a ficheiros ocultos no servidor web:',de:'Sperren Sie den Zugriff auf versteckte Dateien am Webserver:'},
      technical:'Apache: <Files ".*">\n  Require all denied\n</Files>\nNginx: location ~ /\\. { deny all; }'
    },
    'env.safe':{detail:{es:'El archivo .env no es accesible públicamente (HTTP {status}).',en:'The .env file is not publicly accessible (HTTP {status}).',fr:'Le fichier .env n’est pas accessible publiquement (HTTP {status}).',it:'Il file .env non è accessibile pubblicamente (HTTP {status}).','pt-PT':'O ficheiro .env não está acessível publicamente (HTTP {status}).',de:'Die .env-Datei ist nicht öffentlich erreichbar (HTTP {status}).'}},
    'git.unknown':{detail:{es:'No se pudo verificar el directorio .git por un error de conexión.',en:'The .git directory could not be verified because of a connection error.',fr:'Le répertoire .git n’a pas pu être vérifié en raison d’une erreur de connexion.',it:'Non è stato possibile verificare la directory .git a causa di un errore di connessione.','pt-PT':'Não foi possível verificar o diretório .git devido a um erro de ligação.',de:'Das .git-Verzeichnis konnte wegen eines Verbindungsfehlers nicht geprüft werden.'}},
    'git.exposed':{
      detail:{es:'¡PELIGRO CRÍTICO! El directorio .git está expuesto públicamente y permite reconstruir el código fuente.',en:'CRITICAL DANGER! The .git directory is publicly exposed and can be used to reconstruct the source code.',fr:'DANGER CRITIQUE ! Le répertoire .git est publiquement accessible et permet de reconstruire le code source.',it:'PERICOLO CRITICO! La directory .git è esposta pubblicamente e consente di ricostruire il codice sorgente.','pt-PT':'PERIGO CRÍTICO! O diretório .git está exposto publicamente e permite reconstruir o código-fonte.',de:'KRITISCHE GEFAHR! Das .git-Verzeichnis ist öffentlich zugänglich und ermöglicht die Rekonstruktion des Quellcodes.'},
      fix:{es:'Bloquea el acceso al directorio .git:',en:'Block access to the .git directory:',fr:'Bloquez l’accès au répertoire .git :',it:'Blocca l’accesso alla directory .git:','pt-PT':'Bloqueie o acesso ao diretório .git:',de:'Sperren Sie den Zugriff auf das .git-Verzeichnis:'},
      technical:'Apache: RedirectMatch 404 /\\.git\nNginx: location ~ /\\.git { deny all; }'
    },
    'git.safe':{detail:{es:'El repositorio .git no es accesible públicamente (HTTP {status}).',en:'The .git repository is not publicly accessible (HTTP {status}).',fr:'Le dépôt .git n’est pas accessible publiquement (HTTP {status}).',it:'Il repository .git non è accessibile pubblicamente (HTTP {status}).','pt-PT':'O repositório .git não está acessível publicamente (HTTP {status}).',de:'Das .git-Repository ist nicht öffentlich erreichbar (HTTP {status}).'}},
    'backups.exposed':{
      detail:{es:'¡PELIGRO! Archivos sensibles accesibles públicamente: {files}.',en:'DANGER! Sensitive files are publicly accessible: {files}.',fr:'DANGER ! Des fichiers sensibles sont accessibles publiquement : {files}.',it:'PERICOLO! File sensibili accessibili pubblicamente: {files}.','pt-PT':'PERIGO! Ficheiros sensíveis acessíveis publicamente: {files}.',de:'GEFAHR! Sensible Dateien sind öffentlich erreichbar: {files}.'},
      fix:{es:'Elimina los respaldos del directorio público o bloquea su acceso:',en:'Remove backup files from the public directory or block access to them:',fr:'Supprimez les sauvegardes du répertoire public ou bloquez leur accès :',it:'Rimuovi i backup dalla directory pubblica o bloccane l’accesso:','pt-PT':'Remova as cópias do diretório público ou bloqueie o acesso:',de:'Entfernen Sie Sicherungen aus dem öffentlichen Verzeichnis oder sperren Sie den Zugriff:'},
      technical:'Apache: <FilesMatch "\\.(bak|old|sql)$">\n  Require all denied\n</FilesMatch>\nNginx: location ~* \\.(bak|old|sql)$ { deny all; }'
    },
    'backups.unknown':{detail:{es:'No se pudieron verificar todos los archivos sensibles: {unknown_files}.',en:'Not all sensitive files could be verified: {unknown_files}.',fr:'Tous les fichiers sensibles n’ont pas pu être vérifiés : {unknown_files}.',it:'Non è stato possibile verificare tutti i file sensibili: {unknown_files}.','pt-PT':'Não foi possível verificar todos os ficheiros sensíveis: {unknown_files}.',de:'Nicht alle sensiblen Dateien konnten geprüft werden: {unknown_files}.'}},
    'backups.safe':{detail:{es:'No se encontraron archivos de respaldo o configuración expuestos.',en:'No exposed backup or configuration files were found.',fr:'Aucun fichier de sauvegarde ou de configuration exposé n’a été trouvé.',it:'Non sono stati trovati file di backup o configurazione esposti.','pt-PT':'Não foram encontrados ficheiros de cópia ou configuração expostos.',de:'Es wurden keine offengelegten Sicherungs- oder Konfigurationsdateien gefunden.'}},
    'cert.valid':{detail:{es:'Certificado de confianza para {host}, emitido por {issuer}.',en:'Trusted certificate for {host}, issued by {issuer}.',fr:'Certificat de confiance pour {host}, émis par {issuer}.',it:'Certificato attendibile per {host}, emesso da {issuer}.','pt-PT':'Certificado de confiança para {host}, emitido por {issuer}.',de:'Vertrauenswürdiges Zertifikat für {host}, ausgestellt von {issuer}.'}},
    'cert.invalid':{
      detail:{es:'El certificado no es de confianza: {error}.',en:'The certificate is not trusted: {error}.',fr:'Le certificat n’est pas fiable : {error}.',it:'Il certificato non è attendibile: {error}.','pt-PT':'O certificado não é de confiança: {error}.',de:'Das Zertifikat ist nicht vertrauenswürdig: {error}.'},
      fix:{es:'Instala un certificado válido para el dominio exacto y envía la cadena intermedia completa.',en:'Install a valid certificate for the exact domain and serve the complete intermediate chain.',fr:'Installez un certificat valide pour le domaine exact et fournissez la chaîne intermédiaire complète.',it:'Installa un certificato valido per il dominio esatto e fornisci la catena intermedia completa.','pt-PT':'Instale um certificado válido para o domínio exato e envie a cadeia intermédia completa.',de:'Installieren Sie ein gültiges Zertifikat für die genaue Domain und liefern Sie die vollständige Zwischenzertifikatskette aus.'},
      technical:'Let\'s Encrypt: certbot --apache -d {host}'
    },
    'cert.unavailable':{
      detail:{es:'No hay HTTPS disponible en el puerto 443.',en:'HTTPS is not available on port 443.',fr:'HTTPS n’est pas disponible sur le port 443.',it:'HTTPS non è disponibile sulla porta 443.','pt-PT':'O HTTPS não está disponível na porta 443.',de:'HTTPS ist auf Port 443 nicht verfügbar.'},
      fix:{es:'Activa HTTPS con un certificado válido y redirige todo el tráfico HTTP a HTTPS.',en:'Enable HTTPS with a valid certificate and redirect all HTTP traffic to HTTPS.',fr:'Activez HTTPS avec un certificat valide et redirigez tout le trafic HTTP vers HTTPS.',it:'Attiva HTTPS con un certificato valido e reindirizza tutto il traffico HTTP a HTTPS.','pt-PT':'Ative HTTPS com um certificado válido e redirecione todo o tráfego HTTP para HTTPS.',de:'Aktivieren Sie HTTPS mit einem gültigen Zertifikat und leiten Sie sämtlichen HTTP-Verkehr auf HTTPS um.'}
    },
    'tls.modern':{detail:{es:'Negociado: {protocol}{cipher_suffix}.',en:'Negotiated: {protocol}{cipher_suffix}.',fr:'Négocié : {protocol}{cipher_suffix}.',it:'Negoziato: {protocol}{cipher_suffix}.','pt-PT':'Negociado: {protocol}{cipher_suffix}.',de:'Ausgehandelt: {protocol}{cipher_suffix}.'}},
    'tls.legacy':{
      detail:{es:'Se negoció una versión TLS obsoleta: {protocol}{cipher_suffix}.',en:'A legacy TLS version was negotiated: {protocol}{cipher_suffix}.',fr:'Une version TLS obsolète a été négociée : {protocol}{cipher_suffix}.',it:'È stata negoziata una versione TLS obsoleta: {protocol}{cipher_suffix}.','pt-PT':'Foi negociada uma versão TLS obsoleta: {protocol}{cipher_suffix}.',de:'Es wurde eine veraltete TLS-Version ausgehandelt: {protocol}{cipher_suffix}.'},
      fix:{es:'Desactiva TLS 1.0 y TLS 1.1.',en:'Disable TLS 1.0 and TLS 1.1.',fr:'Désactivez TLS 1.0 et TLS 1.1.',it:'Disattiva TLS 1.0 e TLS 1.1.','pt-PT':'Desative TLS 1.0 e TLS 1.1.',de:'Deaktivieren Sie TLS 1.0 und TLS 1.1.'},
      technical:'Nginx: ssl_protocols TLSv1.2 TLSv1.3;\nApache: SSLProtocol -all +TLSv1.2 +TLSv1.3'
    },
    'tls.unavailable':{detail:{es:'No se pudo negociar TLS.',en:'TLS could not be negotiated.',fr:'TLS n’a pas pu être négocié.',it:'Non è stato possibile negoziare TLS.','pt-PT':'Não foi possível negociar TLS.',de:'TLS konnte nicht ausgehandelt werden.'}},
    'legacy_tls.unverified':{
      detail:{es:'El entorno del auditor no permite comprobar versiones TLS de forma explícita.',en:'The auditor environment cannot test individual TLS versions explicitly.',fr:'L’environnement de l’auditeur ne permet pas de tester explicitement chaque version TLS.',it:'L’ambiente dell’auditor non consente di verificare esplicitamente le singole versioni TLS.','pt-PT':'O ambiente do auditor não permite testar explicitamente cada versão TLS.',de:'Die Umgebung des Auditors kann einzelne TLS-Versionen nicht ausdrücklich prüfen.'},
      fix:{es:'Actualiza PHP/OpenSSL o verifica el servidor con testssl.sh.',en:'Update PHP/OpenSSL or verify the server with testssl.sh.',fr:'Mettez à jour PHP/OpenSSL ou vérifiez le serveur avec testssl.sh.',it:'Aggiorna PHP/OpenSSL o verifica il server con testssl.sh.','pt-PT':'Atualize PHP/OpenSSL ou verifique o servidor com testssl.sh.',de:'Aktualisieren Sie PHP/OpenSSL oder prüfen Sie den Server mit testssl.sh.'}
    },
    'legacy_tls.exposed':{
      detail:{es:'El servidor aún acepta protocolos obsoletos: {protocols}.',en:'The server still accepts legacy protocols: {protocols}.',fr:'Le serveur accepte encore des protocoles obsolètes : {protocols}.',it:'Il server accetta ancora protocolli obsoleti: {protocols}.','pt-PT':'O servidor ainda aceita protocolos obsoletos: {protocols}.',de:'Der Server akzeptiert weiterhin veraltete Protokolle: {protocols}.'},
      fix:{es:'Desactiva TLS 1.0 y TLS 1.1.',en:'Disable TLS 1.0 and TLS 1.1.',fr:'Désactivez TLS 1.0 et TLS 1.1.',it:'Disattiva TLS 1.0 e TLS 1.1.','pt-PT':'Desative TLS 1.0 e TLS 1.1.',de:'Deaktivieren Sie TLS 1.0 und TLS 1.1.'},
      technical:'Nginx: ssl_protocols TLSv1.2 TLSv1.3;\nApache: SSLProtocol -all +TLSv1.2 +TLSv1.3'
    },
    'legacy_tls.safe':{detail:{es:'No se aceptaron protocolos obsoletos en las pruebas disponibles ({protocols}).',en:'No legacy protocols were accepted in the available tests ({protocols}).',fr:'Aucun protocole obsolète n’a été accepté lors des tests disponibles ({protocols}).',it:'Nessun protocollo obsoleto è stato accettato nei test disponibili ({protocols}).','pt-PT':'Nenhum protocolo obsoleto foi aceite nos testes disponíveis ({protocols}).',de:'In den verfügbaren Tests wurden keine veralteten Protokolle akzeptiert ({protocols}).'}},
    'cors.none':{detail:{es:'No se envían cabeceras CORS; el navegador aplica la política de mismo origen.',en:'No CORS headers are sent; the browser applies the same-origin policy.',fr:'Aucun en-tête CORS n’est envoyé ; le navigateur applique la politique de même origine.',it:'Non vengono inviati header CORS; il browser applica la policy della stessa origine.','pt-PT':'Não são enviados cabeçalhos CORS; o navegador aplica a política da mesma origem.',de:'Es werden keine CORS-Header gesendet; der Browser wendet die Same-Origin-Richtlinie an.'}},
    'cors.reflected_credentials':{
      detail:{es:'¡PELIGRO! El servidor refleja un origen externo no confiable y permite credenciales.',en:'DANGER! The server reflects an untrusted external origin and allows credentials.',fr:'DANGER ! Le serveur reflète une origine externe non fiable et autorise les identifiants.',it:'PERICOLO! Il server riflette un’origine esterna non attendibile e consente le credenziali.','pt-PT':'PERIGO! O servidor reflete uma origem externa não fiável e permite credenciais.',de:'GEFAHR! Der Server spiegelt eine nicht vertrauenswürdige externe Origin und erlaubt Zugangsdaten.'},
      fix:{es:'Valida Origin contra una lista explícita; no lo reflejes automáticamente.',en:'Validate Origin against an explicit allowlist; do not reflect it automatically.',fr:'Validez Origin à partir d’une liste autorisée explicite ; ne le reflétez pas automatiquement.',it:'Convalida Origin rispetto a un elenco esplicito; non rifletterlo automaticamente.','pt-PT':'Valide Origin com uma lista explícita; não o reflita automaticamente.',de:'Prüfen Sie Origin anhand einer expliziten Zulassungsliste; spiegeln Sie ihn nicht automatisch.'}
    },
    'cors.reflected':{
      detail:{es:'El servidor refleja un origen externo no confiable; la política es demasiado amplia.',en:'The server reflects an untrusted external origin; the policy is too broad.',fr:'Le serveur reflète une origine externe non fiable ; la politique est trop large.',it:'Il server riflette un’origine esterna non attendibile; la policy è troppo ampia.','pt-PT':'O servidor reflete uma origem externa não fiável; a política é demasiado ampla.',de:'Der Server spiegelt eine nicht vertrauenswürdige externe Origin; die Richtlinie ist zu weit gefasst.'},
      fix:{es:'Valida Origin contra una lista explícita de orígenes permitidos.',en:'Validate Origin against an explicit allowlist.',fr:'Validez Origin à partir d’une liste explicite d’origines autorisées.',it:'Convalida Origin rispetto a un elenco esplicito di origini consentite.','pt-PT':'Valide Origin com uma lista explícita de origens permitidas.',de:'Prüfen Sie Origin anhand einer expliziten Liste erlaubter Origins.'}
    },
    'cors.wildcard_credentials':{
      detail:{es:'CORS combina cualquier origen (*) con credenciales; es una configuración insegura.',en:'CORS combines any origin (*) with credentials; this is an unsafe configuration.',fr:'CORS combine toute origine (*) avec des identifiants ; cette configuration est dangereuse.',it:'CORS combina qualsiasi origine (*) con le credenziali; è una configurazione non sicura.','pt-PT':'O CORS combina qualquer origem (*) com credenciais; esta configuração é insegura.',de:'CORS kombiniert jede Origin (*) mit Zugangsdaten; diese Konfiguration ist unsicher.'},
      fix:{es:'No combines el comodín con credenciales; restringe el origen autorizado.',en:'Do not combine the wildcard with credentials; restrict the allowed origin.',fr:'Ne combinez pas le caractère générique avec des identifiants ; limitez l’origine autorisée.',it:'Non combinare il carattere jolly con le credenziali; limita l’origine consentita.','pt-PT':'Não combine o caráter universal com credenciais; restrinja a origem permitida.',de:'Kombinieren Sie den Platzhalter nicht mit Zugangsdaten; beschränken Sie die erlaubte Origin.'},
      technical:'Access-Control-Allow-Origin: https://your-frontend.example'
    },
    'cors.wildcard':{
      detail:{es:'CORS permite cualquier origen (*). Puede ser intencional para recursos públicos, pero es riesgoso con datos privados.',en:'CORS allows any origin (*). This may be intentional for public resources but is risky for private data.',fr:'CORS autorise toute origine (*). Cela peut être intentionnel pour des ressources publiques, mais présente un risque pour les données privées.',it:'CORS consente qualsiasi origine (*). Può essere intenzionale per risorse pubbliche, ma è rischioso per dati privati.','pt-PT':'O CORS permite qualquer origem (*). Pode ser intencional para recursos públicos, mas é arriscado para dados privados.',de:'CORS erlaubt jede Origin (*). Dies kann für öffentliche Ressourcen beabsichtigt sein, ist bei privaten Daten jedoch riskant.'},
      fix:{es:'Restringe el acceso a los orígenes que realmente lo necesiten.',en:'Restrict access to the origins that actually need it.',fr:'Limitez l’accès aux origines qui en ont réellement besoin.',it:'Limita l’accesso alle origini che ne hanno realmente bisogno.','pt-PT':'Restrinja o acesso às origens que realmente necessitam dele.',de:'Beschränken Sie den Zugriff auf Origins, die ihn tatsächlich benötigen.'},
      technical:'Access-Control-Allow-Origin: https://your-app.example'
    },
    'cors.restricted':{detail:{es:'CORS restringe el acceso al origen: {origin}.',en:'CORS restricts access to the origin: {origin}.',fr:'CORS limite l’accès à l’origine : {origin}.',it:'CORS limita l’accesso all’origine: {origin}.','pt-PT':'O CORS restringe o acesso à origem: {origin}.',de:'CORS beschränkt den Zugriff auf die Origin: {origin}.'}},
    'hsts.valid':{detail:{es:'max-age={max_age}: los navegadores forzarán HTTPS.',en:'max-age={max_age}: browsers will enforce HTTPS.',fr:'max-age={max_age} : les navigateurs imposeront HTTPS.',it:'max-age={max_age}: i browser imporranno HTTPS.','pt-PT':'max-age={max_age}: os navegadores irão impor HTTPS.',de:'max-age={max_age}: Browser erzwingen HTTPS.'}},
    'hsts.non_https':{
      detail:{es:'La respuesta final no usa HTTPS; HSTS no puede protegerla.',en:'The final response does not use HTTPS, so HSTS cannot protect it.',fr:'La réponse finale n’utilise pas HTTPS ; HSTS ne peut donc pas la protéger.',it:'La risposta finale non usa HTTPS, quindi HSTS non può proteggerla.','pt-PT':'A resposta final não utiliza HTTPS, pelo que o HSTS não a pode proteger.',de:'Die endgültige Antwort verwendet kein HTTPS und kann daher nicht durch HSTS geschützt werden.'},
      fix:{es:'Sirve la respuesta final mediante HTTPS y configura HSTS con al menos seis meses.',en:'Serve the final response over HTTPS and configure HSTS for at least six months.',fr:'Servez la réponse finale via HTTPS et configurez HSTS pour au moins six mois.',it:'Fornisci la risposta finale tramite HTTPS e configura HSTS per almeno sei mesi.','pt-PT':'Sirva a resposta final através de HTTPS e configure HSTS por pelo menos seis meses.',de:'Liefern Sie die endgültige Antwort über HTTPS aus und konfigurieren Sie HSTS für mindestens sechs Monate.'},
      technical:'Strict-Transport-Security: max-age=31536000; includeSubDomains'
    },
    'hsts.missing':{
      detail:{es:'Falta la cabecera Strict-Transport-Security.',en:'The Strict-Transport-Security header is missing.',fr:'L’en-tête Strict-Transport-Security est absent.',it:'Manca l’header Strict-Transport-Security.','pt-PT':'Falta o cabeçalho Strict-Transport-Security.',de:'Der Strict-Transport-Security-Header fehlt.'},
      fix:{es:'Configura HSTS con al menos seis meses; se recomienda un año.',en:'Configure HSTS for at least six months; one year is recommended.',fr:'Configurez HSTS pour au moins six mois ; un an est recommandé.',it:'Configura HSTS per almeno sei mesi; è consigliato un anno.','pt-PT':'Configure HSTS por pelo menos seis meses; recomenda-se um ano.',de:'Konfigurieren Sie HSTS für mindestens sechs Monate; empfohlen wird ein Jahr.'},
      technical:'Strict-Transport-Security: max-age=31536000; includeSubDomains'
    },
    'hsts.short':{
      detail:{es:'max-age={max_age} es inferior a seis meses.',en:'max-age={max_age} is shorter than six months.',fr:'max-age={max_age} est inférieur à six mois.',it:'max-age={max_age} è inferiore a sei mesi.','pt-PT':'max-age={max_age} é inferior a seis meses.',de:'max-age={max_age} ist kürzer als sechs Monate.'},
      fix:{es:'Aumenta max-age a 15552000 como mínimo; se recomienda 31536000.',en:'Increase max-age to at least 15552000; 31536000 is recommended.',fr:'Augmentez max-age à au moins 15552000 ; 31536000 est recommandé.',it:'Aumenta max-age ad almeno 15552000; si consiglia 31536000.','pt-PT':'Aumente max-age para pelo menos 15552000; recomenda-se 31536000.',de:'Erhöhen Sie max-age auf mindestens 15552000; empfohlen wird 31536000.'},
      technical:'Strict-Transport-Security: max-age=31536000; includeSubDomains'
    },
    'hsts_subdomains.enabled':{detail:{es:'includeSubDomains protege también los subdominios.',en:'includeSubDomains also protects subdomains.',fr:'includeSubDomains protège également les sous-domaines.',it:'includeSubDomains protegge anche i sottodomini.','pt-PT':'includeSubDomains também protege os subdomínios.',de:'includeSubDomains schützt auch Subdomains.'}},
    'hsts_subdomains.missing':{
      detail:{es:'HSTS no incluye subdominios; un subdominio inseguro podría debilitar la protección.',en:'HSTS does not include subdomains; an insecure subdomain could weaken protection.',fr:'HSTS n’inclut pas les sous-domaines ; un sous-domaine non sécurisé pourrait affaiblir la protection.',it:'HSTS non include i sottodomini; un sottodominio non sicuro potrebbe indebolire la protezione.','pt-PT':'O HSTS não inclui subdomínios; um subdomínio inseguro pode enfraquecer a proteção.',de:'HSTS umfasst keine Subdomains; eine unsichere Subdomain könnte den Schutz schwächen.'},
      fix:{es:'Añade includeSubDomains cuando todos los subdominios usen HTTPS.',en:'Add includeSubDomains when every subdomain uses HTTPS.',fr:'Ajoutez includeSubDomains lorsque tous les sous-domaines utilisent HTTPS.',it:'Aggiungi includeSubDomains quando tutti i sottodomini usano HTTPS.','pt-PT':'Adicione includeSubDomains quando todos os subdomínios utilizarem HTTPS.',de:'Fügen Sie includeSubDomains hinzu, sobald alle Subdomains HTTPS verwenden.'},
      technical:'Strict-Transport-Security: max-age=31536000; includeSubDomains'
    },
    'hsts_preload.enabled':{detail:{es:'La directiva preload está presente.',en:'The preload directive is present.',fr:'La directive preload est présente.',it:'La direttiva preload è presente.','pt-PT':'A diretiva preload está presente.',de:'Die preload-Direktive ist vorhanden.'}},
    'hsts_preload.missing':{
      detail:{es:'La directiva preload no está presente. Es opcional y requiere HTTPS en todo el dominio.',en:'The preload directive is not present. It is optional and requires HTTPS across the entire domain.',fr:'La directive preload est absente. Elle est facultative et exige HTTPS sur tout le domaine.',it:'La direttiva preload non è presente. È facoltativa e richiede HTTPS su tutto il dominio.','pt-PT':'A diretiva preload não está presente. É opcional e requer HTTPS em todo o domínio.',de:'Die preload-Direktive fehlt. Sie ist optional und erfordert HTTPS für die gesamte Domain.'},
      fix:{es:'Solo si todos los subdominios usan HTTPS, añade preload y registra el dominio en hstspreload.org.',en:'Only if every subdomain uses HTTPS, add preload and register the domain at hstspreload.org.',fr:'Uniquement si tous les sous-domaines utilisent HTTPS, ajoutez preload et inscrivez le domaine sur hstspreload.org.',it:'Solo se tutti i sottodomini usano HTTPS, aggiungi preload e registra il dominio su hstspreload.org.','pt-PT':'Apenas se todos os subdomínios utilizarem HTTPS, adicione preload e registe o domínio em hstspreload.org.',de:'Fügen Sie preload nur hinzu, wenn alle Subdomains HTTPS verwenden, und registrieren Sie die Domain bei hstspreload.org.'}
    },
    'csp.missing':{
      detail:{es:'No hay CSP; nada limita qué scripts pueden ejecutarse.',en:'No CSP is present, so nothing limits which scripts may execute.',fr:'Aucune CSP n’est présente ; rien ne limite les scripts pouvant être exécutés.',it:'Non è presente una CSP, quindi nulla limita gli script eseguibili.','pt-PT':'Não existe CSP, pelo que nada limita os scripts que podem ser executados.',de:'Es ist keine CSP vorhanden; ausführbare Skripte werden daher nicht eingeschränkt.'},
      fix:{es:'Empieza con una política mínima y ajústala a los recursos reales del sitio.',en:'Start with a minimal policy and adjust it to the site’s actual resources.',fr:'Commencez par une politique minimale et adaptez-la aux ressources réelles du site.',it:'Inizia con una policy minima e adattala alle risorse effettive del sito.','pt-PT':'Comece com uma política mínima e ajuste-a aos recursos reais do site.',de:'Beginnen Sie mit einer minimalen Richtlinie und passen Sie sie an die tatsächlich verwendeten Ressourcen an.'},
      technical:"Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'"
    },
    'csp.weak':{
      detail:{es:'La CSP está presente, pero es débil: {issue_codes}.',en:'CSP is present but weak: {issue_codes}.',fr:'La CSP est présente mais faible : {issue_codes}.',it:'La CSP è presente ma debole: {issue_codes}.','pt-PT':'A CSP está presente, mas é fraca: {issue_codes}.',de:'Die CSP ist vorhanden, aber schwach: {issue_codes}.'},
      fix:{es:'Elimina unsafe-inline y unsafe-eval mediante nonces o hashes, y evita comodines en script-src.',en:'Remove unsafe-inline and unsafe-eval by using nonces or hashes, and avoid wildcards in script-src.',fr:'Supprimez unsafe-inline et unsafe-eval avec des nonces ou des hachages, et évitez les caractères génériques dans script-src.',it:'Elimina unsafe-inline e unsafe-eval usando nonce o hash ed evita caratteri jolly in script-src.','pt-PT':'Remova unsafe-inline e unsafe-eval através de nonces ou hashes e evite caracteres universais em script-src.',de:'Entfernen Sie unsafe-inline und unsafe-eval mithilfe von Nonces oder Hashes und vermeiden Sie Platzhalter in script-src.'}
    },
    'csp.strong':{detail:{es:'La CSP está presente y restringe la ejecución de scripts.',en:'CSP is present and restricts script execution.',fr:'La CSP est présente et limite l’exécution des scripts.',it:'La CSP è presente e limita l’esecuzione degli script.','pt-PT':'A CSP está presente e restringe a execução de scripts.',de:'Die CSP ist vorhanden und schränkt die Skriptausführung ein.'}},
    'framing.protected':{detail:{es:'El sitio controla en qué páginas puede incrustarse.',en:'The site controls which pages may embed it.',fr:'Le site contrôle les pages autorisées à l’intégrer.',it:'Il sito controlla quali pagine possono incorporarlo.','pt-PT':'O site controla as páginas que o podem incorporar.',de:'Die Website steuert, auf welchen Seiten sie eingebettet werden darf.'}},
    'framing.missing':{
      detail:{es:'Cualquier sitio puede incrustar esta página en un iframe.',en:'Any site may embed this page in an iframe.',fr:'N’importe quel site peut intégrer cette page dans un iframe.',it:'Qualsiasi sito può incorporare questa pagina in un iframe.','pt-PT':'Qualquer site pode incorporar esta página num iframe.',de:'Jede Website kann diese Seite in einem iframe einbetten.'},
      fix:{es:'Restringe el enmarcado mediante X-Frame-Options o frame-ancestors.',en:'Restrict framing with X-Frame-Options or frame-ancestors.',fr:'Limitez l’intégration avec X-Frame-Options ou frame-ancestors.',it:'Limita l’incorporamento con X-Frame-Options o frame-ancestors.','pt-PT':'Restrinja o enquadramento com X-Frame-Options ou frame-ancestors.',de:'Beschränken Sie die Einbettung mit X-Frame-Options oder frame-ancestors.'},
      technical:'X-Frame-Options: SAMEORIGIN\nContent-Security-Policy: frame-ancestors \'self\''
    },
    'nosniff.enabled':{detail:{es:'nosniff está activo.',en:'nosniff is enabled.',fr:'nosniff est actif.',it:'nosniff è attivo.','pt-PT':'nosniff está ativo.',de:'nosniff ist aktiviert.'}},
    'nosniff.missing':{
      detail:{es:'Falta nosniff; el navegador podría interpretar incorrectamente tipos MIME.',en:'nosniff is missing; the browser may incorrectly infer MIME types.',fr:'nosniff est absent ; le navigateur peut déduire incorrectement les types MIME.',it:'Manca nosniff; il browser potrebbe dedurre erroneamente i tipi MIME.','pt-PT':'Falta nosniff; o navegador pode inferir incorretamente os tipos MIME.',de:'nosniff fehlt; der Browser könnte MIME-Typen falsch ableiten.'},
      fix:{es:'Activa la protección contra detección de tipos MIME.',en:'Enable MIME type sniffing protection.',fr:'Activez la protection contre la détection des types MIME.',it:'Attiva la protezione dal rilevamento dei tipi MIME.','pt-PT':'Ative a proteção contra deteção de tipos MIME.',de:'Aktivieren Sie den Schutz vor MIME-Type-Sniffing.'},
      technical:'X-Content-Type-Options: nosniff'
    },
    'referrer.missing':{
      detail:{es:'Referrer-Policy no está definida; se usa el valor predeterminado del navegador.',en:'Referrer-Policy is not defined; the browser default is used.',fr:'Referrer-Policy n’est pas définie ; la valeur par défaut du navigateur est utilisée.',it:'Referrer-Policy non è definita; viene usato il valore predefinito del browser.','pt-PT':'Referrer-Policy não está definida; é utilizado o valor predefinido do navegador.',de:'Referrer-Policy ist nicht definiert; der Browser-Standardwert wird verwendet.'},
      fix:{es:'Define una política de referencia restrictiva.',en:'Define a restrictive referrer policy.',fr:'Définissez une politique de référent restrictive.',it:'Definisci una policy referrer restrittiva.','pt-PT':'Defina uma política de referência restritiva.',de:'Definieren Sie eine restriktive Referrer-Richtlinie.'},
      technical:'Referrer-Policy: strict-origin-when-cross-origin'
    },
    'referrer.safe':{detail:{es:'Valor seguro: {value}.',en:'Safe value: {value}.',fr:'Valeur sûre : {value}.',it:'Valore sicuro: {value}.','pt-PT':'Valor seguro: {value}.',de:'Sicherer Wert: {value}.'}},
    'referrer.unsafe':{
      detail:{es:'Valor demasiado permisivo: {value}.',en:'Overly permissive value: {value}.',fr:'Valeur trop permissive : {value}.',it:'Valore troppo permissivo: {value}.','pt-PT':'Valor demasiado permissivo: {value}.',de:'Zu freizügiger Wert: {value}.'},
      fix:{es:'Usa una política que limite la información enviada a otros orígenes.',en:'Use a policy that limits information sent to other origins.',fr:'Utilisez une politique limitant les informations envoyées à d’autres origines.',it:'Usa una policy che limiti le informazioni inviate ad altre origini.','pt-PT':'Utilize uma política que limite a informação enviada para outras origens.',de:'Verwenden Sie eine Richtlinie, die an andere Origins gesendete Informationen begrenzt.'},
      technical:'Referrer-Policy: strict-origin-when-cross-origin'
    },
    'permissions.configured':{detail:{es:'Permissions-Policy está configurada: {value}.',en:'Permissions-Policy is configured: {value}.',fr:'Permissions-Policy est configurée : {value}.',it:'Permissions-Policy è configurata: {value}.','pt-PT':'Permissions-Policy está configurada: {value}.',de:'Permissions-Policy ist konfiguriert: {value}.'}},
    'permissions.missing':{
      detail:{es:'Cámara, micrófono y geolocalización no están restringidos explícitamente.',en:'Camera, microphone and geolocation are not explicitly restricted.',fr:'La caméra, le microphone et la géolocalisation ne sont pas explicitement limités.',it:'Fotocamera, microfono e geolocalizzazione non sono limitati esplicitamente.','pt-PT':'A câmara, o microfone e a geolocalização não estão explicitamente restringidos.',de:'Kamera, Mikrofon und Standort sind nicht ausdrücklich eingeschränkt.'},
      fix:{es:'Restringe las funciones del navegador que el sitio no utiliza.',en:'Restrict browser features that the site does not use.',fr:'Limitez les fonctions du navigateur que le site n’utilise pas.',it:'Limita le funzioni del browser non utilizzate dal sito.','pt-PT':'Restrinja as funcionalidades do navegador que o site não utiliza.',de:'Beschränken Sie Browserfunktionen, die die Website nicht verwendet.'},
      technical:'Permissions-Policy: camera=(), microphone=(), geolocation=()'
    },
    'coop.safe':{detail:{es:'Aislamiento entre ventanas activo: {value}.',en:'Cross-window isolation is enabled: {value}.',fr:'L’isolation entre fenêtres est active : {value}.',it:'L’isolamento tra finestre è attivo: {value}.','pt-PT':'O isolamento entre janelas está ativo: {value}.',de:'Die Isolation zwischen Fenstern ist aktiviert: {value}.'}},
    'coop.weak':{detail:{es:'COOP usa un valor permisivo: {value}.',en:'COOP uses a permissive value: {value}.',fr:'COOP utilise une valeur permissive : {value}.',it:'COOP usa un valore permissivo: {value}.','pt-PT':'O COOP utiliza um valor permissivo: {value}.',de:'COOP verwendet einen freizügigen Wert: {value}.'},fix:{es:'Usa same-origin si la aplicación no necesita interacción entre orígenes.',en:'Use same-origin unless the application needs cross-origin window interaction.',fr:'Utilisez same-origin sauf si l’application nécessite une interaction entre fenêtres inter-origines.',it:'Usa same-origin salvo che l’applicazione richieda interazione tra finestre cross-origin.','pt-PT':'Utilize same-origin salvo se a aplicação necessitar de interação entre janelas de origens diferentes.',de:'Verwenden Sie same-origin, sofern die Anwendung keine Origin-übergreifende Fensterinteraktion benötigt.'},technical:'Cross-Origin-Opener-Policy: same-origin'},
    'coop.missing':{detail:{es:'COOP no está definida; otras pestañas podrían interactuar con la ventana.',en:'COOP is not defined; other tabs may interact with the window.',fr:'COOP n’est pas définie ; d’autres onglets peuvent interagir avec la fenêtre.',it:'COOP non è definita; altre schede potrebbero interagire con la finestra.','pt-PT':'O COOP não está definido; outros separadores podem interagir com a janela.',de:'COOP ist nicht definiert; andere Tabs könnten mit dem Fenster interagieren.'},fix:{es:'Activa el aislamiento entre ventanas.',en:'Enable cross-window isolation.',fr:'Activez l’isolation entre fenêtres.',it:'Attiva l’isolamento tra finestre.','pt-PT':'Ative o isolamento entre janelas.',de:'Aktivieren Sie die Isolation zwischen Fenstern.'},technical:'Cross-Origin-Opener-Policy: same-origin'},
    'corp.safe':{detail:{es:'CORP restringe la carga de recursos: {value}.',en:'CORP restricts resource loading: {value}.',fr:'CORP limite le chargement des ressources : {value}.',it:'CORP limita il caricamento delle risorse: {value}.','pt-PT':'O CORP restringe o carregamento de recursos: {value}.',de:'CORP beschränkt das Laden von Ressourcen: {value}.'}},
    'corp.weak':{detail:{es:'CORP usa un valor no recomendado: {value}.',en:'CORP uses a non-recommended value: {value}.',fr:'CORP utilise une valeur non recommandée : {value}.',it:'CORP usa un valore non consigliato: {value}.','pt-PT':'O CORP utiliza um valor não recomendado: {value}.',de:'CORP verwendet einen nicht empfohlenen Wert: {value}.'},fix:{es:'Restringe los recursos al mismo origen cuando sea compatible.',en:'Restrict resources to the same origin when compatible.',fr:'Limitez les ressources à la même origine lorsque cela est compatible.',it:'Limita le risorse alla stessa origine quando compatibile.','pt-PT':'Restrinja os recursos à mesma origem quando compatível.',de:'Beschränken Sie Ressourcen nach Möglichkeit auf dieselbe Origin.'},technical:'Cross-Origin-Resource-Policy: same-origin'},
    'corp.missing':{detail:{es:'CORP no está definida; sitios externos podrían incrustar recursos.',en:'CORP is not defined; external sites may embed resources.',fr:'CORP n’est pas définie ; des sites externes peuvent intégrer des ressources.',it:'CORP non è definita; siti esterni potrebbero incorporare risorse.','pt-PT':'O CORP não está definido; sites externos podem incorporar recursos.',de:'CORP ist nicht definiert; externe Websites könnten Ressourcen einbetten.'},fix:{es:'Define una política de carga de recursos compatible con el sitio.',en:'Define a resource loading policy compatible with the site.',fr:'Définissez une politique de chargement des ressources compatible avec le site.',it:'Definisci una policy di caricamento delle risorse compatibile con il sito.','pt-PT':'Defina uma política de carregamento de recursos compatível com o site.',de:'Definieren Sie eine mit der Website kompatible Richtlinie zum Laden von Ressourcen.'},technical:'Cross-Origin-Resource-Policy: same-origin'},
    'cookies.none':{detail:{es:'La respuesta no establece cookies.',en:'The response does not set cookies.',fr:'La réponse ne définit aucun cookie.',it:'La risposta non imposta cookie.','pt-PT':'A resposta não define cookies.',de:'Die Antwort setzt keine Cookies.'}},
    'cookies.safe':{detail:{es:'{count} cookie(s) con Secure, HttpOnly y SameSite.',en:'{count} cookie(s) include Secure, HttpOnly and SameSite.',fr:'{count} cookie(s) incluent Secure, HttpOnly et SameSite.',it:'{count} cookie includono Secure, HttpOnly e SameSite.','pt-PT':'{count} cookie(s) incluem Secure, HttpOnly e SameSite.',de:'{count} Cookie(s) enthalten Secure, HttpOnly und SameSite.'}},
    'cookies.unsafe':{
      detail:{es:'Cookies con atributos incompletos: {cookie_issues}.',en:'Cookies have incomplete attributes: {cookie_issues}.',fr:'Des attributs de cookies sont incomplets : {cookie_issues}.',it:'I cookie hanno attributi incompleti: {cookie_issues}.','pt-PT':'Os cookies têm atributos incompletos: {cookie_issues}.',de:'Cookies haben unvollständige Attribute: {cookie_issues}.'},
      fix:{es:'Configura Secure, HttpOnly y SameSite según la función de cada cookie.',en:'Configure Secure, HttpOnly and SameSite according to each cookie’s purpose.',fr:'Configurez Secure, HttpOnly et SameSite selon la fonction de chaque cookie.',it:'Configura Secure, HttpOnly e SameSite in base alla funzione di ogni cookie.','pt-PT':'Configure Secure, HttpOnly e SameSite de acordo com a função de cada cookie.',de:'Konfigurieren Sie Secure, HttpOnly und SameSite entsprechend dem Zweck jedes Cookies.'},
      technical:'PHP: session.cookie_secure=1, session.cookie_httponly=1, session.cookie_samesite=Lax'
    },
    'leak.exposed':{detail:{es:'Se exponen versiones o tecnologías: {values}.',en:'Versions or technologies are disclosed: {values}.',fr:'Des versions ou technologies sont divulguées : {values}.',it:'Vengono divulgate versioni o tecnologie: {values}.','pt-PT':'São divulgadas versões ou tecnologias: {values}.',de:'Versionen oder Technologien werden offengelegt: {values}.'},fix:{es:'Reduce la información tecnológica expuesta por las cabeceras.',en:'Reduce technology information disclosed by response headers.',fr:'Réduisez les informations technologiques divulguées par les en-têtes.',it:'Riduci le informazioni tecnologiche divulgate dagli header.','pt-PT':'Reduza a informação tecnológica divulgada pelos cabeçalhos.',de:'Reduzieren Sie die durch Antwort-Header offengelegten Technologieinformationen.'},technical:'PHP: expose_php = Off\nApache: ServerTokens Prod\nNginx: server_tokens off;'},
    'leak.safe':{detail:{es:'No se exponen versiones del servidor ni del lenguaje.',en:'No server or language versions are disclosed.',fr:'Aucune version du serveur ou du langage n’est divulguée.',it:'Non vengono divulgate versioni del server o del linguaggio.','pt-PT':'Não são divulgadas versões do servidor ou da linguagem.',de:'Es werden keine Server- oder Sprachversionen offengelegt.'}},
    'expiry.valid':{detail:{es:'El certificado vence en {days} días.',en:'The certificate expires in {days} days.',fr:'Le certificat expire dans {days} jours.',it:'Il certificato scade tra {days} giorni.','pt-PT':'O certificado expira dentro de {days} dias.',de:'Das Zertifikat läuft in {days} Tagen ab.'}},
    'expiry.expiring':{detail:{es:'El certificado vence en {days} días.',en:'The certificate expires in {days} days.',fr:'Le certificat expire dans {days} jours.',it:'Il certificato scade tra {days} giorni.','pt-PT':'O certificado expira dentro de {days} dias.',de:'Das Zertifikat läuft in {days} Tagen ab.'},fix:{es:'Renueva el certificado y automatiza las renovaciones.',en:'Renew the certificate and automate renewals.',fr:'Renouvelez le certificat et automatisez les renouvellements.',it:'Rinnova il certificato e automatizza i rinnovi.','pt-PT':'Renove o certificado e automatize as renovações.',de:'Erneuern Sie das Zertifikat und automatisieren Sie Verlängerungen.'},technical:'certbot renew'},
    'expiry.expired':{detail:{es:'El certificado expiró hace {days} días.',en:'The certificate expired {days} days ago.',fr:'Le certificat a expiré il y a {days} jours.',it:'Il certificato è scaduto {days} giorni fa.','pt-PT':'O certificado expirou há {days} dias.',de:'Das Zertifikat ist seit {days} Tagen abgelaufen.'},fix:{es:'Renueva el certificado inmediatamente y automatiza las renovaciones.',en:'Renew the certificate immediately and automate renewals.',fr:'Renouvelez immédiatement le certificat et automatisez les renouvellements.',it:'Rinnova immediatamente il certificato e automatizza i rinnovi.','pt-PT':'Renove imediatamente o certificado e automatize as renovações.',de:'Erneuern Sie das Zertifikat sofort und automatisieren Sie Verlängerungen.'},technical:'certbot renew'},
    'expiry.unavailable':{detail:{es:'No hay un certificado que evaluar.',en:'There is no certificate to evaluate.',fr:'Aucun certificat ne peut être évalué.',it:'Non è disponibile un certificato da valutare.','pt-PT':'Não existe um certificado para avaliar.',de:'Es ist kein Zertifikat zur Bewertung vorhanden.'}},
    'redirect.port_closed':{detail:{es:'El puerto 80 no responde; el sitio solo se sirve mediante HTTPS.',en:'Port 80 does not respond; the site is served only over HTTPS.',fr:'Le port 80 ne répond pas ; le site est servi uniquement via HTTPS.',it:'La porta 80 non risponde; il sito viene servito solo tramite HTTPS.','pt-PT':'A porta 80 não responde; o site é servido apenas através de HTTPS.',de:'Port 80 antwortet nicht; die Website wird ausschließlich über HTTPS ausgeliefert.'}},
    'redirect.enabled':{detail:{es:'HTTP redirige a HTTPS (estado {status}).',en:'HTTP redirects to HTTPS (status {status}).',fr:'HTTP redirige vers HTTPS (état {status}).',it:'HTTP reindirizza a HTTPS (stato {status}).','pt-PT':'O HTTP redireciona para HTTPS (estado {status}).',de:'HTTP leitet auf HTTPS weiter (Status {status}).'}},
    'redirect.missing':{detail:{es:'HTTP responde con estado {status} y no redirige a HTTPS.',en:'HTTP responds with status {status} and does not redirect to HTTPS.',fr:'HTTP répond avec l’état {status} et ne redirige pas vers HTTPS.',it:'HTTP risponde con stato {status} e non reindirizza a HTTPS.','pt-PT':'O HTTP responde com estado {status} e não redireciona para HTTPS.',de:'HTTP antwortet mit Status {status} und leitet nicht auf HTTPS weiter.'},fix:{es:'Redirige permanentemente todo el tráfico HTTP a HTTPS.',en:'Permanently redirect all HTTP traffic to HTTPS.',fr:'Redirigez définitivement tout le trafic HTTP vers HTTPS.',it:'Reindirizza permanentemente tutto il traffico HTTP a HTTPS.','pt-PT':'Redirecione permanentemente todo o tráfego HTTP para HTTPS.',de:'Leiten Sie sämtlichen HTTP-Verkehr dauerhaft auf HTTPS um.'},technical:'Apache: RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\nNginx: return 301 https://$host$request_uri;'},
    'sectxt.unknown':{detail:{es:'No se pudo verificar por completo security.txt.',en:'security.txt could not be fully verified.',fr:'security.txt n’a pas pu être entièrement vérifié.',it:'Non è stato possibile verificare completamente security.txt.','pt-PT':'Não foi possível verificar completamente o security.txt.',de:'security.txt konnte nicht vollständig geprüft werden.'}},
    'sectxt.valid':{detail:{es:'security.txt contiene un contacto y una fecha de expiración vigente.',en:'security.txt contains a contact and a current expiration date.',fr:'security.txt contient un contact et une date d’expiration valide.',it:'security.txt contiene un contatto e una data di scadenza valida.','pt-PT':'O security.txt contém um contacto e uma data de expiração válida.',de:'security.txt enthält einen Kontakt und ein gültiges Ablaufdatum.'}},
    'sectxt.invalid':{detail:{es:'security.txt debe incluir Contact y una fecha Expires futura.',en:'security.txt must include Contact and a future Expires date.',fr:'security.txt doit inclure Contact et une date Expires future.',it:'security.txt deve includere Contact e una data Expires futura.','pt-PT':'O security.txt deve incluir Contact e uma data Expires futura.',de:'security.txt muss Contact und ein zukünftiges Expires-Datum enthalten.'},fix:{es:'Publica un archivo conforme con RFC 9116.',en:'Publish a file compliant with RFC 9116.',fr:'Publiez un fichier conforme à la RFC 9116.',it:'Pubblica un file conforme alla RFC 9116.','pt-PT':'Publique um ficheiro em conformidade com a RFC 9116.',de:'Veröffentlichen Sie eine RFC-9116-konforme Datei.'},technical:'Contact: mailto:seguridad@{host}\nExpires: {expires}\nPreferred-Languages: es, en'},
    'cipher.weak':{detail:{es:'Cifrado débil detectado: {cipher}.',en:'Weak cipher detected: {cipher}.',fr:'Chiffrement faible détecté : {cipher}.',it:'Cifratura debole rilevata: {cipher}.','pt-PT':'Cifra fraca detetada: {cipher}.',de:'Schwache Verschlüsselung erkannt: {cipher}.'},fix:{es:'Configura suites AEAD modernas.',en:'Configure modern AEAD cipher suites.',fr:'Configurez des suites AEAD modernes.',it:'Configura suite AEAD moderne.','pt-PT':'Configure conjuntos de cifras AEAD modernos.',de:'Konfigurieren Sie moderne AEAD-Chiffren.'},technical:'TLS_AES_256_GCM_SHA384\nTLS_CHACHA20_POLY1305_SHA256'},
    'cipher.modern':{detail:{es:'Suite de cifrado moderna activa: {cipher}.',en:'Modern cipher suite active: {cipher}.',fr:'Suite de chiffrement moderne active : {cipher}.',it:'Suite di cifratura moderna attiva: {cipher}.','pt-PT':'Conjunto de cifras moderno ativo: {cipher}.',de:'Moderne Cipher Suite aktiv: {cipher}.'}},
    'cipher.acceptable':{detail:{es:'Cifrado aceptable, pero no óptimo: {cipher}.',en:'Acceptable but not optimal cipher: {cipher}.',fr:'Chiffrement acceptable mais non optimal : {cipher}.',it:'Cifratura accettabile ma non ottimale: {cipher}.','pt-PT':'Cifra aceitável, mas não ideal: {cipher}.',de:'Akzeptable, aber nicht optimale Verschlüsselung: {cipher}.'},fix:{es:'Prioriza AES-GCM o ChaCha20.',en:'Prioritize AES-GCM or ChaCha20.',fr:'Privilégiez AES-GCM ou ChaCha20.',it:'Dai priorità ad AES-GCM o ChaCha20.','pt-PT':'Dê prioridade a AES-GCM ou ChaCha20.',de:'Priorisieren Sie AES-GCM oder ChaCha20.'}},
    'cipher.unavailable':{detail:{es:'No se pudo determinar la suite de cifrado negociada.',en:'The negotiated cipher suite could not be determined.',fr:'La suite de chiffrement négociée n’a pas pu être déterminée.',it:'Non è stato possibile determinare la suite di cifratura negoziata.','pt-PT':'Não foi possível determinar o conjunto de cifras negociado.',de:'Die ausgehandelte Cipher Suite konnte nicht bestimmt werden.'}},
    'spf.unknown':{detail:{es:'No se pudo consultar SPF por un error DNS.',en:'SPF could not be queried because of a DNS error.',fr:'SPF n’a pas pu être interrogé en raison d’une erreur DNS.',it:'Non è stato possibile interrogare SPF a causa di un errore DNS.','pt-PT':'Não foi possível consultar o SPF devido a um erro de DNS.',de:'SPF konnte wegen eines DNS-Fehlers nicht abgefragt werden.'}},
    'spf.multiple':{detail:{es:'Hay {count} registros SPF; esto provoca PermError.',en:'There are {count} SPF records; this causes PermError.',fr:'Il existe {count} enregistrements SPF ; cela provoque PermError.',it:'Sono presenti {count} record SPF; ciò causa PermError.','pt-PT':'Existem {count} registos SPF; isto provoca PermError.',de:'Es sind {count} SPF-Einträge vorhanden; dies verursacht PermError.'},fix:{es:'Consolida todos los mecanismos en un único registro SPF.',en:'Consolidate all mechanisms into one SPF record.',fr:'Regroupez tous les mécanismes dans un seul enregistrement SPF.',it:'Consolida tutti i meccanismi in un unico record SPF.','pt-PT':'Consolide todos os mecanismos num único registo SPF.',de:'Fassen Sie alle Mechanismen in einem SPF-Eintrag zusammen.'}},
    'spf.strict':{detail:{es:'SPF estricto activo: {record}',en:'Strict SPF is active: {record}',fr:'SPF strict actif : {record}',it:'SPF rigoroso attivo: {record}','pt-PT':'SPF estrito ativo: {record}',de:'Striktes SPF ist aktiv: {record}'}},
    'spf.softfail':{detail:{es:'SPF usa softfail: {record}',en:'SPF uses softfail: {record}',fr:'SPF utilise softfail : {record}',it:'SPF usa softfail: {record}','pt-PT':'O SPF utiliza softfail: {record}',de:'SPF verwendet Softfail: {record}'},fix:{es:'Tras verificar todos los remitentes, finaliza la política con -all.',en:'After verifying all senders, end the policy with -all.',fr:'Après avoir vérifié tous les expéditeurs, terminez la politique par -all.',it:'Dopo aver verificato tutti i mittenti, termina la policy con -all.','pt-PT':'Depois de verificar todos os remetentes, termine a política com -all.',de:'Beenden Sie die Richtlinie nach Prüfung aller Absender mit -all.'},technical:'v=spf1 mx include:your-provider.example -all'},
    'spf.permissive':{detail:{es:'SPF es permisivo o incompleto: {record}',en:'SPF is permissive or incomplete: {record}',fr:'SPF est permissif ou incomplet : {record}',it:'SPF è permissivo o incompleto: {record}','pt-PT':'O SPF é permissivo ou incompleto: {record}',de:'SPF ist freizügig oder unvollständig: {record}'},fix:{es:'Autoriza únicamente los remitentes reales y finaliza con -all.',en:'Authorize only real senders and end with -all.',fr:'Autorisez uniquement les expéditeurs réels et terminez par -all.',it:'Autorizza solo i mittenti reali e termina con -all.','pt-PT':'Autorize apenas os remetentes reais e termine com -all.',de:'Autorisieren Sie nur tatsächliche Absender und schließen Sie mit -all ab.'}},
    'spf.missing':{detail:{es:'No se encontró un registro SPF para {host}.',en:'No SPF record was found for {host}.',fr:'Aucun enregistrement SPF n’a été trouvé pour {host}.',it:'Nessun record SPF trovato per {host}.','pt-PT':'Não foi encontrado um registo SPF para {host}.',de:'Für {host} wurde kein SPF-Eintrag gefunden.'},fix:{es:'Publica un registro SPF que incluya los proveedores autorizados.',en:'Publish an SPF record containing authorized providers.',fr:'Publiez un enregistrement SPF contenant les fournisseurs autorisés.',it:'Pubblica un record SPF con i provider autorizzati.','pt-PT':'Publique um registo SPF com os fornecedores autorizados.',de:'Veröffentlichen Sie einen SPF-Eintrag mit den autorisierten Anbietern.'},technical:'v=spf1 mx ~all'},
    'dmarc.unknown':{detail:{es:'No se pudo consultar DMARC por un error DNS.',en:'DMARC could not be queried because of a DNS error.',fr:'DMARC n’a pas pu être interrogé en raison d’une erreur DNS.',it:'Non è stato possibile interrogare DMARC a causa di un errore DNS.','pt-PT':'Não foi possível consultar o DMARC devido a um erro de DNS.',de:'DMARC konnte wegen eines DNS-Fehlers nicht abgefragt werden.'}},
    'dmarc.enforced':{detail:{es:'DMARC p={policy} aplicado al {percent}%.',en:'DMARC p={policy} is enforced at {percent}%.',fr:'DMARC p={policy} est appliqué à {percent} %.',it:'DMARC p={policy} è applicato al {percent}%.','pt-PT':'DMARC p={policy} é aplicado a {percent}%.',de:'DMARC p={policy} wird zu {percent}% durchgesetzt.'}},
    'dmarc.partial':{detail:{es:'DMARC p={policy} solo se aplica al {percent}%.',en:'DMARC p={policy} is only enforced at {percent}%.',fr:'DMARC p={policy} n’est appliqué qu’à {percent} %.',it:'DMARC p={policy} è applicato solo al {percent}%.','pt-PT':'DMARC p={policy} é aplicado apenas a {percent}%.',de:'DMARC p={policy} wird nur zu {percent}% durchgesetzt.'},fix:{es:'Aumenta gradualmente pct hasta 100.',en:'Gradually increase pct to 100.',fr:'Augmentez progressivement pct jusqu’à 100.',it:'Aumenta gradualmente pct fino a 100.','pt-PT':'Aumente gradualmente pct até 100.',de:'Erhöhen Sie pct schrittweise auf 100.'}},
    'dmarc.monitoring':{detail:{es:'DMARC está en modo monitoreo (p={policy}) y no bloquea suplantaciones.',en:'DMARC is in monitoring mode (p={policy}) and does not block spoofing.',fr:'DMARC est en mode surveillance (p={policy}) et ne bloque pas l’usurpation.',it:'DMARC è in modalità monitoraggio (p={policy}) e non blocca lo spoofing.','pt-PT':'O DMARC está em modo de monitorização (p={policy}) e não bloqueia falsificações.',de:'DMARC befindet sich im Überwachungsmodus (p={policy}) und blockiert Spoofing nicht.'},fix:{es:'Avanza gradualmente a quarantine o reject con pct=100.',en:'Gradually move to quarantine or reject with pct=100.',fr:'Passez progressivement à quarantine ou reject avec pct=100.',it:'Passa gradualmente a quarantine o reject con pct=100.','pt-PT':'Avance gradualmente para quarantine ou reject com pct=100.',de:'Wechseln Sie schrittweise zu quarantine oder reject mit pct=100.'},technical:'v=DMARC1; p=quarantine; pct=100; rua=mailto:dmarc-reports@{host}'},
    'dmarc.missing':{detail:{es:'Falta un registro DMARC en _dmarc.{host}.',en:'A DMARC record is missing at _dmarc.{host}.',fr:'Un enregistrement DMARC manque sur _dmarc.{host}.',it:'Manca un record DMARC in _dmarc.{host}.','pt-PT':'Falta um registo DMARC em _dmarc.{host}.',de:'Unter _dmarc.{host} fehlt ein DMARC-Eintrag.'},fix:{es:'Publica una política DMARC y habilita informes agregados.',en:'Publish a DMARC policy and enable aggregate reports.',fr:'Publiez une politique DMARC et activez les rapports agrégés.',it:'Pubblica una policy DMARC e abilita i report aggregati.','pt-PT':'Publique uma política DMARC e ative relatórios agregados.',de:'Veröffentlichen Sie eine DMARC-Richtlinie und aktivieren Sie aggregierte Berichte.'},technical:'v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@{host}'},
    'caa.unknown':{detail:{es:'No se pudieron consultar los registros CAA por un error DNS.',en:'CAA records could not be queried because of a DNS error.',fr:'Les enregistrements CAA n’ont pas pu être interrogés en raison d’une erreur DNS.',it:'Non è stato possibile interrogare i record CAA a causa di un errore DNS.','pt-PT':'Não foi possível consultar os registos CAA devido a um erro de DNS.',de:'CAA-Einträge konnten wegen eines DNS-Fehlers nicht abgefragt werden.'}},
    'caa.configured':{detail:{es:'CAA restringe las autoridades autorizadas: {records}.',en:'CAA restricts authorized certificate authorities: {records}.',fr:'CAA limite les autorités de certification autorisées : {records}.',it:'CAA limita le autorità di certificazione autorizzate: {records}.','pt-PT':'O CAA restringe as autoridades certificadoras autorizadas: {records}.',de:'CAA beschränkt die autorisierten Zertifizierungsstellen: {records}.'}},
    'caa.missing':{detail:{es:'No hay registros CAA; cualquier autoridad pública podría emitir si fuera engañada.',en:'No CAA records exist; any public authority could issue a certificate if deceived.',fr:'Aucun enregistrement CAA n’existe ; toute autorité publique pourrait émettre un certificat si elle était trompée.',it:'Non esistono record CAA; qualsiasi autorità pubblica potrebbe emettere un certificato se ingannata.','pt-PT':'Não existem registos CAA; qualquer autoridade pública poderá emitir um certificado se for enganada.',de:'Es gibt keine CAA-Einträge; jede öffentliche Zertifizierungsstelle könnte bei Täuschung ein Zertifikat ausstellen.'},fix:{es:'Autoriza únicamente a tu proveedor de certificados.',en:'Authorize only your certificate provider.',fr:'Autorisez uniquement votre fournisseur de certificats.',it:'Autorizza solo il tuo fornitore di certificati.','pt-PT':'Autorize apenas o seu fornecedor de certificados.',de:'Autorisieren Sie nur Ihren Zertifikatsanbieter.'},technical:'0 issue "letsencrypt.org"\n0 issuewild "letsencrypt.org"'}
  };
  const catalogLabel = (catalog, key, code, fallback) => (catalog[key] && (catalog[key][code] || catalog[key].es)) || fallback;
  const localizeCheckMessage = (check, code) => {
    const entry = CHECK_MESSAGES[check.message_code];
    if (!entry) return { detail: check.detail, fix: check.fix };
    const params = check.message_params || {};
    const format = template => (template || '').replace(/\{([a-z_]+)\}/g, (_, key) => {
      const value = params[key];
      if (key === 'issue_codes' && Array.isArray(value)) {
        return value.map(issue => catalogLabel(CSP_ISSUE_LABELS, issue, code, issue)).join('; ');
      }
      if (key === 'cookie_issues') {
        return ['no_secure', 'no_http_only', 'no_same_site'].flatMap(issue => {
          const names = params[issue];
          if (!Array.isArray(names) || names.length === 0) return [];
          const template = catalogLabel(COOKIE_ISSUE_LABELS, issue, code, issue + ': {names}');
          return [template.replace('{names}', names.join(', '))];
        }).join(' | ');
      }
      return Array.isArray(value) ? value.join(', ') : String(value ?? '');
    });
    const translated = field => format(entry[field] && (entry[field][code] || entry[field].es));
    const fix = translated('fix');
    return { detail: translated('detail') || check.detail, fix: fix ? fix + (entry.technical ? '\n' + format(entry.technical) : '') : check.fix };
  };
  const RISK_LABELS = {
    es:{bajo:'bajo',medio:'medio',alto:'alto'}, en:{bajo:'low',medio:'medium',alto:'high'},
    fr:{bajo:'faible',medio:'moyen',alto:'élevé'}, it:{bajo:'basso',medio:'medio',alto:'alto'},
    'pt-PT':{bajo:'baixo',medio:'médio',alto:'alto'}, de:{bajo:'niedrig',medio:'mittel',alto:'hoch'}
  };
  const REPORT_COPY = {
    es:{newRequest:'Puedes enviar una nueva solicitud. Límite: 10 análisis por hora y 4 por sitio por minuto.',nextRequest:'Podrás solicitar otro análisis en ',tally:n=>`${n.fail} fallas, ${n.warn} mejorables, ${n.unknown} no verificados, ${n.pass} correctos.`,note:'Evaluación externa orientativa; no reemplaza un pentest.',cta:'Podemos corregir esto por ti, sin tocar tu diseño ni tu contenido.',contact:'Hablar con Green PC',timeout:'El análisis superó el tiempo máximo de 65 segundos.',failed:'Revisa el dominio e inténtalo de nuevo en unos segundos.'},
    en:{newRequest:'You can submit a new request. Limit: 10 analyses per hour and 4 per site per minute.',nextRequest:'You can request another analysis in ',tally:n=>`${n.fail} failures, ${n.warn} improvements, ${n.unknown} not verified, ${n.pass} compliant.`,note:'External guidance only; it does not replace a penetration test.',cta:'We can fix this for you without changing your design or content.',contact:'Talk to Green PC',timeout:'The analysis exceeded the 65-second time limit.',failed:'Check the domain and try again in a few seconds.'},
    fr:{newRequest:'Vous pouvez envoyer une nouvelle demande. Limite : 10 analyses par heure et 4 par site et par minute.',nextRequest:'Vous pourrez demander une nouvelle analyse dans ',tally:n=>`${n.fail} échecs, ${n.warn} à améliorer, ${n.unknown} non vérifiés, ${n.pass} conformes.`,note:'Évaluation externe indicative ; elle ne remplace pas un test d’intrusion.',cta:'Nous pouvons corriger ces points sans modifier votre design ni votre contenu.',contact:'Contacter Green PC',timeout:'L’analyse a dépassé la limite de 65 secondes.',failed:'Vérifiez le domaine et réessayez dans quelques secondes.'},
    it:{newRequest:'Puoi inviare una nuova richiesta. Limite: 10 analisi all’ora e 4 per sito al minuto.',nextRequest:'Potrai richiedere un’altra analisi tra ',tally:n=>`${n.fail} errori, ${n.warn} da migliorare, ${n.unknown} non verificati, ${n.pass} conformi.`,note:'Valutazione esterna indicativa; non sostituisce un penetration test.',cta:'Possiamo correggere questi punti senza modificare design o contenuti.',contact:'Parla con Green PC',timeout:'L’analisi ha superato il limite di 65 secondi.',failed:'Controlla il dominio e riprova tra pochi secondi.'},
    'pt-PT':{newRequest:'Pode enviar um novo pedido. Limite: 10 análises por hora e 4 por site por minuto.',nextRequest:'Poderá pedir outra análise dentro de ',tally:n=>`${n.fail} falhas, ${n.warn} a melhorar, ${n.unknown} não verificados, ${n.pass} conformes.`,note:'Avaliação externa indicativa; não substitui um teste de intrusão.',cta:'Podemos corrigir estes pontos sem alterar o design ou o conteúdo.',contact:'Falar com a Green PC',timeout:'A análise excedeu o limite de 65 segundos.',failed:'Verifique o domínio e tente novamente dentro de alguns segundos.'},
    de:{newRequest:'Sie können eine neue Anfrage senden. Limit: 10 Analysen pro Stunde und 4 pro Website und Minute.',nextRequest:'Eine weitere Analyse ist möglich in ',tally:n=>`${n.fail} Fehler, ${n.warn} verbesserbar, ${n.unknown} nicht geprüft, ${n.pass} konform.`,note:'Externe Orientierung; ersetzt keinen Penetrationstest.',cta:'Wir können diese Punkte beheben, ohne Design oder Inhalte zu ändern.',contact:'Green PC kontaktieren',timeout:'Die Analyse hat das Zeitlimit von 65 Sekunden überschritten.',failed:'Prüfen Sie die Domain und versuchen Sie es in einigen Sekunden erneut.'}
  };
  const PROGRESS_COPY = {
    es:{request:'No cierres esta página; puede tardar hasta un minuto.',preparing:'Preparando análisis…',validating:'Validando el destino de forma segura.',stages:[['Verificando transporte…','Certificado, protocolos TLS y redirecciones.'],['Revisando cabeceras…','CSP, cookies, CORS y controles del navegador.'],['Buscando exposiciones…','Archivos de configuración, copias y repositorios.'],['Analizando DNS y correo…','SPF, DMARC y CAA.']]},
    en:{request:'Do not close this page; it may take up to one minute.',preparing:'Preparing analysis…',validating:'Validating the target securely.',stages:[['Checking transport…','Certificate, TLS protocols and redirects.'],['Reviewing headers…','CSP, cookies, CORS and browser controls.'],['Looking for exposures…','Configuration files, backups and repositories.'],['Analysing DNS and email…','SPF, DMARC and CAA.']]},
    fr:{request:'Ne fermez pas cette page ; l’analyse peut durer jusqu’à une minute.',preparing:'Préparation de l’analyse…',validating:'Validation sécurisée de la cible.',stages:[['Vérification du transport…','Certificat, protocoles TLS et redirections.'],['Contrôle des en-têtes…','CSP, cookies, CORS et contrôles du navigateur.'],['Recherche d’expositions…','Fichiers de configuration, sauvegardes et dépôts.'],['Analyse du DNS et de la messagerie…','SPF, DMARC et CAA.']]},
    it:{request:'Non chiudere questa pagina; l’analisi può richiedere fino a un minuto.',preparing:'Preparazione dell’analisi…',validating:'Validazione sicura della destinazione.',stages:[['Verifica del trasporto…','Certificato, protocolli TLS e reindirizzamenti.'],['Controllo degli header…','CSP, cookie, CORS e controlli del browser.'],['Ricerca di esposizioni…','File di configurazione, backup e repository.'],['Analisi DNS ed email…','SPF, DMARC e CAA.']]},
    'pt-PT':{request:'Não feche esta página; a análise pode demorar até um minuto.',preparing:'A preparar a análise…',validating:'A validar o destino de forma segura.',stages:[['A verificar o transporte…','Certificado, protocolos TLS e redirecionamentos.'],['A rever os cabeçalhos…','CSP, cookies, CORS e controlos do navegador.'],['A procurar exposições…','Ficheiros de configuração, cópias e repositórios.'],['A analisar DNS e correio…','SPF, DMARC e CAA.']]},
    de:{request:'Schließen Sie diese Seite nicht; die Analyse kann bis zu einer Minute dauern.',preparing:'Analyse wird vorbereitet…',validating:'Das Ziel wird sicher validiert.',stages:[['Transport wird geprüft…','Zertifikat, TLS-Protokolle und Weiterleitungen.'],['Header werden geprüft…','CSP, Cookies, CORS und Browserkontrollen.'],['Offenlegungen werden gesucht…','Konfigurationsdateien, Sicherungen und Repositorys.'],['DNS und E-Mail werden analysiert…','SPF, DMARC und CAA.']]}
  };
  const currentLocale = () => LOCALES[lang.value] || LOCALES.es;
  const currentCopy = () => REPORT_COPY[lang.value] || REPORT_COPY.es;
  let cooldownTimer = null;
  const setServiceState = (state, text) => { serviceStatus.dataset.state = state; serviceText.textContent = text; };
  const setLimitNote = (html) => { limitNote.replaceChildren(); limitNote.append(...html); };
  const textNode = text => document.createTextNode(text);
  function setReady() {
    window.clearInterval(cooldownTimer);
    btn.disabled = false;
    btn.textContent = currentLocale().scan;
    setServiceState('ready', currentLocale().ready);
    setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().server }), textNode(currentCopy().newRequest)]);
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
      setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().protection }), textNode(currentCopy().nextRequest + time + '.')]);
    };
    tick();
    cooldownTimer = window.setInterval(tick, 1000);
  }
  const el = (tag, cls, text) => { const n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };
  const LABEL = (status, code = lang.value) => {
    const locale = LOCALES[code] || LOCALES.es;
    return status === 'unknown' ? (UNKNOWN_LABELS[code] || UNKNOWN_LABELS.es) : ({ pass: locale.correct, warn: locale.improvable, fail: locale.failure }[status]);
  };

  function render(d) {
    const L = LOCALES[d.lang] || currentLocale();
    const T = REPORT_COPY[d.lang] || currentCopy();
    const checkTitle = check => catalogLabel(CHECK_LABELS, check.id, d.lang, check.title);
    const groupTitle = check => catalogLabel(GROUP_LABELS, check.group_id, d.lang, check.group);
    const checkMessage = check => localizeCheckMessage(check, d.lang);
    document.documentElement.lang = d.lang || lang.value;
    out.replaceChildren();
    const wrap = el('div', 'report');

    const s = el('aside', 'summary');
    s.append(el('p', 'report-section-label', L.executive));
    const risk = d.risk || (d.grade === 'A' || d.grade === 'B' ? 'bajo' : (d.grade === 'C' ? 'medio' : 'alto'));
    const riskLabel = (RISK_LABELS[d.lang] || RISK_LABELS.es)[risk] || risk;
    s.append(el('p', 'grade g-' + d.grade, d.grade), el('p', 'score', d.score + ' ' + L.score), el('span', 'risk risk-' + risk, L.risk + riskLabel));
    const dl = el('dl');
    const row = (k, v) => { if (v) { dl.append(el('dt', '', k), el('dd', '', v)); } };
    row(L.site, d.host);
    row('IP', d.ip);
    if (d.tls && d.tls.protocol) row('TLS', d.tls.protocol);
    if (d.tls && d.tls.cipher) row(L.cipher, d.tls.cipher);
    if (d.tls && d.tls.issuer) row(L.issuer, d.tls.issuer);
    row(L.response, d.status + ' · ' + d.ms + ' ms');
    if (Number.isFinite(d.analysis_ms)) row(DURATION_LABELS[d.lang] || DURATION_LABELS.es, (d.analysis_ms / 1000).toFixed(1) + ' s');
    if (Number.isFinite(d.coverage)) row(COVERAGE_LABELS[d.lang] || COVERAGE_LABELS.es, d.coverage + '%');
    if (d.chain.length > 1) row(L.redirects, String(d.chain.length - 1));
    s.append(dl);

    const n = { pass: 0, warn: 0, fail: 0, unknown: 0 };
    d.checks.forEach(c => n[c.status]++);
    s.append(el('p', 'tally', T.tally(n)), el('p', 'summary-note', T.note));
    const actionCopy = PDF_ACTION_COPY[d.lang] || PDF_ACTION_COPY.es;
    const buildPdfPayload = () => ({
      lang: d.lang || 'es',
      localeCode: L.code,
      title: L.report,
      subtitle: L.automated,
      host: d.host,
      ip: d.ip,
      tlsProtocol: d.tls && d.tls.protocol,
      cipher: d.tls && d.tls.cipher,
      issuer: d.tls && d.tls.issuer,
      response: d.status + ' · ' + d.ms + ' ms',
      duration: Number.isFinite(d.analysis_ms) ? (d.analysis_ms / 1000).toFixed(1) + ' s' : '',
      coverage: Number.isFinite(d.coverage) ? d.coverage : null,
      reportId: d.report_id || L.reference,
      issued,
      grade: d.grade,
      score: d.score,
      risk,
      riskLabel,
      counts: n,
      labels: { score:L.score, site:L.site, cipher:L.cipher, issuer:L.issuer, response:L.response, reference:L.reference, issued:L.issued },
      priorities: (d.priorities || []).map(check => {
        const message = checkMessage(check);
        return { title:checkTitle(check), detail:message.detail };
      }),
      checks: d.checks.map(check => {
        const message = checkMessage(check);
        return {
          id:check.id, groupKey:check.group_id || check.group, group:groupTitle(check),
          title:checkTitle(check), status:check.status, statusLabel:LABEL(check.status, d.lang),
          earned:check.earned, weight:check.weight, detail:message.detail, fix:message.fix,
        };
      }),
      scope:L.scope,
      signature:L.signature,
      assetUrls:{ productLogo:'assets/img/web-auditor-logo.webp', corporateLogo:'assets/img/GPC-logo-pequeño.png' },
    });
    const actions = el('div', 'report-actions');
    const downloadBtn = el('button', 'primary', actionCopy.download);
    downloadBtn.type = 'button';
    const actionStatus = el('p', 'print-note', actionCopy.note);
    actionStatus.setAttribute('aria-live', 'polite');
    downloadBtn.addEventListener('click', async () => {
      const original = downloadBtn.textContent;
      downloadBtn.disabled = true;
      downloadBtn.textContent = actionCopy.generating;
      actionStatus.textContent = actionCopy.generating;
      try {
        const filename = await window.WebAuditorPdf.download(buildPdfPayload());
        actionStatus.textContent = actionCopy.ready + ' · ' + filename;
      } catch (error) {
        console.error(error);
        actionStatus.textContent = actionCopy.error;
      } finally {
        downloadBtn.disabled = false;
        downloadBtn.textContent = original;
      }
    });
    const printBtn = el('button', '', actionCopy.print);
    printBtn.type = 'button';
    printBtn.addEventListener('click', () => window.print());
    actions.append(downloadBtn, printBtn, actionStatus);
    s.append(actions);
    if (d.grade !== 'A') {
      const cta = el('div', 'cta', T.cta);
      const a = el('a', '', T.contact);
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
    reference.append(el('strong', '', d.report_id || L.reference), el('span', '', L.issued + ': ' + issued), el('span', '', L.site + ': ' + d.host));
    cover.append(brand, reference);
    if (Array.isArray(d.priorities) && d.priorities.length) {
      const priorities = el('section', 'priorities');
      priorities.append(el('h2', '', L.priorities), el('p', '', L.priorityIntro));
      const ordered = el('ol');
      d.priorities.forEach(c => {
        const item = el('li');
        item.append(el('strong', '', checkTitle(c)), el('span', '', checkMessage(c).detail));
        ordered.append(item);
      });
      priorities.append(ordered);
      list.append(priorities);
    }
    const groups = new Map();
    d.checks.forEach(c => {
      const key = c.group_id || c.group;
      if (!groups.has(key)) groups.set(key, { name: groupTitle(c), items: [] });
      groups.get(key).items.push(c);
    });
    groups.forEach(group => {
      const g = el('section', 'group');
      g.append(el('h2', '', group.name));
      group.items.forEach(c => {
        const det = el('details', 'chk ' + c.status);
        if (c.status !== 'pass') det.open = true;
        const sum = el('summary');
        sum.append(el('span', 'badge', LABEL(c.status, d.lang)), el('span', 'ttl', checkTitle(c)), el('span', 'pts', c.status === 'unknown' ? 'N/V' : c.earned + '/' + c.weight));
        const body = el('div', 'body');
        const message = checkMessage(c);
        body.append(el('p', '', message.detail));
        if (message.fix) { body.append(el('p', 'fixlbl', L.fix), el('pre', '', message.fix)); }
        det.append(sum, body);
        g.append(det);
      });
      list.append(g);
    });

    const signature = el('section', 'report-signature');
    const signatureText = el('p', '', L.scope);
    const signatureBlock = el('div');
    const signatureBrand = el('div', 'signature-brand');
    const signatureLogo = document.createElement('img');
    signatureLogo.src = 'assets/img/GPC-logo-pequeño.png';
    signatureLogo.alt = 'Green PC';
    signatureLogo.className = 'signature-logo';
    signatureBrand.append(signatureLogo);
    signatureBlock.append(signatureBrand, el('div', 'signature-role', L.signature));
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
    const P = PROGRESS_COPY[lang.value] || PROGRESS_COPY.es;
    window.clearInterval(cooldownTimer);
    btn.disabled = true; btn.textContent = currentLocale().busy + '…';
    setServiceState('busy', currentLocale().busy);
    setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().request }), textNode(P.request)]);
    out.setAttribute('aria-busy', 'true');
    const progress = el('div', 'progress');
    const title = el('b', '', P.preparing);
    const detail = el('small', '', P.validating);
    progress.append(title, detail);
    out.replaceChildren(progress);
    const stages = P.stages;
    let stage = 0;
    const stageTimer = window.setInterval(() => {
      const current = stages[Math.min(stage++, stages.length - 1)];
      title.textContent = current[0]; detail.textContent = current[1];
    }, 1700);
    let completed = false;
    const controller = new AbortController();
    const requestTimer = window.setTimeout(() => controller.abort(), 65000);
    try {
      const res = await fetch('api.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ url, lang: lang.value }), signal: controller.signal
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
      const message = e.name === 'AbortError' ? currentCopy().timeout : e.message;
      const error = el('div', 'err', message);
      error.setAttribute('role', 'alert');
      out.replaceChildren(error);
      out.setAttribute('aria-busy', 'false');
      if (e.retryAfter > 0) {
        startCooldown(e.retryAfter);
      } else {
        setServiceState('unavailable', 'Servicio temporalmente no disponible');
        setLimitNote([Object.assign(document.createElement('strong'), { textContent: currentLocale().failure + '. ' }), textNode(currentCopy().failed)]);
      }
      out.focus();
    } finally {
      window.clearInterval(stageTimer);
      window.clearTimeout(requestTimer);
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
