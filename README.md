# Prestamos TEC - Arquitectura de Sistemas (MVP)

## Descripción del proyecto
Sistema para la digitalización y administración del préstamo de apoyo tecnológico de la universidad (Bloque 1, Piso 3). Reemplaza el registro en papel para eliminar errores de diligenciamiento, mejorar la trazabilidad y optimizar el control del inventario.

## Integrantes
- Santiago Cortes (backend, API REST y repositorio)
- Juan David Ospina
- Samuel Silva

## Docente
- Fernando Higuita Bentancur

## Institución y año
- UCLA - 2026

## Tecnologías utilizadas
- Frontend: HTML, CSS, JavaScript
- Backend: PHP (con PDO)
- Base de datos: MySQL (diseñada en MySQL Workbench)
- Servidor local: XAMPP
- Documentación de la API: Swagger

## Módulo priorizado para re-arquitectura
- Inventario: control y estado de los equipos tecnológicos (disponible, prestado, mantenimiento, baja). Nos enfocamos en mejorar los atributos de Mantenibilidad, Confiabilidad e Integridad de Datos.

## Problemas arquitectónicos identificados
1. Concentración de responsabilidades: cada archivo PHP se conectaba a la base, hacía la consulta y armaba la respuesta en el mismo lugar, y las mismas consultas se repetían en varios archivos.
2. Dependencia entre módulos: el estado de los equipos no lo controlaba Inventario, sino dos triggers guardados en la tabla de préstamos. Por eso:
   - se podía prestar dos veces el mismo equipo si dos personas lo pedían al mismo tiempo;
   - la devolución respondía "OK" aunque no hubiera cambiado nada.
3. Escalabilidad limitada: el sistema está pensado para un solo punto de préstamo.

Para resolverlos separamos el backend en capas, dejamos que el módulo de Inventario sea el único que cambia el estado de los equipos y pasamos el MVP a una API REST.

## Arquitectura
El MVP quedó en tres capas:
- Presentación: `index.html` y los controladores de la API (`src/api.php`, `src/Inventario/Http/`, `src/Prestamos/Http/`).
- Negocio: los servicios y `EstadoObjeto` (`Servicio/` y `Dominio/`).
- Datos: los repositorios (`Repositorio/`) y la base de datos.

Si se ve como MVC, la vista es `index.html`, los controladores son `ObjetoControlador` y `PrestamoControlador`, y el modelo son los servicios y repositorios.

![Vista lógica](docs/Diagramas/Vista%20logica.png)

## Documentación
- Documento de arquitectura final: [docs/ENTREGA FINAL ARQUITECTURA.pdf](docs/ENTREGA%20FINAL%20ARQUITECTURA.pdf)
- Componentes y responsabilidades: [docs/arquitectura.md](docs/arquitectura.md)
- Diagramas: [docs/Diagramas](docs/Diagramas)
- Decisiones de refactorización (ADR): [docs/ADR](docs/ADR)
- API en Swagger: [docs/api](docs/api)
- Evidencias: [docs/evidencias](docs/evidencias)

## Estructura del repositorio
- `docs/`: documento final, diagramas, ADR, documentación de la API, evidencias y entregas anteriores.
- `src/`: backend en PHP.
  - `api.php` recibe las rutas de la API.
  - `Inventario/` es el módulo MVP.
  - `Prestamos/`, `Compartido/` y `Config/` tienen lo demás.
  - Los archivos `.php` sueltos son las partes que no se migraron (dashboard, usuarios, etc.).
- `resources/database/`: scripts de la base de datos.
  - `BDSQL V2.sql` es la versión anterior.
  - `BDSQL V3.sql` es la actual.
- `index.html`: la página del sistema.

## Requisitos
- XAMPP (Apache y MySQL) con PHP 7.4 o superior.
- MySQL Workbench para crear la base de datos.
- Navegador web.

## Cómo ejecutarlo
1. Copiar el proyecto en `C:\xampp\htdocs\prestamos`.
2. En XAMPP, encender Apache y MySQL.
3. En MySQL Workbench, abrir y ejecutar [`resources/database/BDSQL V3.sql`](resources/database/BDSQL%20V3.sql). Este script borra la base `prestamos_tec` si existe y la crea de nuevo.
4. Abrir en el navegador http://localhost/prestamos/
5. Para probar: crear un usuario y un equipo, y luego registrar un préstamo y su devolución.

La conexión usa los valores por defecto de XAMPP: usuario `root`, sin contraseña, base `prestamos_tec`.

La API queda en http://localhost/prestamos/api y Swagger en http://localhost/prestamos/docs/api/

## Endpoints
Si algo sale mal la API responde con `{"error": "..."}` y uno de estos códigos: 404 (no existe), 405 (método no permitido), 409 (conflicto con el estado actual) o 422 (datos incompletos o inválidos).

| Método | Ruta | Descripción | Ejemplo de entrada | Ejemplo de respuesta |
|---|---|---|---|---|
| GET | `/api/objetos` | Lista el inventario (sin los equipos dados de baja). | - | `200` `[{"id_objeto": 1, "nombre": "Portátil Lenovo", "codigo_inv": "POR-001", "estado": "disponible", "descripcion": "ThinkPad", "categoria": "Portátiles", "total_prestamos": 0}]` |
| GET | `/api/objetos?estado=disponible` | Lista los equipos que se pueden prestar. | - | `200` `[{"id_objeto": 1, "nombre": "Portátil Lenovo", "codigo_inv": "POR-001"}]` |
| PATCH | `/api/objetos/{id}/estado` | Cambia el estado de un equipo a mantenimiento, baja o disponible. | `{"estado": "mantenimiento"}` | `200` `{"mensaje": "OK: Equipo #2 ahora está en estado mantenimiento.", "id_objeto": 2, "estado_anterior": "disponible", "estado": "mantenimiento"}` |
| GET | `/api/prestamos` | Lista los préstamos con el usuario y el equipo. | - | `200` `[{"id_prestamo": 1, "usuario": "Ana Ruiz", "objeto": "Portátil Lenovo", "fecha_prestamo": "2026-10-29 17:51:21", "fecha_devolucion_esperada": "2026-11-01 17:51:21", "fecha_devolucion_real": null, "estado": "activo"}]` |
| POST | `/api/prestamos` | Registra un préstamo. `dias` va de 1 a 30. | `{"id_usuario": 1, "id_objeto": 1, "dias": 3, "obs": "Clase de redes"}` | `201` `{"mensaje": "OK: Préstamo #1 registrado.", "id_prestamo": 1}` |
| POST | `/api/prestamos/{id}/devolucion` | Registra la devolución y deja el equipo disponible. | `{"obs": "Llegó bien"}` | `200` `{"mensaje": "OK: Devolución del préstamo #1 registrada.", "id_prestamo": 1}` |

Ejemplo de error, si se intenta prestar un equipo que ya está prestado:
`409` `{"error": "El equipo \"Portátil Lenovo\" no está disponible (estado actual: prestado)."}`
