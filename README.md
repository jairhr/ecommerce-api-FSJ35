# 🛒 E-commerce API — Laravel 12

API RESTful para un sistema de comercio electrónico desarrollada con **Laravel 12**, **PHP 8.2+**, **MySQL**, **Laravel Sanctum**, **Stripe** y **Swagger/OpenAPI**.

El proyecto permite gestionar usuarios, productos, órdenes de compra y pagos mediante Stripe, proporcionando una API segura y completamente documentada.

---

## 📋 Características

* Registro de usuarios.
* Autenticación mediante Laravel Sanctum.
* Inicio y cierre de sesión.
* Catálogo público de productos.
* CRUD completo de productos.
* Control de stock.
* Creación de órdenes de compra.
* Historial de órdenes por usuario.
* Consulta individual de órdenes.
* Integración con Stripe PaymentIntents.
* Webhooks de Stripe.
* Registro de pagos.
* Manejo de pagos exitosos y fallidos.
* Validaciones mediante Laravel Form Requests.
* Respuestas JSON consistentes.
* Manejo de errores HTTP.
* Documentación completa mediante Swagger/OpenAPI.
* Seeders para productos de prueba.
* Protección de endpoints mediante Bearer Token.

---

## 🛠️ Tecnologías utilizadas

| Tecnología      | Versión / Uso                 |
| --------------- | ----------------------------- |
| PHP             | 8.2+                          |
| Laravel         | 12                            |
| MySQL           | Base de datos                 |
| Laravel Sanctum | Autenticación mediante tokens |
| Stripe PHP      | Procesamiento de pagos        |
| L5-Swagger      | Documentación OpenAPI         |
| Composer        | Gestión de dependencias       |

---

# 🚀 Instalación

## 1. Clonar el repositorio

```bash
git clone https://github.com/TU-USUARIO/ecommerce-api-FSJ35.git
```

Ingresar al proyecto:

```bash
cd ecommerce-api-FSJ35
```

---

## 2. Instalar dependencias

```bash
composer install
```

---

## 3. Crear el archivo `.env`

Copiar el archivo de ejemplo:

```bash
cp .env.example .env
```

En Windows también puede utilizarse:

```bash
copy .env.example .env
```

---

## 4. Generar la clave de Laravel

```bash
php artisan key:generate
```

---

# 🗄️ Configuración de base de datos

Crear una base de datos MySQL, por ejemplo:

```sql
CREATE DATABASE ecommerce;
```

Configurar las variables correspondientes en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

> Ajustar `DB_USERNAME` y `DB_PASSWORD` según la configuración local de MySQL.

---

## 5. Ejecutar migraciones

```bash
php artisan migrate
```

Las migraciones crean las tablas necesarias para:

* Usuarios
* Productos
* Órdenes
* Detalles de órdenes
* Pagos
* Tokens de autenticación

---

## 6. Ejecutar los seeders

Para cargar productos de ejemplo:

```bash
php artisan db:seed
```

También puede utilizarse:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh --seed` elimina las tablas existentes y vuelve a crearlas. Utilizar únicamente en ambientes de desarrollo.

---

# 🔐 Autenticación

La API utiliza **Laravel Sanctum** para autenticación mediante tokens Bearer.

Después de registrar o autenticar un usuario, la API devuelve un token:

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {},
        "token": "11|xxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

Para consumir endpoints protegidos se debe enviar:

```http
Authorization: Bearer TOKEN
```

Ejemplo:

```http
Authorization: Bearer 11|xxxxxxxxxxxxxxxxxxxxxxxx
```

---

# 📚 Swagger / OpenAPI

La documentación interactiva de la API está disponible en:

```text
http://127.0.0.1:8000/api/documentation
```

Para regenerar la documentación:

```bash
php artisan l5-swagger:generate
```

Luego iniciar el servidor:

```bash
php artisan serve
```

Y acceder a:

```text
http://127.0.0.1:8000/api/documentation
```

Swagger permite probar directamente los endpoints de la API.

Para endpoints protegidos:

1. Ejecutar `register` o `login`.
2. Copiar el token generado.
3. Presionar **Authorize** en Swagger.
4. Introducir:

```text
Bearer TOKEN
```

5. Ejecutar los endpoints protegidos.

---

# 🔗 Endpoints

## 👤 Autenticación

### Registrar usuario

```http
POST /api/register
```

Ejemplo:

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

Respuesta:

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {},
        "token": "TOKEN",
        "token_type": "Bearer"
    }
}
```

