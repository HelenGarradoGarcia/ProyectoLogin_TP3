# Proyecto Login

- **Alumno/a:** Helen Garrado Garcia
- **Materia:** Análisis de Sistema
- **Año de cursada:** 2026

Sistema de login con registro de usuarios, roles, historial de accesos, recuperación de contraseña, pantalla de bienvenida y modo oscuro/claro.

## Tecnologías

- **Front-end:** HTML5, CSS3 (responsive) y JavaScript
- **Back-end:** PHP 8 (PDO)
- **Base de datos:** MySQL, puerto 3306 (tablas `roles`, `usuarios` y `accesos`)
- **Servidor:** XAMPP, Apache en `localhost` (127.0.0.1)

## Estructura

```
ProyectoLogin_TP3/
├── public/                  Lo que se abre en el navegador
│   ├── index.php            Login
│   ├── registro.php         Alta de usuario nuevo
│   ├── recuperar.php        Recuperar contraseña
│   ├── dashboard.php        Escritorio: bienvenida, rol e historial de accesos
│   ├── css/style.css        Hoja de estilo
│   └── js/script.js         Validación de campos y modo oscuro/claro
├── src/                     PHP: conexión a la base y procesamiento de los formularios
└── database/database.sql    Script SQL de la base de datos
```

## Cómo ejecutarlo

1. Copiar la carpeta en `C:/xampp/htdocs/`.
2. Ejecutar `database/database.sql` en MySQL Workbench.
3. Iniciar Apache en XAMPP y abrir `http://localhost/ProyectoLogin_TP3/public/`.
