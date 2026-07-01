<style>
  @media print {
    @page {
      size: A4 portrait;
      margin: 8mm;
    }
    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    body, html {
      margin: 0;
      padding: 0;
      font-size: 10px;
      line-height: 1.2;
    }
    div.m_content {
      margin: 0;
      padding: 0;
    }
    .page-break-after {
      page-break-after: always;
      break-after: page;
      height: 0 !important;
      margin: 0 !important;
      padding: 0 !important;
      display: block;
    }
    .tbl-resp-listing {
      page-break-inside: avoid;
      break-inside: avoid;
      height: 138mm;
      box-sizing: border-box;
      margin: 0;
      padding-top: 5mm;
      padding-bottom: 0;
      padding-left: 0;
      padding-right: 0;
      width: 100%;
      overflow: hidden;
    }
    /* Second student on each page gets more top padding to match visual balance */
    .student-divider + .tbl-resp-listing {
      padding-top: 10mm;
    }
    .tbl-resp-listing table {
      page-break-inside: avoid;
      break-inside: avoid;
      margin: 0;
      padding: 0;
      font-size: 10px;
      width: 92%;
      border-collapse: collapse;
      line-height: 1.2;
      margin-left: auto;
      margin-right: auto;
    }
    .tbl-resp-listing table tr {
      page-break-inside: avoid;
      break-inside: avoid;
      height: auto;
    }
    .student-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-top: 0;
      margin-bottom: 2px;
      width: 92%;
      margin-left: auto;
      margin-right: auto;
    }
    .student-header .student-name {
      font-size: 13px;
      font-weight: bold;
      color: #222;
    }
    .student-header .school-name {
      font-size: 13px;
      font-weight: bold;
      font-style: italic;
      color: #222;
    }
    .tbl-resp-listing table tr:first-child th {
      background: #ffffcc !important;
      font-weight: bold;
    }
    .tbl-resp-listing table tr:nth-child(n+2) td {
      background: #fff !important;
    }
    .tbl-resp-listing table tr.projected-path-row td {
      background: #ffffcc !important;
    }
    .tbl-resp-listing table td,
    .tbl-resp-listing table th {
      padding: 1px 3px !important;
      font-size: 9px !important;
      line-height: 1.15 !important;
      margin: 0 !important;
      border: 1px solid #333;
      vertical-align: top;
      word-break: break-word;
    }
    .tbl-resp-listing table th.sorting_paging {
      font-size: 9px !important;
      padding: 1px 3px !important;
    }
    .tbl-resp-listing table tr td span {
      font-size: 9px;
      line-height: 1.15;
    }
    .student-divider {
      border: none;
      border-top: 1px solid #999;
      margin: 0;
      display: block;
    }
    .panel-body {
      margin: 0;
      padding: 0;
    }
    .ersu_message {
      margin: 0;
      padding: 0;
      display: none;
    }
    form {
      display: contents;
      margin: 0;
      padding: 0;
    }
    section.lstng-section {
      margin: 0;
      padding: 0;
    }
    .topn {
      display: none;
    }
    button, input[type="checkbox"], input[type="radio"] {
      display: none;
    }
  }
  .topn {display:none;}
</style>

<div class="m_content" id="listID">
	<?php echo $this->element("Admin/Schedulingreports/bystudentsshow"); ?>
</div>

<script type="text/javascript">
window.onload = function() {
  window.print();
};
</script>