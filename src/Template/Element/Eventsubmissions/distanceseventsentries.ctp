<?php
use Cake\ORM\TableRegistry;
$this->Users            = TableRegistry::get('Users');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
$this->Crstudentevents  = TableRegistry::get('Crstudentevents');
$kickDistances          = [10,15,20,25,30,35,40,45,50];
$soccerKickDistanceEventNumbers = ['112', '142', '172', '212', '242', '272'];
$isSoccerKickDistanceEvent = in_array((string)($eventD->event_id_number ?? ''), $soccerKickDistanceEventNumbers, true);
$formatEntryDate = function ($value) {
  if ($value instanceof \DateTimeInterface) {
    return $value->format('M d, Y');
  }

  $value = trim((string)$value);
  if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
    return '-';
  }

  $timestamp = strtotime($value);
  if ($timestamp === false || $timestamp <= 0) {
    return '-';
  }

  return date('M d, Y', $timestamp);
};
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>

<style>
/* ─── Soccer Kick Scoresheet ─────────────────────────────── */
.sk-wrap      { overflow-x:auto; padding-bottom:20px; }
.sk-legend    { font-size:12px; font-style:italic; color:#444; margin-bottom:10px; }
.sk-legend span { margin-right:18px; }

.sk-table     { border-collapse:collapse; font-size:12px; width:100%; min-width:900px; table-layout:fixed; }
.sk-table th,
.sk-table td  { border:1px solid #bbb; padding:4px 6px; text-align:center; vertical-align:middle; }

/* header rows */
.sk-th-group  { background:#f0f2f8; font-weight:700; color:#1c2452; font-size:11px; padding:6px 4px; }
.sk-th-kp     { background:#e8eaf4; font-style:italic; font-weight:700; color:#1c2452; font-size:12.5px; letter-spacing:.5px; }
.sk-th-dist   { background:#f5f6fb; font-weight:700; font-size:11px; color:#333; padding:4px 2px; min-width:56px; width:56px; }
.sk-th-att    { background:#fafafa; font-size:9.5px; color:#777; font-weight:400; padding:2px; }

/* data cells */
.sk-td-name   { text-align:left; width:160px; min-width:160px; max-width:160px; font-weight:600; color:#222; white-space:normal; word-break:break-word; }
.sk-td-school { text-align:left; width:150px; min-width:150px; max-width:150px; color:#444; white-space:normal; word-break:break-word; }
.sk-td-date   { width:82px; min-width:82px; max-width:82px; color:#666; font-size:11px; white-space:nowrap; }
.sk-dist-cell { padding:3px 2px !important; width:56px; min-width:56px; }

/* attempt toggle buttons inside each distance cell */
.sk-att-btns  { display:flex; flex-direction:column; gap:2px; align-items:center; }
.sk-att-btn   { display:inline-flex; align-items:center; justify-content:center;
                width:26px; height:20px; border:1px solid #ccc; border-radius:3px;
                font-size:10px; font-weight:700; cursor:pointer;
                background:#f8f8f8; color:#666; user-select:none; transition:.1s; }
.sk-att-btn:hover           { background:#e8eaf4; border-color:#1c2452; color:#1c2452; }
.sk-att-btn.sk-checked { background:#27ae60; border-color:#1e8449; color:#fff; font-family:Arial,sans-serif; }
.sk-att-btn.sk-checked::after { content:' \2713'; font-family:Arial,sans-serif; }
.sk-att-cb   { display:none; }

/* best / attempt display */
.sk-best-val  { font-weight:700; font-size:14px; color:#1c2452; display:block; }
.sk-att-val   { font-size:13px; font-weight:700; color:#e67e22; display:block; }
.sk-best-cell { min-width:68px; }

/* place */
.sk-place-inp { width:46px; border:1px solid #aaa; border-radius:3px;
                text-align:center; font-size:12px; padding:3px 2px; }

/* withdrawn */
.sk-with-cell { background:#fffde7; }
.sk-with-cb   { width:18px; height:18px; cursor:pointer; }

/* row shading */
.sk-table tbody tr:nth-child(odd)  td { background:#fff; }
.sk-table tbody tr:nth-child(even) td { background:#f9f9ff; }
.sk-table tbody tr:hover           td { background:#eef2fb !important; }

/* submit row */
.sk-submit-row td { border:none !important; padding-top:14px; text-align:left; }

/* standard distance sheet */
.dist-legend { font-size:12px; font-style:italic; color:#444; margin-bottom:10px; }
.dist-wrap { overflow-x:auto; padding-bottom:20px; }
.dist-table { border-collapse:collapse; font-size:12px; width:100%; min-width:900px; table-layout:fixed; }
.dist-table th,
.dist-table td { border:1px solid #bbb; padding:6px; text-align:center; vertical-align:middle; }
.dist-table tbody tr:nth-child(odd) td { background:#fff; }
.dist-table tbody tr:nth-child(even) td { background:#f9f9ff; }
.dist-table tbody tr:hover td { background:#eef2fb !important; }
.dist-th-group { background:#f0f2f8; font-weight:700; color:#1c2452; }
.dist-th-attempt { background:#f5f6fb; font-weight:700; color:#333; }
.dist-name { text-align:left; width:170px; font-weight:600; white-space:normal; word-break:break-word; }
.dist-school { text-align:left; width:160px; color:#444; white-space:normal; word-break:break-word; }
.dist-date { width:82px; color:#666; font-size:11px; white-space:nowrap; }
.dist-attempt-input { width:78px; border:1px solid #aaa; border-radius:3px; padding:4px 6px; text-align:center; }
.dist-best-val { font-weight:700; font-size:14px; color:#1c2452; display:block; }
</style>

<?php if (!$eventsubmissions->isEmpty()): ?>
<div class="panel-body">
  <?php echo $this->Form->create(null, ['id'=>'actionFrom','method'=>'Post']); ?>
  <section class="lstng-section">

    <?php if ($isSoccerKickDistanceEvent): ?>

    <p class="sk-legend">
      <span>Marking guide:</span>
      <span><span style="display:inline-block;background:#27ae60;color:#fff;border-radius:3px;padding:1px 6px;font-size:11px;">✓</span> = Successful Attempt</span>
      <span>Unchecked = Missed / Not attempted</span>
      <span><strong>W</strong> = Withdrawn from event</span>
    </p>

    <div class="sk-wrap">
      <table class="sk-table">
        <thead>
          <tr>
            <th class="sk-th-group sk-td-name" rowspan="3"><?php echo ($eventD->group_event_yes_no==1)?'Group':'Student Name'; ?></th>
            <th class="sk-th-group sk-td-school" rowspan="3">School</th>
            <th class="sk-th-group sk-td-date" rowspan="3">Date</th>
            <th class="sk-th-kp" colspan="<?php echo count($kickDistances); ?>">Kicking Position</th>
            <th class="sk-th-group sk-best-cell" rowspan="3">Furthest<br>Distance<br>Kicked</th>
            <th class="sk-th-group" rowspan="3">Attempt<br>Kick<br>Achieved<br>(1, 2 or 3)</th>
            <th class="sk-th-group sk-with-cell" rowspan="3">W</th>
          </tr>
          <tr>
            <?php foreach($kickDistances as $dm): ?>
              <th class="sk-th-dist"><?php echo $dm; ?>m</th>
            <?php endforeach; ?>
          </tr>
          <tr>
            <?php foreach($kickDistances as $dm): ?>
              <th class="sk-th-att">Att<br>1&nbsp;2&nbsp;3</th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $cntrES = 0;
        foreach($eventsubmissions as $datarecord):
          $cntrES++;
          $judge_id = $this->request->session()->read("user_id");
          $jEval = $this->Judgeevaluations->find()->where([
            'Judgeevaluations.eventsubmission_id'  => $datarecord->id,
            'Judgeevaluations.uploaded_by_user_id' => $judge_id
          ])->first();

          $checkedW    = ($jEval && $jEval->withdraw_yes_no==1) ? 'checked' : '';
          $savedBest   = ($jEval) ? $jEval->soccer_kick_best_kick : null;
          $savedGrid   = ['1'=>[],'2'=>[],'3'=>[]];
          if($jEval && !empty($jEval->soccer_kick_all_kicks)) {
            $dec = json_decode($jEval->soccer_kick_all_kicks, true);
            if(is_array($dec)) $savedGrid = $dec;
          }
          $savedAttempt = null;
          if($savedBest) {
            foreach([1,2,3] as $a) {
              $k = (string)$a;
              if(isset($savedGrid[$k]) && in_array((int)$savedBest, array_map('intval',(array)$savedGrid[$k]))) {
                $savedAttempt = $a; break;
              }
            }
          }
        ?>
        <tr>
          <td class="sk-td-name">
          <?php if($eventD->group_event_yes_no==1):
            $cg = ["(Crstudentevents.conventionregistration_id='{$datarecord->conventionregistration_id}' AND Crstudentevents.event_id='{$datarecord->event_id}' AND Crstudentevents.group_name='{$datarecord->group_name}')"];
            $gm = $this->Crstudentevents->find()->where($cg)->contain(['Students'])->limit(4)->all();
            $mn = [];
            foreach($gm as $g) { $mn[] = trim($g->Students['first_name'].' '.$g->Students['middle_name'].' '.$g->Students['last_name']); }
            echo '<strong>'.h($datarecord->group_name).'</strong><br><small style="font-weight:400;color:#555;">'.h(implode(', ',$mn)).'</small>';
          else:
            echo h($datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name']);
          endif; ?>
          </td>
          <td class="sk-td-school"><?php echo h($datarecord->Users['first_name']); ?></td>
          <td class="sk-td-date"><?php echo h($formatEntryDate($datarecord->created ?? null)); ?></td>

          <?php foreach($kickDistances as $dm): ?>
          <td class="sk-dist-cell">
            <div class="sk-att-btns">
            <?php foreach([1,2,3] as $att):
              $k = (string)$att;
              $isCk = (isset($savedGrid[$k]) && in_array($dm, array_map('intval',(array)$savedGrid[$k])));
              $ckd  = $isCk ? 'checked' : '';
              $cls  = $isCk ? 'sk-att-btn sk-checked' : 'sk-att-btn';
            ?>
              <label class="<?php echo $cls; ?>" data-row="<?php echo $cntrES; ?>" data-att="<?php echo $att; ?>" data-dist="<?php echo $dm; ?>">
                <?php echo $att; ?>
                <input type="checkbox"
                  class="sk-att-cb kick-cb-<?php echo $cntrES; ?>-<?php echo $att; ?>"
                  name="kick_<?php echo $cntrES; ?>_<?php echo $att; ?>_<?php echo $dm; ?>"
                  data-row="<?php echo $cntrES; ?>"
                  data-att="<?php echo $att; ?>"
                  data-dist="<?php echo $dm; ?>"
                  <?php echo $ckd; ?>
                />
              </label>
            <?php endforeach; ?>
            </div>
          </td>
          <?php endforeach; ?>

          <td class="sk-best-cell">
            <span class="sk-best-val" id="best_dist_<?php echo $cntrES; ?>"><?php echo $savedBest ? $savedBest.'m' : '–'; ?></span>
            <input type="hidden" name="soccer_kick_best_kick_<?php echo $cntrES; ?>" id="hid_best_<?php echo $cntrES; ?>" value="<?php echo $savedBest; ?>" />
            <input type="hidden" name="soccer_kick_all_kicks_<?php echo $cntrES; ?>" id="hid_grid_<?php echo $cntrES; ?>" value="<?php echo h(json_encode($savedGrid)); ?>" />
          </td>
          <td>
            <span class="sk-att-val" id="best_att_<?php echo $cntrES; ?>"><?php echo $savedAttempt ? $savedAttempt : '–'; ?></span>
            <input type="hidden" name="best_attempt_<?php echo $cntrES; ?>" id="hid_att_<?php echo $cntrES; ?>" value="<?php echo $savedAttempt; ?>" />
            <input type="hidden" name="place_<?php echo $cntrES; ?>" value="" />
          </td>
          <td class="sk-with-cell">
            <input type="checkbox" class="sk-with-cb" name="withdrawn_<?php echo $cntrES; ?>" <?php echo $checkedW; ?> />
            <input type="hidden" name="submission_id_<?php echo $cntrES; ?>" value="<?php echo $datarecord->id; ?>" />
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="sk-submit-row">
            <td colspan="<?php echo 3 + count($kickDistances) + 3; ?>">
              <input type="submit" value="Submit Scores" class="btn btn-primary btn-sm" />
              <input type="hidden" name="total_records" id="total_records" value="<?php echo $cntrES; ?>" />
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <?php else: ?>

    <p class="dist-legend">Enter the three measured attempts for each competitor. The best distance is calculated automatically.</p>

    <div class="dist-wrap">
      <table class="dist-table">
        <thead>
          <tr>
            <th class="dist-th-group dist-name"><?php echo ($eventD->group_event_yes_no==1)?'Group':'Student Name'; ?></th>
            <th class="dist-th-group dist-school">School</th>
            <th class="dist-th-group dist-date">Date</th>
            <th class="dist-th-attempt">Attempt 1</th>
            <th class="dist-th-attempt">Attempt 2</th>
            <th class="dist-th-attempt">Attempt 3</th>
            <th class="dist-th-group">Best Distance</th>
            <th class="dist-th-group sk-with-cell">W</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $cntrES = 0;
        foreach($eventsubmissions as $datarecord):
          $cntrES++;
          $judge_id = $this->request->session()->read("user_id");
          $jEval = $this->Judgeevaluations->find()->where([
            'Judgeevaluations.eventsubmission_id'  => $datarecord->id,
            'Judgeevaluations.uploaded_by_user_id' => $judge_id
          ])->first();

          $checkedW = ($jEval && $jEval->withdraw_yes_no==1) ? 'checked' : '';
          $attempt1 = ($jEval && $jEval->distance_attempt_1 !== null && $jEval->distance_attempt_1 !== '') ? $jEval->distance_attempt_1 : '';
          $attempt2 = ($jEval && $jEval->distance_attempt_2 !== null && $jEval->distance_attempt_2 !== '') ? $jEval->distance_attempt_2 : '';
          $attempt3 = ($jEval && $jEval->distance_attempt_3 !== null && $jEval->distance_attempt_3 !== '') ? $jEval->distance_attempt_3 : '';
          $bestScore = ($jEval && $jEval->distance_score !== null && $jEval->distance_score !== '') ? $jEval->distance_score : '';
        ?>
        <tr>
          <td class="dist-name">
          <?php if($eventD->group_event_yes_no==1):
            $cg = ["(Crstudentevents.conventionregistration_id='{$datarecord->conventionregistration_id}' AND Crstudentevents.event_id='{$datarecord->event_id}' AND Crstudentevents.group_name='{$datarecord->group_name}')"];
            $gm = $this->Crstudentevents->find()->where($cg)->contain(['Students'])->limit(4)->all();
            $mn = [];
            foreach($gm as $g) { $mn[] = trim($g->Students['first_name'].' '.$g->Students['middle_name'].' '.$g->Students['last_name']); }
            echo '<strong>'.h($datarecord->group_name).'</strong><br><small style="font-weight:400;color:#555;">'.h(implode(', ',$mn)).'</small>';
          else:
            echo h($datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name']);
          endif; ?>
          </td>
          <td class="dist-school"><?php echo h($datarecord->Users['first_name']); ?></td>
          <td class="dist-date"><?php echo h($formatEntryDate($datarecord->created ?? null)); ?></td>
          <td>
            <input type="number" step="0.01" min="0" class="dist-attempt-input dist-attempt-row-<?php echo $cntrES; ?>" name="distance_attempt_1_<?php echo $cntrES; ?>" value="<?php echo h($attempt1); ?>" data-row="<?php echo $cntrES; ?>" />
          </td>
          <td>
            <input type="number" step="0.01" min="0" class="dist-attempt-input dist-attempt-row-<?php echo $cntrES; ?>" name="distance_attempt_2_<?php echo $cntrES; ?>" value="<?php echo h($attempt2); ?>" data-row="<?php echo $cntrES; ?>" />
          </td>
          <td>
            <input type="number" step="0.01" min="0" class="dist-attempt-input dist-attempt-row-<?php echo $cntrES; ?>" name="distance_attempt_3_<?php echo $cntrES; ?>" value="<?php echo h($attempt3); ?>" data-row="<?php echo $cntrES; ?>" />
          </td>
          <td>
            <span class="dist-best-val" id="distance_best_<?php echo $cntrES; ?>"><?php echo ($bestScore !== '') ? h($bestScore).'m' : '–'; ?></span>
            <input type="hidden" name="soccer_kick_best_kick_<?php echo $cntrES; ?>" value="" />
            <input type="hidden" name="soccer_kick_all_kicks_<?php echo $cntrES; ?>" value="" />
            <input type="hidden" name="place_<?php echo $cntrES; ?>" value="" />
          </td>
          <td class="sk-with-cell">
            <input type="checkbox" class="sk-with-cb" name="withdrawn_<?php echo $cntrES; ?>" <?php echo $checkedW; ?> />
            <input type="hidden" name="submission_id_<?php echo $cntrES; ?>" value="<?php echo $datarecord->id; ?>" />
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="sk-submit-row">
            <td colspan="8">
              <input type="submit" value="Submit Scores" class="btn btn-primary btn-sm" />
              <input type="hidden" name="total_records" id="total_records" value="<?php echo $cntrES; ?>" />
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <?php endif; ?>

  </section>
  <?php echo $this->Form->end(); ?>
</div>

<?php else: ?>
  <div class="admin_no_record">No record found.</div>
<?php endif; ?>

<script>
$(document).ready(function() {
  var totalRows = parseInt($('#total_records').val()) || 0;

  // Toggle button style on click
  $(document).on('click', '.sk-att-btn', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var $cb  = $btn.find('.sk-att-cb');
    var isNowChecked = !$cb.is(':checked');
    $cb.prop('checked', isNowChecked);
    $btn.toggleClass('sk-checked', isNowChecked);
    var ri = parseInt($btn.data('row'));
    recalcRow(ri);
  });

  function recalcRow(ri) {
    var bestDist = 0, bestAtt = null;
    var grid = {'1':[],'2':[],'3':[]};
    for (var att = 1; att <= 3; att++) {
      $('.kick-cb-' + ri + '-' + att + ':checked').each(function() {
        var d = parseInt($(this).data('dist'));
        grid[att].push(d);
        if (d > bestDist) { bestDist = d; bestAtt = att; }
      });
    }
    $('#best_dist_' + ri).text(bestDist > 0 ? bestDist + 'm' : '–');
    $('#best_att_'  + ri).text(bestAtt ? bestAtt : '–');
    $('#hid_best_'  + ri).val(bestDist > 0 ? bestDist : '');
    $('#hid_att_'   + ri).val(bestAtt ? bestAtt : '');
    $('#hid_grid_'  + ri).val(JSON.stringify(grid));
  }

  function recalcDistanceRow(ri) {
    var bestDist = null;
    $('.dist-attempt-row-' + ri).each(function() {
      var raw = $(this).val();
      if (raw === '') {
        return;
      }
      var value = parseFloat(raw);
      if (!isNaN(value) && (bestDist === null || value > bestDist)) {
        bestDist = value;
      }
    });
    $('#distance_best_' + ri).text(bestDist !== null ? bestDist + 'm' : '–');
  }

  $(document).on('input', '.dist-attempt-input', function() {
    var ri = parseInt($(this).data('row'), 10);
    recalcDistanceRow(ri);
  });

  for (var r = 1; r <= totalRows; r++) {
    recalcRow(r);
    recalcDistanceRow(r);
  }
});
</script>
