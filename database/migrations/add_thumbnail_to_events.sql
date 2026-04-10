-- Migration: Add thumbnail column to events table
-- Date: 2026-04-10
-- Description: Add thumbnail image for event cards in the landing page (separate from cover_image used in event detail)

ALTER TABLE `events` ADD COLUMN `thumbnail` VARCHAR(255) DEFAULT NULL AFTER `cover_image`;
