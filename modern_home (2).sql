-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-11-2024 a las 01:23:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `modern_home`
--
CREATE DATABASE IF NOT EXISTS `modern_home` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `modern_home`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'No especificado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `usuario_id`, `fecha`, `total`, `metodo_pago`) VALUES
(1, 1, '2024-11-07 23:57:58', 16500.00, 'No especificado'),
(2, 2, '2024-11-07 23:57:58', 13500.00, 'No especificado'),
(3, 3, '2024-11-07 23:57:58', 22000.00, 'No especificado'),
(4, 4, '2024-11-07 23:57:58', 9500.00, 'No especificado'),
(5, 1, '2024-11-08 00:44:28', 12000.00, 'No especificado'),
(6, 1, '2024-11-11 20:43:02', 24000.00, 'No especificado'),
(7, 1, '2024-11-14 18:19:05', 20000.00, 'paypal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `emisor` varchar(100) NOT NULL,
  `rfc_emisor` varchar(13) NOT NULL,
  `receptor` varchar(100) NOT NULL,
  `rfc_receptor` varchar(13) NOT NULL,
  `detalles` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `compra_id`, `emisor`, `rfc_emisor`, `receptor`, `rfc_receptor`, `detalles`) VALUES
(1, 1, 'Modern Home', 'MODHOM1234567', 'Juan Pérez', 'JUAP890123HML', 'Sofá Moderno x1, Mesa de Centro x1'),
(2, 2, 'Modern Home', 'MODHOM1234567', 'María López', 'MALO850412HML', 'Mesa de Comedor x1, Sillas de Comedor x4'),
(3, 3, 'Modern Home', 'MODHOM1234567', 'Carlos García', 'CAGA810905HML', 'Cama Queen Size x1, Armario x1, Lámpara de Pie x2'),
(4, 4, 'Modern Home', 'MODHOM1234567', 'Ana Martínez', 'ANMA750628HML', 'Estantería x1, Lámpara de Pie x1'),
(5, 5, 'Modern Home', 'MODHOM1234567', 'Juan Pérez', 'JUAP890123HML', 'Sofá Moderno x1'),
(6, 6, '', '', '', '', 'Sofá Moderno x2'),
(7, 7, '', '', '', '', 'Sillas de Comedor x1, Sofá Moderno x1, Mesa de Centro x1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `stock`) VALUES
(1, 'Sofá Moderno', 'Perfecto para darle un toque contemporáneo a tu sala.', 12000.00, 'img/sofa_moderno.jpeg', 10),
(2, 'Mesa de Centro', 'Elegante mesa de centro de vidrio templado.', 3500.00, 'img/mesa_centro.jpeg', 15),
(3, 'Mesa de Comedor', 'Moderna mesa de comedor para 6 personas.', 9000.00, 'img/mesa_comedor.jpeg', 5),
(4, 'Sillas de Comedor', 'Juego de 4 sillas de madera de alta calidad.', 4500.00, 'img/sillas_comedor.jpeg', 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `rfc` varchar(13) NOT NULL,
  `tipo` enum('cliente','administrador') NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rfc`, `tipo`) VALUES
(1, 'Juan Pérez', 'juan.perez@example.com', '1234', 'JUAP890123HML', 'cliente'),
(2, 'María López', 'maria.lopez@example.com', 'password456', 'MALO850412HML', 'cliente'),
(3, 'Carlos García', 'carlos.garcia@example.com', 'password789', 'CAGA810905HML', 'cliente'),
(4, 'Ana Martínez', 'ana.martinez@example.com', 'password321', 'ANMA750628HML', 'cliente'),
(5, 'Admin', 'admin@modernhome.com', '1234', 'MODHOM1234567', 'administrador');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
