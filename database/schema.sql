-- =====================================================================
-- WATTWISE — HOUSEHOLD ELECTRICAL ENERGY MANAGEMENT EXPERT SYSTEM
-- Standalone Relational Database Schema (MySQL 8.0+ / MariaDB 10.4+)
-- Compliant with WattWise Master Development Rules
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `wattwise_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `wattwise_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `recommendation_history`;
DROP TABLE IF EXISTS `rule_conditions`;
DROP TABLE IF EXISTS `expert_rules`;
DROP TABLE IF EXISTS `actual_meter_readings`;
DROP TABLE IF EXISTS `calculation_items`;
DROP TABLE IF EXISTS `calculation_periods`;
DROP TABLE IF EXISTS `appliances`;
DROP TABLE IF EXISTS `appliance_categories`;
DROP TABLE IF EXISTS `households`;
DROP TABLE IF EXISTS `tariff_rates`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. USERS & ROLES
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `email_verified_at` TIMESTAMP NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TARIFF RATES (Electricity tariff categories with validity periods)
-- ---------------------------------------------------------------------
CREATE TABLE `tariff_rates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,          -- e.g. 'R-1/TR 1300VA'
    `name` VARCHAR(150) NOT NULL,                 -- e.g. 'Rumah Tangga Daya 1.300 VA'
    `power_limit_va` DECIMAL(10, 2) NOT NULL,     -- 1300.00 VA
    `rate_per_kwh` DECIMAL(12, 4) NOT NULL,       -- Rp 1.444,70 / kWh
    `base_fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `valid_from` DATE NOT NULL,
    `valid_to` DATE NULL,                         -- NULL means currently active
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tariff_code` (`code`),
    INDEX `idx_tariff_validity` (`valid_from`, `valid_to`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. HOUSEHOLDS (Profiles owned by users)
-- ---------------------------------------------------------------------
CREATE TABLE `households` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `tariff_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,                 -- e.g. 'Rumah Utama'
    `address` VARCHAR(255) NULL,
    `occupant_count` INT UNSIGNED NOT NULL DEFAULT 4,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tariff_id`) REFERENCES `tariff_rates` (`id`) ON DELETE RESTRICT,
    INDEX `idx_households_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. APPLIANCE CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE `appliance_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL UNIQUE,          -- e.g. 'cooling', 'lighting'
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. APPLIANCES (User inventory of electrical equipment)
-- ---------------------------------------------------------------------
CREATE TABLE `appliances` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `household_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,                 -- e.g. 'AC Kamar Utama'
    `power_watt` DECIMAL(8, 2) NOT NULL,          -- Watt (W)
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `daily_hours` DECIMAL(4, 2) NOT NULL,         -- Operating hours / day (0.10 - 24.00)
    `days_per_month` TINYINT UNSIGNED NOT NULL DEFAULT 30, -- 1 - 31 days
    `usage_factor` DECIMAL(4, 3) NOT NULL DEFAULT 1.000,   -- 0.050 - 1.000 (Duty cycle)
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `appliance_categories` (`id`) ON DELETE RESTRICT,
    INDEX `idx_appliances_household` (`household_id`),
    INDEX `idx_appliances_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. CALCULATION PERIODS (Historical and Current Calculation Runs)
-- ---------------------------------------------------------------------
CREATE TABLE `calculation_periods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `household_id` BIGINT UNSIGNED NOT NULL,
    `period_name` VARCHAR(100) NOT NULL,          -- e.g. 'November 2026'
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `total_estimated_kwh` DECIMAL(12, 3) NOT NULL,
    `total_estimated_cost` DECIMAL(14, 2) NOT NULL,
    `applied_rate_per_kwh` DECIMAL(12, 4) NOT NULL,
    `calculation_assumptions` JSON NULL,          -- Stores assumptions, formula weights
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
    INDEX `idx_calc_periods_household` (`household_id`, `start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. CALCULATION ITEMS (Per-Appliance Breakdown for each period)
-- ---------------------------------------------------------------------
CREATE TABLE `calculation_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `calculation_period_id` BIGINT UNSIGNED NOT NULL,
    `appliance_id` BIGINT UNSIGNED NOT NULL,
    `power_watt` DECIMAL(8, 2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `daily_hours` DECIMAL(4, 2) NOT NULL,
    `days_per_month` TINYINT UNSIGNED NOT NULL,
    `usage_factor` DECIMAL(4, 3) NOT NULL,
    `monthly_kwh` DECIMAL(10, 3) NOT NULL,
    `monthly_cost` DECIMAL(12, 2) NOT NULL,
    `percentage_of_total` DECIMAL(5, 2) NOT NULL, -- e.g. 42.50%
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`calculation_period_id`) REFERENCES `calculation_periods` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`appliance_id`) REFERENCES `appliances` (`id`) ON DELETE CASCADE,
    INDEX `idx_calc_items_period` (`calculation_period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. ACTUAL METER READINGS (Measured vs Estimated Electricity Data)
-- ---------------------------------------------------------------------
CREATE TABLE `actual_meter_readings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `household_id` BIGINT UNSIGNED NOT NULL,
    `calculation_period_id` BIGINT UNSIGNED NULL,
    `reading_date` DATE NOT NULL,
    `meter_start_kwh` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `meter_end_kwh` DECIMAL(10, 2) NOT NULL,
    `actual_kwh_consumed` DECIMAL(10, 2) NOT NULL,
    `actual_cost_billed` DECIMAL(14, 2) NULL,
    `variance_kwh` DECIMAL(10, 2) NULL,           -- actual_kwh_consumed - estimated_kwh
    `variance_pct` DECIMAL(6, 2) NULL,           -- ((actual - est) / actual) * 100
    `receipt_image_url` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`calculation_period_id`) REFERENCES `calculation_periods` (`id`) ON DELETE SET NULL,
    INDEX `idx_meter_readings_household` (`household_id`, `reading_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. EXPERT RULES (Rule-based IF-THEN Knowledge Base)
-- ---------------------------------------------------------------------
CREATE TABLE `expert_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_code` VARCHAR(50) NOT NULL UNIQUE,      -- e.g. 'R-DOM-01'
    `category` VARCHAR(50) NOT NULL,              -- 'Dominance', 'Cooling', 'Vampire', 'Lighting', 'Capacity'
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `recommendation_template` TEXT NOT NULL,
    `actionable_steps` TEXT NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `priority` INT NOT NULL DEFAULT 100,          -- Lower number = higher evaluation precedence
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_rules_active_priority` (`is_active`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. RULE CONDITIONS (Predicates for Forward Chaining Inference)
-- ---------------------------------------------------------------------
CREATE TABLE `rule_conditions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `rule_id` BIGINT UNSIGNED NOT NULL,
    `parameter_name` VARCHAR(80) NOT NULL,        -- e.g. 'appliance_share_pct', 'daily_hours', 'power_watt'
    `operator` ENUM('>', '>=', '<', '<=', '==', '!=', 'IN', 'CONTAINS') NOT NULL,
    `comparison_value` VARCHAR(100) NOT NULL,     -- e.g. '35.0', '8.0', 'cooling'
    `logical_operator` ENUM('AND', 'OR') NOT NULL DEFAULT 'AND',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`rule_id`) REFERENCES `expert_rules` (`id`) ON DELETE CASCADE,
    INDEX `idx_rule_conditions_rule` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. RECOMMENDATION HISTORY (Explainable Inferences Log)
-- ---------------------------------------------------------------------
CREATE TABLE `recommendation_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `calculation_period_id` BIGINT UNSIGNED NOT NULL,
    `rule_id` BIGINT UNSIGNED NOT NULL,
    `rule_code` VARCHAR(50) NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `explanation_evidence` TEXT NOT NULL,         -- Verifiable mathematical facts triggered
    `recommendation_text` TEXT NOT NULL,
    `actionable_steps` TEXT NOT NULL,
    `estimated_kwh_saving` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `estimated_cost_saving` DECIMAL(14, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'acknowledged', 'implemented', 'dismissed') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`calculation_period_id`) REFERENCES `calculation_periods` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`rule_id`) REFERENCES `expert_rules` (`id`) ON DELETE RESTRICT,
    INDEX `idx_rec_history_period` (`calculation_period_id`),
    INDEX `idx_rec_history_rule` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SEED DATA: OFFICIAL TARIFFS, CATEGORIES, EXPERT RULES & INITIAL USERS
