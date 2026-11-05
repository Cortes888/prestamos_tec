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

## Confiabilidad (ADR-003)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 10 | Pedir el mismo equipo (Bafle) desde dos pestañas | La primera se registra ("OK: Préstamo #4 registrado.") y la segunda dice "El equipo "Bafle" no está disponible (estado actual: prestado)." | [10_dos_pestanas.png](10_dos_pestanas.png) |

## Integridad de datos (ADR-004)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 7 | Devolver un préstamo que ya se devolvió (desde otra pestaña sin recargar) | "El préstamo #3 ya había sido devuelto." | [07_devolucion_repetida.png](07_devolucion_repetida.png) |
| 11 | Registrar desde Swagger un préstamo con un usuario que no existe (id 9999) | Error 404 "El usuario #9999 no existe o está inactivo." | [11_usuario_inexistente.png](11_usuario_inexistente.png) |
| 12 | Consulta de control en Workbench (equipos prestados sin préstamo abierto) | 0 filas | [12_consulta_control.png](12_consulta_control.png) |

## Mantenibilidad (ADR-002)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 8 | `SHOW TRIGGERS FROM prestamos_tec;` | 0 filas: no hay triggers | [08_sin_triggers.png](08_sin_triggers.png) |
| 9 | Pasar un equipo a mantenimiento desde Inventario | "OK: Equipo #2 ahora está en estado mantenimiento." | [09_mantenimiento.png](09_mantenimiento.png) |
| 13 | Revisar `EstadoObjeto.php` | Los estados y sus cambios permitidos están en un solo lugar | [13_estado_objeto.png](13_estado_objeto.png) |
