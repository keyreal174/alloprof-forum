jQuery(document).ready(function($) {
    var data = localStorage.getItem('draft');
    var newdata = "";
    if (data) {
        $.each(data.split('&'), function (index, elem) {
            var vals = elem.split('=');
            if (vals[0] == 'TransientKey') {
                newdata += 'TransientKey=' + gdn.definition('TransientKey');
            } else {
                newdata += elem;
            }
            newdata += "&";
        });
        newdata = newdata.slice(0, -1);
        $.post(gdn.url('/post/comment'), newdata, function( res ) {
            localStorage.removeItem('draft');
            let nextUrl = res.split('<meta property="og:url" content="')[1].split('"')[0];
            window.location.replace(nextUrl);
        })
        .fail(function(response) {
            localStorage.removeItem('draft');
        });
    }
});