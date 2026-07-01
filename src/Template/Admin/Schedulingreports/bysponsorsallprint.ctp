<style>
	@media print {
		.page-break-before { page-break-before: always; break-before: page; }
	}
	.content-wrapper { margin: 0 !important; padding: 0 !important; min-height: 0 !important; }
	.content { padding: 0 !important; margin: 0 !important; }
</style>
<script type="text/javascript">
window.addEventListener('load', function() { window.print(); });
</script>

<?php
$allSponsorsData = $allSponsorsData ?? [];
$conventionSD    = $conventionSD ?? null;
$convention_season_slug = $convention_season_slug ?? '';
?>

<div class="content-wrapper">
	<section class="content">
		<?php foreach ($allSponsorsData as $i => $sponsorEntry):
			$sponsorD             = $sponsorEntry['sponsorD'];
			$sponsor_id           = $sponsorEntry['sponsor_id'];
			$schedulingTimingsList = $sponsorEntry['schedulingTimingsList'];
		?>
		<div class="<?php echo $i > 0 ? 'page-break-before' : ''; ?>">
			<?php echo $this->element('Admin/Schedulingreports/bysponsorsshow', [
				'sponsorD'             => $sponsorD,
				'sponsor_id'           => $sponsor_id,
				'schedulingTimingsList' => $schedulingTimingsList,
				'conventionSD'         => $conventionSD,
				'convention_season_slug' => $convention_season_slug,
			]); ?>
		</div>
		<?php endforeach; ?>
	</section>
</div>
