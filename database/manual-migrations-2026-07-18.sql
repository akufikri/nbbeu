-- Manual SQL for migration added 2026-07-18 (run via cPanel phpMyAdmin / SQL tool).
-- Only needed if member_profiles.proposed_by_user_id / seconded_by_user_id already
-- exist on the server (i.e. 2026-07-17's manual-migrations SQL was already run).

ALTER TABLE `member_profiles`
  DROP FOREIGN KEY `member_profiles_proposed_by_user_id_foreign`,
  DROP FOREIGN KEY `member_profiles_seconded_by_user_id_foreign`,
  DROP COLUMN `proposed_by_user_id`,
  DROP COLUMN `seconded_by_user_id`,
  ADD COLUMN `proposed_by_name` VARCHAR(255) NULL AFTER `present_salary`,
  ADD COLUMN `seconded_by_name` VARCHAR(255) NULL AFTER `proposed_by_name`;

SET @next_batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_07_18_000001_replace_sponsor_ids_with_names_on_member_profiles_table', @next_batch);
