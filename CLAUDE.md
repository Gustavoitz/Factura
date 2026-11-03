# Proyecto Factura (Gustavo Rivas)

Sitio PHP desplegado en Render (Docker `php:8.2-apache`, extensión mysqli) desde un repositorio de GitHub.
Cada push a `main` redespliega el sitio. URL: https://factura-ybyf.onrender.com

## Qué hay en el proyecto
- `index.php` (raíz): portada con tres botones: App de banco, Facturación y Página web.
- `facturacion.php`: menú de la app de facturación (trabajo anterior). Usa `Cservicios.php`, `registrardatos.php`, `registrarfactura.php`, `consultar_*.php`, `mostrartodos*.php`, etc. Tablas: `clientes`, `facturas`, `detalle_factura`, `productos`. Procedimientos: `insertar_cliente`, `consultar_cliente`, `mostrar_clientes`, `actualizar_cliente`, `insertar_factura`, `insertar_detalle_factura`, `consultar_factura`, `mostrar_facturas`.
- `index.html`: página web "El Sistema Solar" (usa `estilos.css` de la raíz).
- `cuenta_clara/`: **app de banco**, el proyecto principal en el que se trabaja (trabajo final de Computación Móvil).

## App de banco (cuenta_clara)
- PHP + MySQL. Todo acceso a datos va con **procedimientos almacenados** (`config.php` tiene `llamar_procedimiento`). Se mejora lo que ya existe: no se elimina nada anterior.
- Cuentas de clientes: registro y login con tablas `cuenta_clientes` y `cuenta_abonos` (base `defaultdb`). Procedimientos: `sp_cuenta_buscar_cliente`, `sp_cuenta_registrar_cliente`, `sp_cuenta_listar_abonos`, `sp_cuenta_agregar_abono` (rechaza montos <= 0). SQL en `cuenta_clara/base_de_datos.sql`.
- Pantallas: ingreso (`index.php`), registro, `cuenta.php` (saldo, agregar dinero, historial de abonos, botón "Ir a Ahorros"), cierre de sesión.
- Agregar dinero: monto entero de 1 a 50.000.000, métodos Transferencia / Efectivo / Nequi, token CSRF, redirección después de guardar, fecha en hora de Colombia.
- Cuentas de prueba: solo `demo` y `demo2`. No usan base de datos; sus abonos viven en la sesión y se borran al salir.
- **Ahorros** (novedad del profesor): `ahorros.php`, a la que se entra desde `cuenta.php` con la sesión iniciada. Pide el id del cliente y un valor; el procedimiento `sp_ahorrar` lo resta del saldo y lo suma al ahorro. Avisa si el cliente no existe, no tiene saldo (cero) o el valor es mayor al saldo. Usa una base aparte, `banco_ahorros`, con **una sola tabla**: `ahorros` (`id` llave primaria, `saldo`, `ahorro`), 5 clientes de ejemplo (el cliente 3 tiene saldo 0). Procedimientos: `sp_ahorros_listar`, `sp_ahorros_consultar`, `sp_ahorrar`. SQL en `cuenta_clara/ahorros.sql`. La conexión es `conectar_ahorros()` en `config.php`.
- El profesor revisa la app desde el **celular** (se abre desde appcreator24.com), así que debe verse bien sobre todo en móvil, y también en PC.
- Los archivos usan saltos de línea CRLF: conservarlos.

## Base de datos
- Producción: MySQL en Aiven. `defaultdb` guarda la facturación y las tablas del banco; `banco_ahorros` guarda solo la tabla de ahorros. Conexión por variables de entorno (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_NAME`, `DB_PASSWORD`) configuradas en Render. Opcional: `AHORROS_DB_NAME` (por defecto `banco_ahorros`).
- Pruebas locales: XAMPP, base `cuenta_test`, usuario `prueba` (Gustavo sabe la contraseña). En PowerShell, desde `cuenta_clara`:
  `$env:DB_HOST="127.0.0.1"; $env:DB_PORT="3306"; $env:DB_USER="prueba"; $env:DB_NAME="cuenta_test"; $env:DB_PASSWORD="<contraseña>"; php -S localhost:8000`
  Para probar Ahorros en local hay que cargar `ahorros.sql` (crea la base `banco_ahorros`) y dar permisos al usuario `prueba` sobre ella.
- Sin esas variables, la app solo permite entrar con `demo` y `demo2`.

## Reglas de trabajo
- No hacer commit ni push salvo que Gustavo lo pida. Un push redespliega el sitio real.
- El SQL sobre Aiven lo ejecuta Gustavo con la extensión Database Client de VS Code. Claude Code no tiene acceso a esa base.
- No tocar las tablas ni los procedimientos de facturación sin avisar. Todo lo que empieza por `cuenta_` / `sp_cuenta_` es del banco.
- Centrarse en las novedades pedidas; no rehacer ni eliminar lo que ya funciona.
- No escribir contraseñas ni claves en el repositorio.
- Responder en español, con pasos claros y tono cercano.

## Pendiente
- Crear la base `banco_ahorros` en Aiven con `ahorros.sql` (la ejecuta Gustavo) y probar Ahorros en el celular.
- Decidir si se borran las tablas de la app de facturación.
