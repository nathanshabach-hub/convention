<?php
use Cake\ORM\TableRegistry;

$this->Eventcategories = TableRegistry::getTableLocator()->get('Eventcategories');
$this->Divisions = TableRegistry::getTableLocator()->get('Divisions');
$this->Events = TableRegistry::getTableLocator()->get('Events');

$studentGender = strtoupper((string)$checkCRS->Students['gender']);
$isMale = strpos($studentGender, 'M') === 0;
$isFemale = strpos($studentGender, 'F') === 0;
$isSmallConvention = isset($convention_type) && (int)$convention_type === 3;

$arrConvSeasonEventsCatsImplode = implode(",", $arrConvSeasonEventsCats);
$condEvCats = ["(Eventcategories.id IN ($arrConvSeasonEventsCatsImplode) )"];
$eventCatList = $this->Eventcategories->find()->where($condEvCats)->order(['Eventcategories.name' => 'ASC'])->all()->toList();

$categoryPriority = [
	'Academics' => 1,
	'Exhibits' => 2,
	'Music' => 3,
	'Physical Education' => 4,
	'Platform' => 5,
	'Scripture' => 6,
];

usort($eventCatList, function ($a, $b) use ($categoryPriority) {
	$ap = $categoryPriority[$a->name] ?? 999;
	$bp = $categoryPriority[$b->name] ?? 999;
	if ($ap === $bp) {
		return strcmp($a->name, $b->name);
	}
	return $ap <=> $bp;
});