-- =====================================================================

-- Users (Default password: 'password123' bcrypt hashed)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrator WattWise', 'admin@wattwise.local', '$2y$12$fTeq.bJ2Kk1k18u3p1jVgeYd9m25kQ4iE.yF9fF13x.mGyQ7HkQ1i', 'admin'),
(2, 'Keluarga Budi Santoso', 'budi@wattwise.local', '$2y$12$fTeq.bJ2Kk1k18u3p1jVgeYd9m25kQ4iE.yF9fF13x.mGyQ7HkQ1i', 'user');

-- Indonesian Standard PLN Tariff Rates (Golongan Rumah Tangga 2026)
INSERT INTO `tariff_rates` (`id`, `code`, `name`, `power_limit_va`, `rate_per_kwh`, `base_fee`, `valid_from`, `valid_to`, `is_active`) VALUES
(1, 'R-1/TR-900', 'Rumah Tangga R-1/TR 900 VA (Subsidi/Khusus)', 900.00, 1352.0000, 0.00, '2024-01-01', NULL, TRUE),
(2, 'R-1/TR-1300', 'Rumah Tangga R-1/TR 1.300 VA', 1300.00, 1444.7000, 0.00, '2024-01-01', NULL, TRUE),
(3, 'R-1/TR-2200', 'Rumah Tangga R-1/TR 2.200 VA', 2200.00, 1444.7000, 0.00, '2024-01-01', NULL, TRUE),
(4, 'R-2/TR-3500-5500', 'Rumah Tangga Menengah R-2/TR 3.500 VA - 5.500 VA', 3500.00, 1699.5300, 0.00, '2024-01-01', NULL, TRUE),
(5, 'R-3/TR-6600+', 'Rumah Tangga Besar R-3/TR 6.600 VA ke atas', 6600.00, 1699.5300, 0.00, '2024-01-01', NULL, TRUE);

