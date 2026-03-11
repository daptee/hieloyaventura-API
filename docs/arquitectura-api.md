# Documentación Técnica — API Hielo y Aventura

## Tabla de contenidos

1. [Visión general](#1-visión-general)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Autenticación y guards JWT](#3-autenticación-y-guards-jwt)
4. [Middleware disponibles](#4-middleware-disponibles)
5. [Sistema de permisos por módulo](#5-sistema-de-permisos-por-módulo)
6. [Patrón Bridge — API externa HyA](#6-patrón-bridge--api-externa-hya)
7. [Endpoints agrupados por consumidor](#7-endpoints-agrupados-por-consumidor)
8. [Modelos de datos clave](#8-modelos-de-datos-clave)
9. [Seguridad implementada](#9-seguridad-implementada)
10. [Estructura propuesta de optimización](#10-estructura-propuesta-de-optimización)
11. [Roadmap de mejoras](#11-roadmap-de-mejoras)

---

## 1. Visión general

La API central de Hielo y Aventura es una API REST construida en **Laravel** que sirve de backend unificado para cuatro frontends:

| Frontend | Descripción |
|----------|-------------|
| **Web** | Sitio público de ventas (booking, pagos, información) |
| **Admin** | Panel de administración interno (reservas, usuarios, configuración) |
| **Agencias** | Portal web para agencias de viaje socias |
| **Integración v1** | API REST pública para que agencias externas integren por API key |

### Por qué existe esta API como intermediaria

Muchos endpoints de esta API actúan como **puente (bridge)** hacia una API externa privada de HyA. Esa API externa pertenece a un sistema de escritorio interno y **solo acepta peticiones provenientes de la IP del servidor**. Por eso, todos los frontends (web, agencias, admin) nunca llaman directamente a esa API externa — siempre pasan por esta API, que es quien realmente hace la llamada al sistema de escritorio.

```
Frontend Web / Admin / Agencias
        │
        ▼
  API Laravel (esta API)
  ├─ Lógica de negocio propia (reservas, usuarios, etc.)
  └─ Bridge → API externa HyA (sistema de escritorio)
                  └─ Solo acepta IP del servidor
```

---

## 2. Stack tecnológico

- **Framework**: Laravel (PHP)
- **Autenticación**: `tymon/jwt-auth` — JSON Web Tokens
- **Base de datos**: MySQL (a través de Eloquent ORM)
- **HTTP Cliente externo**: `Illuminate\Support\Facades\Http` (Guzzle wrapper)
- **Pagos**: MercadoPago SDK (PHP)
- **Correos**: Laravel Mail

---

## 3. Autenticación y guards JWT

Existen **dos tipos de usuario** con flujos JWT separados:

### Guard `web` (por defecto) — Usuarios admin/staff/web

- **Tabla**: `users`
- **Modelo**: `App\Models\User`
- **Login**: flujo en **dos pasos** (ver 2FA más abajo)
- **Driver**: sesión (pero JWT se maneja manualmente con `JWTAuth::parseToken()->authenticate()`)
- **Middleware**: `jwt.verify` → `JwtMiddleware`
- **Recuperación en controladores**: `getAuthenticatedAdmin()` (lee de `request()->attributes` para evitar colisión de singleton JWT)

### Guard `agency` — Usuarios de agencias

- **Tabla**: `agency_users`
- **Modelo**: `App\Models\AgencyUser`
- **Login**: flujo en **dos pasos** (ver 2FA más abajo)
- **Driver**: JWT (`tymon/jwt-auth` con guard configurado explícitamente)
- **Middleware**: `jwt.agency` → `AgencyJwtMiddleware`
- **Recuperación en controladores**: `Auth::guard('agency')->user()`

### Flujo de login con 2FA — Web / Admin

El login web y admin requiere verificación en dos pasos. El JWT **no se emite** hasta completar ambos:

```
Paso 1: POST /api/login           (web)
        POST /api/login/admin     (admin)
        { email, password }
        → verifica password_expired (si true → 400 con password_expired: true)
        → valida credenciales
        → genera OTP de 6 dígitos (expira en 10 min)
        → envía OTP al correo del usuario
        → responde: { pending_2fa: true }

Paso 2: POST /api/login/verify-otp
        { email, otp }
        → valida OTP
        → emite JWT
        → limpia otp_code y otp_expires_at de la DB
        → responde: { access_token, data: { user } }
```

### Flujo de login con 2FA — Agencias

El login de agencias requiere verificación en dos pasos. El JWT **no se emite** hasta completar ambos:

```
Paso 1: POST /api/login/agency/user
        { email, password }
        → verifica password_expired (si true → 400 con password_expired: true)
        → valida credenciales
        → genera OTP de 6 dígitos (expira en 10 min)
        → envía OTP al correo del usuario
        → responde: { pending_2fa: true }

Paso 2: POST /api/login/agency/verify-otp
        { email, otp }
        → valida OTP
        → emite JWT
        → limpia otp_code y otp_expires_at de la DB
        → responde: { access_token, data: { user } }
```

### TTL de tokens JWT

Configurado en `config/jwt.php`:
```php
'ttl' => env('JWT_TTL', 60)  // 60 minutos
```
Se puede sobreescribir con la variable de entorno `JWT_TTL`. Aplica a **todos los guards**.

### Guard dual `jwt.admin_or_agency`

Algunos endpoints son compartidos entre admin y usuarios de agencia. El middleware `AdminOrAgencyMiddleware`:

1. Intenta autenticar como admin via `JWTAuth::parseToken()->authenticate()`
2. Si falla (usuario no existe en `users`), intenta via `Auth::guard('agency')->check()`
3. Si el admin es autenticado, guarda el objeto en `$request->attributes->set('authenticated_admin', $user)` — esto es crítico para evitar la **colisión del singleton JWT** (ver sección de bugs conocidos)

### Integración por API Key — Agencias externas

- **Middleware**: `agency.apikey` → `ValidateAgencyApiKey`
- Valida el header `X-API-KEY` contra el campo `api_key` de la tabla `agencies`
- Guarda la agencia autenticada en `$request['authenticated_agency']`

---

## 4. Middleware disponibles

### Autenticación

| Alias | Clase | Uso |
|-------|-------|-----|
| `jwt.verify` | `JwtMiddleware` | Endpoints de admin/staff y web autenticados |
| `jwt.agency` | `AgencyJwtMiddleware` | Endpoints exclusivos de portal de agencias |
| `jwt.admin_or_agency` | `AdminOrAgencyMiddleware` | Endpoints compartidos admin + agencias |
| `agency.apikey` | `ValidateAgencyApiKey` | Endpoints de integración v1 por API key |

### Seguridad (globales — aplican a todas las requests)

| Clase | Descripción |
|-------|-------------|
| `SecurityHeaders` | Agrega headers HTTP de seguridad: `X-Content-Type-Options`, `X-Frame-Options: DENY`, `X-XSS-Protection`, `Strict-Transport-Security`, `Referrer-Policy`, `Permissions-Policy` |
| `RequestSizeLimit` | Rechaza con 413 requests que superen 1 MB (JSON) o 10 MB (multipart/form-data) |
| `BotDetection` | Detecta y bloquea user-agents de bots conocidos y patrones de escaneo automático |

### Auditoría

| Alias | Clase | Descripción |
|-------|-------|-------------|
| `audit.log` | `AccessAuditLog` | Registra en `storage/logs/security/access-audit-YYYY-MM.log` todos los accesos autenticados: método, ruta, IP, usuario, status. Aplicado a todas las rutas con JWT. |

---

## 5. Sistema de permisos por módulo

Los usuarios admin tienen módulos asignados en la tabla `user_modules`. Un admin puede operar solo sobre los módulos que tenga habilitados.

### Módulos disponibles

| ID | Constante | Descripción |
|----|-----------|-------------|
| 1 | `Module::USUARIOS` | Gestión de usuarios del sistema |
| 2 | `Module::RESERVAS_WEB` | Reservas del sitio web |
| 3 | `Module::CONFIGURACIONES` | Configuraciones generales |
| 4 | `Module::AGENCIAS` | Gestión de agencias y sus usuarios |
| 5 | `Module::EXCURSIONES` | Gestión de excursiones y características |
| 6 | `Module::RESERVAS_AGENCIAS` | Reservas del portal de agencias |

### Tipos de usuario admin

| ID | Constante | Descripción |
|----|-----------|-------------|
| 1 | `UserType::CLIENTE` | Cliente final (no puede loguear como admin) |
| 2 | `UserType::ADMIN` | Administrador con acceso por módulos |
| 3 | `UserType::VENDEDOR` | Vendedor (actualmente sin uso activo) |
| 4 | `UserType::EDITOR` | Editor (acceso a EXCURSIONES y CONFIGURACIONES) |

### Helpers de verificación en `Controller.php`

```php
// Solo ADMIN con módulo específico
$this->requireAdminModule(Module::AGENCIAS)

// Solo ADMIN con al menos uno de varios módulos
$this->requireAdminAnyModule([Module::RESERVAS_WEB, Module::RESERVAS_AGENCIAS])

// ADMIN o EDITOR con módulo específico
$this->requireModule(Module::EXCURSIONES)
```

Todos devuelven `null` si el permiso está OK, o una `JsonResponse` 403 si no.

### Tipos de usuario de agencia (`agency_user_type`)

| ID | Constante | Descripción |
|----|-----------|-------------|
| 1 | `AgencyUserType::ADMIN` | Admin de la agencia |
| 2 | `AgencyUserType::VENDEDOR` | Vendedor de la agencia |
| 3 | `AgencyUserType::COMERCIAL` | Comercial de la agencia |

---

## 6. Patrón Bridge — API externa HyA

### Configuración

La URL base de la API externa se determina en `HyAController::get_url()` y `AgencyUserController::get_url()`:

```php
// DEV
"https://apihya.hieloyaventura.com/apihya_dev"
// PROD
"https://apihya.hieloyaventura.com/apihya"
```

Se determina según `config('app.environment')`.

### Controladores que actúan como bridge

| Controlador | Usado por | Descripción |
|-------------|-----------|-------------|
| `HyAController` | Web pública | Proxy de endpoints HyA para el sitio web |
| `AgencyUserController` | Portal agencias | Proxy de endpoints HyA con validaciones IDOR para agencias |
| `AgencyExternalHyAController` | Integración v1 | Proxy para integración por API key |

### Endpoints HyA proxeados (resumen)

| Endpoint HyA externo | Ruta nuestra (web) | Ruta nuestra (agencias) |
|----------------------|--------------------|-------------------------|
| `GET /TiposPasajeros` | `GET /hya/passengers_types` | `GET /agency/hya/TiposPasajeros` |
| `GET /Naciones` | `GET /hya/nationalities` | `GET /agency/hya/Naciones` |
| `GET /Hoteles` | `GET /hya/hotels` | `GET /agency/hya/Hoteles` |
| `GET /Productos` | `GET /hya/excursions` | `GET /agency/hya/Productos` |
| `GET /Turnos` | `GET /hya/shifts` | `GET /agency/hya/Turnos` |
| `GET /ReservaxCodigo` | `GET /hya/ReservaxCodigo` | `GET /agency/hya/ReservaxCodigo` |
| `POST /IniciaReserva` | `POST /hya/IniciaReserva` | `POST /agency/hya/IniciaReserva` |
| `POST /CancelaReserva` | `POST /hya/CancelaReserva` | `POST /agency/hya/CancelaReserva` |
| `POST /ConfirmaReserva` | `POST /hya/ConfirmaReserva` | `POST /agency/hya/ConfirmaReserva` |
| `POST /ConfirmaPasajeros` | `POST /hya/ConfirmaPasajeros` | `POST /agency/hya/ConfirmaPasajeros` |
| `GET /ReservasAG` | — | `GET /agency/hya/ReservasAG` |
| `GET /Agencias` | — | `GET /agency/hya/Agencias` |
| `GET /ProductosAG` | — | `GET /agency/hya/ProductosAG` |
| `GET /TurnosAG` | — | `GET /agency/hya/TurnosAG` |
| `POST /resumen_servicios_diarios` | — | `POST /agency/hya/resumen_servicios_diarios` |
| `GET /SolicitudesAG` | `GET /hya/SolicitudesAG` | — |
| `GET /CtaCteAG` | `GET /hya/CtaCteAG` | — |
| `GET /Tarifas` | `GET /hya/rates` | — |
| `GET /Ofertas` | `GET /hya/oferts` | — |
| `GET /Promociones` | `GET /hya/Promociones` | — |
| `GET /RecuperaPrecioReserva` | `GET /hya/RecuperaPrecioReserva` | — |
| `POST /CreaSolicitudAG` | `POST /hya/CreaSolicitudAG` | — |
| `GET /ValidaCupon` | `GET /hya/ValidaCupon` | — |

### Protecciones IDOR en llamadas al bridge

En varios endpoints de agencias, se fuerza el parámetro `AG` (código de agencia) con el valor del usuario autenticado, ignorando cualquier valor que envíe el cliente. Esto impide que un usuario de una agencia acceda a datos de otra:

- `GET /agency/hya/Agencias` — fuerza código de agencia propio
- `GET /agency/hya/ReservasAG` — fuerza `AG = agency_code` del usuario autenticado
- `GET /agency/hya/ReservaxCodigo` — valida que la reserva devuelta pertenezca a la agencia del usuario
- `GET /hya/SolicitudesAG` — fuerza `AG = agency_code` del usuario autenticado
- `GET /hya/CtaCteAG` — fuerza `AG = agency_code` del usuario autenticado

---

## 7. Endpoints agrupados por consumidor

### 7.1 Web pública (sin autenticación o con jwt.verify para el usuario web)

**Auth**
- `POST /api/login` — Login usuario web (paso 1: valida credenciales, envía OTP)
- `POST /api/login/verify-otp` — Verificar OTP y recibir JWT (paso 2 del login web y admin)
- `POST /api/register` — Registro usuario web
- `POST /api/recover-password` — Recuperar contraseña usuario web (limpia `password_expired`)
- `POST /api/agency-recover-password-user` — Recuperar contraseña usuario agencia (limpia `password_expired`)

**Perfil de usuario web** *(jwt.verify)*
- `PUT /api/user_edit` — Editar perfil. Si se envía nuevo email → inicia OTP. No permite cambiar email y contraseña en la misma request.
- `POST /api/user_edit/confirm-email-change` — Confirmar cambio de email con OTP
- `PUT /api/new_password` — Cambiar contraseña (valida contraseña actual, luego envía OTP)
- `POST /api/new_password/confirm` — Confirmar cambio de contraseña con OTP
- `POST /api/logout` — Logout

**Excursiones y contenido**
- `GET /api/excurtions` — Listar excursiones
- `GET /api/excurtions/{id}` — Detalle excursión
- `GET /api/excurtions/{id}/{language}` — Detalle en idioma
- `GET /api/excurtions/by-external-id/{id}` — Detalle por ID externo
- `GET /api/faqs` — Listar FAQs
- `GET /api/web/general_configuration` — Configuración general
- `GET /api/excurtion/{id}/pictures/files` — Fotos de excursión

**Bridge HyA para web** *(sin auth salvo indicado)*
- `GET /api/hya/passengers_types` — Tipos de pasajero
- `GET /api/hya/nationalities` — Nacionalidades
- `GET /api/hya/hotels` — Hoteles
- `GET /api/hya/rates` — Tarifas
- `GET /api/hya/oferts` — Ofertas
- `GET /api/hya/excursions` — Productos/excursiones HyA
- `GET /api/hya/shifts` — Turnos disponibles
- `GET /api/hya/ReservaxCodigo` — Reserva por código
- `POST /api/hya/IniciaReserva` — Inicia reserva
- `POST /api/hya/CancelaReserva` — Cancela reserva
- `POST /api/hya/ConfirmaReserva` — Confirma reserva
- `POST /api/hya/ConfirmaPasajeros` — Confirma pasajeros
- `GET /api/hya/Promociones` — Promociones
- `GET /api/hya/RecuperaPrecioReserva` — Recupera precio reserva
- `POST /api/hya/CreaSolicitudAG` — Crea solicitud AG
- `GET /api/hya/SolicitudesAG` *(jwt.agency)* — Solicitudes AG
- `GET /api/hya/ValidaCupon` — Valida cupón
- `GET /api/hya/CtaCteAG` *(jwt.agency)* — Cuenta corriente AG

**Proceso de reserva web**
- `POST /api/users_reservations` — Crear reserva web
- `GET /api/users_reservations/{id}` *(jwt.verify)* — Ver reserva
- `GET /api/users_reservations/number/{number}` — Ver por número
- `GET /api/users_reservations/number/encrypted/{number}` — Ver por número encriptado
- `PUT /api/users_reservations/{id}` — Actualizar reserva
- `POST /api/paxs` — Registrar pasajero
- `POST /api/medical/record` — Registro médico
- `POST /api/passenger/diseases/{hash}/{mail}` — Enfermedades pasajero
- `GET /api/diseases/{language_id}` — Listado de enfermedades
- `POST /api/payment/mercadopago/preference` — Crear preferencia MercadoPago
- `POST /api/mercadopago/notification` — Webhook MercadoPago (recibe MP)

**Formularios web**
- `POST /api/consults` — Formulario de consulta
- `POST /api/contact-form` — Formulario de contacto
- `POST /api/online-return` — Formulario de devolución
- `POST /api/process-cv` — Formulario CV/empleo
- `POST /api/group-excurtion` — Solicitud excursión grupal

---

### 7.2 Portal de agencias *(jwt.agency o jwt.admin_or_agency)*

**Auth**
- `POST /api/login/agency/user` — Login usuario agencia (paso 1: valida credenciales, envía OTP)
- `POST /api/agency/verify-otp` — Verificar OTP y recibir JWT (paso 2 del login)
- `POST /api/logout` *(jwt.admin_or_agency)* — Logout (válido para admin y agencias)

**Perfil propio** *(jwt.agency)*
- `PUT /api/agency/users/profile` — Editar perfil. Si se envía nuevo email → inicia OTP. Si se envía nueva contraseña → inicia OTP. No se pueden cambiar ambos en la misma request.
- `POST /api/agency/users/profile/confirm-email-change` — Confirmar cambio de email con OTP
- `POST /api/agency/users/profile/confirm-password-change` — Confirmar cambio de contraseña con OTP

**Bridge HyA para agencias** *(jwt.agency)*
- `GET /api/agency/hya/Agencias` *(jwt.admin_or_agency)* — Lista agencias
- `GET /api/agency/hya/Productos` — Productos
- `GET /api/agency/hya/TiposPasajeros` — Tipos de pasajero
- `GET /api/agency/hya/Naciones` — Naciones
- `GET /api/agency/hya/Hoteles` — Hoteles
- `GET /api/agency/hya/Turnos` — Turnos
- `GET /api/agency/hya/ReservasAG` — Reservas de la agencia
- `GET /api/agency/hya/ReservaxCodigo` — Reserva por código
- `GET /api/agency/hya/ProductosAG` — Productos AG
- `GET /api/agency/hya/TurnosAG` — Turnos AG
- `POST /api/agency/hya/IniciaReserva` — Inicia reserva
- `POST /api/agency/hya/CancelaReserva` — Cancela reserva
- `POST /api/agency/hya/ConfirmaReserva` — Confirma reserva
- `POST /api/agency/hya/ConfirmaPasajeros` — Confirma pasajeros
- `POST /api/agency/hya/resumen_servicios_diarios` — Resumen servicios
- `POST /api/agency/hya/resumen_servicios_diarios/excel` — Resumen excel
- `POST /api/agency/users_reservations/request/change` — Solicitud cambio reserva
- `GET /api/agency/reservation/{reservation}/requests` — Ver solicitudes de cambio

**Gestión propia de agencia** *(jwt.admin_or_agency)*
- `GET /api/agency/users` — Usuarios de la agencia
- `GET /api/agency/users/types` — Tipos de usuario
- `GET /api/agency/users/filter/code` — Filtrar por código
- `GET /api/agency/modules` — Módulos disponibles
- `GET /api/agency/reservations/path_file` — Ruta PDF reserva

**Reservas agencias** *(jwt.verify o jwt.agency)*
- `POST /api/agency/users_reservations` *(jwt.verify)* — Crear reserva agencia
- `GET /api/users_reservations/get/with_filters` *(jwt.agency)* — Reservas con filtros
- `POST /api/agency_paxs` *(jwt.verify)* — Registrar pasajero agencia

**Onboarding agencia** *(jwt.verify)*
- `POST /api/agency/users/seller_load` — Configurar carga de vendedores
- `GET /api/agency/users/seller_load/{agency_code}` — Ver carga
- `POST /api/agency/users/terms_and_conditions` — Aceptar T&C

---

### 7.3 Panel Admin *(jwt.verify — usuario tipo ADMIN o EDITOR con módulo habilitado)*

**Auth admin**
- `POST /api/login/admin` — Login admin (paso 1: valida credenciales, envía OTP)
- `POST /api/login/verify-otp` — Verificar OTP y recibir JWT (paso 2, compartido con login web)

**Usuarios** *(Módulo: USUARIOS)*
- `GET /api/users` — Listar usuarios
- `GET /api/users/get_all/with_out_filters` — Todos sin filtros
- `GET /api/users/types` — Tipos de usuario
- `GET /api/modules/user` — Módulos del usuario autenticado
- `POST /api/users` — Crear usuario
- `POST /api/users/{id}` — Actualizar usuario
- `DELETE /api/users/{id}` — Eliminar usuario
- `PUT /api/users/{id}/admin` — Actualizar como admin

**Excursiones** *(Módulo: EXCURSIONES — ADMIN o EDITOR)*
- `POST /api/excurtions` — Crear excursión
- `POST /api/excurtions/{id}` — Actualizar excursión
- `POST /api/characteristics` — Crear característica
- `POST /api/characteristics/array` — Crear características múltiples
- `POST /api/characteristics/{id}/excurtion` — Agregar característica a excursión
- `PUT /api/characteristics/{id}` — Actualizar característica
- `PUT /api/characteristics/{id}/array` — Actualizar múltiples
- `GET /api/characteristics/{id}` — Ver característica
- `GET /api/characteristics_types` — Tipos de característica
- `POST /api/characteristics_types` — Crear tipo
- `POST /api/excurtion-characteristics/{id}` — Agregar característica
- `POST /api/excurtion/characteristics/{id}` — Agregar características
- `POST /api/excurtion/pictures/manage/files` — Gestionar fotos

**Reservas web** *(Módulo: RESERVAS_WEB o RESERVAS_AGENCIAS)*
- `GET /api/reservations` — Listar
- `GET /api/reservations/{id}` — Ver
- `PUT /api/reservations/{id}` — Actualizar
- `POST /api/reservations/resend/email_welcome` — Reenviar bienvenida
- `POST /api/reservations/resend/email_voucher` — Reenviar voucher
- `POST /api/reservations/update/internal_closed/{id}` — Cerrar internamente
- `POST /api/reservations/new/observation` — Agregar observación

**Reservas agencias** *(Módulo: RESERVAS_AGENCIAS)*
- `POST /api/agency/users_reservations` — Crear reserva de agencia
- `GET /api/agency/reservations/path_file` — Ruta PDF

**Configuraciones** *(Módulo: CONFIGURACIONES — ADMIN o según endpoint)*
- `GET /api/consults` — Ver consultas
- `POST /api/consults/change` — Cambiar email de consultas
- `GET /api/web/general_configuration` — Configuración general
- `POST /api/web/general_configuration` — Guardar configuración
- `POST /api/faqs` — Crear FAQ
- `GET /api/lenguages` — Idiomas
- `DELETE /api/pdfs/delete-by-range` — Limpiar PDFs
- `DELETE /api/pdfs/agencies/delete-by-range` — Limpiar PDFs agencias
- `GET /api/clear-cache` — Limpiar caché Laravel

**Agencias** *(Módulo: AGENCIAS)*
- `GET /api/agency/users` *(jwt.admin_or_agency)* — Ver usuarios agencia
- `GET /api/agency/users/types` — Tipos usuario agencia
- `GET /api/agency/users/filter/code` — Filtrar por código
- `GET /api/agency/modules` — Módulos agencia
- `GET /api/agency/users/seller/{agency_code}` — Vendedores por agencia
- `GET /api/agency/users/no_admin/{agency_code}` — Usuarios no admin
- `POST /api/agency/users` — Crear usuario agencia
- `POST /api/agency/users/update/{id}` — Actualizar usuario agencia
- `POST /api/agency/users/active_inactive` — Activar/desactivar usuario
- `POST /api/agency/users/emergency-password-reset` — Reset masivo de contraseñas de agencias. Requiere `{ confirm: true, admin_password }`. Setea `password_expired = true`. Devuelve lista de nuevas contraseñas (visible una sola vez).
- `GET /api/agencies/{agency_code}` — Ver agencia
- `POST /api/agencies` — Crear/actualizar agencia
- `PUT /api/agency/settings` — Actualizar configuración de agencia
- `POST /api/admin/send-integration-api-welcome` — Enviar email bienvenida integración

**Usuarios web/admin** *(Módulo: USUARIOS o solo ADMIN)*
- `POST /api/users/emergency-password-reset` — Reset masivo de contraseñas de tabla `users`. Solo ADMIN. Requiere `{ confirm: true, admin_password }`. Setea `password_expired = true`. Devuelve lista de nuevas contraseñas (visible una sola vez).

---

### 7.4 Integración v1 por API Key *(agency.apikey)*

Endpoints pensados para que agencias externas integren directamente sus sistemas contra la API, sin pasar por el portal web de agencias.

- `GET /api/agencies/v1/availability` — Disponibilidad
- `GET /api/agencies/v1/hotels` — Hoteles
- `GET /api/agencies/v1/nationalities` — Nacionalidades
- `GET /api/agencies/v1/reservations` — Reservas de la agencia
- `GET /api/agencies/v1/reservation/{number}` — Reserva por número
- `POST /api/agencies/v1/reservation` — Crear reserva
- `PUT /api/agencies/v1/reservation/{number}` — Editar reserva
- `DELETE /api/agencies/v1/reservation/{number}` — Cancelar reserva
- `PUT /api/agencies/v1/settings` — Actualizar configuración

---

## 8. Modelos de datos clave

### Usuarios del sistema (`users`)
Campos principales: `name`, `email`, `password`, `password_expired`, `user_type_id`, `active`
Relaciones: `user_type`, `modules` (vía `user_modules`)

**Campos de seguridad:**
- `otp_code` — código OTP de 6 dígitos (login 2FA, cambio de email, cambio de contraseña)
- `otp_expires_at` — expiración del OTP (10 minutos)
- `pending_email` — email nuevo pendiente de confirmar via OTP. Si está seteado junto con `otp_code`, el OTP es para cambio de email. Si está null, el OTP es para cambio de contraseña.
- `password_expired` — flag booleano. `true` indica contraseña reseteada de forma forzada. El front detecta este campo en la respuesta del login (400) y muestra el aviso para que el usuario use recuperación de contraseña.

### Usuarios de agencia (`agency_users`)
Campos principales: `user` (username), `name`, `last_name`, `email`, `password`, `password_expired`, `agency_code`, `agency_user_type_id`, `can_view_all_sales`, `active`, `terms_and_conditions`, `otp_code`, `otp_expires_at`, `pending_email`
Relaciones: `user_type` (AgencyUserType), `modules` (AgencyUserModule)

**Campos de seguridad:**
- `otp_code` — código OTP de 6 dígitos (login 2FA, cambio de email, cambio de contraseña)
- `otp_expires_at` — expiración del OTP (10 minutos)
- `pending_email` — email nuevo pendiente de confirmar via OTP. Si está seteado junto con `otp_code`, el OTP es para cambio de email. Si está null, el OTP es para cambio de contraseña.
- `password_expired` — flag booleano. `true` indica que la contraseña fue reseteada de forma forzada por un admin. El front debe mostrar aviso al usuario para que use el flujo de recuperación de contraseña.

### Agencias (`agencies`)
Campos principales: `agency_code`, `api_key`, `configurations`, `email_integration_notification`

### Reservas internas (`user_reservations`)
Son las reservas registradas en nuestra DB, que luego se sincronizan con el sistema HyA.
Relaciones: `user`, `userAgency`, `status`, `excurtion`, `billing_data`, `contact_data`, `paxes`

### Reservas HyA
Las reservas en el sistema de escritorio HyA se manejan directamente a través del bridge. No se duplican completamente en nuestra DB — solo se guarda metadata necesaria.

---

## 9. Seguridad implementada

### Rate limiting en endpoints de login

Configurado en `RouteServiceProvider::configureRateLimiting()`. Al superar el límite se devuelve **429** y se envía alerta por email a `SECURITY_ALERT_EMAILS`.

| Endpoint | Límite | Clave |
|----------|--------|-------|
| `POST /api/login/admin` | 5 intentos / minuto | por email |
| `POST /api/login` (web) | 10 intentos / minuto | por email |
| `POST /api/login/agency/user` | 10 intentos / minuto | por email |
| `POST /api/login/verify-otp` | 10 intentos / minuto | por email |
| `POST /api/login/agency/verify-otp` | 10 intentos / minuto | por email |

> **Importante**: la clave del rate limit es el **email**, no la IP. Esto impide la evasión del límite mediante rotación de IPs (patrón detectado en producción).

Variable de entorno requerida:
```
SECURITY_ALERT_EMAILS=sistemas@ejemplo.com,otro@ejemplo.com
```

### 2FA en logins

Todos los logins requieren verificación en dos pasos. Ver flujo completo en [Sección 3](#3-autenticación-y-guards-jwt). El OTP se limpia de la DB en todos los casos: éxito, expiración o verificación del OTP (el campo queda null después de cada uso).

| Guard | Paso 1 | Paso 2 |
|-------|--------|--------|
| Web/Admin | `POST /api/login` o `/api/login/admin` | `POST /api/login/verify-otp` |
| Agencias | `POST /api/login/agency/user` | `POST /api/login/agency/verify-otp` |

### Protección de cambios sensibles en el perfil

Tanto el cambio de email como el cambio de contraseña requieren verificación OTP antes de aplicarse. Aplica a **todos los tipos de usuario** (web, admin y agencias).

**Cambio de email — web/admin:**
```
PUT /api/user_edit  { email: "nuevo@mail.com" }
→ OTP al correo actual
→ POST /api/user_edit/confirm-email-change  { otp }
```

**Cambio de contraseña — web/admin:**
```
PUT /api/new_password  { current_password }
→ OTP al correo
→ POST /api/new_password/confirm  { otp, password, password_confirmation }
```

**Cambio de email — agencias:**
```
PUT /api/agency/users/profile  { email: "nuevo@mail.com" }
→ OTP al correo actual
→ POST /api/agency/users/profile/confirm-email-change  { otp }
```

**Cambio de contraseña — agencias:**
```
PUT /api/agency/users/profile  { password, password_confirmation }
→ OTP al correo
→ POST /api/agency/users/profile/confirm-password-change  { otp, password, password_confirmation }
```

No se permite enviar email y contraseña nuevos en la misma request (devuelve 422).

### Emergency password reset

Endpoints para resetear masivamente las contraseñas ante un incidente de seguridad. Requieren autenticación admin + módulo AGENCIAS (para agencias) o solo ADMIN (para users) + confirmación con la contraseña del admin ejecutante.

| Endpoint | Scope |
|----------|-------|
| `POST /api/agency/users/emergency-password-reset` | Todos los usuarios de `agency_users` |
| `POST /api/users/emergency-password-reset` | Todos los usuarios de `users` |

Body requerido:
```json
{ "confirm": true, "admin_password": "contraseña-del-admin-autenticado" }
```

Respuesta: lista con `id`, `name`, `email`, `password` nueva de cada usuario. **Mostrar una sola vez — no se vuelve a recuperar.**

Al ejecutarse, setea `password_expired = true` en cada usuario.

### Flag `password_expired`

Presente en `users` y `agency_users`. Se setea en `true` al ejecutar un emergency reset. El front debe leer este campo en la respuesta del login y mostrar el aviso correspondiente.

Se limpia a `false` cuando el usuario cambia su contraseña a través de:
- Flujo de recuperación de contraseña (`recover_password_user` / `agency_recover_password_user`)
- Cambio de contraseña desde perfil + OTP (`confirm_password_change`) — web, admin y agencias

### Invalidación masiva de tokens JWT

Para cerrar todas las sesiones activas simultáneamente (todos los guards):
1. Generar nuevo secret (en consola del navegador): `Array.from(crypto.getRandomValues(new Uint8Array(32)), b => b.toString(16).padStart(2,'0')).join('')`
2. Reemplazar `JWT_SECRET=...` en el `.env` de producción via cPanel → File Manager
3. Todos los tokens emitidos con el secret anterior quedan inválidos instantáneamente

### Variables de entorno de seguridad

```env
JWT_TTL=60                                    # Expiración de tokens en minutos
JWT_SECRET=<cadena aleatoria de 64+ chars>    # Secret de firma JWT
SECURITY_ALERT_EMAILS=a@a.com,b@b.com         # Destinatarios de alertas de seguridad
```

---

## 10. Estructura propuesta de optimización

### Problema actual

El archivo `routes/api.php` mezcla rutas de todos los consumidores sin una organización clara. Los controladores como `AgencyUserController` hacen demasiadas cosas (gestión de usuarios + bridge HyA + lógica de negocio). Hay duplicación de lógica bridge entre `HyAController` y `AgencyUserController`.

### Propuesta de reorganización por prefijos y archivos de rutas

```
routes/
├── api.php              # Solo bootstrap: incluye los sub-archivos
├── api/
│   ├── web.php          # Rutas del sitio web público
│   ├── admin.php        # Rutas del panel admin
│   ├── agencies.php     # Rutas del portal de agencias
│   └── integration.php  # Rutas v1 por API key
```

### Propuesta de prefijos de URL limpios

| Consumidor | Prefijo actual (mezclado) | Prefijo propuesto |
|------------|--------------------------|-------------------|
| Web pública | `/api/hya/*`, `/api/users_reservations`, etc. | `/api/web/*` |
| Admin | `/api/reservations`, `/api/users`, etc. | `/api/admin/*` |
| Portal agencias | `/api/agency/hya/*`, `/api/agency/users/*` | `/api/agencies/*` |
| Integración v1 | `/api/agencies/v1/*` | `/api/v1/*` |

### Propuesta de separación de controladores

| Controlador actual | Responsabilidad actual (mezclada) | Split propuesto |
|--------------------|----------------------------------|-----------------|
| `AgencyUserController` | Gestión usuarios + bridge HyA + solicitudes cambio | `AgencyUserController` + `AgencyHyABridgeController` + `AgencyReservationController` |
| `HyAController` | Bridge HyA web | `WebHyABridgeController` |
| `UserReservationController` | Reservas web + reservas agencias | `WebReservationController` + `AgencyReservationController` |

### Propuesta de un servicio bridge compartido

En lugar de duplicar la lógica de llamadas HTTP a HyA en `HyAController` y `AgencyUserController`, crear un `HyAService` inyectable:

```php
// app/Services/HyAService.php
class HyAService {
    private string $baseUrl;

    public function getProducts(string $date): array { ... }
    public function getShifts(string $from, string $to, string $productId): array { ... }
    public function startReservation(array $data): array { ... }
    // ...
}
```

Esto centralizaría el manejo de errores de la API externa y evitaría código duplicado.

---

## 11. Roadmap de mejoras

### Alta prioridad (seguridad)

- [ ] **Revisar endpoints sin autenticación que exponen datos**: algunos endpoints del bridge HyA son públicos y podrían exponer información sensible según los datos que devuelva la API externa.
- [x] ~~**Agregar rate limiting** en endpoints de login y recuperación de contraseña para prevenir brute force.~~ *(implementado)*
- [x] ~~**Headers de seguridad HTTP** en todas las respuestas.~~ *(implementado)*
- [x] ~~**Límite de tamaño de requests** (413).~~ *(implementado)*
- [x] ~~**2FA para login de agencias**.~~ *(implementado)*
- [x] ~~**2FA para login de admin y web**.~~ *(implementado)*
- [x] ~~**OTP para cambio de email y contraseña** desde perfil de agencias.~~ *(implementado)*
- [x] ~~**OTP para cambio de email y contraseña** desde perfil de web/admin.~~ *(implementado)*
- [x] ~~**Audit log de accesos autenticados**.~~ *(implementado)*
- [x] ~~**Flag `password_expired`** para forzar cambio de contraseña.~~ *(implementado)*
- [x] ~~**Emergency password reset** para agencias y usuarios.~~ *(implementado)*
- [ ] **Endpoints pendientes de revisión de seguridad**:
  - `DELETE /pdfs/delete-by-range` — operación destructiva, verificar validación de rangos
  - `GET /clear-cache` — solo debería ser accesible por ADMIN
  - `POST /agency_paxs` — verificar que la agencia del pax corresponda al usuario autenticado
  - `POST /agency/users/seller_load` — verificar validación
- [x] ~~`POST /agency/users/terms_and_conditions` — middleware corregido a `jwt.agency`~~ *(corregido)*
- [x] ~~**2FA para login de admin** (panel de administración)~~ *(implementado)*
- [ ] **Lista blanca de IPs para acceso al panel admin**
- [ ] **Actualizar dependencias con vulnerabilidades conocidas**: `laravel/framework` (CVE-2024-52301), `symfony/http-foundation`, `symfony/process` (CVE-2024-51736, crítico en Windows), `league/commonmark`

### Media prioridad (calidad de código)

- [ ] **Centralizar lógica bridge en `HyAService`**: eliminar duplicación entre `HyAController` y `AgencyUserController`.
- [ ] **Separar `AgencyUserController`**: actualmente hace gestión de usuarios + bridge + reservas + solicitudes. Dividir en controladores especializados.
- [ ] **Validaciones de request con Form Requests**: reemplazar validaciones inline con clases `FormRequest` para mayor claridad y reusabilidad.
- [ ] **Manejo consistente de errores del bridge**: las llamadas `$response->throw()` en caso de error de HyA pueden exponer stack traces. Centralizar manejo en el servicio.

### Baja prioridad (deuda técnica)

- [ ] **Eliminar tablas sin uso**: `tickets`, `ticket_messages`, `ticket_status` (código comentado, no se usa).
- [ ] **Reorganizar `routes/api.php`**: separar en archivos por consumidor (ver sección 9).
- [ ] **Unificar nomenclatura**: hay inconsistencias entre español/inglés y entre `excurtion`/`excursion` (typo histórico en el codebase).
- [ ] **Documentar la API con Scalar/Swagger**: ya existe un setup de Scalar (`/docs`), expandirlo con todos los endpoints actualizados.
- [ ] **Diseño de correos transaccionales**: aplicar template visual consistente (con logo, colores de marca, footer) a todos los correos que envía la API (confirmación de reserva, voucher, recuperación de contraseña, notificaciones, etc.), similar al diseño ya aplicado en los correos de bienvenida de integración por API para agencias.
- [ ] **Documentación Postman completa**: una vez reestructurada la API (prefijos limpios por consumidor, controladores separados), armar una colección de Postman prolija con todos los endpoints organizados por carpetas (Web, Admin, Agencias, Integración v1), con ejemplos de request/response, variables de entorno para dev y prod, y descripción de cada endpoint. Esto reemplazaría/complementaría la documentación Scalar existente y facilitaría el onboarding de nuevos desarrolladores y agencias que integren por API.

---

## Bugs conocidos / Notas de implementación

### Colisión del singleton JWT en endpoints `jwt.admin_or_agency`

**Problema**: Cuando un admin hace una petición a un endpoint con `jwt.admin_or_agency`, el middleware autentica correctamente al admin via `JWTAuth::parseToken()->authenticate()`. Sin embargo, si el controlador luego llama `Auth::guard('agency')->check()` para distinguir si es admin o agencia, el guard `agency` usa el mismo singleton de JWTAuth internamente. Esto corrompe el estado del singleton, haciendo que `Auth::user()` y `JWTAuth::user()` devuelvan `null` en llamadas subsecuentes.

**Solución implementada**:
1. `AdminOrAgencyMiddleware` guarda el usuario admin en `$request->attributes->set('authenticated_admin', $user)` antes de continuar.
2. La función global `getAuthenticatedAdmin()` en `Controller.php` lee primero de `request()->attributes`, evitando tocar el singleton JWT.

### Typo histórico `excurtion` vs `excursion`

El modelo, tabla y muchos endpoints usan `excurtion` (sin la 's'). No corregir sin un plan de migración completo ya que afecta rutas, columnas DB, y todos los frontends.

---

## Tests automatizados

### Cómo ejecutar los tests

Los tests usan **SQLite en memoria** (configurado en `phpunit.xml`) para no depender de la base de datos MySQL de desarrollo.

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar solo una suite
php artisan test --testsuite=Feature

# Ejecutar un archivo específico
php artisan test tests/Feature/Agency/AuthAgencyTwoFactorTest.php
```

> **Nota**: Los tests solo se pueden ejecutar localmente (donde hay acceso al servidor con PHP). No hay acceso SSH al servidor de producción/staging para ejecutarlos allí.

### Estructura de tests

```
tests/
└── Feature/
    ├── Concerns/
    │   └── CreatesUsers.php        ← helpers: createAdminWithModules(), createAgencyUser(), etc.
    ├── Admin/
    │   ├── AuthAdminTest.php        ← login admin: casos exitosos y de error
    │   └── ModulePermissionsTest.php ← control de acceso por módulo (AGENCIAS, USUARIOS, etc.)
    ├── Agency/
    │   ├── AuthAgencyTwoFactorTest.php ← flujo 2FA: paso 1 (OTP) y paso 2 (JWT)
    │   ├── AgencyProfileTest.php       ← edición de perfil + protección de cambio de email
    │   └── AgencyIsolationTest.php     ← aislamiento cross-agency (agencia A no ve datos de B)
    └── Web/
        └── AuthWebTest.php             ← login web + verificación de que el token web no da acceso a admin/agencias
```

### Cobertura actual (64 tests)

| Área | Test | Casos cubiertos |
|------|------|-----------------|
| Admin | `AuthAdminTest` | login OK, password incorrecto, email inexistente, usuario CLIENTE, campos faltantes, email inválido |
| Admin | `ModulePermissionsTest` | con/sin módulo AGENCIAS, con/sin módulo USUARIOS, sin token, usuario CLIENTE con token |
| Agencias | `AuthAgencyTwoFactorTest` | OTP enviado, usuario inactivo, password incorrecto, OTP correcto, OTP incorrecto, OTP expirado, sin OTP pendiente, no reutilizable, usuario eliminado (soft delete), OTP de usuario B no válido para usuario A |
| Agencias | `AgencyProfileTest` | actualizar nombre, sin token, campos faltantes, cambio de email + OTP, email en uso, confirmar email OK, OTP incorrecto, OTP expirado, sin cambio pendiente |
| Agencias | `AgencyIsolationTest` | ver solo usuarios propios, admin ve todos, vendedores de otra agencia, sin token, token agencia no pasa jwt.verify |
| Web | `AuthWebTest` | login OK, admin en login web, password incorrecto, email inexistente, campos faltantes, token web no accede a admin, token web no accede a agencias |
| Seguridad | `SecurityHeadersTest` | headers presentes en endpoints públicos, login, respuestas 401 |
| Seguridad | `RequestSizeLimitTest` | request pequeño pasa, JSON >1MB devuelve 413, multipart >10MB devuelve 413 |
| Seguridad | `RateLimitingTest` | 429 tras 5 intentos admin, 429 tras 10 intentos web, 429 tras 10 intentos agencia |

### Importante: reestructuración futura de la API

Cuando se realice la reestructuración de rutas y controladores planificada en la [Sección 9](#9-estructura-propuesta-de-optimización), **los tests de feature deberán rehacerse en su mayoría** porque:

- Los paths de los endpoints cambiarán (e.g., `/api/agencies` podría pasar a `/api/admin/agencies`).
- Los controladores serán distintos (actualmente `AgencyUserController` concentra demasiada responsabilidad).
- La separación de responsabilidades puede cambiar qué middleware aplica a qué ruta.

En ese momento se recomienda aprovechar para también agregar tests de integración más completos, incluyendo los flujos del bridge con HyA (usando mocks de `Http::fake()`).

### Consideraciones de infraestructura

- **Sin acceso SSH a producción/staging**: no es posible ejecutar `php artisan migrate` ni `php artisan test` en esos servidores. Los deploys de migraciones se hacen mediante queries SQL ejecutados manualmente en phpMyAdmin.
- **Clear-cache**: el endpoint de clear-cache (`/api/clear-cache`) es suficiente para que Laravel tome los cambios de código nuevos tras un `git pull`. No ejecuta migraciones.
- **Tests en CI**: si en el futuro se configura un pipeline CI (GitHub Actions, etc.), los tests pueden ejecutarse automáticamente en cada push porque usan SQLite en memoria y no requieren infraestructura externa.