$categoryColumns = [[], [], []];
if ($isSmallConvention) {
	$musicCategory = null;
	$physicalCategory = null;
	$platformCategory = null;
	$remainingCategories = [];

	foreach ($eventCatList as $eventCategory) {
		$categoryName = strtolower(trim((string)$eventCategory->name));
		if ($categoryName === 'music') {
			$musicCategory = $eventCategory;
			continue;
		}
		if ($categoryName === 'physical education') {
			$physicalCategory = $eventCategory;
			continue;
		}
		if ($categoryName === 'platform') {
			$platformCategory = $eventCategory;
			continue;
		}
		$remainingCategories[] = $eventCategory;
	}

	$categoryColumns = [[], []];
	if ($musicCategory) {
		$categoryColumns[0][] = $musicCategory;
	}
	if ($physicalCategory) {
		$categoryColumns[1][] = $physicalCategory;
	}
	if ($platformCategory) {
		$categoryColumns[1][] = $platformCategory;
	}

	foreach ($remainingCategories as $index => $eventCategory) {
		$categoryColumns[$index % 2][] = $eventCategory;
	}
} else {
	foreach ($eventCatList as $index => $eventCategory) {
		$categoryColumns[$index % 3][] = $eventCategory;
	}

	// Keep Platform directly under Physical Education for easier scanning.
	$physicalColumnIndex = null;
	$physicalItemIndex = null;
	$platformColumnIndex = null;
	$platformItemIndex = null;

	foreach ($categoryColumns as $colIndex => $categories) {
		foreach ($categories as $itemIndex => $category) {
			if (strcasecmp((string)$category->name, 'Physical Education') === 0) {
				$physicalColumnIndex = $colIndex;
				$physicalItemIndex = $itemIndex;
			}
			if (strcasecmp((string)$category->name, 'Platform') === 0) {
				$platformColumnIndex = $colIndex;
				$platformItemIndex = $itemIndex;
			}
		}
	}

	if ($physicalColumnIndex !== null && $platformColumnIndex !== null) {
		$platformCategory = $categoryColumns[$platformColumnIndex][$platformItemIndex];
		array_splice($categoryColumns[$platformColumnIndex], $platformItemIndex, 1);

		if ($platformColumnIndex === $physicalColumnIndex && $platformItemIndex < $physicalItemIndex) {
			$physicalItemIndex--;
		}

		array_splice($categoryColumns[$physicalColumnIndex], $physicalItemIndex + 1, 0, [$platformCategory]);
	}
}
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary mb-3']); ?>

			<div class="pdf-form-shell">
				<div class="pdf-form-head">EVENT REGISTRATION FORM</div>

				<div class="pdf-meta-grid">
					<div><strong>First Name*</strong><span><?php echo h($checkCRS->Students['first_name']); ?></span></div>
					<div><strong>Middle Name(s)</strong><span><?php echo h($checkCRS->Students['middle_name']); ?></span></div>
					<div><strong>Surname*</strong><span><?php echo h($checkCRS->Students['last_name']); ?></span></div>
					<div><strong>School/HSSP</strong><span><?php echo h($checkCRS->Users['first_name']); ?></span></div>
					<div><strong>Year of Birth</strong><span><?php echo h($checkCRS->Students['birth_year']); ?></span></div>
					<div><strong>Gender</strong>
						<span class="gender-ticks">
							<label><input type="checkbox" disabled <?php echo $isMale ? 'checked' : ''; ?> /> Male</label>
							<label><input type="checkbox" disabled <?php echo $isFemale ? 'checked' : ''; ?> /> Female</label>
						</span>
					</div>
				</div>

				<div class="pdf-counter-row">
					<div><strong>Total Number of Events Entered:</strong> <span id="live_event_counter"><?php echo $liveEventsCounter; ?></span>/<?php echo $minMaxEventsArr['max_events_student']; ?></div>
					<div><input id="filter" type="text" data-type="search" class="form-control" placeholder="Type to search event number or name"></div>
				</div>

				<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudentevent', 'type' => 'file', 'class' => '']); ?>

				<div id="event_blocks" class="category-columns <?php echo $isSmallConvention ? 'small-convention-grid' : ''; ?>">
					<?php foreach ($categoryColumns as $columnCategories): ?>
					<div class="category-column">
						<?php foreach ($columnCategories as $eventcat): ?>
						<div class="category-block">
							<?php $categoryBodyId = 'category_body_' . (int)$eventcat->id; ?>
							<button type="button" class="category-title accordion-trigger is-open" data-target="<?php echo h($categoryBodyId); ?>" aria-expanded="true">
								<span class="title-text"><?php echo strtoupper(h($eventcat->name)); ?></span>
								<span class="title-badges">
									<span class="title-meta"><?php echo $eventcat->max_events; ?> max</span>
									<span class="accordion-icon">−</span>
								</span>
							</button>
							<div id="<?php echo h($categoryBodyId); ?>" class="category-content">

						<?php
						$arrConvSeasonEventsDivsImplode = implode(",", $arrConvSeasonEventsDivs);
						$condEvDivs = [
							"(Divisions.eventcategory_id = '" . $eventcat->id . "')",
							"(Divisions.id IN ($arrConvSeasonEventsDivsImplode) )"
						];
						$eventDivList = $this->Divisions->find()->where($condEvDivs)->order(['Divisions.sort_order' => 'ASC'])->all();
						foreach ($eventDivList as $eventdiv):
						$showDivisionHeading = strcasecmp(trim((string)$eventdiv->name), trim((string)$eventcat->name)) !== 0;
						$divisionBodyId = 'division_body_' . (int)$eventcat->id . '_' . (int)$eventdiv->id;
						?>
						<?php if ($showDivisionHeading): ?>
						<div class="division-block">
							<button type="button" class="division-name accordion-trigger division-trigger is-open" data-target="<?php echo h($divisionBodyId); ?>" aria-expanded="true">
								<span class="title-text"><?php echo strtoupper(h($eventdiv->name)); ?></span>
								<span class="title-badges">
									<span class="title-meta"><?php echo $eventdiv->max_events; ?> max</span>
									<span class="accordion-icon small">−</span>
								</span>
							</button>
							<div id="<?php echo h($divisionBodyId); ?>" class="division-content">
						<?php endif; ?>
						<table class="table table-bordered mini-event-table">
							<thead>
								<tr>
									<th class="col-name">Event Name</th>
									<th class="col-select">Select</th>
								</tr>
							</thead>
							<tbody>
								<?php
								$arrConvSeasonEventsListImplode = implode(",", $arrConvSeasonEventsList);
								$condEvents = [
									"(Events.division_id = '" . $eventdiv->id . "')",
									"(Events.id IN ($arrConvSeasonEventsListImplode) )"
								];
								$eventList = $this->Events->find()->where($condEvents)->order(['Events.event_name' => 'ASC'])->all();
								foreach ($eventList as $event):
								$searchText = strtolower(trim((string)$event->event_id_number . ' ' . (string)$event->event_name));
								?>
								<tr class="event-entry" data-search="<?php echo h($searchText); ?>">

									<td class="col-name"><span class="event-code"><?php echo '(' . h($event->event_id_number) . ')'; ?></span> <span class="event-label"><?php echo h($event->event_name); ?></span></td>
									<td class="col-select">
										<label class="tick-wrap" for="event_id_<?php echo $event->id; ?>">
											<input class="event-checkbox" type="checkbox" name="eventIDS[]" value="<?php echo $event->id; ?>" id="event_id_<?php echo $event->id; ?>" <?php echo in_array($event->id, (array)$selectedEvents) ? 'checked' : ''; ?> <?php if ($regAccepted == 0) echo 'disabled'; ?> />
											<span class="tick-circle"></span>
										</label>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php if ($showDivisionHeading): ?>
							</div>
						</div>
						<?php endif; ?>
						<?php endforeach; ?>
							</div>
					</div>
						<?php endforeach; ?>
					</div>
					<?php endforeach; ?>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary" <?php if ($regAccepted == 0) echo 'disabled'; ?>>Save Event Registration</button>
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary']); ?>
				</div>

				<?php echo $this->Form->end(); ?>
			</div>
		</main>
	</div>
