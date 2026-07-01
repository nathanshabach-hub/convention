-- Add optional round-robin scheduling toggle to Scheduling Wizard settings.
ALTER TABLE schedulings
    ADD COLUMN round_robin_day_distribution_yes_no TINYINT(1) NOT NULL DEFAULT 0
    AFTER sports_day_having_events_after_sport_yes_no;
