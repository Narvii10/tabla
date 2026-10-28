# Tabla: contexto del proyecto

## Qué es

Tabla es una aplicación web para **Charcutería [nombre]**, un negocio familiar en Guatemala que vende embutidos, jamones, quesos y vinos, y prepara platos gourmet (tablas, hamburguesas, tacos, paninis) para comer en el local o llevar.

Reemplaza lo que hoy se hace en papel: pedidos anotados a mano, comandas a cocina en papel, cuenta calculada a mano y un menú en PDF que se rehace con cada cambio.

Es el proyecto final del curso Desarrollo Web (Ingeniería en Sistemas, Universidad Mariano Gálvez). Equipo de 5 personas con Scrum.

- **Entrega:** viernes 30 de octubre de 2026.
- **Meta interna:** todo funcionando y desplegado el jueves 29 en la noche.
- **Calificación (15 pts):** gestión y documentación 3, calidad de código y dominio técnico 4, funcionalidad 5, tecnologías y arquitectura 3.
- Se revisan commits, ramas y pull requests de **cada** integrante, y cada uno debe poder explicar su parte en la presentación.

## Alcance de esta versión

Flujo completo: **menú → pedido → cocina → cobro → cierre de caja**.

Incluye:
- Roles: admin, mesero, cocina, caja.
- Administración del menú: categorías y productos, con botón **agotado** y **vino sugerido** (maridaje).
- Menú público responsivo en un link fijo y un QR fijo por mesa.
- Mesas con QR y mapa de mesas con color por estado.
- Toma de pedidos con notas y rondas adicionales.
- Pantalla de cocina en tiempo real (consulta cada 5 s), color por tiempo de espera, resumen de pendientes.
- Caja: cuenta, propina sugerida, división en partes iguales, pagos con vuelto y cierre de caja del día.

**No incluye (fase 2, no programar):** venta por peso, tablas armables, modificadores estructurados, pedidos para llevar, llamar al mesero desde el QR, ingeniería de menú, inventario, reportes avanzados, facturación FEL, pagos en línea.

## Stack y entorno

- Laravel (versión de `composer.json`), PHP 8.3 en Docker con FrankenPHP, MySQL 8.0.
- Frontend: **Blade + Bootstrap 5 desde CDN + JavaScript con `fetch`**. No usar Vite, npm ni Tailwind.
- Levantar: `docker compose up -d`. App en http://localhost:8003. MySQL desde el host: puerto 3307.
- Comandos de artisan y composer **siempre dentro del contenedor**:
  `docker compose exec app php artisan ...`
  `docker compose exec app composer ...`
- Interfaz en español. Moneda en quetzales con formato `Q 1,234.50`. Zona horaria `America/Guatemala`.

## Convenciones de código

- Tablas, modelos, columnas, variables y rutas en **inglés**. Textos de la interfaz en **español**.
- Montos de dinero en `decimal(10,2)`, nunca `float`.
- Controladores de recurso (`Route::resource`) cuando aplique; validación con **Form Requests**; nada de lógica de negocio en las vistas.
- Relaciones de Eloquent en los modelos; usar `with()` para evitar consultas N+1 en listados.
- Listados de administración paginados (15 por página).
- Vistas en `resources/views/<area>/<modulo>/`. Componentes compartidos en `resources/views/components/`.
- Montos en vistas con el componente `<x-money :amount="$valor" />`.
- Mensajes de éxito o error con `session('success')` / `session('error')`; el layout ya los muestra.
- Endpoints que consulta JavaScript devuelven JSON y viven en las rutas web del área (no en `routes/api.php`), con nombre terminado en `.data`.

## Modelo de datos

Ya definido en la base. **No modificar migraciones existentes**: si falta una columna, crear una migración nueva y avisar al líder técnico.

| Tabla | Columnas principales |
| --- | --- |
| `users` | name, email, password, `role` (admin, mesero, cocina, caja), `active` (bool) |
| `categories` | name, `sort_order`, `active` |
| `products` | category_id, name, description, `price`, `cost` (nullable), `photo_path` (nullable), `is_available` (bool, agotado = false), `is_wine` (bool), `pairing_product_id` (nullable, FK a products: vino sugerido) |
| `dining_tables` | `number` (único), capacity, zone (nullable), `qr_token` (único), `status` (libre, ocupada, esperando, comiendo, cuenta) |
| `orders` | dining_table_id, user_id (mesero), `status` (abierta, cerrada, cancelada), `tip` (decimal, default 0), `opened_at`, `closed_at` |
| `kitchen_tickets` | order_id, `sent_at`, `status` (pendiente, preparando, listo, entregado). Una comanda por cada ronda enviada a cocina |
| `order_items` | order_id, kitchen_ticket_id (nullable hasta enviarse), product_id, quantity, `unit_price` (precio copiado al momento de la venta), notes (nullable) |
| `payments` | order_id, user_id (cajero), `method` (efectivo, tarjeta, transferencia), amount, received (nullable), change (nullable) |
| `cash_closings` | user_id, `date` (única), expected_cash, expected_card, expected_transfer, counted_cash, difference, notes |

