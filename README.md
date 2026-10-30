# Tabla

Sistema web para La Charcutería, un negocio familiar de Guatemala que vende embutidos, quesos y vinos, y sirve platos gourmet en el local. Reemplaza los pedidos en papel, las comandas a mano, la cuenta calculada a mano y el menú en PDF.

Proyecto final del curso Desarrollo Web, Ingeniería en Sistemas, Universidad Mariano Gálvez.

## Qué hace

El sistema cubre el flujo completo de una mesa: **menú → pedido → cocina → cobro → cierre de caja**.

| Área | Ruta | Rol | Funciones |
| --- | --- | --- | --- |
| Menú público | `/menu`, `/menu/mesa/{token_qr}` | sin login | Menú en el celular, filtros, productos agotados y vino sugerido |
| Administración | `/admin` | admin | Categorías, productos, mesas con QR imprimible y usuarios |
| Mesero | `/mesero` | mesero, admin | Mapa de mesas en tiempo real, pedidos con notas, rondas y entrega |
| Cocina | `/cocina` | cocina, admin | Comandas en tiempo real, color por tiempo de espera y resumen de pendientes |
| Caja | `/caja` | caja, admin | Cuenta, propina, división en partes iguales, pagos con vuelto y cierre del día |

## Tecnologías

- Laravel 13 y PHP 8.3
- MySQL 8.0
- Blade, Bootstrap 5 y JavaScript con `fetch`
- Docker con FrankenPHP

## Arquitectura

La aplicación sigue el patrón MVC de Laravel, organizada por áreas:

```
app/Http/Controllers/{Admin,Auth,Caja,Cocina,Mesero}   Controladores de cada área
app/Http/Requests/                                     Validación en servidor (Form Requests)
app/Http/Middleware/VerificarRol.php                   Acceso por rol: rol:mesero,admin
app/Models/                                            Modelos, relaciones y reglas del negocio
resources/views/<area>/                                Vistas Blade de cada área
public/js/                                             JavaScript de las pantallas
public/css/                                            Estilos del sistema y del menú público
tests/                                                 Pruebas
```

### Decisiones importantes

- **Reglas del negocio en el modelo.** `Pedido` calcula subtotal, total, pagado y saldo, y `cerrar()` termina el pedido y libera la mesa.
- **El dinero nunca es `float` en la base.** Los montos usan `decimal(10,2)`, y la pantalla de caja calcula en centavos.
- **El precio se copia al vender.** Cambiar el menú no altera los pedidos anteriores.
- **Tiempo real con consultas cada 5 segundos.** El mapa de mesas y cocina piden sus datos a un endpoint JSON, sin recargar la página.
- **Bloqueos en la base.** Abrir pedidos, enviar a cocina y cobrar usan `lockForUpdate` para que dos personas no choquen.
- **Sin borrado de historial.** Los usuarios se desactivan, y los productos y mesas con pedidos no se eliminan.

### Modelo de datos

```
categorias 1─N productos
mesas      1─N pedidos 1─N comandas 1─N detalles_pedido N─1 productos
                pedidos 1─N pagos
users      1─N pedidos (mesero), pagos y cierres_caja (cajero)
```

Una **comanda** es una ronda enviada a cocina, y `cierres_caja` guarda un cierre por día.

### Estados

- **Mesa:** libre → ocupada → esperando → comiendo → cuenta → libre.
- **Comanda:** pendiente → preparando → listo (cocina) → entregado (mesero).
- **Pedido:** abierto → cerrado, o cancelado si no tiene productos.

## Instalación

Requisitos: Docker y Docker Compose.

```bash
git clone https://github.com/Narvii10/tabla.git
cd tabla
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

La aplicación queda en http://localhost:8003. MySQL queda disponible desde el equipo en el puerto 3307.

### Usuarios de ejemplo

Los crea `db:seed`. La contraseña de todos es `password`, y son solo para desarrollo.

| Rol | Correo |
| --- | --- |
| Administrador | `admin@tabla.test` |
| Mesero | `mesero@tabla.test` |
| Cocina | `cocina@tabla.test` |
| Caja | `caja@tabla.test` |

## Pruebas

```bash
docker compose exec app php artisan test
```

Las pruebas usan SQLite en memoria y no tocan la base de desarrollo.

## Despliegue

En un servidor nuevo o después de actualizar el código:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
```

En producción, `.env` debe tener `APP_ENV=production`, `APP_DEBUG=false` y una `APP_KEY` propia generada con `php artisan key:generate`. Las carpetas `storage` y `bootstrap/cache` deben tener permisos de escritura.

## Equipo

Proyecto desarrollado por un equipo de 5 personas con Scrum. Cada módulo se trabajó en su propia rama e integró a `develop` por pull request.
