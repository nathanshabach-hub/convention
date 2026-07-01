<style>
  @page {
    size: A4 portrait;
    margin: 8mm;
  }
  @media print {
    .page-break-after {
      page-break-after: always;
    }
  }
  .topn {display:none;}
</style>
<script type="text/javascript">
<!--
window.print();
//-->
</script>

<?php echo $this->element("Admin/Schedulingreports/smallprogram_booklet"); ?>