Reglas de negocio compartidas:
- El total de un pedido es la suma de `quantity * unit_price` de sus `order_items`, más `tip`.
- `unit_price` se copia del producto al agregarlo. Cambiar el precio del menú no altera pedidos anteriores.
- Un producto con `is_available = false` no se puede agregar a un pedido y aparece como "Agotado" en el menú público.

Estados de una mesa y quién los cambia:

| De | A | Cuándo | Módulo |
| --- | --- | --- | --- |
| libre | ocupada | El mesero abre un pedido | Pedidos |
| ocupada / comiendo | esperando | Se envía una ronda a cocina | Pedidos |
| esperando | comiendo | El mesero marca entregadas todas las comandas | Pedidos |
| cualquiera | cuenta | El mesero marca que la mesa pidió la cuenta | Pedidos |
| cuenta | libre | Caja registra el pago completo y cierra el pedido | Caja |

Estados de una comanda: `pendiente → preparando → listo` (los cambia Cocina) `→ entregado` (lo marca el mesero en Pedidos).

## Rutas por área

| Área | Prefijo | Nombre | Rol |
| --- | --- | --- | --- |
| Administración | `/admin` | `admin.` | admin |
| Mesero | `/mesero` | `mesero.` | mesero, admin |
| Cocina | `/cocina` | `cocina.` | cocina, admin |
| Caja | `/caja` | `caja.` | caja, admin |
| Menú público | `/menu`, `/menu/mesa/{qr_token}` | `menu.` | sin login |

**Mientras el login no esté listo**, los grupos de rutas no llevan middleware y `/` muestra una página de desarrollo con enlaces a cada área. El módulo de Login agrega `auth` y `role:...` a cada grupo; los demás módulos no tocan eso.

## Módulos y responsables

Cada módulo se trabaja en su propia rama y entra a `develop` por pull request.

| Módulo | Responsable | Rama |
| --- | --- | --- |
| Base | @Narvii10 (líder técnico) | `feature/base` |
| Menú | @Narvii10 | `feature/menu` |
| Login y usuarios | Integrante 2 (Scrum Master) | `feature/login` |
| Mesas | @Narvii10 | `feature/mesas` |
| Pedidos | Integrante 3 | `feature/pedidos` |
| Cocina | Integrante 4 | `feature/cocina` |
| Caja | Integrante 1 (Product Owner) | `feature/caja` |
| Despliegue | @Narvii10 | `feature/deploy` |

### Base
Migraciones de todas las tablas, modelos con relaciones y casts, seeders con datos de ejemplo, layouts (`layouts/app` con barra de navegación por área y mensajes flash; `layouts/public` para el menú público, pensado para celular), componente `<x-money>`, grupos de rutas por área con páginas provisionales y página de desarrollo en `/`.

Seeders: un usuario por rol (`admin@tabla.test`, `mesero@tabla.test`, `cocina@tabla.test`, `caja@tabla.test`, contraseña `password`), categorías (Tablas, Paninis y sándwiches, Hamburguesas, Tacos, Vinos, Bebidas), unos 15 productos con precios en quetzales y vinos sugeridos, y 8 mesas.

### Menú (patrón de referencia para los demás módulos)
- Admin: CRUD de categorías y productos, con búsqueda y paginación. Botón para marcar o desmarcar agotado. Selección de vino sugerido entre los productos con `is_wine = true`.
- Público: `/menu` y `/menu/mesa/{qr_token}` muestran productos activos por categoría, con filtro y búsqueda; los agotados se ven como "Agotado"; cada plato muestra su vino sugerido. Si el token no existe, 404.

### Login y usuarios
- Formulario de login con email y contraseña, cierre de sesión, y redirección según rol al área correspondiente.
- Middleware `role` que recibe uno o varios roles (`role:mesero,admin`) y responde 403 si no corresponde.
- Agregar `auth` y `role:...` a los grupos de rutas según la tabla de áreas.
- Admin: crear, editar y desactivar usuarios. Un usuario con `active = false` no puede iniciar sesión.
- Sin registro público ni recuperación de contraseña.

