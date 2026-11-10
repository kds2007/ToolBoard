# ToolBoard

Sistema de gestión y control de herramientas para un taller industrial. La interfaz estática usa HTML, CSS y JavaScript; las operaciones y los datos persistentes se sirven desde una API PHP conectada a MySQL o MariaDB.

## Componentes

- **Frontend estático:** `index.html`, `login.html`, `registro.html`, `panel.html`, `style.css`, `responsive.css`, `script.js`, `app.js` y `config.js`.
- **Backend:** `api.php` y sus dependencias PHP (`auth.php`, `functions.php`, `database.php` y `config.php`), además de `toolboard.sql` para crear la base de datos.
- **GitHub Pages:** publica únicamente los archivos estáticos. No ejecuta PHP ni se conecta directamente a MySQL.

Las páginas usan rutas relativas, por lo que también funcionan bajo la ruta de proyecto de GitHub Pages. El archivo `.nojekyll` evita que Pages omita archivos y directorios cuyo nombre empieza con punto.

## Configuración del backend

1. Aloje los archivos PHP en un servidor con PHP 8+, PDO MySQL y una base MySQL/MariaDB. Importe `toolboard.sql` desde phpMyAdmin o su herramienta habitual.
2. Copie `config.example.php` como `config.local.php` en el directorio del backend y complete las credenciales. Mantenga `config.local.php` fuera del repositorio.
3. Configure `ALLOWED_ORIGIN` con el origen exacto del frontend, sin ruta ni barra final. Para este proyecto en Pages es `https://kds2007.github.io`. No use `*` en producción.
4. Compruebe que `api.php` es accesible por HTTPS desde el dominio público.

## Conectar el frontend

Edite `config.js` y asigne `window.TB_API` a la URL HTTPS real del endpoint:

```js
window.TB_API = 'https://api.ejemplo.com/ToolBoard/api.php';
```

No deje una URL `localhost` en una publicación: desde el navegador de cada visitante, `localhost` apunta a su propio equipo. El frontend envía login, registro, consultas y operaciones como JSON; usa el token devuelto por la API para las solicitudes autenticadas. La API y las pantallas PHP comparten la misma conexión de base de datos del backend.

Si `TB_API` está vacío, el frontend muestra un error de configuración en vez de simular datos.

## Publicar en GitHub Pages

La URL del sitio es [https://kds2007.github.io/ToolBoard/](https://kds2007.github.io/ToolBoard/).

1. En GitHub, abra **Settings → Pages** del repositorio.
2. Seleccione **Deploy from a branch**, la rama `main` y la carpeta `/(root)`; guarde los cambios.
3. Confirme que la URL de Pages sirve `index.html`, `login.html` y `config.js`.
4. Configure y publique `config.js` con la URL del backend HTTPS. Verifique que `ALLOWED_ORIGIN` en el backend coincida con `https://kds2007.github.io`.
5. Pruebe el acceso, la lectura del panel y una operación de escritura. Después elimine cualquier dato de prueba.

Al integrar cambios a `main`, Pages vuelve a publicar la raíz del repositorio. Los archivos PHP pueden alojarse en el mismo dominio o en otro servidor; Pages no los ejecutará.

## Acceso inicial

La instalación de base de datos crea el usuario inicial `admin` con contraseña `123`. Cambie esa contraseña y las credenciales de base de datos antes de usar el sistema en producción. Las cuentas registradas desde el frontend reciben el rol `usuario`.
