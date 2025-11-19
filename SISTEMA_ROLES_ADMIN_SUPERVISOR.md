# Sistema de Roles con Admin-Supervisor

## 📋 Resumen

Se ha implementado un sistema que permite que un **admin también pueda supervisar estudiantes** manteniendo su rol de administrador.

---

## 🎯 Cómo Funciona

### **Campo `es_supervisor`**

Se agregó un campo booleano `es_supervisor` a la tabla `users`:
- `false` (default): Usuario normal según su rol
- `true`: Si el rol es 'admin', puede supervisar estudiantes

### **Jerarquía de Acceso**

El middleware `ValidarRol` implementa jerarquía:
- **Admin** puede acceder a TODAS las rutas (admin, supervisor, estudiante)
- **Supervisor** solo puede acceder a rutas de supervisor
- **Estudiante** solo puede acceder a rutas de estudiante

---

## 🔧 Configuración Inicial

### **Paso 1: Ejecutar migraciones**

```bash
php artisan migrate
```

Esto creará los campos:
- `rol` (enum: admin, estudiante, supervisor)
- `es_supervisor` (boolean, default: false)

### **Paso 2: Actualizar dependencias**

```bash
composer update
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 👥 Gestión de Usuarios

### **Asignar roles a usuarios existentes**

```bash
php artisan tinker
```

```php
// Asignar admin
$admin = User::where('email', 'admin@unah.edu.hn')->first();
$admin->rol = 'admin';
$admin->save();

// Asignar estudiantes
User::where('email', 'LIKE', '%@unah.hn')->update(['rol' => 'estudiante']);

// Asignar supervisores
User::where('email', 'LIKE', '%@unah.edu.hn')
    ->whereNull('rol')
    ->update(['rol' => 'supervisor']);
```

### **Convertir admin en supervisor**

Cuando un admin TAMBIÉN necesita supervisar:

```php
$admin = User::where('email', 'admin@unah.edu.hn')->first();
$admin->es_supervisor = true;
$admin->save();

// Crear perfil de supervisor
Supervisor::create([
    'user_id' => $admin->id,
    'activo' => true,
    'max_estudiantes' => 10,  // Ajustar según necesidad
]);
```

### **Convertir supervisor en admin**

```php
$supervisor = User::where('email', 'supervisor@unah.edu.hn')->first();
$supervisor->rol = 'admin';
$supervisor->es_supervisor = true;  // Mantener capacidad de supervisar
$supervisor->save();
```

---

## 🖥️ Interfaz de Usuario

### **Dashboard Admin**

El dashboard del admin mostrará una tarjeta adicional SI `es_supervisor = true`:

```blade
@if(auth()->user()->es_supervisor && auth()->user()->tieneEstudiantesAsignados())
    <div class="card">
        <h3>Mis Supervisiones</h3>
        <p>Tienes estudiantes asignados</p>
        <a href="{{ route('admin.mis-supervisiones') }}">
            Ver mis estudiantes
        </a>
    </div>
@endif
```

### **Ruta para supervisiones del admin**

```
/admin/mis-supervisiones
```

Esta ruta muestra:
- Lista de estudiantes asignados
- Estados de sus solicitudes
- Documentos subidos
- Supervisiones realizadas

---

## 📊 Helpers en el Modelo User

### **Métodos disponibles:**

```php
// Verificar roles
$user->isAdmin();           // true si rol === 'admin'
$user->isSupervisor();      // true si rol === 'supervisor'
$user->isEstudiante();      // true si rol === 'estudiante'

// Verificar capacidad de supervisión
$user->puedeSuperviar();    // true si supervisor O (admin + es_supervisor)
$user->tieneEstudiantesAsignados();  // true si tiene estudiantes activos
```

### **Ejemplos de uso:**

```php
// En controladores
if (auth()->user()->puedeSuperviar()) {
    // Mostrar opciones de supervisión
}

// En vistas Blade
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
@endif

@if(auth()->user()->puedeSuperviar())
    <a href="{{ route('admin.mis-supervisiones') }}">Mis Estudiantes</a>
@endif
```

---

## 🔐 Protección de Rutas

### **Middleware `ValidarRol`**

```php
// Solo admin
Route::middleware(['auth', 'verified', 'rol:admin'])->group(function() {
    // Admin puede acceder aquí
});

// Solo supervisor (pero admin TAMBIÉN puede)
Route::middleware(['auth', 'verified', 'rol:supervisor'])->group(function() {
    // Supervisor puede acceder
    // Admin TAMBIÉN puede acceder (por jerarquía)
});

