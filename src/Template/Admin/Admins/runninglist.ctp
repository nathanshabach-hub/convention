<?php
use Cake\ORM\TableRegistry;

$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>

<style>
#running_list_table th,
#running_list_table td {
    vertical-align: middle;
}

#running_list_table th.action-col,
#running_list_table td.action-col {
    width: 190px;
}

#running_list_table th.order-col,
#running_list_table td.order-col {
    width: 120px;
}

#running_list_table th.combine-col,
#running_list_table td.combine-col {
    width: 140px;
}

#running_list_table td.order-col {
    text-align: center;
}

#running_list_table td.combine-col {
    text-align: center;
}

.running-action-controls {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.running-action-controls .heat-size-input {
    width: 54px;
    height: 30px;
    text-align: center;
    padding: 4px;
}

.running-order-input {
    width: 54px;
    height: 30px;
    text-align: center;
    padding: 4px;
}

.running-combine-input {
    width: 90px;
    height: 30px;
    text-align: center;
    padding: 4px;
}

.running-action-controls .print-sheet-btn {
    height: 30px;
    padding: 4px 10px;
}

@media (max-width: 768px) {
    #running_list_table th.action-col,
    #running_list_table td.action-col {
        width: 165px;
    }

    #running_list_table th.order-col,
    #running_list_table td.order-col {
        width: 95px;
    }

    #running_list_table th.combine-col,
    #running_list_table td.combine-col {
        width: 110px;
    }

    .running-action-controls {
        gap: 4px;
    }

    .running-action-controls .print-sheet-btn {
        padding: 4px 8px;
    }
}
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Running List
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li class="active"> Running List </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Season Event Running List</h3>
            </div>

            <div class="box-body">
                <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

                <?php if (!$conventionseasonevents->isEmpty()) : ?>
                    <div class="table-responsive">
                        <table id="running_list_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Event Name</th>
                                    <th>Event ID Number</th>
                                    <th>Entries</th>
                                    <th class="order-col">Running Order</th>
                                    <th class="combine-col">Combine Group</th>
                                    <th class="action-col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conventionseasonevents as $datarecord) { ?>
                                    <?php
                                    $condEVT = array();
                                    $condEVT[] = "(Eventsubmissions.conventionseason_id = '" . $convSeasonD->id . "' AND Eventsubmissions.event_id = '" . $datarecord->event_id . "' AND Eventsubmissions.event_id_number = '" . $datarecord->Events['event_id_number'] . "')";
                                    $condEVT[] = "(Eventsubmissions.convention_id = '" . $convSeasonD->convention_id . "' AND Eventsubmissions.season_id = '" . $convSeasonD->season_id . "' AND Eventsubmissions.season_year = '" . $convSeasonD->season_year . "')";
                                    $event_entries = $this->Eventsubmissions->find()->where($condEVT)->count();
                                    ?>
                                    <tr>
                                        <td><?php echo $datarecord->event_id; ?></td>
                                        <td><?php echo $datarecord->Events['event_name']; ?></td>
                                        <td><?php echo $datarecord->Events['event_id_number']; ?></td>
                                        <td><?php echo $event_entries; ?></td>
                                        <td class="order-col">
                                            <input type="number" class="form-control input-sm running-order-input order-input" value="<?php echo isset($datarecord->order) ? $datarecord->order : ''; ?>" min="1" max="99" title="Running Order">
                                        </td>
                                        <td class="combine-col">
                                            <input type="text" class="form-control input-sm running-combine-input combine-input" value="" maxlength="20" placeholder="e.g. G1" title="Events with the same group are combined into one race in Print All. Use G1, G2, G3 etc.">
                                        </td>
                                        <td class="action-col">
                                            <div class="running-action-controls">
                                                <input type="number" class="form-control input-sm heat-size-input" value="<?php echo $event_entries > 0 ? $event_entries : 0; ?>" min="0" max="99" title="Runners per heat">
                                                <button class="btn btn-xs btn-primary print-sheet-btn"
                                                    data-cse-id="<?php echo (int)$datarecord->id; ?>"
                                                    data-url="<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'runninglistprint', $datarecord->id]); ?>">
                                                    Preview Sheet
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">No running list records found for the selected season.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Sort Action column using the numeric value from the heat-size input.
    $.fn.dataTable.ext.order['dom-heat-size'] = function(settings, col) {
        return this.api().column(col, {order: 'index'}).nodes().map(function(td) {
            var value = $('input.heat-size-input', td).val();
            var parsed = parseFloat(value);
            return isNaN(parsed) ? -1 : parsed;
        });
    };

    var runningListTable = $('#running_list_table').DataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "pageLength": 100,
        order: [[0, 'desc']],
        "columnDefs": [
            { "targets": 6, "orderDataType": 'dom-heat-size' }
        ],
        "dom": '<"row"<"col-sm-6 print-all-slot"><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
        "initComplete": function() {
            var printAllHtml = '<button id="print_all_btn" class="btn btn-sm btn-success" data-url="<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'runninglistprintall']); ?>">' +
                '<i class="fa fa-print"></i> Print All Sheets</button> ' +
                '<button id="download_csv_btn" class="btn btn-sm btn-info" data-url="<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'runninglistcsv']); ?>">' +
                '<i class="fa fa-download"></i> Download CSV</button>';
            $('#running_list_table_wrapper .print-all-slot').html(printAllHtml);
        }
    });

    function buildRunningListQueryString() {
        var heatMap = {};
        var orderMap = {};
        var combineMap = {};
        var eventRows = [];

        runningListTable.rows().nodes().each(function(row) {
            var $row = $(row);
            var cseId = $row.find('.print-sheet-btn').data('cse-id');
            var heatSize = $row.find('.heat-size-input').val();
            var order = $row.find('.order-input').val();
            var combineValue = String($row.find('.combine-input').val() || '').trim();
            var normalizedOrder = parseInt(order, 10);

            if (cseId && heatSize) {
                heatMap[cseId] = heatSize;
            }
            if (cseId && order) {
                orderMap[cseId] = order;
            }
            if (cseId && combineValue !== '') {
                combineMap[cseId] = combineValue;
            }

            if (cseId) {
                eventRows.push({
                    cseId: String(cseId),
                    order: isFinite(normalizedOrder) && normalizedOrder > 0 ? normalizedOrder : Number.MAX_SAFE_INTEGER
                });
            }
        });

        eventRows.sort(function(left, right) {
            if (left.order !== right.order) {
                return left.order - right.order;
            }

            return parseInt(left.cseId, 10) - parseInt(right.cseId, 10);
        });

        var eventOrder = eventRows.map(function(row) {
            return row.cseId;
        });

        return $.param({
            heatmap: heatMap,
            ordermap: orderMap,
            eventorder: eventOrder,
            combinemap: combineMap
        });
    }

    $(document).on('click', '.print-sheet-btn', function() {
        var $btn = $(this);
        var heatSize = parseInt($btn.closest('.running-action-controls').find('.heat-size-input').val()) || 6;
        var url = $btn.data('url') + '/' + heatSize;
        window.open(url, '_blank');
    });

    $(document).on('click', '#print_all_btn', function() {
        var queryString = buildRunningListQueryString();
        window.open('<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'runninglistprintall']); ?>?' + queryString, '_blank');
    });

    $(document).on('click', '#download_csv_btn', function() {
        var queryString = buildRunningListQueryString();
        window.open('<?php echo $this->Url->build(['controller' => 'admins', 'action' => 'runninglistcsv']); ?>?' + queryString, '_blank');
    });
});
</script>

<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
