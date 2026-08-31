ALTER TABLE conventionregistrations
    ADD COLUMN interest_event_ids TEXT NULL
    COMMENT 'event ids marked as interest by judge';
