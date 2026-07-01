<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Golden Awards List Print</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            margin: 0;
            padding: 0;
            background: #f3f3f3;
            color: #1f1f1f;
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
        .page {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 16px 18px 18px;
            box-sizing: border-box;
            background: #fff;
            border: 1px solid #d9d9d9;
        }
        .page-title {
            margin: 0 0 14px;
            font-size: 46px;
            font-weight: 500;
            letter-spacing: -0.3px;
            font-family: "Times New Roman", Georgia, serif;
            color: #202020;
        }
        .award {
            margin-bottom: 14px;
            page-break-inside: avoid;
            border: 1px solid #e3e3e3;
            padding: 8px 10px 10px;
        }
        .award-header {
            display: table;
            width: 100%;
            border-bottom: 1px solid #464646;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .award-title,
        .award-books {
            display: table-cell;
            vertical-align: top;
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
        .award-title {
            width: 58%;
            color: #1732c8;
            font-size: 29px;
            font-weight: 700;
            line-height: 1.05;
        }
        .award-books {
            width: 42%;
            font-size: 19px;
            font-style: italic;
            line-height: 1.14;
            color: #2f2f2f;
            text-align: left;
            padding-top: 3px;
        }
        .division {
            margin: 6px 0 2px;
            color: #1fb150;
            font-size: 26px;
            font-style: italic;
            font-weight: 700;
            line-height: 1;
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
        .division-block {
            border-top: 1px dashed #676767;
            margin-bottom: 6px;
            padding-top: 4px;
        }
        .school {
            border-bottom: 1px dashed #7a7a7a;
            padding: 2px 0 3px;
            margin-bottom: 3px;
        }
        .school-name {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
        }
        .student {
            margin: 1px 0 2px 34px;
            font-size: 15px;
            font-style: italic;
            font-family: "Source Sans Pro", "Helvetica Neue", Arial, sans-serif;
            color: #2f2f2f;
        }
        @media print {
            body {
                background: #fff;
            }
            .page {
                border: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<?php
$divisionOrder = [
    'U16' => 'U16',
    'Open' => 'Open',
];
?>

<?php if (!empty($goldenAwardSections)) { ?>
    <div class="page">
        <h1 class="page-title"><?php echo h($conventionD->name.' '.$conventionSD->season_year); ?></h1>

        <?php foreach ($goldenAwardSections as $section) { ?>
            <div class="award">
                <div class="award-header">
                    <div class="award-title"><?php echo h($section['award_title']); ?></div>
                    <div class="award-books"><?php echo h($section['books_title']); ?></div>
                </div>

                <?php foreach ($divisionOrder as $divisionKey => $divisionLabel) { ?>
                    <?php if (empty($section['divisions'][$divisionKey])) { continue; } ?>
                    <div class="division"><?php echo h($divisionLabel); ?></div>
                    <div class="division-block">
                        <?php foreach ($section['divisions'][$divisionKey] as $schoolName => $students) { ?>
                            <div class="school">
                                <p class="school-name"><?php echo h($schoolName); ?></p>
                                <?php foreach ($students as $studentName) { ?>
                                    <p class="student"><?php echo h($studentName); ?></p>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div class="page">
        <h1 class="page-title"><?php echo h($conventionD->name.' '.$conventionSD->season_year); ?></h1>
        <p>No Golden Awards entries found for this season.</p>
    </div>
<?php } ?>

<script>
window.print();
</script>
</body>
</html>
