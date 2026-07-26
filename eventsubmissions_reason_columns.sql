ALTER TABLE eventsubmissions
  ADD COLUMN guideline_breach_reason TEXT NULL AFTER guideline_breach_by_judge_id,
  ADD COLUMN command_performance_reason TEXT NULL AFTER mark_command_by_judge_id;