### Mesas
- Admin: CRUD de mesas. Al crear una mesa se genera su `qr_token` único.
- Página imprimible con el QR de cada mesa apuntando a `/menu/mesa/{qr_token}`. El QR se genera en el navegador con una librería JS desde CDN.
- Mesero: mapa de mesas en cuadrícula con color por estado y aviso cuando una comanda de esa mesa está "listo". Se actualiza cada 5 s con `fetch` a un endpoint JSON. Clic en una mesa lleva a su pedido.

### Pedidos
- Abrir pedido en una mesa libre (la mesa pasa a ocupada).
- Agregar productos con cantidad y nota, buscando por categoría; los agotados no se pueden agregar; al elegir un plato se muestra su vino sugerido para ofrecerlo.
- Enviar a cocina: crea una `kitchen_ticket` con los productos aún no enviados (mesa a esperando). Se pueden enviar más rondas.
- Ver el pedido con sus comandas y estados; marcar comandas entregadas; marcar que la mesa pidió la cuenta.

### Cocina
- Pantalla con comandas pendientes, en preparación y listas, ordenadas de la más antigua a la más reciente. Cada una muestra mesa, hora, productos, cantidades y notas resaltadas.
- Se actualiza cada 5 s con `fetch` a un endpoint JSON, sin recargar la página.
- Color según minutos de espera: verde menos de 10, amarillo de 10 a 20, rojo más de 20.
- Botones para pasar a "preparando" y "listo".
- Resumen de productos pendientes sumando todas las comandas (por ejemplo, "4 paninis, 2 tablas").
- Botón para marcar un producto como agotado.

### Caja
- Lista de mesas con pedido abierto, resaltando las que pidieron la cuenta.
- Detalle de la cuenta: productos, subtotal, propina sugerida del 10% (opcional) y total.
- Dividir en partes iguales: muestra cuánto paga cada persona.
- Registrar uno o varios pagos con método; en efectivo, calcular el vuelto. Cuando el saldo llega a Q0, el pedido se cierra y la mesa queda libre.
- Cierre de caja del día: totales esperados por método, efectivo contado y diferencia.

### Despliegue
Railway, usando el `Dockerfile` del repositorio y MySQL de Railway. Variables de entorno en Railway, nunca en el repositorio.

## Flujo de trabajo con Git

- `main` y `develop` están protegidas: todo entra por pull request y requiere aprobación de @Narvii10 (`CODEOWNERS`).
- Crear la rama desde `develop` actualizado: `git checkout develop && git pull && git checkout -b feature/<modulo>`.
- Commits pequeños y frecuentes, en español e imperativo: "Agregar pantalla de cocina", "Validar cantidad en pedidos".
- Abrir el pull request hacia `develop` apenas haya algo funcionando; no esperar a terminar todo.
- Antes de abrir el PR: `git pull origin develop` en la rama, resolver conflictos y probar.
- No modificar archivos de otros módulos. Si hace falta, avisar al responsable.
- Al trabajar en pareja, agregar al commit la línea `Co-authored-by: Nombre <correo>`.

## Reglas para Claude Code

- El estudiante que te usa debe poder explicar su código en la presentación. **Explica qué hace cada archivo que crees o cambies**, en lenguaje sencillo y en español.
- Trabaja solo dentro del módulo de la rama actual. No toques archivos de otros módulos ni la base sin que te lo pidan.
- No modifiques migraciones existentes. Si falta algo en la base de datos, propón una migración nueva y espera confirmación.
- No agregues paquetes de Composer ni librerías sin preguntar.
- Sigue el patrón del módulo de Menú (controlador, Form Request, vistas, rutas) para que todo el proyecto se vea igual.
- Valida en servidor con Form Requests y en cliente con atributos HTML (`required`, `min`, `type`).
- Protege contra vulnerabilidades comunes: `@csrf` en formularios, salida escapada con `{{ }}`, nada de SQL concatenado.
- Antes de terminar, corre las pruebas (`docker compose exec app php artisan test`) y revisa la pantalla en el navegador, también en ancho de celular.

## Pruebas

Cada módulo agrega al menos una prueba de funcionalidad en `tests/Feature/` que cubra su caso principal, por ejemplo:
- Login: un mesero no puede entrar a `/admin` (403).
- Pedidos: agregar un producto agotado falla; enviar a cocina crea una comanda.
- Caja: pagar el total cierra el pedido y libera la mesa.
