# Despliegue seguro con Apache

Este proyecto es PHP nativo y funciona bien sobre **Apache 2.4 + PHP-FPM**. No es necesario migrar a Nginx solo para publicarlo. En un VPS dedicado, Nginx suele usar algo menos de memoria bajo mucha concurrencia; en un hosting compartido o un Apache bien configurado, Apache es suficiente para el arranque.

## Perfil recomendado

| Uso | Recursos mínimos | Límite inicial |
|---|---:|---:|
| Lanzamiento público | 2 vCPU, 2 GB RAM, 2 GB swap | 4 análisis simultáneos |
| Tráfico sostenido | 2 vCPU, 4 GB RAM | 6 análisis simultáneos |
| Alto tráfico | 4 vCPU, 8 GB RAM + Redis/WAF | medir antes de ampliar |

El límite de aplicación se mantiene en **20 análisis por IP/hora** y **8 por dominio/minuto**. Para un primer despliegue público conviene bajarlo a **10/IP/hora** y **4/dominio/minuto**, y medir. No use solo archivos temporales para coordinar varias réplicas: use Redis o un WAF.

## Apache (VPS con control de VirtualHost)

1. Copie `deploy/apache-web_auditor.conf` y ajuste dominio, `DocumentRoot` y socket PHP-FPM.
2. Habilite los módulos necesarios:

```bash
sudo a2enmod headers ssl rewrite reqtimeout proxy_fcgi setenvif
sudo a2enconf php8.3-fpm
sudo apachectl configtest
sudo systemctl reload apache2
```

En AlmaLinux/RHEL, use `httpd` en lugar de `apache2`; el socket suele ser `/run/php-fpm/www.sock`.

La configuración fuerza HTTPS, bloquea `/lib/`, dotfiles y respaldos, permite solo POST a `api.php`, limita el cuerpo a 4 KiB y ejecuta PHP por PHP-FPM.

## Hosting compartido Apache

Suba también el archivo [`.htaccess`](.htaccess) a la raíz pública. Pide `AllowOverride` con las clases `Options`, `Limit` y `FileInfo`; si el proveedor no las permite, solicite que apliquen las reglas del VirtualHost. El archivo no reemplaza el rate limiting de borde.

## Rate limit y autobloqueo

Apache no ofrece un límite de solicitudes por IP fiable con reglas básicas del VirtualHost. Aplíquelo en el WAF/CDN (Cloudflare, por ejemplo):

- Más de **8 POST a /api.php por minuto**: Managed Challenge durante 10 minutos.
- Más de **30 POST a /api.php en 10 minutos**: bloquear durante 1 hora.
- Mantenga la respuesta 429 de la aplicación como limitación temporal, no como bloqueo permanente.

Para no saturar un VPS pequeño, limite el pool PHP-FPM a cuatro workers simultáneos al inicio. Los análisis abren varias conexiones de red; la concurrencia, no el número de visitas estáticas, es el factor crítico.

## Verificar el entorno antes de publicar

Ejecute por SSH y comparta la salida sin IPs privadas, dominios o rutas sensibles:

```bash
cat /etc/os-release
uname -a
apachectl -V 2>/dev/null || httpd -V
apachectl -M 2>/dev/null || httpd -M
php -v
php -m | grep -E 'curl|openssl|intl'
free -h
nproc
df -hT
ps -eo pid,comm,%cpu,%mem --sort=-%mem | head -n 12
systemctl status apache2 php*-fpm --no-pager 2>/dev/null || systemctl status httpd php-fpm --no-pager
```

Para saber si es hosting compartido, pruebe `sudo -n true`: si falla y no controla el VirtualHost/PHP-FPM, aplique solo `.htaccess` y las reglas del WAF. En ese caso, un VPS Ubuntu 24.04 LTS con Nginx puede dar más control, pero no es un requisito: un VPS con Apache + PHP-FPM bien dimensionado ofrece la misma seguridad para esta aplicación.

## Reglas necesarias

- Servir únicamente por HTTPS y renovar certificados automáticamente.
- Mantener `TRUST_CF_HEADER=false` salvo que el origen acepte tráfico exclusivamente desde Cloudflare y se configure `mod_remoteip` con los rangos oficiales.
- Bloquear acceso web a `/lib/`.
- No ejecutar el servidor integrado de PHP en producción.
- Proteger `main` y exigir que la comprobación de sintaxis y los tests pasen antes de desplegar.

## Cadena de suministro

- Proteja `main`: pull request obligatorio y comprobaciones de GitHub Actions requeridas.
- Fije las acciones de GitHub a commits SHA si el repositorio se vuelve crítico.
- Revise dependencias y módulos de Apache/PHP instalados; el proyecto no necesita dependencias Composer.
