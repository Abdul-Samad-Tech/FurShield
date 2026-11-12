-- FurShield MySQL schema + seed notes
-- Import into an empty database named `furshield`, then prefer:
--   php artisan migrate:fresh --seed
-- This file documents table definitions for submission. For a ready database,
-- run Laravel migrations/seeders after configuring .env for MySQL.

CREATE DATABASE IF NOT EXISTS furshield CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE furshield;

-- Prefer Laravel migrations for exact schema:
-- php artisan migrate:fresh --seed
--
-- Demo users created by DatabaseSeeder:
-- owner@furshield.test / password
-- vet@furshield.test / password
-- shelter@furshield.test / password
