# Prestamos TEC - Arquitectura de Sistemas (MVP)

## Descripción del proyecto
Sistema para la digitalización y administración del préstamo de apoyo tecnológico de la universidad (Bloque 1, Piso 3). Reemplaza el registro en papel para eliminar errores de diligenciamiento, mejorar la trazabilidad y optimizar el control del inventario.

## Integrantes
- Santiago Cortes
- Juan David Ospina
- Samuel Silva

## Docente
- Fernando Higuita Bentancur

## Institución y año
- UCLA - 2026

## Tecnologías utilizadas
- Frontend: HTML, CSS, JavaScript
- Backend: PHP sin framework, con PDO para el módulo MVP
- Base de datos: MySQL (diseñada en MySQL Workbench)
- Servidor local: XAMPP (Apache y MySQL)
- Documentación de la API: OpenAPI con Swagger UI

## Módulo priorizado para re-arquitectura
- Inventario: control y estado de los equipos tecnológicos (disponible, prestado, mantenimiento, baja). Nos enfocamos en mejorar los atributos de Mantenibilidad, Confiabilidad e Integridad de Datos.

## Problemas arquitectónicos identificados
1. Concentración de responsabilidades: cada archivo PHP se conectaba a la base, hacía la consulta y armaba la respuesta en el mismo lugar, y las mismas consultas se repetían en varios archivos.
2. Dependencia entre módulos: el estado de los equipos no lo controlaba Inventario, sino dos triggers guardados en la tabla de préstamos. Por eso:
   - se podía prestar dos veces el mismo equipo si dos personas lo pedían al mismo tiempo;
   - la devolución respondía "OK" aunque no hubiera cambiado nada.
3. Escalabilidad limitada: el sistema está pensado para un solo punto de préstamo.

Para resolverlos separamos el backend en capas (controladores, servicios y repositorios), dejamos que el módulo de Inventario sea el único que cambia el estado de los equipos y expusimos el MVP como una API REST.

## Documentación
- Componentes y responsabilidades: [docs/arquitectura.md](docs/arquitectura.md)
- Diagramas C4: [docs/Diagramas](docs/Diagramas)
- Decisiones de refactorización (ADR): [docs/ADR](docs/ADR)
- Especificación de la API: [docs/api/openapi.yaml](docs/api/openapi.yaml)

## Estructura del repositorio
- `docs/`: arquitectura, diagramas C4, decisiones (ADR), documentación de la API y entregas anteriores.
- `src/`: backend en PHP.
  - `api.php` recibe todas las rutas `/api/` y el Router las reparte a los controladores.
  - `Inventario/` es el módulo MVP: `Http/` (controlador), `Servicio/`, `Dominio/` y `Repositorio/`.
  - `Prestamos/` tiene la misma organización.
  - `Compartido/` y `Config/` tienen la conexión, la transacción, el Router y la respuesta JSON.
  - Los demás archivos `.php` sueltos (dashboard, usuarios, etc.) no se migraron y la página los llama directamente.
- `resources/database/`: scripts de la base de datos.
  - `BDSQL V2.sql` es la versión anterior.
  - `BDSQL V3.sql` es la actual.
- `.htaccess`: envía las rutas `/api/` a `src/api.php`.
- `index.html`: la página del sistema.

## Requisitos
- XAMPP con PHP 7.4 o superior (cualquier XAMPP 8 sirve). Trae Apache y MySQL, y ya vienen activos `mod_rewrite` y la extensión `pdo_mysql`, que son los que usa la API.
- MySQL Workbench, para crear la base de datos (también sirve phpMyAdmin).
- Un navegador web. Para probar la API también sirve Postman o la página de Swagger del proyecto.

No hay que instalar paquetes con Composer ni con npm.

## Instalación
1. Copiar el proyecto en `C:\xampp\htdocs\prestamos`. La carpeta debe llamarse así para que funcionen las direcciones de abajo.
2. En XAMPP, encender Apache y MySQL.
3. En MySQL Workbench, abrir y ejecutar [`resources/database/BDSQL V3.sql`](resources/database/BDSQL%20V3.sql). Este script borra la base `prestamos_tec` si existe y la crea de nuevo, vacía, solo con facultades y categorías.

La conexión usa los valores por defecto de XAMPP: usuario `root`, sin contraseña, base `prestamos_tec`.

## Ejecución
1. Abrir en el navegador http://localhost/prestamos/
2. Crear un usuario y un equipo.
3. Registrar un préstamo, revisar que el equipo pase a "prestado" en Inventario y luego registrar la devolución.

La API queda en http://localhost/prestamos/api. Por ejemplo, http://localhost/prestamos/api/objetos devuelve el inventario en JSON.

La documentación interactiva de la API (Swagger) queda en http://localhost/prestamos/docs/api/. Necesita internet porque carga Swagger UI desde un CDN.