---

### Iniciar sesión

```http
POST /api/login
```

Body:

```json
{
    "email": "john@example.com",
    "password": "Password123"
}
```

---

### Cerrar sesión

```http
POST /api/logout
```

**Requiere autenticación.**

Header:

```http
Authorization: Bearer TOKEN
```

---

# 📦 Productos

## Listar productos

```http
GET /api/products
```

Endpoint público.

Devuelve únicamente productos activos.

---

## Consultar producto

```http
GET /api/products/{id}
```

Ejemplo:

```text
GET /api/products/1
```

Endpoint público.

---

## Crear producto

```http
POST /api/products
```

**Requiere autenticación.**

Ejemplo:

```json
{
    "name": "Laptop Lenovo",
    "description": "Laptop para uso profesional",
    "price": 899.99,
    "stock": 10,
    "is_active": true
}
```

---

## Actualizar producto

```http
PUT /api/products/{id}
```

**Requiere autenticación.**

Ejemplo:

```json
{
    "name": "Laptop Lenovo ThinkPad",
    "price": 949.99,
    "stock": 15
}
```

Los campos pueden actualizarse parcialmente.

---

## Eliminar producto

```http
DELETE /api/products/{id}
```

**Requiere autenticación.**

---

# 🛒 Órdenes

## Crear una orden

```http
POST /api/orders
```

**Requiere autenticación.**

Body:

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

Durante la creación de la orden el sistema:

1. Obtiene el usuario autenticado.
2. Crea la orden.
3. Valida que los productos estén activos.
4. Verifica la existencia de stock.
5. Bloquea los registros de productos durante la operación.
6. Calcula el subtotal de cada producto.
7. Descuenta el stock.
8. Calcula el total de la orden.
9. Registra los detalles de la orden.
10. Ejecuta toda la operación dentro de una transacción de base de datos.

Si ocurre un error durante la operación, la transacción se revierte.

---

## Historial de órdenes

```http
GET /api/orders
```

**Requiere autenticación.**

Devuelve únicamente las órdenes pertenecientes al usuario autenticado.

---

## Consultar una orden

```http
GET /api/orders/{id}
```

**Requiere autenticación.**

El usuario únicamente puede consultar sus propias órdenes.

---

# 💳 Pagos con Stripe

El proyecto utiliza **Stripe PaymentIntents** para gestionar los pagos.

## Crear PaymentIntent

```http
POST /api/orders/{order}/payment
```

**Requiere autenticación.**

El sistema verifica:

* Que la orden pertenezca al usuario autenticado.
* Que la orden se encuentre en estado `pending`.
* El monto de la orden.
* La creación del PaymentIntent en Stripe.
* El registro del pago en la base de datos.

La respuesta contiene información necesaria para que un cliente frontend pueda completar el pago:

```json
{
    "success": true,
    "message": "Payment intent created successfully.",
    "data": {
        "payment_id": 1,
        "payment_intent_id": "pi_xxxxxxxxx",
        "client_secret": "pi_xxxxxxxxx_secret_xxxxxxxxx",
        "amount": 100,
        "currency": "usd",
        "status": "pending"
    }
}
```

---

# 🔔 Stripe Webhook

Stripe notifica al backend mediante:

```http
POST /api/stripe/webhook
```

El webhook valida la firma enviada por Stripe antes de procesar el evento.

Actualmente se manejan los siguientes eventos:

### Pago exitoso

```text
payment_intent.succeeded
```

Actualiza:

```text
payments.status = succeeded
payments.paid_at = fecha actual
orders.status = paid
```

### Pago fallido

```text
payment_intent.payment_failed
```

Actualiza:

```text
payments.status = failed
orders.status = failed
```

Las actualizaciones se realizan dentro de transacciones de base de datos.

---

# 🔑 Configuración de Stripe

Agregar las credenciales en `.env`:

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxx
```

Las credenciales deben corresponder al ambiente de Stripe que se esté utilizando.

> Nunca subir las claves privadas de Stripe al repositorio.

---

# 🌱 Variables de entorno

El archivo `.env.example` debe contener las variables necesarias para configurar el proyecto.

Ejemplo:

```env
APP_NAME="E-commerce API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

