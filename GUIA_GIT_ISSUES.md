# Guía de trabajo con GitHub Issues

## Crear un issue

1. Abra la pestaña **Issues** del repositorio y seleccione **New issue**.
2. Use un título breve que describa el cambio o el problema.
3. Incluya contexto, pasos para reproducir (si corresponde), resultado esperado y criterios de aceptación.
4. No publique contraseñas, tokens, datos personales ni credenciales de producción. Para un problema de seguridad, evite divulgar detalles explotables en un issue público.

## Trabajar en un issue

1. Cree una rama desde `main` con un nombre descriptivo, por ejemplo `feature/issue-12-filtro-historial` o `fix/issue-13-ruta-api`.
2. Mantenga el cambio enfocado en los criterios del issue y verifique las páginas o pruebas afectadas.
3. Haga commits con mensajes breves que expliquen el cambio.
4. Abra un Pull Request hacia `main`, describa el cambio y sus verificaciones, y enlace el issue con `Closes #12` (reemplace el número).
5. Después de revisar y aprobar el Pull Request, intégrelo a `main` y cierre el issue.

## Publicación del frontend

GitHub Pages publica la raíz de `main` cuando el repositorio está configurado en **Settings → Pages → Deploy from a branch → main → /(root)**. Tras integrar un cambio, compruebe el sitio en [https://kds2007.github.io/ToolBoard/](https://kds2007.github.io/ToolBoard/). Si un cambio requiere un backend, confirme además que `config.js` apunta a la URL HTTPS correcta y que el backend permite el origen del sitio.
