ALTER TABLE conventionrooms
  ADD COLUMN available_from TIME NULL AFTER status,
  ADD COLUMN available_to TIME NULL AFTER available_from;
