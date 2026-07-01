<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <style>
        .btn-silver-print {
            color: #2b2f36 !important;
            background: linear-gradient(180deg, #f6f8fb 0%, #d9dee6 100%) !important;
            border-color: #b9c0cb !important;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
        }
        .btn-silver-print:hover,
        .btn-silver-print:focus,
        .btn-silver-print:active {
            color: #1f2329 !important;
            background: linear-gradient(180deg, #e9edf3 0%, #c7cfdb 100%) !important;
            border-color: #a6afbd !important;
        }

        .btn-golden-print {
            color: #4d3a06 !important;
            background: linear-gradient(180deg, #fff4c4 0%, #e7c65a 100%) !important;
            border-color: #c9a63e !important;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
        }
        .btn-golden-print:hover,
        .btn-golden-print:focus,
        .btn-golden-print:active {
            color: #3f2e05 !important;
            background: linear-gradient(180deg, #ffeeb0 0%, #d9b64d 100%) !important;
            border-color: #b99636 !important;
        }
    </style>

    <section class="content-header">
      <h1>
            Scripture Reading List - <?php echo $conventionD->name; ?> - <?php echo $conventionSD->season_year; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
          <li class="active">Scripture Reading List <?php echo $conventionSD->season_year; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-body">
                <div style="margin-bottom: 12px;">
                    <?php echo $this->Html->link('<i class="fa fa-print"></i> Print Silver Apple List', ['controller' => 'conventions', 'action' => 'scripturereadinglistprint', $slug_convention_season, $slug_convention], ['escape' => false, 'class' => 'btn btn-default btn-silver-print', 'target' => '_blank']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-print"></i> Print Golden Awards List', ['controller' => 'conventions', 'action' => 'goldenawardslistprint', $slug_convention_season, $slug_convention], ['escape' => false, 'class' => 'btn btn-default btn-golden-print', 'target' => '_blank', 'style' => 'margin-left: 8px;']); ?>
                </div>
                <?php if (!empty($readingListRows)) { ?>
                    <div class="table-responsive">
                        <table id="scriptureReadingListTable" class="table table-bordered table-striped table-condensed cf">
                            <thead>
                                <tr>
                                    <th style="width:70px;">No.</th>
                                    <th>Student</th>
                                    <th id="sortEventId" style="cursor:pointer; user-select:none;">
                                        Event ID
                                    </th>
                                    <th id="sortBookSubmitted" style="cursor:pointer; user-select:none;">
                                        Book Submitted
                                    </th>
                                    <th id="sortPlace" style="cursor:pointer; user-select:none;">
                                        Place
                                    </th>
                                    <th id="sortSchool" style="cursor:pointer; user-select:none;">
                                        School
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rowNo = 1; ?>
                                <?php foreach ($readingListRows as $row) { ?>
                                    <tr>
                                        <td><?php echo $rowNo; ?></td>
                                        <td><?php echo h($row['student_name']); ?></td>
                                        <td><?php echo h($row['event_ids']); ?></td>
                                        <td><?php echo h($row['book_names']); ?></td>
                                        <td><?php echo h($row['place']); ?></td>
                                        <td><?php echo h($row['school_name']); ?></td>
                                    </tr>
                                    <?php $rowNo++; ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info" style="margin-bottom:0;">
                        No scripture reading submissions found for this season.
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
(function () {
    var table = document.getElementById('scriptureReadingListTable');
    var sortEventIdHeader = document.getElementById('sortEventId');
    var sortBookHeader = document.getElementById('sortBookSubmitted');
    var sortPlaceHeader = document.getElementById('sortPlace');
    var sortSchoolHeader = document.getElementById('sortSchool');
    if (!table || !sortEventIdHeader || !sortBookHeader || !sortPlaceHeader || !sortSchoolHeader) {
        return;
    }

    var sortState = {
        eventIdAsc: true,
        bookAsc: true,
        placeAsc: true,
        schoolAsc: true
    };

    function renumberRows() {
        var rows = table.tBodies[0].rows;
        for (var i = 0; i < rows.length; i++) {
            rows[i].cells[0].textContent = (i + 1).toString();
        }
    }

    function sortByColumn(columnIndex, isAsc, compareAsText) {
        var tbody = table.tBodies[0];
        var rows = Array.prototype.slice.call(tbody.rows);

        function parsePlaceValue(text) {
            var raw = (text || '').toLowerCase().trim();
            if (raw === '' || raw === '-') {
                return Number.POSITIVE_INFINITY;
            }

            var firstNumberMatch = raw.match(/\d+/);
            if (firstNumberMatch) {
                return parseInt(firstNumberMatch[0], 10);
            }

            return Number.POSITIVE_INFINITY;
        }

        rows.sort(function (a, b) {
            var aText = (a.cells[columnIndex].textContent || '').toLowerCase().trim();
            var bText = (b.cells[columnIndex].textContent || '').toLowerCase().trim();

            if (columnIndex === 4) {
                var aPlace = parsePlaceValue(aText);
                var bPlace = parsePlaceValue(bText);

                if (aPlace !== bPlace) {
                    return isAsc ? (aPlace - bPlace) : (bPlace - aPlace);
                }

                return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            }

            if (!compareAsText) {
                return isAsc
                    ? aText.localeCompare(bText, undefined, {numeric: true, sensitivity: 'base'})
                    : bText.localeCompare(aText, undefined, {numeric: true, sensitivity: 'base'});
            }

            return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });

        for (var i = 0; i < rows.length; i++) {
            tbody.appendChild(rows[i]);
        }

        renumberRows();
    }

    sortEventIdHeader.addEventListener('click', function () {
        sortByColumn(2, sortState.eventIdAsc, false);
        sortState.eventIdAsc = !sortState.eventIdAsc;
    });

    sortBookHeader.addEventListener('click', function () {
        sortByColumn(3, sortState.bookAsc, true);
        sortState.bookAsc = !sortState.bookAsc;
    });

    sortPlaceHeader.addEventListener('click', function () {
        sortByColumn(4, sortState.placeAsc, false);
        sortState.placeAsc = !sortState.placeAsc;
    });

    sortSchoolHeader.addEventListener('click', function () {
        sortByColumn(5, sortState.schoolAsc, true);
        sortState.schoolAsc = !sortState.schoolAsc;
    });
})();
</script>
