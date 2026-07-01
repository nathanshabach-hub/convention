<style>
	@media print {
		.page-break-after {
			page-break-after: always;
		}
	}
	.topn { display: none; }
	.content-wrapper { margin: 0 !important; padding: 0 !important; min-height: 0 !important; }
	.content { padding: 0 !important; margin: 0 !important; }
</style>
<script type="text/javascript">
window.addEventListener('load', function() {
    var PAGE_H      = 740;
    var HEADER_H    = 96;
    var BODY_PAD    = 24;  // top+bottom body padding in print
    var HEADING_H   = 22;
    var THEAD_H     = 20;
    var ROW_H       = 22;  // natural row height at 3px padding
    var ROUND_GAP   = 10;
    var FONT_LINE_H = 16;

    var allRounds = document.querySelectorAll('.sd-round');

    // Calculate natural total height for all rounds
    var naturalTotal = HEADER_H + BODY_PAD;
    allRounds.forEach(function(r) {
        var rows = r.querySelectorAll('tbody tr').length;
        naturalTotal += HEADING_H + THEAD_H + (rows * ROW_H) + ROUND_GAP;
    });

    // Only stretch Round 1 if content won't fit on one page naturally
    if (naturalTotal > PAGE_H) {
        var round1 = allRounds[0];
        if (round1) {
            var tbodyRows = round1.querySelectorAll('tbody tr');
            if (tbodyRows.length > 0) {
                var available = PAGE_H - HEADER_H - BODY_PAD - HEADING_H - THEAD_H - 10;
                var perRow = available / tbodyRows.length;
                var padPx = Math.max(3, Math.floor((perRow - FONT_LINE_H) / 2));
                for (var i = 0; i < tbodyRows.length; i++) {
                    var cells = tbodyRows[i].querySelectorAll('td');
                    for (var j = 0; j < cells.length; j++) {
                        cells[j].style.paddingTop = padPx + 'px';
                        cells[j].style.paddingBottom = padPx + 'px';
                    }
                }
            }
        }
    } else {
        // Everything fits on one page — suppress all page breaks between rounds
        document.querySelector('.sd-sheet').classList.add('sd-single-page');
    }
    window.print();
});
</script>

<?php
$eventD = $eventD ?? null;
$conventionSD = $conventionSD ?? null;
$eventName = $eventD ? (string)($eventD->event_name ?? '') : '';
$conventionName = $conventionSD ? (string)($conventionSD->Conventions['name'] ?? '') : '';
$seasonYear = $conventionSD ? (string)($conventionSD->season_year ?? '') : '';
?>

<div class="content-wrapper">
		<section class="content">
				<div class="m_content" id="listID">
						<?php echo $this->element('Admin/Schedulingreports/sportsdraw_bracket'); ?>
				</div>
		</section>
</div>