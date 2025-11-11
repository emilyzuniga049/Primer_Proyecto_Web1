# AVENTONES – Sistema de Compartir Viajes

**Aventones** es una aplicación web desarrollada como proyecto académico para la carrera de Ingeniería en Software en la **Universidad Técnica Nacional (UTN)**, Costa Rica.  
Su objetivo es ofrecer una plataforma sencilla donde **conductores y pasajeros** puedan coordinar viajes compartidos de forma segura y organizada.

---

## Descripción General

El sistema permite que usuarios de tipo **chofer** publiquen viajes (origen, destino, horario, asientos, vehículo, tarifa), mientras que los **pasajeros** pueden buscar, reservar y gestionar sus viajes dentro de la aplicación.

Incluye un flujo completo de autenticación y notificaciones:
- Registro con activación por correo electrónico (PHPMailer).
- Perfiles personalizados con foto y biografía.
- Gestión de vehículos por parte de los choferes.
- Búsqueda avanzada de viajes con filtros por día, origen y destino.
- Reservas con flujo de aceptación/rechazo.
- Notificaciones mediante script php para solicitudes pendientes.

---

## Tecnologías Utilizadas

| Tecnología | Uso principal |
|-------------|----------------|
| **PHP ** | Lógica del servidor y conexión con la base de datos |
| **MySQL ** | Almacenamiento de usuarios, viajes y reservas |
| **HTML5 / CSS3 / JS** | Interfaz de usuario y manejo de eventos en el navegador |
| **PHPMailer** | Envío de correos electrónicos (verificación, notificaciones) |
| **XAMPP** | Entorno local de desarrollo (Apache + PHP + MySQL) |

---

## Autores

Proyecto desarrollado por:

- **Emily Zúñiga Solano**  
  Estudiante de Ingeniería en Software – UTN  
  [GitHub: emilyzuniga049](https://github.com/emilyzuniga049)

- **Dylan Jiménez Alfaro**  
  Estudiante de Ingeniería en Software – UTN  
  [GitHub: DylanJA0809](https://github.com/dylanja0809)

## Estructura de la Base de Datos

- CREATE TABLE `email_verification_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

- CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `ride_id` int(10) UNSIGNED NOT NULL,
  `passenger_id` int(10) UNSIGNED NOT NULL,
  `seats` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

- CREATE TABLE `rides` (
  `id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED NOT NULL,
  `vehicle_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  `origin` varchar(120) NOT NULL,
  `destination` varchar(120) NOT NULL,
  `departure_time` time DEFAULT NULL,
  `days_set` set('mon','tue','wed','thu','fri','sat','sun') DEFAULT NULL,
  `seat_price` decimal(10,2) DEFAULT NULL,
  `total_seats` tinyint(3) UNSIGNED NOT NULL,
  `available_seats` tinyint(3) UNSIGNED NOT NULL,
  `status` enum('active','cancelled','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

- CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role` enum('driver','passenger','admin') NOT NULL,
  `status` enum('pending','active','inactive') NOT NULL DEFAULT 'pending',
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `national_id` varchar(30) NOT NULL,
  `birth_date` date NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

- CREATE TABLE `vehicles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plate` varchar(20) NOT NULL,
  `make` varchar(60) NOT NULL,
  `model` varchar(60) NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `seat_capacity` tinyint(3) UNSIGNED NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

---
