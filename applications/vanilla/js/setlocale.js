jQuery(document).ready(function($) {
    var currentLocale = $("html").attr("lang");
    $(".LocaleSelect option[value='" + currentLocale + "']").attr('selected','selected');
    $(document).on('change','.LocaleSelect', function() {
        var url = $(this).find(":selected").attr("data-url");
        $("#LocaleSelectForm").attr("action", url);
        $("#LocaleSelectForm").submit();
   });
});
