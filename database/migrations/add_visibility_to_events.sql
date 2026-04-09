-- Migration: Add visibility column to events table
-- Date: 2026-04-09
-- Description: Add public/private visibility option for events

ALTER TABLE `events` ADD COLUMN `visibility` ENUM('public', 'private') DEFAULT 'public' AFTER `status`;

-- Update index for better query performance
CREATE INDEX `idx_visibility` ON `events` (`visibility`);