</div>

<!-- Validation Error Modal -->
<div class="modal fade" id="validationErrorModal" tabindex="-1" aria-labelledby="validationErrorLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="validationErrorLabel">Event Selection Error</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<!-- Error message will be inserted here -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>

<style>
:root {
	--ui-surface: #ffffff;
	--ui-border: #dce4ef;
	--ui-border-strong: #c3cfdf;
	--ui-text: #172235;
	--ui-accent: #1b5fa7;
	--ui-accent-soft: #e6f1ff;
	--ui-selected: #fff8dd;
}

.pdf-form-shell {
	background: linear-gradient(180deg, #f9fbff 0%, #f4f8ff 100%);
	border: 1px solid var(--ui-border-strong);
	border-radius: 12px;
	padding: 16px;
	margin-bottom: 25px;
	font-family: "Segoe UI", "Trebuchet MS", Verdana, sans-serif;
	color: var(--ui-text);
}

.pdf-form-head {
	font-size: 24px;
	font-weight: 800;
	text-align: left;
	letter-spacing: 0.4px;
	margin-bottom: 10px;
}

.pdf-meta-grid {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 8px;
	margin-bottom: 10px;
}

.pdf-meta-grid > div {
	border: 1px solid var(--ui-border);
	border-radius: 10px;
	background: var(--ui-surface);
	padding: 10px 12px;
	min-height: 64px;
}

.pdf-meta-grid strong {
	display: block;
	font-size: 13px;
	margin-bottom: 4px;
	text-transform: uppercase;
}

.pdf-meta-grid span {
	font-size: 18px;
	line-height: 1.35;
}

.gender-ticks {
	display: flex;
	gap: 14px;
	font-size: 13px;
}

.pdf-counter-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 12px;
	margin-bottom: 14px;
	font-size: 15px;
	padding: 10px;
	border: 1px solid var(--ui-border);
	border-radius: 10px;
	background: var(--ui-surface);
	position: sticky;
	top: 8px;
	z-index: 5;
}

.pdf-counter-row .form-control {
	width: 300px;
	height: 40px;
	font-size: 15px;
	border: 1px solid var(--ui-border-strong);
	border-radius: 8px;
	background: #fff;
	box-shadow: none;
}

.category-columns {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 10px;
	align-items: start;
}

.category-columns.small-convention-grid {
	grid-template-columns: repeat(2, minmax(0, 1fr));
}