---

# 🧪 Flujo de prueba recomendado

Para probar completamente la API:

### 1. Registrar usuario

```http
POST /api/register
```

Obtener el token.

### 2. Autenticarse en Swagger

Presionar:

```text
Authorize
```

Y utilizar:

```text
Bearer TOKEN
```

### 3. Crear productos

```http
POST /api/products
```

### 4. Consultar productos

```http
GET /api/products
```

### 5. Crear una orden

```http
POST /api/orders
```

Ejemplo:

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

### 6. Consultar las órdenes

```http
GET /api/orders
```

### 7. Consultar una orden

```http
GET /api/orders/1
```

### 8. Crear el PaymentIntent

```http
POST /api/orders/1/payment
```

### 9. Procesar el pago

Utilizar el `client_secret` devuelto por Stripe desde el cliente frontend.

### 10. Verificar el Webhook

Stripe enviará el evento correspondiente a:

```http
POST /api/stripe/webhook
```

La orden deberá cambiar a:

```text
paid
```

cuando el pago sea exitoso.

---

# 🧱 Estructura principal del proyecto

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── OrderController.php
│   │       ├── PaymentController.php
│   │       ├── ProductController.php
│   │       └── StripeWebhookController.php
│   │
│   └── Requests/
│       ├── LoginRequest.php
│       ├── RegisterRequest.php
│       ├── StoreOrderRequest.php
│       ├── StoreProductRequest.php
│       └── UpdateProductRequest.php
│
├── Models/
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Payment.php
│   ├── Product.php
│   └── User.php
│
└── OpenApi/
    └── OpenApiSpec.php

database/
├── migrations/
└── seeders/

routes/
├── api.php
└── web.php
```

---

# 🛡️ Validaciones y manejo de errores

Las validaciones de entrada se implementan mediante **Form Requests** de Laravel.

Entre las validaciones implementadas se encuentran:

* Campos obligatorios.
* Formato de correo electrónico.
* Confirmación de contraseña.
* Tipos de datos.
* Precios mayores a cero.
* Stock no negativo.
* Cantidad de productos mayor a cero.
* Existencia de productos.
* Órdenes con al menos un producto.

Las respuestas utilizan códigos HTTP apropiados, entre ellos:

| Código | Uso                                     |
| -----: | --------------------------------------- |
|    200 | Operación exitosa                       |
|    201 | Recurso creado                          |
|    401 | Usuario no autenticado                  |
|    403 | Usuario no autorizado                   |
|    404 | Recurso no encontrado                   |
|    422 | Error de validación o reglas de negocio |
|    500 | Error interno                           |
|    502 | Error al comunicarse con Stripe         |

Las respuestas de la API utilizan una estructura JSON consistente:

```json
{
    "success": false,
    "message": "Descripción del error"
}
```

---

# 🗃️ Base de datos

El sistema utiliza relaciones entre las principales entidades:

```text
User
 │
 └──< Order
       │
       ├──< OrderItem >── Product
       │
       └──< Payment
```

### Relaciones principales

* Un usuario puede tener muchas órdenes.
* Una orden pertenece a un usuario.
* Una orden contiene múltiples detalles.
* Cada detalle pertenece a un producto.
* Una orden puede tener registros de pago.
* Un pago pertenece a una orden.

Las migraciones definen las relaciones y restricciones necesarias para mantener la integridad de los datos.

---

# 📖 Documentación

La documentación completa de la API está disponible mediante Swagger UI:

```text
http://127.0.0.1:8000/api/documentation
```

La documentación incluye:

* Endpoints.
* Métodos HTTP.
* Parámetros.
* Request bodies.
* Autenticación Bearer.
* Códigos de respuesta.
* Tags por funcionalidad.
* Descripción de las operaciones.

---

# ▶️ Ejecutar el proyecto

Después de completar la configuración:

```bash
php artisan serve
```

El servidor estará disponible en:

```text
http://127.0.0.1:8000
```

Swagger:

```text
http://127.0.0.1:8000/api/documentation
```

---

# 👨‍💻 Autor

**Jairo Antonio Hernández**

Proyecto desarrollado como parte del programa:

**KODIGO Academy — Full Stack Junior 35**

Módulo: **Backend con PHP y Laravel**

Actividad: **API de E-commerce Segura con Swagger Completo**
