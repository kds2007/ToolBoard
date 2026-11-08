# ToolBoard
Sistema de gestión y control de herramientas de un taller industrial (PHP 8+, MySQL/MariaDB, PDO, HTML/CSS/JS).

## Arquitectura
- **Frontend estático (raíz)** → GitHub Pages: `index.html`, `login.html`, `registro.html`, `panel.html`, `style.css`, `responsive.css`, `script.js`, `app.js`, `config.js`. Rutas relativas, sin `/` inicial.
- **Backend PHP + MySQL (`servidor-php/`)** → hosting con PHP 8+ y MySQL. `api.php` es la API JSON que usa el frontend; las pantallas PHP originales siguen funcionando en el mismo servidor. GitHub Pages no ejecuta PHP ni se conecta a MySQL: por eso hacen falta ambas partes.

## Puesta en marcha
1. Suba `servidor-php/` a su hosting (o `htdocs` en XAMPP) y en phpMyAdmin importe `servidor-php/toolboard.sql`.
2. Copie `config.example.php` a `config.local.php` y complete las credenciales de MySQL y `ALLOWED_ORIGIN` (p. ej. `https://usuario.github.io`).
3. Edite `config.js` de la raíz: `window.TB_API = 'https://su-dominio/ToolBoard/api.php';` (debe ser **https** si el sitio está en GitHub Pages).
4. Publique la raíz en GitHub Pages (Settings → Pages → `main` / root). Local con XAMPP: `http://localhost/ToolBoard/api.php`.
5. Ingrese con `admin` / `123` (cámbiela antes de publicar). Los registros nuevos reciben rol `usuario`.

Sin `TB_API` configurado el sitio muestra un error: ya no hay modo demostración ni datos locales. Autenticación: `api.php` devuelve un token de sesión que el sitio envía en la cabecera `X-TB-Token`. Categorías, reportes y filtros del inventario siguen solo en las pantallas PHP.
