UPDATE conventionseasonevents
SET judging_ends = 1
WHERE conventionseasons_id = 24
  AND event_id = (
    SELECT id FROM events WHERE event_id_number = '001' LIMIT 1
  );