-- Appliance Categories
INSERT INTO `appliance_categories` (`id`, `code`, `name`, `icon`, `description`) VALUES
(1, 'cooling', 'Pendingin Ruangan (AC & Kipas)', 'snowflake', 'Air conditioner, evaporative cooler, kipas angin dinding/berdiri'),
(2, 'refrigeration', 'Pendingin Makanan (Kulkas & Freezer)', 'box', 'Kulkas 1 pintu, kulkas 2 pintu, side-by-side, showcase, freezer box'),
(3, 'lighting', 'Penerangan & Lampu', 'lightbulb', 'Lampu LED, lampu TL/neon, lampu pijar, lampu taman'),
(4, 'entertainment', 'Hiburan & Perangkat IT', 'tv', 'Televisi, sound system, konsol game, PC Desktop, laptop, router Wi-Fi'),
(5, 'kitchen', 'Peralatan Dapur & Memasak', 'utensils', 'Rice cooker, microwave, oven listrik, air fryer, dispenser air, blender'),
(6, 'laundry', 'Pencucian & Kebersihan', 'shirt', 'Mesin cuci front load, mesin cuci top load, setrika listrik, vacuum cleaner'),
(7, 'water_pump', 'Pompa & Pemanas Air', 'droplet', 'Pompa air jet pump, pompa pendorong (booster), water heater listrik'),
(8, 'other', 'Peralatan Lainnya', 'zap', 'Pompa kolam, charger kendaraan listrik, stopkontak serbaguna');

