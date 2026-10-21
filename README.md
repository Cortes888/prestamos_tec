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
- Backend: PHP
- Base de datos: MySQL (diseñada en MySQL Workbench)
- Servidor local: XAMPP

## Módulo priorizado para re-arquitectura
- Inventario: control y estado de los equipos tecnológicos (disponible, prestado, mantenimiento, baja). Nos enfocamos en mejorar los atributos de Mantenibilidad, Confiabilidad e Integridad de Datos.

## Problemas arquitectónicos identificados
1. Concentración de responsabilidades: cada archivo PHP se conectaba a la base, hacía la consulta y armaba la respuesta en el mismo lugar, y las mismas consultas se repetían en varios archivos.
2. Dependencia entre módulos: el estado de los equipos no lo controlaba Inventario, sino dos triggers guardados en la tabla de préstamos. Por eso:
   - se podía prestar dos veces el mismo equipo si dos personas lo pedían al mismo tiempo;
   - la devolución respondía "OK" aunque no hubiera cambiado nada.
3. Escalabilidad limitada: el sistema está pensado para un solo punto de préstamo.

Para resolverlos separamos el backend en capas (controladores, servicios y repositorios) y dejamos que el módulo de Inventario sea el único que cambia el estado de los equipos.

## Documentación
- Componentes y responsabilidades: [docs/arquitectura.md](docs/arquitectura.md)
- Diagramas C4: [docs/Diagramas](docs/Diagramas)
- Decisiones de refactorización (ADR): [docs/ADR](docs/ADR)

## Estructura del repositorio
- `docs/`: arquitectura, diagramas C4, decisiones (ADR) y entregas anteriores.
- `src/`: backend en PHP.
  - Los archivos `.php` sueltos reciben las peticiones de la página.
  - `Inventario/` es el módulo MVP.
  - `Prestamos/`, `Compartido/` y `Config/` tienen lo demás.
- `resources/database/`: scripts de la base de datos.
  - `BDSQL V2.sql` es la versión anterior.
  - `BDSQL V3.sql` es la actual.
- `index.html`: la página del sistema.

## Cómo ejecutarlo
1. Copiar el proyecto en `C:\xampp\htdocs\prestamos`.
2. En XAMPP, encender Apache y MySQL.
3. En MySQL Workbench, abrir y ejecutar [`resources/database/BDSQL V3.sql`](resources/database/BDSQL%20V3.sql). Este script borra la base `prestamos_tec` si existe y la crea de nuevo.
4. Abrir en el navegador http://localhost/prestamos/
5. Para probar: crear un usuario y un equipo, y luego registrar un préstamo y su devolución.