.category-column {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.category-block {
	border: 1px solid var(--ui-border);
	border-radius: 12px;
	padding: 8px;
	background: var(--ui-surface);
	box-shadow: 0 1px 4px rgba(20, 32, 52, 0.05);
	width: 100%;
}

.category-title {
	width: 100%;
	font-size: 15px;
	font-weight: 700;
	border: 1px solid #dde7f5;
	border-radius: 8px;
	padding: 8px 10px;
	margin-bottom: 6px;
	background: linear-gradient(90deg, #f2f7ff 0%, #f9fcff 100%);
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	text-align: left;
}

.category-content {
	display: block;
}

.title-badges {
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.accordion-icon {
	font-size: 16px;
	font-weight: 700;
	line-height: 1;
	color: var(--ui-accent);
	min-width: 12px;
	text-align: center;
}

.accordion-icon.small {
	font-size: 14px;
}

.division-name {
	width: 100%;
	font-size: 13px;
	font-weight: 700;
	margin: 8px 0 4px;
	text-transform: uppercase;
	color: var(--ui-text);
	padding: 6px 8px;
	border-left: 4px solid #b5c8e2;
	background: #f7fafe;
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	text-align: left;
}

.division-content {
	display: block;
}

.title-meta {
	font-size: 12px;
	font-weight: 700;
	text-transform: none;
	padding: 3px 9px;
	border-radius: 999px;
	background: var(--ui-accent-soft);
	color: var(--ui-accent);
}

.mini-event-table {
	width: 100%;
	table-layout: fixed;
	border-collapse: collapse;
	margin-bottom: 8px;
}


.mini-event-table td,
.mini-event-table th {
	border: 1px solid var(--ui-border);
	padding: 7px 8px;
	font-size: 13px;
	line-height: 1.35;
}

.event-entry {
	cursor: pointer;
}

.mini-event-table tbody tr:nth-child(even) td {
	background: #fbfdff;
}

.mini-event-table tbody tr:hover td {
	background: #f3f8ff;
}

.event-entry.is-selected td.col-name {
	background: var(--ui-selected) !important;
	font-weight: 700;
	box-shadow: inset 3px 0 0 #d6b23c;
}

.event-entry td {
	transition: background-color 0.12s ease-in-out;
}

.mini-event-table th {
	font-weight: 700;
	background: #eef3fa;
	position: sticky;
	top: 54px;
	z-index: 2;
}

.col-select {
	text-align: center;
	vertical-align: middle;
	width: 68px;
}

.col-name {
	font-size: 13px;
	line-height: 1.35;
}

.event-code {
	display: inline-block;
	font-weight: 700;
	color: var(--ui-accent);
	min-width: 62px;
}

.event-label {
	color: var(--ui-text);
}

.form-btns .btn {
	border-radius: 8px;
	border: 1px solid var(--ui-border-strong);
}

.tick-wrap {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	margin: 0;
	position: relative;
}

.tick-wrap input[type="checkbox"] {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}

.tick-circle {
	width: 16px;
	height: 16px;
	border: 2px solid #335b8d;
	border-radius: 50%;
	background: #fff;
	position: relative;
}

.tick-wrap input[type="checkbox"]:checked + .tick-circle::after {
	content: '';
	position: absolute;
	top: 3px;
	left: 3px;
	width: 6px;
	height: 6px;
	border-radius: 50%;
	background: #113f70;
}

@media (max-width: 1200px) {
	.category-columns {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 992px) {
	.pdf-meta-grid {
		grid-template-columns: 1fr;
	}

	.pdf-counter-row {
		flex-direction: column;
		align-items: flex-start;
	}

	.pdf-counter-row .form-control {
		width: 100%;
	}

	.category-columns {
		grid-template-columns: 1fr;
	}
}
</style>

<script>
$(document).ready(function() {
	function setAccordionState($trigger, shouldOpen) {
		var targetId = $trigger.data('target');
		if (!targetId) {
			return;
		}

		var $content = $('#' + targetId);
		$trigger.toggleClass('is-open', shouldOpen).attr('aria-expanded', shouldOpen ? 'true' : 'false');
		$trigger.find('.accordion-icon').text(shouldOpen ? '−' : '+');
		if (shouldOpen) {
			$content.stop(true, true).slideDown(120);
		} else {
			$content.stop(true, true).slideUp(120);
		}
	}

	$('#event_blocks').on('click', '.accordion-trigger', function(event) {
		event.preventDefault();
		setAccordionState($(this), !$(this).hasClass('is-open'));
	});

	function syncEventRowState(rowElement) {
		var $row = $(rowElement);
		var isChecked = $row.find('.event-checkbox').is(':checked');
		$row.toggleClass('is-selected', isChecked);
	}

	$('#event_blocks .event-entry').each(function() {
		syncEventRowState(this);
	});

	$('#event_blocks').on('click', '.event-entry', function(event) {
		if ($(event.target).closest('input, label, a, button').length) {
			return;
		}

		var $checkbox = $(this).find('.event-checkbox');
		if ($checkbox.length && !$checkbox.prop('disabled')) {
			$checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
		}
	});

	$('#event_blocks').on('change', '.event-checkbox', function(event) {
		syncEventRowState($(this).closest('.event-entry'));

		var lastCheckedEVID = $(this).val();
		event.preventDefault();

		var checkedEventIDS = $('#event_blocks .event-checkbox:checked').map(function() {
			return $(this).val();
		}).get();

		$.ajax({
			type: 'POST',
			url: "<?php echo $this->Url->build(['controller' => 'homes', 'action' => 'checkstudentevent', $checkCRS->slug]); ?>/" + checkedEventIDS + "/" + lastCheckedEVID,
			cache: false,
			success: function(result) {
				var objReturned = $.parseJSON(result);
				var totalEvChecked = objReturned.totalEvChecked;

				if (objReturned.errorFlag) {
					// Show Bootstrap modal with error message instead of alert
					var errorMessage = objReturned.errorMsg.join('\n');
					$('#validationErrorModal .modal-body').text(errorMessage);
					var modal = new bootstrap.Modal(document.getElementById('validationErrorModal'));
					modal.show();
				}

				if (objReturned.discardLastEventSelected) {
					$('#event_id_' + objReturned.lastEventIDChecked).prop('checked', false);
					syncEventRowState($('#event_id_' + objReturned.lastEventIDChecked).closest('.event-entry'));
					totalEvChecked = totalEvChecked - 1;
				}

				$('#live_event_counter').html(totalEvChecked);
			}
		});

		return false;
	});

	$('#filter').keyup(function() {
		var filter = $(this).val().toLowerCase();
		$('#event_blocks .event-entry').each(function() {
			var searchText = ($(this).data('search') || '').toString();
			if (searchText.indexOf(filter) === -1) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});
	});
});
</script>
<?php echo $this->element("jquery_datatable_code"); ?>