-- Expert System Rule Base
INSERT INTO `expert_rules` (`id`, `rule_code`, `category`, `title`, `description`, `recommendation_template`, `actionable_steps`, `severity`, `priority`, `is_active`) VALUES
(1, 'R-DOM-01', 'Dominance', 'Beban Energi Dominan Terdeteksi (>35%)', 
 'Mendeteksi peralatan yang menyerap lebih dari 35% total tagihan listrik bulanan rumah tangga.',
 'Peralatan :appliance_name menyerap :appliance_share_pct% dari total konsumsi listrik rumah Anda (:monthly_kwh kWh/bulan). Fokus efisiensi pada peralatan ini akan memberikan dampak penghematan terbesar.',
 '1. Kurangi durasi operasional 1-2 jam per hari.\n2. Lakukan pengecekan efisiensi energi atau gunakan timer otomatis.\n3. Pertimbangkan peremajaan ke tipe inverter berdaya lebih hemat.',
 'high', 10, TRUE),

(2, 'R-COOL-01', 'Cooling', 'Optimasi Operasional Pendingin Ruangan (AC)',
 'Mendeteksi pendingin ruangan dengan durasi operasional tinggi (>= 8 jam/hari).',
 'AC Anda (:appliance_name) beroperasi selama :daily_hours jam per hari dengan konsumsi :monthly_kwh kWh/bulan. Menaikkan suhu setelan dari 20-22°C ke 24-25°C dapat menghemat hingga 15-20% listrik AC.',
 '1. Setel suhu pendingin pada 24°C - 25°C dan gunakan mode Eco/Comfort.\n2. Bersihkan filter AC setiap 2-4 minggu sekali.\n3. Pastikan ruangan tertutup rapat dari ventilasi luar saat AC menyala.',
 'medium', 20, TRUE),

(3, 'R-VAMP-01', 'Vampire', 'Beban Siluman / Standby Power Terdeteksi',
 'Mendeteksi perangkat hiburan/elektronik yang menyala non-stop 24 jam dengan konsumsi standby signifikan.',
 'Perangkat :appliance_name aktif terus menerus 24 jam/hari dan mengonsumsi energi tanpa henti. Beban standby elektronik rumah tangga dapat menyumbang 5-10% tagihan tanpa disadari.',
 '1. Gunakan stopkontak saklar (switch extension cord) untuk memutus total arus saat tidak digunakan.\n2. Matikan colokan TV, konsol, dan adaptor charger saat malam atau bepergian.',
 'medium', 30, TRUE),

(4, 'R-LIGHT-01', 'Lighting', 'Potensi Pemborosan Daya Penerangan',
 'Mendeteksi lampu dengan daya tinggi (>= 25W per titik) yang berpotensi masih menggunakan teknologi pijar/CFL konvensional.',
 'Lampu penerangan :appliance_name memiliki daya :power_watt W. Mengganti lampu konvensional ke teknologi LED (7W-10W) mempertahankan tingkat terang (lumen) yang sama dengan daya 70-80% lebih hemat.',
 '1. Ganti unit lampu konvensional dengan lampu LED hemat energi (7W - 9W).\n2. Pasang sensor gerak atau timer otomatis pada lampu teras/halaman.',
 'low', 40, TRUE),

(5, 'R-WATER-01', 'Water_Pump', 'Pemanas / Pompa Air Beroperasi Berlebihan',
 'Mendeteksi pemanas air atau pompa air dengan durasi tinggi atau daya besar.',
 'Pemanas/pompa air :appliance_name berdaya :power_watt W mengonsumsi :monthly_kwh kWh/bulan. Peralatan pemanas berbasis elemen resistif sangat cepat menyedot kuota daya.',
 '1. Pasang timer pada pemanas air agar menyala 15-30 menit sebelum mandi saja.\n2. Untuk pompa air, periksa tandon/pelampung otomatis dan pastikan tidak ada kebocoran pipa.',
 'high', 50, TRUE),

