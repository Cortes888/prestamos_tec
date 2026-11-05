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

| # | Prueba | Resultado esperado | Captura |
|---|---|---|---|
| 7 | Pedir el mismo equipo desde dos pestañas | La primera se registra y la segunda dice que el equipo no está disponible | [07_dos_pestanas.png](07_dos_pestanas.png) |
| 8 | Mandar 10 préstamos del mismo equipo al mismo tiempo con `concurrencia.php` | 1 aprobado (201) y 9 rechazados (409), en menos de 2 segundos | [08_concurrencia.png](08_concurrencia.png) |
| 9 | Insertar a mano en Workbench un segundo préstamo activo del mismo equipo | Error 1062 "Duplicate entry" por el índice único | [09_indice_unico.png](09_indice_unico.png) |

## Integridad de datos (ADR-004)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 10 | Devolver un préstamo que ya se devolvió (desde otra pestaña sin recargar) | "El préstamo #3 ya había sido devuelto." | [10_devolucion_repetida.png](10_devolucion_repetida.png) |
| 11 | Devolver un préstamo que no existe desde Swagger (id 9999) | Error 404 "El préstamo #9999 no existe." | [11_devolucion_inexistente.png](11_devolucion_inexistente.png) |
| 12 | Consulta de control en Workbench | No devuelve filas | [12_consulta_control.png](12_consulta_control.png) |

Consulta de control:

```sql
SELECT o.id_objeto, o.nombre
FROM objeto o
WHERE o.estado = 'prestado'
  AND NOT EXISTS (
    SELECT 1 FROM prestamo p
    WHERE p.id_objeto = o.id_objeto
      AND p.estado IN ('activo', 'vencido'));
```

## Mantenibilidad (ADR-002)

| # | Prueba | Resultado | Captura |
|---|---|---|---|
| 13 | Revisar `EstadoObjeto.php` | Los estados y sus cambios están en un solo lugar | [13_estado_objeto.png](13_estado_objeto.png) |
| 14 | Buscar `'disponible'` y `'prestado'` en `src/Prestamos/` | No hay resultados: Préstamos no maneja los estados del equipo | [14_busqueda_prestamos.png](14_busqueda_prestamos.png) |
| 15 | `SHOW TRIGGERS FROM prestamos_tec;` | 0 filas: no hay triggers | [15_sin_triggers.png](15_sin_triggers.png) |
| 16 | Pasar un equipo a mantenimiento desde Inventario | "OK: Equipo #2 ahora está en estado mantenimiento." | [16_mantenimiento.png](16_mantenimiento.png) |
