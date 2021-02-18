jQuery(document).ready(function($) {
    var currentLocale = $(".LocaleSelect").attr("value");
    $(".LocaleSelect option[value='" + currentLocale + "']").attr('selected','selected');
    $(document).on('change','.LocaleSelect', function() {
        var url = $(this).find(":selected").attr("data-url");
        $("#LocaleSelectForm").attr("action", url);
        $("#LocaleSelectForm").submit();
   });
});
