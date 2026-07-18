-- Manual SQL for migrations added 2026-07-17 (run via cPanel phpMyAdmin / SQL tool).
-- Run top to bottom, in order — later tables/columns depend on earlier ones.
-- Adjust the `migrations` INSERT batch number at the bottom if needed (see note there).

-- ============================================================
-- 1) 2026_07_17_024105_create_member_profiles_table
-- ============================================================
CREATE TABLE `member_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `gender` ENUM('male','female') NULL,
  `race` ENUM('malay','chinese','indian','bumiputra') NULL,
  `race_sub_group` VARCHAR(50) NULL,
  `date_of_birth` DATE NULL,
  `place_of_birth` VARCHAR(255) NULL,
  `ic_no` TEXT NULL,
  `postal_address` TEXT NULL,
  `residential_address` TEXT NULL,
  `occupation` VARCHAR(255) NULL,
  `position` VARCHAR(255) NULL,
  `employer_name` VARCHAR(255) NULL,
  `employer_address` TEXT NULL,
  `employment_date` DATE NULL,
  `bank_name` VARCHAR(255) NULL,
  `bank_branch` VARCHAR(255) NULL,
  `bank_address` TEXT NULL,
  `office_tel` VARCHAR(20) NULL,
  `office_fax` VARCHAR(20) NULL,
  `present_salary` TEXT NULL,
  `salary_increment_date` DATE NULL,
  `proposed_by_user_id` BIGINT UNSIGNED NULL,
  `seconded_by_user_id` BIGINT UNSIGNED NULL,
  `declaration_accepted_at` TIMESTAMP NULL,
  `signature_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_profiles_user_id_unique` (`user_id`),
  KEY `member_profiles_proposed_by_user_id_index` (`proposed_by_user_id`),
  KEY `member_profiles_seconded_by_user_id_index` (`seconded_by_user_id`),
  CONSTRAINT `member_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_profiles_proposed_by_user_id_foreign` FOREIGN KEY (`proposed_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `member_profiles_seconded_by_user_id_foreign` FOREIGN KEY (`seconded_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2) 2026_07_17_024106_create_union_dues_mandates_table
-- ============================================================
CREATE TABLE `union_dues_mandates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `deduction_amount` DECIMAL(10,2) NOT NULL,
  `consent_file_path` VARCHAR(255) NOT NULL,
  `status` ENUM('pending_submission','active','cancelled') NOT NULL DEFAULT 'pending_submission',
  `consent_signed_at` TIMESTAMP NOT NULL,
  `cancelled_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `union_dues_mandates_status_index` (`status`),
  KEY `union_dues_mandates_user_id_foreign` (`user_id`),
  CONSTRAINT `union_dues_mandates_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3) 2026_07_17_024107_create_union_dues_records_table
-- ============================================================
CREATE TABLE `union_dues_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `mandate_id` BIGINT UNSIGNED NOT NULL,
  `period_month` VARCHAR(7) NOT NULL,
  `amount_received` DECIMAL(10,2) NOT NULL,
  `recorded_by` BIGINT UNSIGNED NOT NULL,
  `recorded_at` TIMESTAMP NOT NULL,
  `notes` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mandate_period` (`mandate_id`,`period_month`),
  KEY `union_dues_records_period_month_index` (`period_month`),
  KEY `union_dues_records_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `union_dues_records_mandate_id_foreign` FOREIGN KEY (`mandate_id`) REFERENCES `union_dues_mandates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `union_dues_records_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4) 2026_07_17_034211_add_ic_no_hash_to_member_profiles_table
-- ============================================================
ALTER TABLE `member_profiles`
  ADD COLUMN `ic_no_hash` VARCHAR(64) NULL UNIQUE AFTER `ic_no`;

-- ============================================================
-- 5) 2026_07_17_075011_add_parent_id_to_org_chart_table
-- ============================================================
ALTER TABLE `org_chart`
  ADD COLUMN `parent_id` BIGINT UNSIGNED NULL AFTER `id`,
  ADD KEY `org_chart_parent_id_foreign` (`parent_id`),
  ADD CONSTRAINT `org_chart_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `org_chart` (`id`) ON DELETE SET NULL;

-- ============================================================
-- 6) 2026_07_17_111508_add_photo_to_users_table
-- ============================================================
ALTER TABLE `users`
  ADD COLUMN `photo` VARCHAR(255) NULL AFTER `company`;

-- ============================================================
-- Mark these as run in Laravel's migrations table, so `php artisan migrate`
-- won't try to re-run them once you have CLI access again.
-- Batch number computed automatically — no manual edit needed.
-- ============================================================
SET @next_batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_07_17_024105_create_member_profiles_table', @next_batch),
  ('2026_07_17_024106_create_union_dues_mandates_table', @next_batch),
  ('2026_07_17_024107_create_union_dues_records_table', @next_batch),
  ('2026_07_17_034211_add_ic_no_hash_to_member_profiles_table', @next_batch),
  ('2026_07_17_075011_add_parent_id_to_org_chart_table', @next_batch),
  ('2026_07_17_111508_add_photo_to_users_table', @next_batch);