// Múltiples roles
Route::middleware(['auth', 'verified', 'rol:admin,supervisor'])->group(function() {
    // Admin O supervisor pueden acceder
});
```

---

## 💡 Casos de Uso

### **Caso 1: Admin puro (no supervisa)**

```php
rol = 'admin'
es_supervisor = false
```

**Acceso:**
- ✅ Todas las rutas de admin
- ✅ Puede ver TODO el sistema
- ❌ No aparece tarjeta de supervisiones

---

### **Caso 2: Admin que también supervisa**

```php
rol = 'admin'
es_supervisor = true
```

**Acceso:**
- ✅ Todas las rutas de admin
- ✅ Puede ver TODO el sistema
- ✅ Aparece tarjeta "Mis Supervisiones"
- ✅ Puede acceder a `/admin/mis-supervisiones`
- ✅ Puede acceder a rutas de supervisor

**Requiere:**
- Registro en tabla `supervisores` con `user_id` del admin

---

### **Caso 3: Supervisor que se vuelve admin**

```php
// Antes
rol = 'supervisor'
es_supervisor = false

// Después
rol = 'admin'
es_supervisor = true
```

**Mantiene:**
- ✅ Sus estudiantes asignados
- ✅ Registro en tabla `supervisores`
- ✅ Acceso a supervisiones

**Gana:**
- ✅ Acceso completo como admin
- ✅ Puede gestionar otros supervisores
- ✅ Puede aprobar solicitudes

---

## 📝 Base de Datos

### **Tabla: users**

```sql
ALTER TABLE users
ADD COLUMN rol ENUM('admin', 'estudiante', 'supervisor') NULL,
ADD COLUMN es_supervisor BOOLEAN DEFAULT FALSE;
```

### **Tabla: supervisores**

```sql
-- No cambia, pero ahora user_id puede ser admin
CREATE TABLE supervisores (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,  -- Puede ser admin o supervisor
    activo BOOLEAN DEFAULT TRUE,
    max_estudiantes INT DEFAULT 10,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🚀 Flujo Completo

### **Escenario: Convertir supervisor en admin**

1. **Identificar al supervisor:**
   ```sql
   SELECT * FROM users WHERE email = 'juan@unah.edu.hn';
   ```

2. **Cambiar rol:**
   ```php
   $user = User::find(5);
   $user->rol = 'admin';
   $user->es_supervisor = true;
   $user->save();
   ```

3. **Verificar en tabla supervisores:**
   ```sql
   SELECT * FROM supervisores WHERE user_id = 5;
   -- Debe existir el registro, NO eliminarlo
   ```

4. **Login:**
   - Usuario ingresa como admin
   - Ve dashboard admin completo
   - Ve tarjeta "Mis Supervisiones"
   - Puede acceder a `/admin/mis-supervisiones`

---

## ⚠️ Consideraciones Importantes

1. **No eliminar registros de `supervisores`** cuando cambias un supervisor a admin
2. **Siempre establecer `es_supervisor = true`** si el admin va a supervisar
3. **La jerarquía es automática**: Admin siempre puede acceder a todo
4. **Campo `rol` es principal**: Define el rol base del usuario
5. **Campo `es_supervisor` es adicional**: Solo aplica para admins

---

## 🐛 Solución de Problemas

### **Admin no ve tarjeta de supervisiones**

```php
// Verificar
$user = User::find(1);
dd([
    'rol' => $user->rol,                          // Debe ser 'admin'
    'es_supervisor' => $user->es_supervisor,      // Debe ser true
    'supervisor' => $user->supervisor,            // Debe existir
    'estudiantes' => $user->supervisor?->solicitudes->count()
]);
```

### **Error 403 al acceder a mis-supervisiones**

Verificar método en controlador:
```php
if (!$user->isAdmin() || !$user->es_supervisor) {
    abort(403, 'No tienes estudiantes asignados.');
}
```

Asegurar que:
- `rol = 'admin'`
- `es_supervisor = true`
- Existe en tabla `supervisores`

---

## 📞 Comandos Útiles

```bash
# Ver todos los admins
php artisan tinker
User::where('rol', 'admin')->get(['id', 'name', 'email', 'es_supervisor']);

# Ver admins que supervisan
User::where('rol', 'admin')->where('es_supervisor', true)->get();

# Ver supervisor de un admin
$admin = User::find(1);
$admin->supervisor;

# Ver estudiantes de un admin
$admin->supervisor->solicitudes;
```

---

## ✅ Checklist de Implementación

- [x] Migración campo `es_supervisor` creada
- [x] Modelo User actualizado con helpers
- [x] Middleware ValidarRol con jerarquía
- [x] Controlador con método `misSupervisiones()`
- [x] Ruta `/admin/mis-supervisiones` agregada
- [ ] Vista `admin/mis-supervisiones.blade.php` (opcional)
- [ ] Actualizar dashboard admin para mostrar tarjeta
- [ ] Agregar interfaz para admin cambiar roles de usuarios

---

**Documentación creada:** 2025-11-17
**Sistema:** Laravel 12 + MySQL
**Roles:** Admin, Estudiante, Supervisor
