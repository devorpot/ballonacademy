-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 03-09-2025 a las 16:48:11
-- Versión del servidor: 8.0.42-0ubuntu0.24.04.1
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ballon_academy`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activations`
--

CREATE TABLE `activations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `activations`
--

INSERT INTO `activations` (`id`, `name`, `email`, `phone`, `course_id`, `code`, `hash`, `active`, `created`, `created_at`, `updated_at`) VALUES
(38, 'Daniel L', 'danydevx@gmail.com', '444444444', 17, '92H9OG', 'f8dcceb20acc95bb6806987556eab39d1f60403fc0c134b7bbf256c9ed6f4fa3', 1, '2025-08-29 04:26:12', NULL, NULL),
(39, 'Valentina', 'valentina@orpot.net', '452342344', 17, '12K4JB', '4554ce91bf3cdffb36422fbf306e0cc9653107ef39bb5d95671b1399846552b8', 1, '2025-08-30 02:05:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activities`
--

CREATE TABLE `activities` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `evaluation_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `course_id`, `video_id`, `evaluation_id`, `type`, `description`, `created_at`, `updated_at`) VALUES
(231, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #97) para el curso #17.', '2025-09-02 23:02:50', '2025-09-02 23:02:50'),
(232, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #98) para el curso #17.', '2025-09-02 23:05:13', '2025-09-02 23:05:13'),
(233, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #99) para el curso #17.', '2025-09-02 23:05:46', '2025-09-02 23:05:46'),
(234, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #100) para el curso #17.', '2025-09-02 23:06:56', '2025-09-02 23:06:56'),
(235, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #101) para el curso #17.', '2025-09-02 23:07:45', '2025-09-02 23:07:45'),
(236, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #102) para el curso #17.', '2025-09-02 23:10:17', '2025-09-02 23:10:17'),
(237, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #103) para el curso #17.', '2025-09-02 23:20:16', '2025-09-02 23:20:16'),
(238, 69, 17, NULL, 33, '8', 'El usuario goku reinició la evaluación #33 del curso #17. Envíos eliminados: 1.', '2025-09-02 23:20:26', '2025-09-02 23:20:26'),
(239, 69, 17, NULL, 33, '5', 'El usuario goku envió una nueva evaluación (EvaluationUser #104) para el curso #17.', '2025-09-02 23:34:43', '2025-09-02 23:34:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('academia_cache_translations:es:1755743287', 'a:4:{s:3:\"nav\";a:3:{s:7:\"courses\";s:6:\"Cursos\";s:7:\"profile\";s:6:\"Perfil\";s:6:\"logout\";s:5:\"salir\";}s:7:\"actions\";a:2:{s:4:\"save\";s:7:\"Guardar\";s:4:\"next\";s:9:\"Siguiente\";}s:7:\"profile\";a:2:{s:9:\"myProfile\";s:9:\"Mi Perfil\";s:8:\"security\";s:9:\"Seguridad\";}s:8:\"greeting\";s:11:\"Hola, :name\";}', 2072200022);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `master_id` bigint UNSIGNED NOT NULL,
  `authorized_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `date_expedition` date DEFAULT NULL,
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `master_id`, `authorized_by`, `date_start`, `date_end`, `date_expedition`, `comments`, `photo`, `logo`, `pdf_path`, `created_at`, `updated_at`) VALUES
(22, 69, 13, 'dasasd', '2025-08-06', '2025-08-01', '2025-08-08', 'asdasda', 'certificates/photos/LdB9ix3iqUGmwj5NMRymvxeK6jSjpUNyacjACahx.jpg', 'certificates/logos/2qYQIfsR8qL2PuA4IQZBVROgGEzfbKfLdmnQ6nZo.jpg', 'certificates/pdfs/DCJayUiRWCAr6F2Lav6E4mWMJLhAxQlu4ny5lAvY.pdf', '2025-08-29 03:51:08', '2025-08-30 01:44:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_short` text COLLATE utf8mb4_unicode_ci,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `description_short`, `level`, `image_cover`, `logo`, `date_start`, `date_end`, `created_at`, `updated_at`, `active`, `price`, `payment_link`, `currency_id`) VALUES
(17, 'Plantinum Balloon Artist', 'Con este curso aprenderas todo sobre la globoflexia', 'Con este curso aprenderas todo sobre la globoflexia', '1', 'courses/covers/riaVsdq2cgigllss2MDlBJwgsiQ3x3ZUGKVjHVKy.jpg', NULL, '2025-09-08', '2025-10-30', '2025-07-04 00:03:18', '2025-08-29 00:43:02', 1, 5000.00, 'sadasdasd', 1),
(23, 'Curso MBA', 'asdasd', NULL, '2', 'courses/covers/7KqQOgxUKvvZUj2FAEn0JVrDPzck4BLoQVdFqLvi.png', NULL, '2025-07-09', '2025-07-09', '2025-07-10 05:10:07', '2025-08-29 02:58:16', 0, 7500.00, 'https:/adsasdasd.com', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_activities`
--

CREATE TABLE `course_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `activity` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ended` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `activity_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `course_activities`
--

INSERT INTO `course_activities` (`id`, `course_id`, `user_id`, `lesson_id`, `activity`, `ended`, `activity_date`, `created_at`, `updated_at`) VALUES
(32, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-23', '2025-08-23 06:44:30', '2025-08-23 06:44:30'),
(33, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-23', '2025-08-23 06:46:15', '2025-08-23 06:46:15'),
(34, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-23', '2025-08-23 06:51:46', '2025-08-23 06:51:46'),
(35, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-23', '2025-08-24 02:27:29', '2025-08-24 02:27:29'),
(36, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 06:02:21', '2025-08-26 06:02:21'),
(37, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 06:04:24', '2025-08-26 06:04:24'),
(38, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 21:11:06', '2025-08-26 21:11:06'),
(39, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 21:11:22', '2025-08-26 21:11:22'),
(40, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 21:12:13', '2025-08-26 21:12:13'),
(41, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-26', '2025-08-26 22:11:56', '2025-08-26 22:11:56'),
(42, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 07:01:45', '2025-08-27 07:01:45'),
(43, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 07:39:33', '2025-08-27 07:39:33'),
(44, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 08:02:43', '2025-08-27 08:02:43'),
(45, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 08:02:56', '2025-08-27 08:02:56'),
(46, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 08:03:06', '2025-08-27 08:03:06'),
(47, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 08:03:22', '2025-08-27 08:03:22'),
(48, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 08:03:46', '2025-08-27 08:03:46'),
(49, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 22:04:23', '2025-08-27 22:04:23'),
(50, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 22:06:29', '2025-08-27 22:06:29'),
(51, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 23:06:34', '2025-08-27 23:06:34'),
(52, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 23:06:38', '2025-08-27 23:06:38'),
(53, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-27 23:11:23', '2025-08-27 23:11:23'),
(54, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 00:09:02', '2025-08-28 00:09:02'),
(55, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 02:01:14', '2025-08-28 02:01:14'),
(56, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 02:59:54', '2025-08-28 02:59:54'),
(57, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:24:35', '2025-08-28 05:24:35'),
(58, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:27:50', '2025-08-28 05:27:50'),
(59, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:28:02', '2025-08-28 05:28:02'),
(60, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:29:32', '2025-08-28 05:29:32'),
(61, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:30:27', '2025-08-28 05:30:27'),
(62, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:57:33', '2025-08-28 05:57:33'),
(63, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:57:39', '2025-08-28 05:57:39'),
(64, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:57:48', '2025-08-28 05:57:48'),
(65, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-27', '2025-08-28 05:59:32', '2025-08-28 05:59:32'),
(66, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-28', '2025-08-28 06:00:14', '2025-08-28 06:00:14'),
(67, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-28', '2025-08-28 06:01:12', '2025-08-28 06:01:12'),
(68, 17, 69, NULL, 'Curso finalizado por el usuario', 1, '2025-08-28', '2025-08-29 02:40:13', '2025-08-29 02:40:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_user`
--

CREATE TABLE `course_user` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `course_user`
--

INSERT INTO `course_user` (`id`, `course_id`, `user_id`, `created_at`, `updated_at`) VALUES
(60, 17, 70, NULL, NULL),
(61, 17, 69, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `label`, `created_at`, `updated_at`) VALUES
(1, 'MXN', 'Peso Mexicano', NULL, NULL),
(2, 'USD', 'Dólar Estadounidense', NULL, NULL),
(3, 'ARS', 'Peso Argentino', NULL, NULL),
(4, 'EUR', 'Euro', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distributors`
--

CREATE TABLE `distributors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gmap_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lng` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `distributors`
--

INSERT INTO `distributors` (`id`, `name`, `logo`, `description`, `country`, `state`, `region`, `zone`, `address`, `gmap_link`, `lat`, `lng`, `email`, `whatsapp`, `phone`, `website`, `facebook`, `instagram`, `tiktok`, `created_at`, `updated_at`) VALUES
(1, 'Papeleria Super', 'distributors/logos/LwfyiNY5xGKhWWsOeNjHvM9aJbbw9CuU8hGEjJon.png', 'adssdasdasdasdas', 'México', 'México', 'dasdas', 'asdasd', 'Fray Antonio de Segovia 418, 4', NULL, NULL, NULL, 'devops@orpot.com', '444423423', '233334234', 'http://asdasd.com', 'http://asdasd.com', 'http://asdasd.com', 'http://asdasd.com', '2025-08-22 06:33:18', '2025-08-28 05:23:07'),
(3, 'asdasdas', 'distributors/logos/Gw23pKXBLxvBLb7sqfsGHwYjz9pYRdBbAUJlDG1h.png', 'asdasd', 'Dominica', 'dasdasd', 'asdas', 'asdas', 'asdasdasdas', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-22 06:56:43', '2025-08-22 06:56:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluations`
--

CREATE TABLE `evaluations` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int UNSIGNED NOT NULL DEFAULT '0',
  `course_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `eva_send_date` date DEFAULT NULL,
  `date_evaluation` date DEFAULT NULL,
  `eva_video_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eva_comments` longtext COLLATE utf8mb4_unicode_ci,
  `eva_type` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `status` enum('SEND','REVISION','APROVEED','NO APROVEED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SEND',
  `pdf_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` tinyint UNSIGNED NOT NULL DEFAULT '1' COMMENT '1=Course,2=Video',
  `points_min` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evaluations`
--

INSERT INTO `evaluations` (`id`, `lesson_id`, `title`, `order`, `course_id`, `video_id`, `teacher_id`, `eva_send_date`, `date_evaluation`, `eva_video_file`, `eva_comments`, `eva_type`, `status`, `pdf_file`, `type`, `points_min`, `created_at`, `updated_at`) VALUES
(32, NULL, 'Evaluacion Video 1', 0, 17, 132, NULL, '2025-08-26', NULL, 'evaluations/videos/7yrk9Ob86XBJL0M4oZpdOi86SK3pJHhV97fqqubz.mp4', 'Realiza el cuestionario', 1, 'NO APROVEED', 'evaluations/pdfs/5b6EtjZHVwdX0LHfxxabjpd3LZBlIoWjw4yzHah7.pdf', 2, 100, '2025-08-27 07:09:59', '2025-08-30 03:27:01'),
(33, NULL, 'Evaluacion de Curso 1', 0, 17, NULL, NULL, '2025-08-26', NULL, NULL, 'dasdasdasd', 2, 'SEND', 'evaluations/pdfs/Bi8jDU19M04Q2jCpyikZ5Y80deYIrslgXcgc0TaC.pdf', 1, 1, '2025-08-27 07:22:30', '2025-08-30 03:02:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluations_files`
--

CREATE TABLE `evaluations_files` (
  `id` bigint UNSIGNED NOT NULL,
  `evaluation_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `file_uploaded` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `uploaded` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluations_users`
--

CREATE TABLE `evaluations_users` (
  `id` bigint UNSIGNED NOT NULL,
  `evaluation_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `status` int NOT NULL DEFAULT '0',
  `files` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `approved_user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evaluations_users`
--

INSERT INTO `evaluations_users` (`id`, `evaluation_id`, `user_id`, `course_id`, `video_id`, `comments`, `status`, `files`, `data`, `approved_user_id`, `created_at`, `updated_at`) VALUES
(104, 33, 69, 17, NULL, 'asdasdas', 999, 'evaluations/files/C6Eh0CCVoVVogCSBSNJQI1lwQCzP8ZyYB8N9A3MJ.mp4', '{\"score\": null, \"answers\": [], \"evaluation_id\": 33}', 4, '2025-09-02 23:34:43', '2025-09-03 03:04:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluation_questions`
--

CREATE TABLE `evaluation_questions` (
  `id` bigint UNSIGNED NOT NULL,
  `evaluation_id` bigint UNSIGNED NOT NULL,
  `order` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `points` int UNSIGNED NOT NULL DEFAULT '1',
  `type` tinyint(1) NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `options_json` json DEFAULT NULL,
  `option_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_text` text COLLATE utf8mb4_unicode_ci,
  `response_option` tinyint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `evaluation_questions`
--

INSERT INTO `evaluation_questions` (`id`, `evaluation_id`, `order`, `status`, `points`, `type`, `question`, `options_json`, `option_1`, `option_2`, `option_3`, `option_4`, `option_5`, `response_text`, `response_option`, `created_at`, `updated_at`) VALUES
(25, 32, 1, 1, 1, 0, '1+1', '[{\"label\": \"2\", \"value\": 1}, {\"label\": \"3\", \"value\": 2}, {\"label\": \"5\", \"value\": 3}]', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2025-08-27 08:05:39', '2025-08-27 08:05:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extra_classes`
--

CREATE TABLE `extra_classes` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extract` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_youtube` tinyint NOT NULL DEFAULT '2',
  `youtube_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subt_str` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `extra_classes`
--

INSERT INTO `extra_classes` (`id`, `title`, `extract`, `description`, `image`, `cover`, `is_youtube`, `youtube_url`, `video`, `subt_str`, `category`, `tags`, `active`, `order`, `created_at`, `updated_at`) VALUES
(1, 'asdasd', 'asdasdasdas', 'asdasdasdsa', 'extraclasses/images/8yhMyIL6HczVgbe05kFddEYDjEnk69hwJLz6heVr.jpg', 'extraclasses/covers/y06BHoAdQJsqox6hig2GxJoeM608XWiOTZx6yRTa.jpg', 1, 'https://www.youtube.com/watch?v=iytxJHBsL2w', NULL, NULL, 'test', 'test', 1, 1, '2025-08-30 04:20:38', '2025-08-30 04:20:38'),
(2, 'asdas', 'dasdasd', 'asdasd', 'extraclasses/images/AK5r5BE4Tw7PnIXgeUKjUGPGLPD9wQgX0rZ2wXHl.jpg', 'extraclasses/covers/XE9FR3ynh2fhRZDHyo8wCb4vupksFTI15faQud7v.jpg', 1, 'https://www.youtube.com/watch?v=KzcQHh1F4ak', NULL, NULL, NULL, NULL, 1, 0, '2025-08-30 04:46:13', '2025-08-30 04:46:13'),
(3, 'dasd', 'asdasda', 'asdas', 'extraclasses/images/NvbOGBqhB1asI7KBP2F2yCYHVFy5dGhNSD3xNZmb.jpg', 'extraclasses/covers/c9jFGE1Lng7YSN9J0A1A46ImJ12zp9KJx683I6r4.jpg', 2, NULL, 'extraclasses/videos/4BN5G9XaMA128y0Q6Yp4200r81uhr5E90jkV6EJZ.mp4', NULL, NULL, NULL, 1, 1, '2025-08-30 04:55:02', '2025-08-30 04:55:02'),
(4, 'asdasd', 'asd', 'asdasdas', 'extra_classes/images/eajiyQtz01LXMcAZjU9h2Ct1Nkj0VeayIMaceeBV.jpg', 'extra_classes/covers/kVif84J6mCGRUQsijRWeNmiL6F47Wu2zQEeb0b7f.jpg', 1, 'https://www.youtube.com/watch?v=KzcQHh1F4ak', NULL, NULL, NULL, NULL, 1, 2, '2025-08-30 05:32:05', '2025-08-30 05:32:05'),
(5, 'dasdasd', 'asd', 'asdasd', 'extra_classes/images/LnxOizhS76GmrVwHb7rRD97BsrlTMp31l0VH8P7M.jpg', 'extra_classes/covers/QbZzfBB7xDnKXZNE3j3WkhVLBGOcIx8Vo2opz01A.jpg', 1, 'https://www.youtube.com/watch?v=KzcQHh1F4ak', NULL, NULL, 'dasd', 'asdas', 1, 1, '2025-08-30 17:13:06', '2025-08-30 17:13:06'),
(6, 'asdasd', 'asdasdas', 'dasdasd', 'extra_classes/images/hU1tOkAUM28pGuJDWbJEfIZr0xTWbhfktRSiAdh2.jpg', 'extra_classes/covers/yejh38xL4EaaeOGy5GZaqKbQeCAk5HCkk6rOtyCE.jpg', 1, 'https://www.youtube.com/watch?v=KzcQHh1F4ak', NULL, NULL, NULL, NULL, 1, 1, '2025-08-30 17:35:42', '2025-08-30 17:53:02'),
(7, 'dasdas', 'asdas', 'dasd adasdasd', NULL, 'extra_classes/covers/rdMzADgihaBoT0Ip8kLtlQWN4wd2U2oGtCIWxhao.png', 2, 'https://www.youtube.com/watch?v=KzcQHh1F4ak', 'extra_classes/videos/2nq346ooVDiBUcGrZTI2PEzcrmRPJoaZsoVw86Gd.mp4', NULL, 'dasd', 'asdasd', 1, 1, '2025-08-30 18:04:03', '2025-08-30 21:07:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `description_short` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publish_on` date NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `add_video` tinyint(1) NOT NULL DEFAULT '1',
  `add_evaluation` tinyint(1) NOT NULL DEFAULT '0',
  `add_materials` tinyint(1) NOT NULL DEFAULT '0',
  `videos` json DEFAULT NULL,
  `evaluations` json DEFAULT NULL,
  `materials` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lessons`
--

INSERT INTO `lessons` (`id`, `order`, `active`, `title`, `image`, `image_cover`, `instructions`, `description_short`, `publish_on`, `course_id`, `teacher_id`, `add_video`, `add_evaluation`, `add_materials`, `videos`, `evaluations`, `materials`, `created_at`, `updated_at`, `deleted_at`) VALUES
(10, 1, 1, 'Lecciones Extra Curriculares', NULL, 'lessons/covers/LDPf9qRdZTj973xbIpNc0zdVfaNi89LB8w5MIIZi.jpg', '....', 'asdasd', '2025-08-27', 17, 13, 1, 0, 0, '[]', NULL, NULL, '2025-08-29 01:25:33', '2025-08-29 01:30:10', NULL),
(11, 1, 0, 'Leccion de prueba', NULL, NULL, 'asdas', 'dasdas', '2025-08-29', 23, 13, 1, 0, 0, '[]', NULL, NULL, '2025-08-29 03:05:41', '2025-08-29 03:05:41', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lesson_activities`
--

CREATE TABLE `lesson_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `activity` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ended` tinyint(1) NOT NULL DEFAULT '0',
  `activity_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lesson_activities`
--

INSERT INTO `lesson_activities` (`id`, `course_id`, `user_id`, `lesson_id`, `activity`, `ended`, `activity_date`, `created_at`, `updated_at`) VALUES
(31, 17, 69, 10, 'ended', 1, '2025-08-29 02:40:13', '2025-08-29 02:40:13', '2025-08-29 02:40:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lesson_evaluations`
--

CREATE TABLE `lesson_evaluations` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `evaluation_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED DEFAULT NULL,
  `order` int UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lesson_videos`
--

CREATE TABLE `lesson_videos` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `order` int UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lesson_videos`
--

INSERT INTO `lesson_videos` (`id`, `lesson_id`, `video_id`, `course_id`, `order`, `active`, `created_at`, `updated_at`) VALUES
(51, 10, 132, 17, 1, 1, '2025-08-29 01:27:46', '2025-08-29 01:27:46'),
(52, 11, 133, 23, 1, 1, '2025-08-29 03:06:07', '2025-08-29 03:06:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_06_03_224954_add_role_to_users_table', 1),
(5, '2025_06_03_230345_create_permission_tables', 1),
(6, '2025_06_17_224627_add_student_fields_to_users_table', 2),
(7, '2025_06_17_230505_create_students_table', 2),
(8, '2025_06_18_012932_create_courses_table', 3),
(9, '2025_06_18_023206_create_videos_table', 4),
(10, '2025_06_18_030746_add_date_start_and_date_end_to_courses_table', 5),
(11, '2025_06_18_214158_create_teachers_table', 6),
(12, '2025_06_18_215635_add_columns_to_teachers_table', 7),
(13, '2025_06_18_222707_create_subscriptions_table', 8),
(14, '2025_06_18_225939_create_certificates_table', 9),
(15, '2025_06_21_054554_add_column_to_videos_table', 10),
(16, '2025_06_24_173402_add_teacher_id_to_videos_table', 11),
(17, '2025_06_24_195539_drop_teacher_id_from_teachers_table', 12),
(18, '2025_06_24_220906_create_course_student_table', 13),
(19, '2025_06_24_233841_create_course_user_table', 14),
(20, '2025_06_24_234353_drop_course_student_table', 15),
(21, '2025_06_24_234426_create_course_user_table', 16),
(22, '2025_06_25_011744_remove_student_id_from_students_table', 17),
(23, '2025_06_25_171819_add_active_price_paymentlink_to_courses_table', 18),
(24, '2025_07_01_220758_create_profiles_table', 19),
(25, '2025_07_01_223621_add_extra_fields_to_profiles_table', 20),
(26, '2025_07_08_194125_create_currencies_table', 21),
(27, '2025_07_08_230645_add_currency_id_to_courses_table', 22),
(28, '2025_07_09_215407_remove_full_name_from_profiles_table', 23),
(29, '2025_07_09_225544_add_coupon_fields_to_subscriptions_table', 24),
(30, '2025_07_09_234140_add_payment_stripe_id_to_subscriptions_table', 25),
(31, '2025_07_09_234547_add_payment_refund_to_subscriptions_table', 26),
(32, '2025_07_10_010522_make_coupon_discount_nullable_in_subscriptions_table', 27),
(33, '2025_07_10_145803_create_payment_types_table', 28),
(34, '2025_07_10_150059_add_payment_type_id_to_subscriptions_table', 29),
(35, '2025_07_10_151624_create_payment_statuses_table', 30),
(36, '2025_07_10_151727_add_payment_status_id_to_subscriptions_table', 31),
(37, '2025_07_10_195236_add_video_path_to_videos_table', 32),
(38, '2025_07_15_014931_create_video_activities_table', 33),
(39, '2025_07_15_213714_add_size_and_duration_to_videos_table', 34),
(40, '2025_07_15_235244_create_courses_activities_table', 35),
(41, '2025_07_16_000354_add_activity_date_to_courses_activities_table', 36),
(42, '2025_07_16_202558_create_evaluations_table', 37),
(43, '2025_07_17_194107_create_activities_table', 38),
(44, '2025_07_31_145538_add_firstname_lastname_to_profiles_table', 39),
(45, '2025_07_31_230907_remove_video_url_from_videos_table', 40),
(46, '2025_08_01_192555_create_videos_materials_table', 41),
(47, '2025_08_05_015600_add_teacher_status_fields_to_evaluations_table', 42),
(48, '2025_08_06_163241_add_eva_type_to_evaluations_table', 43),
(49, '2025_08_06_180928_create_evaluation_questions_table', 44),
(50, '2025_08_06_181114_create_evaluation_questions_table', 45),
(51, '2025_08_06_181143_create_evaluation_questions_table', 45),
(52, '2025_08_06_184656_add_order_to_evaluation_questions_table', 45),
(53, '2025_08_06_225834_add_status_to_evaluation_questions_table', 46),
(54, '2025_08_07_001814_add_hint_to_evaluation_questions_table', 47),
(55, '2025_08_07_002735_add_points_to_evaluation_questions_table', 48),
(56, '2025_08_07_173233_remove_user_id_from_evaluations_table', 49),
(57, '2025_08_07_200327_create_evaluations_users_table', 50),
(58, '2025_08_07_201937_add_data_to_evaluations_users_table', 51),
(59, '2025_08_07_224641_add_evaluation_id_to_evaluations_users_table', 52),
(60, '2025_08_12_000315_add_title_to_evaluations_table', 53),
(61, '2025_08_12_214019_add_video_id_to_evaluations_table', 54),
(62, '2025_08_12_234420_add_type_to_evaluations_table', 55),
(63, '2025_08_13_001540_add_order_to_evaluations_table', 56),
(64, '2025_08_13_163948_add_points_min_to_evaluations_table', 57),
(65, '2025_08_18_175751_2025_08_18_000000_create_video_resources_table', 58),
(66, '2025_08_19_132055_add_locale_to_users_table', 59),
(67, '2025_08_19_144540_create_video_captions_table', 60),
(68, '2025_08_19_223436_add_active_to_users_table', 61),
(69, '2025_08_20_165028_create_activations_table', 62),
(70, '2025_08_20_171511_add_contact_fields_to_activations_table', 63),
(71, '2025_08_20_183711_drop_activations', 64),
(72, '2025_08_20_184045_drop_activations_table', 65),
(73, '2025_08_20_184129_create_activations_table', 66),
(74, '2025_08_20_222622_make_subscription_columns_nullable', 67),
(75, '2025_08_21_011757_add_shirt_size_and_address_to_profiles_table', 68),
(76, '2025_08_21_170207_create_lessons_table', 69),
(77, '2025_08_21_174234_point_to_teachers_table_in_lessons', 70),
(78, '2025_08_21_195522_add_lesson_video_table', 71),
(79, '2025_08_21_222915_add_image_and_image_cover_to_lessons', 72),
(80, '2025_08_21_230152_create_lesson_evaluations_table', 73),
(81, '2025_08_21_233154_add_lesson_id_to_evaluations', 74),
(82, '2025_08_22_001909_add_distributors', 75),
(83, '2025_08_22_172928_add_lesson_id_to_videos_table', 76),
(84, '2025_08_22_221940_add_lesson_id_to_video_activities', 77),
(85, '2025_08_22_222130_add_lesson_id_to_course_activities', 78),
(86, '2025_08_22_231643_create_lesson_activities_table', 79),
(87, '2025_08_26_235315_create_evaluations_files_table', 80),
(88, '2025_08_28_213933_make_teacher_personal_fields_nullable', 81),
(89, '2025_08_29_143411_add_extra_fields_to_profiles_table', 82),
(90, '2025_08_29_181705_add_social_and_images_to_teachers_table', 83),
(91, '2025_08_29_193033_add_pdf_path_to_certificates_table', 84),
(92, '2025_08_29_204122_add_pdf_file_to_evaluations_table', 85),
(93, '2025_08_29_215947_create_extra_classes_table', 86),
(94, '2025_08_30_163356_add_video_comments_table', 87),
(95, '2025_09_01_112643_add_unique_subscription_user_course', 88),
(96, '2025_09_01_115024_add_active_to_video_comments_table', 89),
(97, '2025_09_01_115336_add_reply_comment_id_to_video_comments_table', 90);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 4),
(3, 'App\\Models\\User', 69),
(3, 'App\\Models\\User', 70),
(3, 'App\\Models\\User', 72),
(4, 'App\\Models\\User', 74),
(1, 'App\\Models\\User', 78),
(2, 'App\\Models\\User', 79),
(3, 'App\\Models\\User', 93),
(4, 'App\\Models\\User', 108),
(4, 'App\\Models\\User', 109),
(4, 'App\\Models\\User', 110),
(3, 'App\\Models\\User', 119);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('danydevx@gmail.com', '$2y$12$UEMmzmNjyiuZcDoFCqAZmuF53Yr3jJ.6D/1BxWSgxYFD3SwHZK2wa', '2025-08-29 07:49:29'),
('devops@orpot.com', '$2y$12$.wl.5bPkeYLI.8EHxDgW0uI42DGQGDyOhgfV10HZxnh1la20YtxfW', '2025-08-29 02:34:58'),
('kofres@gmail.com', '$2y$12$v.hTigka3GYfXc0slyPpTuUM9THHGd1Kuq7jM9ulMzOIoF/8XCM16', '2025-08-29 08:26:22'),
('valentina@orpot.net', '$2y$12$jJy915eydgxXxqRaE.1iCu9n9GMSLj4G/6bGFjcnNE4uJA6/x8BKC', '2025-08-29 08:30:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_statuses`
--

CREATE TABLE `payment_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `payment_statuses`
--

INSERT INTO `payment_statuses` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Pagado', NULL, NULL),
(2, 'Pendiente', NULL, NULL),
(3, 'Reembolsado', NULL, NULL),
(4, 'Cancelado', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_types`
--

CREATE TABLE `payment_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `payment_types`
--

INSERT INTO `payment_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Tarjeta de crédito', NULL, NULL),
(2, 'Tarjeta de débito', NULL, NULL),
(3, 'Transferencia bancaria', NULL, NULL),
(4, 'Efectivo', NULL, NULL),
(5, 'PayPal', NULL, NULL),
(6, 'Oxxo', NULL, NULL),
(7, 'GPay', NULL, NULL),
(8, 'Apple Pay', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view users', 'web', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(2, 'create users', 'web', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(3, 'edit users', 'web', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(4, 'delete users', 'web', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(5, 'manage roles', 'web', '2025-06-05 06:37:08', '2025-06-05 06:37:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shirt_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `municipality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_regime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfdi_use` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `personal_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `experiencie` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `bussines_own` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bussines_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bussines_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bussines_website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bussines_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `firstname`, `lastname`, `shirt_size`, `address`, `phone`, `rfc`, `business_name`, `street`, `external_number`, `internal_number`, `state`, `municipality`, `neighborhood`, `postal_code`, `billing_email`, `tax_regime`, `cfdi_use`, `created_at`, `updated_at`, `personal_email`, `country`, `whatsapp`, `nickname`, `profile_image`, `cover_image`, `website`, `facebook`, `instagram`, `tiktok`, `youtube`, `description`, `activity`, `experiencie`, `bussines_own`, `bussines_name`, `bussines_logo`, `bussines_website`, `bussines_category`) VALUES
(1, 69, 'Nadia', 'Jimenez', NULL, NULL, NULL, 'LORD800613SI6', 'Lavanderia Manolos', 'Fray Antonio de Segovia', '418', 'dasd', 'AZ', 'asdasd', 'asda', '85001', 'goku@dragonball.com', 'asdasd', 'dasdasdsa', '2025-07-02 04:19:03', '2025-08-29 22:34:03', 'dasdxdasdasddsa@orpot.com', 'México', '3244234', 'jime123', 'profiles/profile_images/7vw6CHOzcu7IU0jDi2Nb02kYsUoDW5qLR6TGyZT2.jpg', 'profiles/cover_images/bLKPzs6kt6kWMB6DICImrVhU241BBqw37wPsUv6m.jpg', 'https://www.facebook.com/orpotweb', 'https://www.facebook.com/jim31123', 'https://www.facebook.com/jim31123', 'https://www.facebook.com/jim31123', 'https://www.facebook.com/jim31123', 'Hola soy Jimena decoradora de globos en Guadalajara Jalisco', 'dasd', 'asdasd', NULL, NULL, NULL, NULL, NULL),
(2, 70, 'Krillin', 'asddasdas.', NULL, NULL, NULL, 'asdasd', 'Lavanderia Manolos', 'Fray Antonio de Segovia', '418', 'dasd', 'AZ', 'asdasd', 'asda', '44840', 'dansiel@orpot.com', NULL, NULL, '2025-07-10 00:56:40', '2025-08-21 06:20:15', 'danielw@orpot.com', 'México', '3321845739', 'dasdasd', NULL, NULL, 'https://orpot.net', 'https://www.facebook.com/orpotweb', 'https://www.facebook.com/orpotweb', 'https://www.facebook.com/orpotweb', 'https://www.facebook.com/orpotweb', 'adasdas', '0', '0', NULL, NULL, NULL, NULL, NULL),
(5, 72, 'asdas', 'dasdasd', NULL, NULL, NULL, NULL, 'Lavanderia Manolos', NULL, NULL, NULL, 'AZ', NULL, NULL, '85001', 'bulma@dragonball.com', NULL, NULL, '2025-07-16 07:01:22', '2025-08-21 06:20:20', 'cosmpanyemail@example.com', 'Estados Unidos', NULL, 'dasd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, NULL, NULL, NULL),
(17, 93, 'Daniel Lopez Ramirez', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-21 09:41:54', '2025-08-21 09:41:54', NULL, 'MX', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, NULL, NULL, NULL),
(40, 119, 'Daniel', 'Lopez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'danydevx@gmail.com', NULL, NULL, '2025-09-01 16:59:34', '2025-09-03 01:42:50', 'danydevx@gmail.com', 'México', NULL, 'danydevx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'asdasd', 'asdasd', 'asdasd', 'asdasd', 'profiles/bussines_logos/GPQlA7oL2fXbWwySwMnvTnYQENfAnm7Kbe83ncsv.jpg', 'asdasd', 'asdasd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `label`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', 'Admin', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(2, 'editor', 'web', 'Editor', '2025-06-05 06:37:08', '2025-06-05 06:37:08'),
(3, 'student', 'web', 'Estudiante', '2025-06-17 02:44:28', '2025-06-17 02:44:28'),
(4, 'teacher', 'web', 'Maestro', '2025-06-19 01:53:46', '2025-06-19 01:53:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(1, 2),
(2, 2),
(3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0S9CitM1s4CnrfEwm7RdOsgDXzNllF4sGEoIISRU', 69, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibUpNazY2WnNueURWV2hBOHJSU3dnNkFZU29Qa09TRnU2b1hQMjdkayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njk7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Nzc6Imh0dHA6Ly9iYWxsb25hY2FkZW15Lm5ldC9mcm9udGVuZC92aWRlby1jb21tZW50cy92aWRlby8xMzI/cGFnZT0xJnBlcl9wYWdlPTEwIjt9fQ==', 1756917977),
('Uz26OlEHDOXmHgSlDrRA8U7QGpHdGilugbDBM13b', 4, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQlF0RGtIYzNVNHZwbkhMS2VTRE9BWU9HNFloNVg3bHNnSUJMUmd5dyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoxMDY6Imh0dHA6Ly9iYWxsb25hY2FkZW15Lm5ldC9hZG1pbi9hY3Rpdml0aWVzL2xpc3Q/cGFnZT0xJnBlclBhZ2U9MzAmc29ydEJ5PWFjdGl2aXRpZXMuY3JlYXRlZF9hdCZzb3J0RGlyPWRlc2MiO319', 1756911658);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `students`
--

CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shirt_size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `students`
--

INSERT INTO `students` (`id`, `user_id`, `firstname`, `lastname`, `phone`, `shirt_size`, `address`, `country`, `created_at`, `updated_at`) VALUES
(16, 69, 'Goku', 'Sayayin', '56465456456', 'c', 'Fray Antonio de Segovia 418', 'México', '2025-06-26 05:15:02', '2025-08-08 12:47:11'),
(17, 70, 'Krillin', '.', '33 4347 7409', 'm', 'Fray Antonio de Segovia 418', 'México', '2025-06-26 05:15:39', '2025-08-08 12:46:30'),
(19, 72, 'Bulma', 'PErez', '33 4347 7409', 'm', 'SUITE 5A-1204, 799 E DRAGRAM', 'Estados Unidos', '2025-06-26 05:30:14', '2025-07-09 00:40:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `used_coupon` tinyint(1) DEFAULT NULL,
  `coupon_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_discount` decimal(10,2) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_currency` bigint UNSIGNED DEFAULT NULL,
  `payment_description` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_card` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_card_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_card_brand` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_bank` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_refund_date` date DEFAULT NULL,
  `payment_refund` tinyint(1) DEFAULT NULL,
  `payment_refund_desc` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_stripe_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_type_id` bigint UNSIGNED DEFAULT NULL,
  `payment_status_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `course_id`, `used_coupon`, `coupon_id`, `coupon_discount`, `payment_amount`, `payment_currency`, `payment_description`, `payment_type`, `payment_card`, `payment_card_type`, `payment_card_brand`, `payment_bank`, `payment_date`, `payment_refund_date`, `payment_refund`, `payment_refund_desc`, `payment_status`, `payment_stripe_id`, `created_at`, `updated_at`, `payment_type_id`, `payment_status_id`) VALUES
(18, 70, 17, 0, NULL, NULL, 5000.00, 1, 'asdasd', NULL, '2333', 'banamex', 'visa', 'banamex', '2025-07-10', NULL, 0, NULL, 'pendiente', 'asdasd', '2025-07-10 23:34:47', '2025-08-29 03:57:12', NULL, NULL),
(19, 69, 17, 0, NULL, NULL, 5000.00, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-18', NULL, 0, NULL, 'pendiente', 'dadasdsa', '2025-07-10 23:53:21', '2025-07-10 23:53:21', NULL, NULL),
(59, 119, 17, 0, NULL, NULL, 5000.00, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-09-01', NULL, 0, NULL, NULL, 'sdfdaewdasd', '2025-09-01 17:28:56', '2025-09-01 17:34:48', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `teachers`
--

INSERT INTO `teachers` (`id`, `created_at`, `updated_at`, `user_id`, `firstname`, `lastname`, `phone`, `specialty`, `address`, `country`, `facebook`, `instagram`, `tiktok`, `website`, `profile_image`, `cover_image`) VALUES
(13, '2025-08-29 01:18:38', '2025-08-30 00:34:05', 108, 'Jony', 'Bear', '5555555555', 'Diseñador de Globos', '...', 'Mexico', 'https://www.facebook.com/', 'https://www.facebook.com/', 'https://www.facebook.com/', 'https://www.facebook.com/', 'teachers/profile_images/LhaALluFfeP76j8r9CE3qPwX7MQEYCbbnZv9Lvoj.jpg', 'teachers/cover_images/LqTfRovPRIFDTreXqSiVBFa9B0VUGDDVgZfdeWyr.jpg'),
(14, '2025-08-29 01:19:54', '2025-08-29 01:19:54', 109, 'Luiz', 'Carlos', '33333333333', '...', '...', 'Brasil', NULL, NULL, NULL, NULL, NULL, NULL),
(15, '2025-08-29 01:32:04', '2025-08-29 01:32:04', 110, 'Maribel', 'Anguiano', '334333333', '....', '....', 'México', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'es' COMMENT 'User UI language, e.g. es | en | en-US'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `active`, `remember_token`, `created_at`, `updated_at`, `role`, `locale`) VALUES
(4, 'Daniel', 'devops@orpot.com', NULL, '$2y$12$HpUHa6RgAXYSVj0yDsOT0eosaB9yS1vrTk1cI6/ORwdeEGhtn58ju', 1, NULL, '2025-06-05 07:25:42', '2025-06-05 07:25:42', 'user', 'es'),
(69, 'goku', 'goku@dragonball.com', NULL, '$2y$12$AJ6n5XNgMjBMnD8kK0XK0OYOka0yr0WW3ad4Br8l3Y.8sDw/8q13a', 1, NULL, '2025-06-26 05:15:02', '2025-08-29 00:34:10', 'user', 'es'),
(70, 'krillin', 'krillin@dragonball.com', NULL, '$2y$12$9UU8M/g9KARjQogod8P.eOQnkaGRaKeKb7CaCC/jklsSe3raseoi6', 1, NULL, '2025-06-26 05:15:39', '2025-08-08 09:39:05', 'user', 'es'),
(72, 'Bulma', 'bulma@dragonball.com', NULL, '$2y$12$bclk1Ye3h7bUgm1x9kfDD.Tb.RYx0A.S1QSyFVj0USexUJCi4oTka', 1, NULL, '2025-06-26 05:30:14', '2025-07-16 07:01:22', 'user', 'es'),
(74, 'Freezer', 'freezer@dragonball.com', NULL, '$2y$12$UaOYl23hUoiw1.5zH.qPTOohfPdfwup17sl99rPfdtHtto0sDiuiG', 1, NULL, '2025-06-26 06:24:40', '2025-06-26 06:24:40', 'user', 'es'),
(78, 'Administrador', 'admin@example.com', NULL, '$2y$12$HvZQQV.9ipBgVPfmKV25keZnnmFI.VgHNhu0Ep3qwzPQ.9mcZNyXq', 1, NULL, '2025-07-10 21:00:49', '2025-07-10 21:00:49', 'user', 'es'),
(79, 'Editor', 'editor@example.com', NULL, '$2y$12$a8sasksVBLQiOwUlF4psk.rrUCaQk2E/KH1Dbf5dYJp/oRI0vbcQK', 1, NULL, '2025-07-10 21:00:49', '2025-07-10 21:00:49', 'user', 'es'),
(93, 'Daniel Lopez Ramirez', 'kofres@gmail.com', NULL, '$2y$12$L5sDYiZkI7UZRLHvmcvwxeqZJ1w376FEHbwxa2YVeDzN/YNTOFML.', 1, NULL, '2025-08-21 09:41:54', '2025-08-21 09:41:54', 'user', 'es'),
(108, 'Jony Bear', 'jonyb@academiainternacionaldeglobos.com', NULL, '$2y$12$DMOIS7IFiePbttTdkwbKH.4ZoxRu.CzF06XHllIn7ao373yJ7Ifmq', 1, NULL, '2025-08-29 01:18:38', '2025-08-29 01:18:38', 'user', 'es'),
(109, 'Luiz Carlos', 'luiz@academiainternacionaldeglobos.com', NULL, '$2y$12$grK9blZOoh.VI2axObvmVeACnn3ibwPQWRAxz4og/z9EOOBTEOlNW', 1, NULL, '2025-08-29 01:19:54', '2025-08-29 01:19:54', 'user', 'es'),
(110, 'maribela', 'maribela@academiainternacionaldeglobos.com', NULL, '$2y$12$f1dnpQbEgStmRQCgFfXjfumiirn8YmZFGFBOH87bpAX/.ZEgxEQnW', 1, NULL, '2025-08-29 01:32:04', '2025-08-29 01:32:04', 'user', 'es'),
(119, 'DanyDevx', 'danydevx@gmail.com', '2025-09-01 17:00:05', '$2y$12$jnPphiuF39t5JkoIolQkWeNy1OoREM5mWIyKD86wxJm1uR8m/Ty06', 1, NULL, '2025-09-01 16:59:34', '2025-09-01 17:00:05', 'user', 'es');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos`
--

CREATE TABLE `videos` (
  `id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_short` text COLLATE utf8mb4_unicode_ci,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `image_cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `teacher_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `videos`
--

INSERT INTO `videos` (`id`, `lesson_id`, `course_id`, `title`, `video_path`, `size`, `duration`, `description`, `description_short`, `comments`, `image_cover`, `created_at`, `updated_at`, `order`, `teacher_id`) VALUES
(132, 10, 17, 'Herramientas y Accesorios', 'videos/O6aHueoSBu21dpw4g3OxMknRdxCGPKgFAiIyFTqg.mp4', '588.82 MB', '00:37:30', 'Clase Extra curricular', 'Clase Extra curricular', NULL, 'videos/image_covers/0NiXyC5XkFEfPb7uJbCpD1zXs80DTV2bp4SPPnXX.png', '2025-08-29 01:27:46', '2025-08-29 02:11:03', 1, 15),
(133, NULL, 23, 'Test', 'videos/3nYFMI3T7EQeL3YsMFIZYGriIWikcHXivlp3r7Yv.mp4', '1.64 MB', '00:00:08', 'asjdhasjkdhkasjhdasjkd', 'asjdhasjkdhkasjhdasjkd', NULL, 'videos/image_covers/pbh6PIuKk7GfsY6EAN56wHPuXB86cFgxqHxaiS3Q.jpg', '2025-08-29 02:58:55', '2025-08-29 02:58:55', 1, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videos_materials`
--

CREATE TABLE `videos_materials` (
  `id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `buy_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `videos_materials`
--

INSERT INTO `videos_materials` (`id`, `video_id`, `name`, `quantity`, `unit`, `notes`, `image`, `price`, `buy_link`, `created_at`, `updated_at`) VALUES
(24, 132, 'Listones enormes', '23', '1', 'debes comprar 10', 'materials/y3SdiYymIeVDUPtIJsuP9NlkdpQTv9BalyyocrA3.jpg', 44.00, 'https://www.amazon.com.mx/Contenedores-Recipientes-Alimentos-Herm%C3%A9tica-Refrigerador/dp/B0BT8HWSSK/', '2025-08-30 21:08:14', '2025-08-30 22:24:51'),
(25, 132, '33', '2', '33', NULL, NULL, 4.23, 'https://www.amazon.com.mx/Contenedores-Recipientes-Alimentos-Herm%C3%A9tica-Refrigerador/dp/B0BT8HWSSK/', '2025-08-30 21:16:02', '2025-08-30 22:18:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `video_activities`
--

CREATE TABLE `video_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `lesson_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `second` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `video_activities`
--

INSERT INTO `video_activities` (`id`, `user_id`, `course_id`, `video_id`, `lesson_id`, `event`, `second`, `created_at`, `updated_at`) VALUES
(1599, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 01:28:11', '2025-08-29 01:28:11'),
(1600, 69, 17, 132, 10, 'pause', 106.75, '2025-08-29 01:28:26', '2025-08-29 01:28:26'),
(1601, 69, 17, 132, 10, 'play', 106.75, '2025-08-29 01:28:26', '2025-08-29 01:28:26'),
(1602, 69, 17, 132, 10, 'play', 314.49, '2025-08-29 01:28:29', '2025-08-29 01:28:29'),
(1603, 69, 17, 132, 10, 'pause', 314.49, '2025-08-29 01:28:29', '2025-08-29 01:28:29'),
(1604, 69, 17, 132, 10, 'pause', 54.82, '2025-08-29 01:28:31', '2025-08-29 01:28:31'),
(1605, 69, 17, 132, 10, 'play', 54.82, '2025-08-29 01:28:31', '2025-08-29 01:28:31'),
(1606, 69, 17, 132, 10, 'pause', 69.25, '2025-08-29 01:28:33', '2025-08-29 01:28:33'),
(1607, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 01:28:33', '2025-08-29 01:28:33'),
(1608, 69, 17, 132, 10, 'pause', 5.77, '2025-08-29 01:28:34', '2025-08-29 01:28:34'),
(1609, 69, 17, 132, 10, 'play', 5.77, '2025-08-29 01:28:34', '2025-08-29 01:28:34'),
(1610, 69, 17, 132, 10, 'pause', 11.93, '2025-08-29 01:28:40', '2025-08-29 01:28:40'),
(1611, 69, 17, 132, 10, 'play', 0.01, '2025-08-29 01:30:24', '2025-08-29 01:30:24'),
(1612, 69, 17, 132, 10, 'pause', 1338.74, '2025-08-29 01:30:40', '2025-08-29 01:30:40'),
(1613, 69, 17, 132, 10, 'play', 1338.74, '2025-08-29 01:30:40', '2025-08-29 01:30:40'),
(1614, 69, 17, 132, 10, 'pause', 1538.73, '2025-08-29 01:30:41', '2025-08-29 01:30:41'),
(1615, 69, 17, 132, 10, 'play', 1538.73, '2025-08-29 01:30:41', '2025-08-29 01:30:41'),
(1616, 69, 17, 132, 10, 'play', 1685.69, '2025-08-29 01:30:44', '2025-08-29 01:30:44'),
(1617, 69, 17, 132, 10, 'pause', 1685.69, '2025-08-29 01:30:44', '2025-08-29 01:30:44'),
(1618, 69, 17, 132, 10, 'pause', 1783.77, '2025-08-29 01:30:46', '2025-08-29 01:30:46'),
(1619, 69, 17, 132, 10, 'play', 1783.77, '2025-08-29 01:30:46', '2025-08-29 01:30:46'),
(1620, 69, 17, 132, 10, 'pause', 1929.79, '2025-08-29 01:30:46', '2025-08-29 01:30:46'),
(1621, 69, 17, 132, 10, 'play', 1929.79, '2025-08-29 01:30:46', '2025-08-29 01:30:46'),
(1622, 69, 17, 132, 10, 'play', 2032.86, '2025-08-29 01:30:48', '2025-08-29 01:30:48'),
(1623, 69, 17, 132, 10, 'pause', 2032.86, '2025-08-29 01:30:48', '2025-08-29 01:30:48'),
(1624, 69, 17, 132, 10, 'pause', 2133.08, '2025-08-29 01:30:50', '2025-08-29 01:30:50'),
(1625, 69, 17, 132, 10, 'play', 2133.08, '2025-08-29 01:30:50', '2025-08-29 01:30:50'),
(1626, 69, 17, 132, 10, 'play', 17.18, '2025-08-29 01:31:03', '2025-08-29 01:31:03'),
(1627, 69, 17, 132, 10, 'pause', 17.18, '2025-08-29 01:31:03', '2025-08-29 01:31:03'),
(1628, 69, 17, 132, 10, 'pause', 17.29, '2025-08-29 01:31:06', '2025-08-29 01:31:06'),
(1629, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 01:31:06', '2025-08-29 01:31:06'),
(1630, 69, 17, 132, 10, 'pause', 3.33, '2025-08-29 01:31:09', '2025-08-29 01:31:09'),
(1631, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 02:04:46', '2025-08-29 02:04:46'),
(1632, 69, 17, 132, 10, 'pause', 7.05, '2025-08-29 02:04:53', '2025-08-29 02:04:53'),
(1633, 69, 17, 132, 10, 'play', 7.05, '2025-08-29 02:10:08', '2025-08-29 02:10:08'),
(1634, 69, 17, 132, 10, 'pause', 12.00, '2025-08-29 02:10:12', '2025-08-29 02:10:12'),
(1635, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 02:16:42', '2025-08-29 02:16:42'),
(1636, 69, 17, 132, 10, 'pause', 492.67, '2025-08-29 02:16:53', '2025-08-29 02:16:53'),
(1637, 69, 17, 132, 10, 'play', 492.67, '2025-08-29 02:16:53', '2025-08-29 02:16:53'),
(1638, 69, 17, 132, 10, 'pause', 720.76, '2025-08-29 02:16:56', '2025-08-29 02:16:56'),
(1639, 69, 17, 132, 10, 'play', 720.76, '2025-08-29 02:16:56', '2025-08-29 02:16:56'),
(1640, 69, 17, 132, 10, 'pause', 737.52, '2025-08-29 02:17:13', '2025-08-29 02:17:13'),
(1643, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 02:38:52', '2025-08-29 02:38:52'),
(1644, 69, 17, 132, 10, 'pause', 6.36, '2025-08-29 02:38:58', '2025-08-29 02:38:58'),
(1645, 69, 17, 132, 10, 'play', 6.36, '2025-08-29 02:39:14', '2025-08-29 02:39:14'),
(1646, 69, 17, 132, 10, 'play', 994.46, '2025-08-29 02:39:18', '2025-08-29 02:39:18'),
(1647, 69, 17, 132, 10, 'pause', 994.46, '2025-08-29 02:39:18', '2025-08-29 02:39:18'),
(1648, 69, 17, 132, 10, 'play', 0.00, '2025-08-29 02:39:54', '2025-08-29 02:39:54'),
(1649, 69, 17, 132, 10, 'play', 1341.16, '2025-08-29 02:39:55', '2025-08-29 02:39:55'),
(1650, 69, 17, 132, 10, 'pause', 1341.16, '2025-08-29 02:39:55', '2025-08-29 02:39:55'),
(1651, 69, 17, 132, 10, 'play', 2244.38, '2025-08-29 02:40:06', '2025-08-29 02:40:06'),
(1652, 69, 17, 132, 10, 'pause', 2244.38, '2025-08-29 02:40:06', '2025-08-29 02:40:06'),
(1653, 69, 17, 132, 10, 'pause', 2250.47, '2025-08-29 02:40:13', '2025-08-29 02:40:13'),
(1654, 69, 17, 132, 10, 'ended', 2250.47, '2025-08-29 02:40:13', '2025-08-29 02:40:13'),
(1655, 69, 17, 132, 10, 'play', 0.00, '2025-08-30 22:31:40', '2025-08-30 22:31:40'),
(1656, 69, 17, 132, 10, 'pause', 903.23, '2025-08-30 22:31:42', '2025-08-30 22:31:42'),
(1657, 69, 17, 132, 10, 'play', 903.23, '2025-08-30 22:31:42', '2025-08-30 22:31:42'),
(1658, 69, 17, 132, 10, 'pause', 908.21, '2025-08-30 22:31:47', '2025-08-30 22:31:47'),
(1659, 69, 17, 132, 10, 'play', 0.00, '2025-08-30 22:46:11', '2025-08-30 22:46:11'),
(1660, 69, 17, 132, 10, 'pause', 10.76, '2025-08-30 22:46:22', '2025-08-30 22:46:22'),
(1661, 119, 17, 132, 10, 'play', 0.00, '2025-09-01 17:29:13', '2025-09-01 17:29:13'),
(1662, 119, 17, 132, 10, 'pause', 24.22, '2025-09-01 17:29:37', '2025-09-01 17:29:37'),
(1663, 69, 17, 132, 10, 'play', 0.00, '2025-09-02 16:28:55', '2025-09-02 16:28:55'),
(1664, 69, 17, 132, 10, 'pause', 1471.93, '2025-09-02 16:28:58', '2025-09-02 16:28:58'),
(1665, 69, 17, 132, 10, 'play', 1471.93, '2025-09-02 16:28:58', '2025-09-02 16:28:58'),
(1666, 69, 17, 132, 10, 'pause', 1478.48, '2025-09-02 16:29:05', '2025-09-02 16:29:05'),
(1667, 69, 17, 132, 10, 'play', 0.00, '2025-09-02 22:55:15', '2025-09-02 22:55:15'),
(1668, 69, 17, 132, 10, 'play', 1225.59, '2025-09-02 22:55:22', '2025-09-02 22:55:22'),
(1669, 69, 17, 132, 10, 'pause', 1225.59, '2025-09-02 22:55:22', '2025-09-02 22:55:22'),
(1670, 69, 17, 132, 10, 'pause', 1225.98, '2025-09-02 22:55:22', '2025-09-02 22:55:22'),
(1671, 69, 17, 132, 10, 'play', 0.00, '2025-09-03 16:14:49', '2025-09-03 16:14:49'),
(1672, 69, 17, 132, 10, 'play', 0.00, '2025-09-03 16:18:19', '2025-09-03 16:18:19'),
(1673, 69, 17, 132, 10, 'play', 0.00, '2025-09-03 16:44:34', '2025-09-03 16:44:34'),
(1674, 69, 17, 132, 10, 'play', 962.83, '2025-09-03 16:44:45', '2025-09-03 16:44:45'),
(1675, 69, 17, 132, 10, 'play', 962.83, '2025-09-03 16:44:45', '2025-09-03 16:44:45'),
(1676, 69, 17, 132, 10, 'pause', 962.83, '2025-09-03 16:44:45', '2025-09-03 16:44:45'),
(1677, 69, 17, 132, 10, 'pause', 962.83, '2025-09-03 16:44:45', '2025-09-03 16:44:45'),
(1678, 69, 17, 132, 10, 'pause', 967.27, '2025-09-03 16:44:49', '2025-09-03 16:44:49'),
(1679, 69, 17, 132, 10, 'play', 967.37, '2025-09-03 16:44:58', '2025-09-03 16:44:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `video_captions`
--

CREATE TABLE `video_captions` (
  `id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `lang` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `captions` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `video_captions`
--

INSERT INTO `video_captions` (`id`, `video_id`, `lang`, `file`, `captions`, `created_at`, `updated_at`) VALUES
(4, 132, 'en', 'videos/captions/fBf3VoZesh3o1kiGa1DomHnC6VuHBWYm4sKe9QV1.vtt', NULL, '2025-08-29 02:16:27', '2025-08-29 02:16:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `video_comments`
--

CREATE TABLE `video_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `course_id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `reply_comment_id` bigint UNSIGNED DEFAULT NULL,
  `likes` int DEFAULT NULL,
  `dislikes` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `video_comments`
--

INSERT INTO `video_comments` (`id`, `user_id`, `course_id`, `video_id`, `comment`, `reply_comment_id`, `likes`, `dislikes`, `active`, `created_at`, `updated_at`) VALUES
(1, 69, 17, 132, 'asdasd', NULL, 1, 0, 1, '2025-08-30 22:46:46', '2025-09-01 16:45:30'),
(2, 69, 17, 132, 'hola mundo', NULL, 0, 1, 1, '2025-09-01 16:43:37', '2025-09-01 16:45:30'),
(3, 69, 17, 132, 'hola mundo', NULL, 0, 1, 1, '2025-09-01 16:44:08', '2025-09-01 16:45:31'),
(4, 69, 17, 132, 'hola mundo', NULL, 0, 1, 1, '2025-09-01 16:44:58', '2025-09-01 16:45:32'),
(5, 69, 17, 132, 'asdasd', NULL, 0, 0, 1, '2025-09-01 16:45:36', '2025-09-01 16:45:36'),
(6, 69, 17, 132, 'asdasda', NULL, 0, 0, 1, '2025-09-01 16:45:43', '2025-09-01 16:45:43'),
(7, 69, 17, 132, 'asdasds', NULL, 0, 0, 1, '2025-09-01 16:45:45', '2025-09-01 16:45:45'),
(8, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:45:47', '2025-09-01 16:45:47'),
(9, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:45:49', '2025-09-01 16:45:49'),
(10, 69, 17, 132, 'asdasdasd', NULL, 0, 0, 1, '2025-09-01 16:45:51', '2025-09-01 16:45:51'),
(11, 69, 17, 132, 'asdasdasd', NULL, 0, 0, 1, '2025-09-01 16:45:53', '2025-09-01 16:45:53'),
(12, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:45:57', '2025-09-01 16:45:57'),
(13, 69, 17, 132, 'asdasd', NULL, 0, 1, 0, '2025-09-01 16:46:01', '2025-09-02 16:20:53'),
(15, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:46:05', '2025-09-01 16:46:05'),
(16, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:46:07', '2025-09-01 16:46:07'),
(17, 69, 17, 132, 'asdasdasd', NULL, 0, 0, 1, '2025-09-01 16:46:09', '2025-09-01 16:46:09'),
(18, 69, 17, 132, 'asdasdasd', NULL, 0, 0, 1, '2025-09-01 16:46:11', '2025-09-01 16:46:11'),
(19, 69, 17, 132, 'asdasdasd', NULL, 0, 0, 1, '2025-09-01 16:46:13', '2025-09-01 16:46:13'),
(20, 69, 17, 132, 'asdasdas', NULL, 0, 0, 1, '2025-09-01 16:46:15', '2025-09-01 16:46:15'),
(21, 69, 17, 132, 'asdasdasd', NULL, 0, 1, 1, '2025-09-01 16:46:17', '2025-09-01 19:57:45'),
(22, 69, 17, 132, 'asdddasdasd', NULL, 0, 1, 1, '2025-09-01 16:46:20', '2025-09-01 19:57:47'),
(23, 119, 17, 132, 'asdasdasd', NULL, 1, 0, 1, '2025-09-01 17:29:17', '2025-09-02 15:41:52'),
(24, 69, 17, 132, 'asdasdas', NULL, 1, 0, 1, '2025-09-01 18:12:18', '2025-09-02 16:19:41'),
(25, 69, 17, 132, 'asdasdsa', 23, 0, 0, 1, '2025-09-01 19:53:46', '2025-09-01 19:53:46'),
(26, 69, 17, 132, 'asdasd', 24, 0, 0, 1, '2025-09-01 19:58:34', '2025-09-01 19:58:34'),
(27, 69, 17, 132, 'asdas', 23, 0, 0, 1, '2025-09-02 15:41:55', '2025-09-02 15:41:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `video_resources`
--

CREATE TABLE `video_resources` (
  `id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` tinyint NOT NULL,
  `file_src` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_src` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `img_src` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `video_resources`
--

INSERT INTO `video_resources` (`id`, `video_id`, `title`, `description`, `type`, `file_src`, `video_src`, `img_src`, `uploaded`, `created_at`, `updated_at`) VALUES
(18, 132, 'asdas', 'dasdsa', 3, NULL, NULL, 'videos/resources/images/crptFboLtjdDtkiHWS9BFQaGTWTlXgmjO6nuP0ff.jpg', '2025-08-30', '2025-08-30 21:20:06', '2025-08-30 21:20:06'),
(19, 132, 'asdas', 'dasd', 2, NULL, 'videos/resources/videos/6VINIV6fVZvABuHiejjZtvL13wvHnF0g0fwmfawC.mp4', NULL, '2025-08-30', '2025-08-30 21:20:26', '2025-08-30 21:20:26'),
(20, 132, 'aasdas', 'dasdasd', 1, 'videos/resources/files/nWOBQURM2Xx6FF2KDx4jydAyLpW3YDY6JdtOGVMX.pdf', NULL, NULL, '2025-08-30', '2025-08-30 21:20:42', '2025-08-30 21:20:42');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activations`
--
ALTER TABLE `activations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activations_email_unique` (`email`),
  ADD UNIQUE KEY `activations_code_unique` (`code`),
  ADD UNIQUE KEY `activations_hash_unique` (`hash`),
  ADD KEY `activations_hash_index` (`hash`);

--
-- Indices de la tabla `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_user_id_foreign` (`user_id`),
  ADD KEY `activities_course_id_foreign` (`course_id`),
  ADD KEY `activities_video_id_foreign` (`video_id`),
  ADD KEY `activities_evaluation_id_foreign` (`evaluation_id`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificates_user_id_foreign` (`user_id`),
  ADD KEY `certificates_master_id_foreign` (`master_id`);

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_currency_id_foreign` (`currency_id`);

--
-- Indices de la tabla `course_activities`
--
ALTER TABLE `course_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_activities_course_id_foreign` (`course_id`),
  ADD KEY `courses_activities_user_id_foreign` (`user_id`),
  ADD KEY `course_activities_lesson_id_foreign` (`lesson_id`);

--
-- Indices de la tabla `course_user`
--
ALTER TABLE `course_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_user_course_id_foreign` (`course_id`),
  ADD KEY `course_user_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `currencies_code_unique` (`code`);

--
-- Indices de la tabla `distributors`
--
ALTER TABLE `distributors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluations_course_id_foreign` (`course_id`),
  ADD KEY `evaluations_teacher_id_foreign` (`teacher_id`),
  ADD KEY `evaluations_video_id_foreign` (`video_id`),
  ADD KEY `evaluations_type_index` (`type`),
  ADD KEY `evaluations_order_index` (`order`),
  ADD KEY `evaluations_lesson_id_foreign` (`lesson_id`);

--
-- Indices de la tabla `evaluations_files`
--
ALTER TABLE `evaluations_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluations_files_evaluation_id_foreign` (`evaluation_id`);

--
-- Indices de la tabla `evaluations_users`
--
ALTER TABLE `evaluations_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluations_users_user_id_foreign` (`user_id`),
  ADD KEY `evaluations_users_course_id_foreign` (`course_id`),
  ADD KEY `evaluations_users_video_id_foreign` (`video_id`),
  ADD KEY `evaluations_users_approved_user_id_foreign` (`approved_user_id`),
  ADD KEY `evaluations_users_evaluation_id_foreign` (`evaluation_id`);

--
-- Indices de la tabla `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_questions_evaluation_id_foreign` (`evaluation_id`);

--
-- Indices de la tabla `extra_classes`
--
ALTER TABLE `extra_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `extra_classes_active_order_index` (`active`,`order`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_course_id_publish_on_index` (`course_id`,`publish_on`),
  ADD KEY `lessons_teacher_id_index` (`teacher_id`);

--
-- Indices de la tabla `lesson_activities`
--
ALTER TABLE `lesson_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_activities_course_id_foreign` (`course_id`),
  ADD KEY `lesson_activities_lesson_id_foreign` (`lesson_id`),
  ADD KEY `lesson_activities_user_id_course_id_lesson_id_index` (`user_id`,`course_id`,`lesson_id`),
  ADD KEY `lesson_activities_activity_date_index` (`activity_date`);

--
-- Indices de la tabla `lesson_evaluations`
--
ALTER TABLE `lesson_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_evaluations_lesson_id_evaluation_id_unique` (`lesson_id`,`evaluation_id`),
  ADD KEY `lesson_evaluations_evaluation_id_foreign` (`evaluation_id`),
  ADD KEY `lesson_evaluations_course_id_index` (`course_id`),
  ADD KEY `lesson_evaluations_lesson_id_order_index` (`lesson_id`,`order`);

--
-- Indices de la tabla `lesson_videos`
--
ALTER TABLE `lesson_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_videos_lesson_id_video_id_unique` (`lesson_id`,`video_id`),
  ADD KEY `lesson_videos_video_id_foreign` (`video_id`),
  ADD KEY `lesson_videos_lesson_id_order_index` (`lesson_id`,`order`),
  ADD KEY `lesson_videos_course_id_active_index` (`course_id`,`active`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indices de la tabla `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `payment_statuses`
--
ALTER TABLE `payment_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_statuses_name_unique` (`name`);

--
-- Indices de la tabla `payment_types`
--
ALTER TABLE `payment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_types_name_unique` (`name`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indices de la tabla `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profiles_personal_email_unique` (`personal_email`),
  ADD UNIQUE KEY `profiles_nickname_unique` (`nickname`),
  ADD KEY `profiles_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indices de la tabla `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `students_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriptions_user_course_unique` (`user_id`,`course_id`),
  ADD KEY `subscriptions_course_id_foreign` (`course_id`),
  ADD KEY `subscriptions_payment_type_id_foreign` (`payment_type_id`),
  ADD KEY `subscriptions_payment_status_id_foreign` (`payment_status_id`);

--
-- Indices de la tabla `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teachers_user_id_foreign` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_course_id_foreign` (`course_id`),
  ADD KEY `videos_teacher_id_foreign` (`teacher_id`),
  ADD KEY `videos_lesson_id_foreign` (`lesson_id`);

--
-- Indices de la tabla `videos_materials`
--
ALTER TABLE `videos_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_materials_video_id_foreign` (`video_id`);

--
-- Indices de la tabla `video_activities`
--
ALTER TABLE `video_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_activities_user_id_foreign` (`user_id`),
  ADD KEY `video_activities_course_id_foreign` (`course_id`),
  ADD KEY `video_activities_video_id_foreign` (`video_id`),
  ADD KEY `video_activities_lesson_id_foreign` (`lesson_id`);

--
-- Indices de la tabla `video_captions`
--
ALTER TABLE `video_captions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_captions_video_id_foreign` (`video_id`);

--
-- Indices de la tabla `video_comments`
--
ALTER TABLE `video_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_comments_user_id_foreign` (`user_id`),
  ADD KEY `video_comments_course_id_foreign` (`course_id`),
  ADD KEY `video_comments_video_id_foreign` (`video_id`),
  ADD KEY `video_comments_reply_comment_id_foreign` (`reply_comment_id`);

--
-- Indices de la tabla `video_resources`
--
ALTER TABLE `video_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_resources_video_id_type_index` (`video_id`,`type`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activations`
--
ALTER TABLE `activations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=240;

--
-- AUTO_INCREMENT de la tabla `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `course_activities`
--
ALTER TABLE `course_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `course_user`
--
ALTER TABLE `course_user`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `distributors`
--
ALTER TABLE `distributors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `evaluations_files`
--
ALTER TABLE `evaluations_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `evaluations_users`
--
ALTER TABLE `evaluations_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `extra_classes`
--
ALTER TABLE `extra_classes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `lesson_activities`
--
ALTER TABLE `lesson_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `lesson_evaluations`
--
ALTER TABLE `lesson_evaluations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `lesson_videos`
--
ALTER TABLE `lesson_videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT de la tabla `payment_statuses`
--
ALTER TABLE `payment_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `payment_types`
--
ALTER TABLE `payment_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT de la tabla `videos_materials`
--
ALTER TABLE `videos_materials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `video_activities`
--
ALTER TABLE `video_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1680;

--
-- AUTO_INCREMENT de la tabla `video_captions`
--
ALTER TABLE `video_captions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `video_comments`
--
ALTER TABLE `video_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `video_resources`
--
ALTER TABLE `video_resources`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activities_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activities_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_master_id_foreign` FOREIGN KEY (`master_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `course_activities`
--
ALTER TABLE `course_activities`
  ADD CONSTRAINT `course_activities_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_activities_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `course_user`
--
ALTER TABLE `course_user`
  ADD CONSTRAINT `course_user_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluations_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `evaluations_files`
--
ALTER TABLE `evaluations_files`
  ADD CONSTRAINT `evaluations_files_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `evaluations_users`
--
ALTER TABLE `evaluations_users`
  ADD CONSTRAINT `evaluations_users_approved_user_id_foreign` FOREIGN KEY (`approved_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluations_users_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `evaluations_users_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `evaluations_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluations_users_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`);

--
-- Filtros para la tabla `evaluation_questions`
--
ALTER TABLE `evaluation_questions`
  ADD CONSTRAINT `evaluation_questions_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lesson_activities`
--
ALTER TABLE `lesson_activities`
  ADD CONSTRAINT `lesson_activities_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_activities_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lesson_evaluations`
--
ALTER TABLE `lesson_evaluations`
  ADD CONSTRAINT `lesson_evaluations_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lesson_evaluations_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_evaluations_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lesson_videos`
--
ALTER TABLE `lesson_videos`
  ADD CONSTRAINT `lesson_videos_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_videos_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesson_videos_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_payment_status_id_foreign` FOREIGN KEY (`payment_status_id`) REFERENCES `payment_statuses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subscriptions_payment_type_id_foreign` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `videos_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `videos_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `videos_materials`
--
ALTER TABLE `videos_materials`
  ADD CONSTRAINT `videos_materials_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `video_activities`
--
ALTER TABLE `video_activities`
  ADD CONSTRAINT `video_activities_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_activities_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_activities_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `video_captions`
--
ALTER TABLE `video_captions`
  ADD CONSTRAINT `video_captions_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `video_comments`
--
ALTER TABLE `video_comments`
  ADD CONSTRAINT `video_comments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_comments_reply_comment_id_foreign` FOREIGN KEY (`reply_comment_id`) REFERENCES `video_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_comments_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `video_resources`
--
ALTER TABLE `video_resources`
  ADD CONSTRAINT `video_resources_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