(6, 'R-CAP-01', 'Capacity', 'Peringatan Kapasitas Beban Maksimum Daya Terpasang',
 'Mendeteksi jika total daya serap simultan peralatan aktif mendekati batas kapasitas MCB PLN.',
 'Total daya peralatan aktif Anda mendekati batas kapasitas daya kontrak PLN (:power_limit_va VA). Risiko trip/jeglek sangat tinggi saat beban puncak.',
 '1. Hindari menyalakan AC, setrika, microwave, dan pompa air secara bersamaan.\n2. Buat jadwal penggunaan peralatan berdaya besar di luar jam beban puncak.',
 'critical', 5, TRUE);

-- Rule Conditions for Forward Chaining Engine
INSERT INTO `rule_conditions` (`id`, `rule_id`, `parameter_name`, `operator`, `comparison_value`, `logical_operator`) VALUES
(1, 1, 'appliance_share_pct', '>=', '35.0', 'AND'),
(2, 1, 'monthly_kwh', '>=', '50.0', 'AND'),
(3, 2, 'category_code', '==', 'cooling', 'AND'),
(4, 2, 'daily_hours', '>=', '8.0', 'AND'),
(5, 3, 'category_code', '==', 'entertainment', 'AND'),
(6, 3, 'daily_hours', '>=', '20.0', 'AND'),
(7, 4, 'category_code', '==', 'lighting', 'AND'),
(8, 4, 'power_watt', '>=', '25.0', 'AND'),
(9, 5, 'category_code', 'IN', 'water_pump', 'AND'),
(10, 5, 'monthly_kwh', '>=', '40.0', 'AND'),
(11, 6, 'simultaneous_load_ratio', '>=', '0.85', 'AND');

-- Sample Household & Inventory
INSERT INTO `households` (`id`, `user_id`, `tariff_id`, `name`, `address`, `occupant_count`) VALUES
(1, 2, 2, 'Rumah Keluarga Budi', 'Jl. Merpati Putih No. 12, Jakarta', 4);

-- Sample Appliances for Household 1
INSERT INTO `appliances` (`id`, `household_id`, `category_id`, `name`, `power_watt`, `quantity`, `daily_hours`, `days_per_month`, `usage_factor`, `is_active`) VALUES
(1, 1, 1, 'AC Inverter Kamar Utama (1 PK)', 650.00, 1, 9.00, 30, 0.700, TRUE),
(2, 1, 1, 'AC Standar Kamar Anak (1/2 PK)', 390.00, 1, 7.00, 30, 0.850, TRUE),
(3, 1, 2, 'Kulkas 2 Pintu Inverter', 100.00, 1, 24.00, 30, 0.500, TRUE),
(4, 1, 3, 'Lampu Pijar Teras Depan', 40.00, 2, 10.00, 30, 1.000, TRUE),
(5, 1, 3, 'Lampu LED Ruang Tamu & Kamar', 9.00, 8, 6.00, 30, 1.000, TRUE),
(6, 1, 4, 'Smart TV LED 50 Inch', 95.00, 1, 5.00, 30, 1.000, TRUE),
(7, 1, 4, 'Wi-Fi Router & Modem', 15.00, 1, 24.00, 30, 1.000, TRUE),
(8, 1, 5, 'Rice Cooker Digital (Memasak + Menghangatkan)', 380.00, 1, 4.00, 30, 0.600, TRUE),
(9, 1, 6, 'Mesin Cuci Front Loading', 350.00, 1, 1.50, 16, 0.900, TRUE),
(10, 1, 6, 'Setrika Listrik Otomatis', 300.00, 1, 1.00, 20, 0.700, TRUE),
(11, 1, 7, 'Pompa Air Otomatis (Jet Pump)', 250.00, 1, 2.00, 30, 1.000, TRUE);

