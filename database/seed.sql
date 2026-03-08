-- ============================================================
-- seed.sql — Default admin user
-- Password: admin123  (bcrypt hash)
-- NOTE: Admin user already exists in itportal.sql dump
-- Run this only if the users table is empty
-- ============================================================

INSERT IGNORE INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `dealer_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$12$RGfNEXEh5G3PD4kjOzg79uYZBfjGBEFw1QT88v6l58YJyvZ3FVuOW', 'Administrator', 'admin', NULL, 1, NOW(), NOW());
