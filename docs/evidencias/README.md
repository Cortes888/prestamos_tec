# Evidencias

Pruebas del MVP en XAMPP con la base creada con `BDSQL V3.sql`. Para las pruebas registramos dos usuarios (Juan David Ospina y Samuel Silva) y cinco equipos: Monitor LG, Cable HDMI, Micrófono, Bafle y Portátil ThinkPad.

## Flujo principal

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 1 | Ver el inventario | Aparecen los equipos con su estado | [01_inventario.png](01_inventario.png) |
| 2 | Registrar un préstamo del micrófono | Queda el préstamo #002 activo | [02_prestamo_registrado.png](02_prestamo_registrado.png) |
| 3 | Ver el inventario otra vez | El micrófono aparece prestado | [03_equipo_prestado.png](03_equipo_prestado.png) |
| 4 | Abrir http://localhost/prestamos/api/objetos | El inventario en JSON | [04_api_objetos.png](04_api_objetos.png) |
| 5 | Devolver el préstamo | "OK: Devolución del préstamo #2 registrada." | [05_devolucion.png](05_devolucion.png) |
| 6 | Abrir Swagger (http://localhost/prestamos/docs/api/) | Aparecen las rutas de objetos y préstamos | [06_swagger.png](06_swagger.png) |

## Integridad de datos (ADR-004)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 7 | Devolver un préstamo que ya se devolvió (desde otra pestaña sin recargar) | "El préstamo #3 ya había sido devuelto." | [07_devolucion_repetida.png](07_devolucion_repetida.png) |

## Mantenibilidad (ADR-002)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 8 | `SHOW TRIGGERS FROM prestamos_tec;` | 0 filas: no hay triggers | [08_sin_triggers.png](08_sin_triggers.png) |
| 9 | Pasar un equipo a mantenimiento desde Inventario | "OK: Equipo #2 ahora está en estado mantenimiento." | [09_mantenimiento.png](09_mantenimiento.png) |
