<p align="center">
  <img src="assets/img/web-auditor-logo.webp" alt="Web Auditor by Green PC" width="360">
</p>

# 🛡️ Web Auditor · Green PC

Auditor externo, ligero y privado para revisar la postura básica de seguridad de un sitio web público. Está desarrollado en **PHP nativo** y JavaScript/CSS sin frameworks.

Analiza transporte TLS, cabeceras HTTP, cookies, exposición de archivos, DNS y autenticación de correo. Entrega una nota **A–F**, nivel de riesgo y una lista de acciones priorizadas para corregir primero lo más importante.

> La evaluación es externa y orientativa: no sustituye una prueba de penetración, revisión de código ni auditoría de cumplimiento.

## Qué hace

### Transporte y TLS

- Valida certificado, emisor, vigencia y nombre del servidor.
- Comprueba activamente TLS 1.0, 1.1, 1.2 y 1.3 cuando PHP/OpenSSL permite forzarlos.
- Alerta si el servidor aún acepta TLS 1.0 o 1.1.
- Evalúa el cifrado **negociado** y avisa sobre algoritmos débiles.
- Verifica HTTP → HTTPS y HSTS por separado:
  - vigencia de `max-age`;
  - `includeSubDomains`;
  - preparación para `preload`.

El cifrado negociado no equivale a un inventario completo de todas las suites habilitadas.

### Cabeceras y navegador

- CSP, clickjacking, `nosniff`, Referrer-Policy, Permissions-Policy, COOP y CORP.
- Cookies: `Secure`, `HttpOnly` y `SameSite`.
- CORS, incluyendo detección de reflexión dinámica de `Origin`.
- Divulgación de versiones mediante `Server` y `X-Powered-By`.

### Exposición y DNS

- Sondas acotadas a `.env`, `.git/HEAD`, copias de WordPress, dumps SQL y `phpinfo.php`.
- `security.txt` con `Contact` y `Expires` futura.
- SPF: ausencia, múltiples registros y política final.
- DMARC: política y porcentaje de aplicación.
- CAA para restringir autoridades certificadoras.

## Informe para clientes

Después del análisis, la interfaz muestra:

- Nota A–F, puntaje y riesgo alto/medio/bajo.
- **“Corrige primero esto”**: las tres acciones con mayor impacto.
- Resultados agrupados, explicación y configuración sugerida para Apache/Nginx.
- Progreso visible durante el análisis y tiempo estimado de 15 a 60 segundos.
- Indicador de disponibilidad y cuenta regresiva exacta cuando el límite temporal está activo.
- Portada de informe con marca, fecha UTC, dominio auditado y referencia única.
- Firma visual de emisión de **Green PC** en el informe imprimible/PDF.
- Selector de idioma para interfaz e informe: español, inglés, francés, italiano, portugués europeo y alemán.\n- Botón **Imprimir / Guardar PDF** para entregar un informe sin almacenar resultados.

La firma incluida acredita la emisión visual de Green PC; no es una firma electrónica certificada. La referencia identifica solo la ejecución actual y no conserva una copia del informe.

No existen cuentas ni historial de auditorías. Para aplicar límites se conservan temporalmente hashes de IP y destino, no informes ni resultados consultables.

## Seguridad del auditor

- Validación SSRF: solo HTTP/HTTPS estándar, sin credenciales en URL ni puertos alternativos.
- Resolución segura de registros A y AAAA; bloquea redes privadas, reservadas y CGNAT.
- Las conexiones usan la IP validada para reducir riesgos de DNS rebinding.
- Límite por IP: 10 análisis por hora.
- Límite por destino: 4 análisis por minuto.
- En un 429 la API envía `Retry-After`; la interfaz deshabilita el botón y muestra el tiempo restante.
- CSP con nonce en la interfaz; la API acepta solo `POST` con `Content-Type: application/json` y cuerpos de hasta 4 KiB.
- El dominio introducido no se añade a la URL ni al historial de la aplicación.
- Las sondas son concurrentes, con timeout y tamaño de respuesta restringidos.

> Los límites basados en archivos son adecuados para una instancia. Para despliegues con múltiples réplicas, usa WAF/Cloudflare y un límite compartido mediante Redis o el balanceador. Consulta [DEPLOYMENT.md](DEPLOYMENT.md).

## Requisitos

- PHP **8.0+**.
- Extensiones: `curl`, `openssl`, `filter`, `json`.
- `intl` es opcional para dominios internacionalizados.
- Apache o Nginx con PHP-FPM para producción.

## Instalación local

```bash
git clone https://github.com/xacq/web_auditor.git
cd web_auditor
php -S localhost:8000
```

Abre <http://localhost:8000>.

Para producción, sigue la guía de [despliegue seguro](DEPLOYMENT.md). El servidor embebido de PHP no es apto para producción.

## API

```http
POST /api.php
Content-Type: application/json

{"url":"ejemplo.com","lang":"de"}
```

La respuesta incluye `score`, `grade`, `risk`, `summary`, `priorities`, `checks`, datos TLS, cadena de redirecciones, `lang` y `report_id` para el informe actual.\n\n`lang` admite `es`, `en`, `fr`, `it`, `pt-PT` y `de`; se valida contra una lista cerrada y no se almacena. La interfaz y el contexto del PDF se localizan con ese valor; los valores técnicos (cabeceras, protocolos, DNS y configuraciones sugeridas) se conservan literalmente para no alterar evidencia.

Cuando se excede un límite se responde con `429 Too Many Requests`, `Retry-After` y `retry_after` (segundos).

## Pruebas y CI

El workflow [PHP quality](.github/workflows/php-quality.yml) valida sintaxis en PHP 8.0 y 8.3, y ejecuta:

- `tests/security-regression.php`: esquema, puertos y URLs inseguras.
- `tests/security-helpers.php`: helpers de destino e IPv6.
- `tests/audit-summary.php`: nivel de riesgo y orden de prioridades.

## Estructura

```text
web_auditor/
├── api.php                 # Endpoint, orquestación y puntuación
├── index.php               # Interfaz y generación de informe imprimible
├── lib/
│   ├── Target.php          # Validación de destino y resolución segura
│   ├── Http.php            # Transporte HTTP y redirecciones
│   └── Tls.php             # Certificado y protocolos TLS
├── tests/                  # Regresiones de seguridad
├── .github/workflows/      # CI
├── DEPLOYMENT.md           # Operación, WAF y escalado
└── README.md
```

## Configuración

Los límites principales están al inicio de `api.php`:

```php
const RATE_MAX = 10;
const RATE_WINDOW = 3600;
const TARGET_RATE_MAX = 4;
const TARGET_RATE_WINDOW = 60;
const TRUST_CF_HEADER = false;
```

Activa `TRUST_CF_HEADER` únicamente cuando el servidor de origen reciba tráfico exclusivamente desde Cloudflare; de lo contrario, un atacante podría falsificar el encabezado.

## Licencia y crédito

Desarrollado por [Green PC](https://greenpc.dev).
