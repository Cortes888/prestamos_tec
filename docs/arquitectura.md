# Componentes y responsabilidades

| Capa | Componente | Responsabilidad | Dependencias | Archivo o paquete actual |
|---|---|---|---|---|
| Presentación | Entrada de la API (api.php y Router) | Recibir todas las rutas /api/ y llamar al controlador según el método y la ruta. Responde 404 si la ruta no existe y 405 si no acepta el método. | Controladores, RespuestaJson | .htaccess, src/api.php, src/Compartido/Http/Router.php |
| Presentación | ObjetoControlador (MVP) | Atender GET /api/objetos y PATCH /api/objetos/{id}/estado, sin SQL. | Contenedor, InventarioServicio, RespuestaJson | src/Inventario/Http/ObjetoControlador.php |
| Negocio | InventarioServicio (MVP) | Ser el único que cambia el estado de un equipo: revisa que el cambio sea válido y lo guarda. También entrega la lista de equipos. | EstadoObjeto, ObjetoRepositorio, Transacción | src/Inventario/Servicio/InventarioServicio.php |
| Negocio | EstadoObjeto (MVP) | Definir los estados de un equipo (disponible, prestado, mantenimiento, baja) y qué cambios se permiten entre ellos. | Ninguna | src/Inventario/Dominio/EstadoObjeto.php |
| Datos | ObjetoRepositorio (MVP) | Hacer todas las consultas SQL de la tabla objeto y bloquear el equipo mientras se registra un préstamo. | Conexión, EstadoObjeto | src/Inventario/Repositorio/ (ObjetoRepositorio.php, MySqlObjetoRepositorio.php) |
| Presentación | PrestamoControlador | Atender GET y POST /api/prestamos y POST /api/prestamos/{id}/devolucion, y responder con el resultado o con el error, sin SQL. | Contenedor, PrestamoServicio, RespuestaJson | src/Prestamos/Http/PrestamoControlador.php |
| Negocio | PrestamoServicio | Registrar préstamos y devoluciones en una sola transacción: revisa usuario, días y que el préstamo siga abierto, y le pide a Inventario cambiar el estado del equipo. | PrestamoRepositorio, InventarioServicio, Transacción | src/Prestamos/Servicio/PrestamoServicio.php |
| Datos | PrestamoRepositorio | Hacer todas las consultas SQL de la tabla prestamo. | Conexión | src/Prestamos/Repositorio/ (PrestamoRepositorio.php, MySqlPrestamoRepositorio.php) |
| Presentación | Contenedor | Armar cada servicio con lo que necesita, para que los controladores no tengan que hacerlo. | Conexión, Transacción, servicios y repositorios | src/Compartido/Contenedor.php, src/bootstrap.php |
| Presentación | RespuestaJson | Enviar la respuesta en JSON y convertir los errores en mensajes claros (404 no existe, 405 método no permitido, 409 conflicto, 422 faltan datos). | ErrorDeNegocio | src/Compartido/Http/RespuestaJson.php, src/Compartido/ErrorDeNegocio.php |
| Negocio | Transacción | Guardar todo o nada: si un paso falla, deshacer los anteriores. | Conexión | src/Compartido/Transaccion.php, src/Compartido/PdoTransaccion.php |
| Datos | Conexión | Abrir la única conexión a la base de datos. | Base de datos | src/Config/Conexion.php |
| Sin capas | Archivos sin migrar (fuera del MVP) | Dashboard, usuarios, notificaciones, categorías, facultades y crear o eliminar objetos y usuarios. Cada archivo sigue haciendo todo. | db.php, Base de datos | src/dashboard.php, src/usuarios.php, src/notificaciones.php, src/marcar_enviada.php, src/categorias.php, src/facultades.php, src/crear_objeto.php, src/eliminar_objeto.php, src/crear_usuario.php, src/eliminar_usuario.php, src/db.php |
| Presentación | Interfaz web | Mostrar las pantallas y llamar al backend. | API REST y archivos sin migrar (por HTTP) | index.html |
| Datos | Base de datos | Guardar la información e impedir datos inválidos: llaves foráneas, estados permitidos y un solo préstamo abierto por equipo. | Ninguna | resources/database/BDSQL V3.sql |