Si `/api/objetos` responde "Not Found" de Apache, es porque `.htaccess` no se está aplicando. La API sigue funcionando con la dirección larga: http://localhost/prestamos/src/api.php/objetos.

## API REST del MVP
Todas las respuestas son JSON. Cuando algo falla, la respuesta trae un código HTTP de error y un mensaje de la forma `{"error": "..."}`.

| Código | Cuándo |
|---|---|
| 200 | Todo salió bien. |
| 201 | Se creó un préstamo. |
| 404 | El equipo, el usuario, el préstamo o la ruta no existen. |
| 405 | La ruta existe pero no acepta ese método. |
| 409 | La operación choca con el estado actual (equipo no disponible, préstamo ya devuelto, cambio de estado no permitido). |
| 422 | Faltan datos o no son válidos. |

### Endpoints

| Método | Ruta | Descripción | Ejemplo de entrada | Ejemplo de respuesta |
|---|---|---|---|---|
| GET | `/api/objetos` | Lista el inventario (sin los equipos dados de baja), con la categoría y el total de préstamos. | (ninguna) | `200` `[{"id_objeto": 1, "nombre": "Portátil Lenovo", "codigo_inv": "POR-001", "estado": "disponible", "descripcion": "ThinkPad", "categoria": "Portátiles", "total_prestamos": 0}]` |
| GET | `/api/objetos?estado=disponible` | Lista solo los equipos que se pueden prestar. | (ninguna) | `200` `[{"id_objeto": 1, "nombre": "Portátil Lenovo", "codigo_inv": "POR-001"}]` |
| PATCH | `/api/objetos/{id}/estado` | Cambia el estado de un equipo a `mantenimiento`, `baja` o `disponible`. El estado `prestado` solo cambia al prestar o devolver. | `{"estado": "mantenimiento"}` | `200` `{"mensaje": "OK: Equipo #2 ahora está en estado mantenimiento.", "id_objeto": 2, "estado_anterior": "disponible", "estado": "mantenimiento"}` |
| GET | `/api/prestamos` | Lista los préstamos con el nombre del usuario y del equipo. | (ninguna) | `200` `[{"id_prestamo": 1, "usuario": "Ana Ruiz", "objeto": "Portátil Lenovo", "fecha_prestamo": "2026-10-29 17:51:21", "fecha_devolucion_esperada": "2026-11-01 17:51:21", "fecha_devolucion_real": null, "estado": "activo"}]` |
| POST | `/api/prestamos` | Registra un préstamo. Bloquea el equipo mientras lo registra, así que dos solicitudes al mismo tiempo no pueden prestarlo dos veces. `dias` va de 1 a 30 (por defecto 3). | `{"id_usuario": 1, "id_objeto": 1, "dias": 3, "obs": "Clase de redes"}` | `201` `{"mensaje": "OK: Préstamo #1 registrado.", "id_prestamo": 1}` |
| POST | `/api/prestamos/{id}/devolucion` | Registra la devolución y deja el equipo disponible. `obs` es opcional. | `{"obs": "Llegó bien"}` | `200` `{"mensaje": "OK: Devolución del préstamo #1 registrada.", "id_prestamo": 1}` |

### Ejemplos de errores

| Petición | Respuesta |
|---|---|
| `POST /api/prestamos` con un equipo que ya está prestado | `409` `{"error": "El equipo \"Portátil Lenovo\" no está disponible (estado actual: prestado)."}` |
| `POST /api/prestamos` con `"dias": 40` | `422` `{"error": "Los días de préstamo deben estar entre 1 y 30."}` |
| `POST /api/prestamos` con un usuario que no existe | `404` `{"error": "El usuario #99 no existe o está inactivo."}` |
| `POST /api/prestamos/1/devolucion` por segunda vez | `409` `{"error": "El préstamo #1 ya había sido devuelto."}` |
| `POST /api/prestamos/9999/devolucion` | `404` `{"error": "El préstamo #9999 no existe."}` |
| `PATCH /api/objetos/1/estado` con el equipo prestado | `409` `{"error": "El equipo \"Portátil Lenovo\" está prestado. Para liberarlo hay que registrar la devolución."}` |
| `DELETE /api/prestamos` | `405` `{"error": "La ruta /api/prestamos no acepta el método DELETE."}` |

### Probar con curl
```bash
curl http://localhost/prestamos/api/objetos

curl -X POST http://localhost/prestamos/api/prestamos -H "Content-Type: application/json" -d "{\"id_usuario\": 1, \"id_objeto\": 1, \"dias\": 3}"

curl -X POST http://localhost/prestamos/api/prestamos/1/devolucion

curl -X PATCH http://localhost/prestamos/api/objetos/1/estado -H "Content-Type: application/json" -d "{\"estado\": \"mantenimiento\"}"
```
