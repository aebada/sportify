-- Socialite-equivalent Google OAuth columns (MySQL / Hostinger).
-- Safe to run once; PHP migrate script uses columnExists and skips if present.

ALTER TABLE users
  ADD COLUMN google_id VARCHAR(64) NULL AFTER email;

-- Unique when set (MySQL allows multiple NULLs)
CREATE UNIQUE INDEX users_google_id_unique ON users (google_id);

-- member_profiles.avatar already exists in schema; ensure if a legacy DB lacks it:
-- ALTER TABLE member_profiles ADD COLUMN avatar VARCHAR(255) NULL;
