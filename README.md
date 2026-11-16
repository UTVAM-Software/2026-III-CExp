# CEx+ — Sistema de Gestión de Citas, Consultas y Expedientes Médicos

Guía de despliegue e instalación en servidor productivo (Linux + Apache + PHP + MySQL/MariaDB) siguiendo el procedimiento estándar de instalación:

## Procedimiento de instalación en servidor productivo

### 1. Crear ambiente virtual *(No aplica)*
Este proyecto está desarrollado en **PHP nativo (8.x)** sin entorno virtual de Python/Node. Si el servidor ya cuenta con `php`, `php-mysqli` y Apache/MySQL, se continúa directamente con el paso 2.

---

### 2. Descargar archivos de código fuente del proyecto
Clonar el repositorio dentro de la raíz del servidor web (por ejemplo `/var/www/html/`):

```bash
cd /var/www/html
git clone https://github.com/UTVAM-Software/2026-III-CExp.git
cd 2026-III-CExp
```

Si las pruebas se encuentran en una rama específica (por ejemplo `feature/vistas`), cambiar a ella:

```bash
git checkout feature/vistas
```

Asegurar permisos de lectura para el usuario del servidor web (`www-data` en Debian/Ubuntu o `apache` en RHEL/CentOS):

```bash
chown -R www-data:www-data /var/www/html/2026-III-CExp
chmod -R 755 /var/www/html/2026-III-CExp
```

---

### 3. Configurar elementos para el funcionamiento (Base de datos y usuarios)

1. **Importar estructura y datos iniciales en MySQL:**

```bash
mysql -u root -p < base_datos/01_estructura.sql
mysql -u root -p < base_datos/02_datos_iniciales.sql
```

Esto crea la base `cexp_database` con todas sus tablas y carga los datos iniciales (roles, catálogo de permisos, usuarios con contraseña encriptada con `password_hash`, médico y pacientes de ejemplo).

2. **Configurar credenciales de conexión:**
El archivo [Sistema_De_Gestion_CExp/config/env.php](Sistema_De_Gestion_CExp/config/env.php) lee automáticamente variables de entorno del servidor si están definidas, o usa los valores por defecto:
- `DB_HOST` (por defecto: `localhost`)
- `DB_NAME` (por defecto: `cexp_database`)
- `DB_USER` (por defecto: `root`)
- `DB_PASS` (por defecto: `''`)

Si en producción se usa un usuario de MySQL dedicado (recomendado), crear el usuario y ajustar `Sistema_De_Gestion_CExp/config/env.php` o definir las variables de entorno en Apache:

```sql
CREATE USER IF NOT EXISTS 'cexp_user'@'localhost' IDENTIFIED BY 'TuPasswordSeguro';
GRANT ALL PRIVILEGES ON cexp_database.* TO 'cexp_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### 4. Instalar herramientas propias de la aplicación *(No aplica)*
Aplica únicamente para proyectos Django. Este sistema no utiliza dependencias de Composer ni paquetes externos de servidor; únicamente requiere PHP con la extensión `mysqli` habilitada (`php-mysqli`).

---

### 5. Activar el servicio web
Habilitar e iniciar MySQL y Apache (ejemplo en Ubuntu/Debian):

```bash
sudo systemctl enable --now mysql
sudo systemctl enable --now apache2
```

*(En CentOS / Rocky / RHEL usar `httpd` y `mariadb`/`mysqld` según corresponda).*

Una vez levantado el servicio, acceder desde el navegador a:

- `http://<IP_O_DOMINIO>/2026-III-CExp/Sistema_De_Gestion_CExp/` (redirige automáticamente a `views/login.php`)

#### Credenciales iniciales incluidas en `02_datos_iniciales.sql`
| Rol | Usuario | Contraseña | Estado |
| :--- | :--- | :--- | :--- |
| Administrador (`id_grupo = 1`) | `adminPRO` | `admin123*` | Activo |
| Médico (`id_grupo = 2`) | `dr.paco` | `12345` | Activo |
| Admin secundario | `adminTEAM` | `123456` | Inactivo |

---

## Solución de problemas comunes

| Problema | Causa probable | Solución |
| :--- | :--- | :--- |
| **`Error de conexión: Unknown database 'cexp_database'`** | No se importaron los scripts SQL en MySQL. | Ejecutar `mysql -u root -p < base_datos/01_estructura.sql` y luego `mysql -u root -p < base_datos/02_datos_iniciales.sql`. |
| **`Error de conexión: Access denied for user...`** | Usuario o contraseña incorrectos en MySQL o en `env.php`. | Verificar `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` en `Sistema_De_Gestion_CExp/config/env.php` (o en las variables de entorno del servidor). |
| **Error `Class "mysqli" not found` o página en blanco** | Falta la extensión `php-mysqli` en PHP. | Instalar y habilitar la extensión (`sudo apt install php-mysqli && sudo systemctl restart apache2`). |
| **`403 Forbidden` o no cargan estilos/archivos en Apache** | Permisos insuficientes en la carpeta clonada. | Aplicar `chown -R www-data:www-data /var/www/html/2026-III-CExp` y `chmod -R 755 /var/www/html/2026-III-CExp`. |
| **`Usuario o contraseña incorrectos` al iniciar sesión** | La tabla `usuario` tiene claves en texto plano o el usuario está `inactivo`. | Verificar que `02_datos_iniciales.sql` se haya importado completo (incluye hashes bcrypt y `adminPRO` activo en grupo `1`). |
| **Sesión no persiste o redirige siempre a `login.php`** | Carpeta de sesiones PHP sin permisos de escritura o servicio web sin reiniciar. | Revisar `session.save_path` en `php.ini` (en Linux `/var/lib/php/sessions` debe pertenecer a `www-data`) y reiniciar Apache. |
