# ToDo App – Guía de instalación y uso

---

## 1. Requisitos previos

- **XAMPP ≥ 8.2** (Apache + MySQL + PHP en un instalador). Perfecto si no quieres usar la terminal.
- **PHP ≥ 8.1 + MySQL** ya instalados. Ideal si prefieres línea de comandos.
- Navegador moderno (Chrome, Edge, Firefox…).
- Programa para descomprimir archivos ZIP.

---

## 2. Descarga y ubicación del proyecto

1. Descarga el ZIP del repositorio o ejecuta:
   ```bash
   git clone https://github.com/tu‑usuario/todo_app.git
   ```
2. Descomprime la carpeta **`todo_app`** en la zona pública de tu servidor.
   - **Windows + XAMPP** → `C:\xampp\htdocs\todo_app`
   - **macOS / Linux + XAMPP** → `/Applications/XAMPP/htdocs/todo_app`
   - **Servidor interno de PHP** → cualquier carpeta (por ejemplo `~/web/todo_app`)
3. Verifica que el archivo de entrada sea `todo_app/public/index.php`.

---

## 3. Crear la base de datos

La estructura está en `schema.sql` dentro del proyecto.

### Opción A – phpMyAdmin (gráfico)

1. Abre `http://localhost/phpmyadmin`.
2. Haz clic en «Nueva», escribe **todo_app** y pulsa «Crear».
3. Con la base seleccionada, ve a «Importar», elige `schema.sql` y confirma.

### Opción B – terminal
```bash
mysql -u root -p < schema.sql
```
(En XAMPP el usuario es *root* y la contraseña suele ser vacía.)

---

## 4. Configuración opcional

Si tu usuario/contraseña de MySQL no es *root*/vacío, edita `todo_app/config.php` y ajusta los valores `user` y `password`.

---

## 5. Iniciar el servidor

### Con XAMPP  
1. Abre el *XAMPP Control Panel* y pulsa **Start** en *Apache* y *MySQL*.  
2. Navega a `http://localhost/todo_app/public/index.php`.

### Con el servidor interno de PHP  
1. Abre la terminal y ejecuta:
   ```bash
   cd todo_app/public
   php -S localhost:8000 -t public
   ```
2. Visita `http://localhost:8000/index.php`.

---

## 6. Primer acceso

1. En la página de inicio selecciona **Crear cuenta**.  
2. Introduce un nombre de usuario y una contraseña.  
3. Inicia sesión: llegarás al panel principal (`index.php`).

---

## 7. Funcionalidades de la aplicación

1. **Crear tarea**  
   - Pulsa «Nueva Tarea» (arriba a la izquierda).  
   - Rellena *Título*, *Descripción*, *Fecha límite* y *Estado*.  
   - Confirma con **Crear** y la tarea aparecerá en la lista.

2. **Buscar tarea**  
   - Usa el cuadro «Buscar» sobre la tabla para filtrar por texto en título o descripción.

3. **Ordenar tareas**  
   - Despliega «Ordenar por» (Creación, Fin, Tarea, Estado) y elige dirección ↑ o ↓.

4. **Editar tarea**  
   - Haz clic en **Editar** en la fila correspondiente, modifica los campos y guarda.

5. **Eliminar tarea**  
   - Pulsa **Eliminar** y confirma, la tarea desaparecerá.

6. **Ver registro de acciones (log)**  
   - Pulsa **Ver Log** sobre la tabla para abrir una lista de creaciones, cambios y eliminados con fecha y hora.

7. **Cambiar tema claro / oscuro**  
   - Abre **Ajustes**, activa el interruptor con el icono ☀️/🌙 y pulsa «Volver».  
   - La preferencia se guarda en el navegador (*localStorage*).

8. **Configurar Webhook**  
   - Dentro de **Ajustes** pega tu URL en «Webhook URL» y pulsa **Guardar**.  
   - Cada alta, edición o eliminación enviará un `POST` JSON a esa dirección.

9. **Cerrar sesión**  
   - Pulsa **Salir** en la barra superior para volver a la pantalla de login.

---

## 8. API REST (token de desarrollo `DEV`)

> La ruta cambia según el servidor que uses. Aquí tienes ambos casos.

### A) Servidor XAMPP / Apache
_Raíz del proyecto:_ `http://localhost/todo_app/`

- **Listar todas las tareas**
  ```bash
  curl http://localhost/todo_app/public/api.php/tareas?token=DEV
  ```

- **Detalle de la tarea 3**
  ```bash
  curl http://localhost/todo_app/public/api.php/tareas/3?token=DEV
  ```

- **Crear tarea**
  ```bash
  curl -X POST -H "Content-Type: application/json"        -d '{ "title":"Tarea de prueba", "due_date":"2025-06-30", "description":"Desde API", "user_id":1 }'        http://localhost/todo_app/public/api.php/tareas?token=DEV
  ```

- **Actualizar tarea**
  ```bash
  curl -X PUT -H "Content-Type: application/json"        -d '{ "title":"Modificada", "status":"completada", "due_date":"2025-07-01", "description":"Actualizada", "user_id":1 }'        http://localhost/todo_app/public/api.php/tareas/3?token=DEV
  ```

- **Eliminar tarea**
  ```bash
  curl -X DELETE http://localhost/todo_app/public/api.php/tareas/3?token=DEV
  ```

### B) Servidor interno de PHP
_Comando usado:_ `php -S localhost:8000` (ejecutado en `todo_app/public`)

- **Listar todas las tareas**
  ```bash
  curl http://localhost:8000/api.php/tareas?token=DEV
  ```

- **Detalle de la tarea 3**
  ```bash
  curl http://localhost:8000/api.php/tareas/3?token=DEV
  ```

- **Crear tarea**
  ```bash
  curl -X POST -H "Content-Type: application/json"        -d '{ "title":"Tarea de prueba", "due_date":"2025-06-30", "description":"Desde API", "user_id":1 }'        http://localhost:8000/api.php/tareas?token=DEV
  ```

- **Actualizar tarea**
  ```bash
  curl -X PUT -H "Content-Type: application/json"        -d '{ "title":"Modificada", "status":"completada", "due_date":"2025-07-01", "description":"Actualizada", "user_id":1 }'        http://localhost:8000/api.php/tareas/3?token=DEV
  ```

- **Eliminar tarea**
  ```bash
  curl -X DELETE http://localhost:8000/api.php/tareas/3?token=DEV

---

## 9. Probar el Webhook

1. Abre https://webhook.site y copia la URL generada.  
2. En la app ve a **Ajustes**, pega la URL y pulsa **Guardar**.  
3. Crea o edita una tarea; la solicitud aparecerá en webhook.site.  

---

## 11. Desinstalar

1. Detén Apache y MySQL, o bien finaliza el comando `php -S` con **Ctrl‑C**.  
2. Borra la carpeta `todo_app` y elimina la base de datos `todo_app`.  
3. Desinstala XAMPP si ya no lo necesitas.

---
