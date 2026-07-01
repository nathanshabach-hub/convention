<!DOCTYPE html>
<html>
<head>
    <title>Nametags - Visitors</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #fff;
        }
        .print-page {
            width: 297mm;
            min-height: 210mm;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(3, 90mm);
            grid-template-rows: repeat(4, 52.5mm);
            column-gap: 0;
            row-gap: 0;
            justify-content: center;
            align-content: start;
        }
        .print-page:last-child {
            min-height: 0;
        }
        .name-card {
            border: 1px solid #ccc;
            text-align: center;
            padding: 4mm 3mm;
            position: relative;
            width: 90mm;
            height: 52.5mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .name-card h4 {
            font-weight: bold;
            color: #1f4fbf;
            margin: 0 0 4mm;
            font-size: 22px;
            line-height: 1.2;
        }
        .name-card p {
            margin: 0 0 2mm;
            color: #1b1464;
            font-size: 14px;
            font-style: italic;
            line-height: 1.25;
        }
        .name-card img {
            position: absolute;
            width: 24px;
            right: 6px;
            bottom: 6px;
        }
        .name-card.blank {
            background: #fff;
        }
        @page {
            size: A4 landscape;
            margin: 0;
        }
        @media print {
            body {
                margin: 0;
            }
            .print-page {
                page-break-after: always;
                break-after: page;
            }
            .print-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
    </style>
</head>
<body onload="window.print()">
<?php
$nametagsArray = is_array($nametags) ? $nametags : $nametags->toArray();
$pages = array_chunk($nametagsArray, 12);
if (empty($pages)) {
    $pages = [[]];
}

foreach ($pages as $pageNametags) {
    $filledPage = array_pad($pageNametags, 12, null);
?>
    <div class="print-page">
        <?php foreach ($filledPage as $datarecord) { ?>
            <?php if ($datarecord) { ?>
                <div class="name-card">
                    <h4><?php echo h(trim($datarecord->first_name . ' ' . $datarecord->last_name)); ?></h4>
                    <p><?php echo h($datarecord->school_company); ?></p>
                    <p><?php echo h($convSeasD->Conventions['name']); ?><br><?php echo h($convSeasD->season_year); ?></p>
                    <?php echo $this->Html->image('front/scce_logo_tags.jpg'); ?>
                </div>
            <?php } else { ?>
                <div class="name-card blank"></div>
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>
</body>
</html>
