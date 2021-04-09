var convertEmbedLinkToNormalLink = function () {
    $('body.Discussions .DataList.Discussions .MessageWrapper .embedLink-link, body.Search .DataList .MessageWrapper .embedLink-link').each(function() {
        if ($(this).hasClass('converted')) {
            return;
        }
        var href = $(this).attr("to");
        $(this).text(href);
        $(this).addClass('.converted');
    });
}

var handleOverflowedContent = function () {
    $('body.Search .DataList .MessageWrapper, body.Discussions .DataList.Discussions .MessageWrapper').each(function(discussionIndex) {
        if ($(this).hasClass('overflowManaged')) {
            return;
        }
        $(this).addClass('overflowManaged');
        var messageWrapperHeight = $(this).outerHeight();
        messageWrapperHeight = messageWrapperHeight < 168 ? messageWrapperHeight : 168;
        var acrossIndex = null;

        $(this).children().each(function(index) {
            var topPoint = $(this).position().top;
            var bottomPoint = $(this).outerHeight();
            if ((bottomPoint + topPoint) < (messageWrapperHeight - 10)) {
                // element is fully visible
            } else {
                // element or part of the element is hidden
                acrossIndex = acrossIndex != null && acrossIndex < index ? acrossIndex : index ;
            }
        });

        var visibleHeight;

        if (acrossIndex == null) {
            // element is fully visible
        } else {
            // element or part of the element is hidden
            var crossedElement = $(this).children().eq(acrossIndex);
            var crossedElementTop = crossedElement.position().top;
            var crossedElementHeight = crossedElement.outerHeight();
            visibleHeight = (168 - crossedElementTop);
            var crossedElementClass = crossedElement.attr('class');
            var crossedElementTagName = crossedElement.prop("tagName").toLowerCase();
            if (crossedElementTagName == 'p') {
                var lineCount = Math.floor(visibleHeight / 18);
                if (lineCount > 1) {
                    if (crossedElement.text() == "") {
                        lineCount --;
                    }
                    $clamp($(this).children()[acrossIndex], {clamp: lineCount});
                }
                $(this).children().eq(acrossIndex + 1).text('...');
            } else {
                if (crossedElementClass.indexOf('embedImage') > -1) {
                    crossedElement.css({
                        'max-height': visibleHeight + 'px',
                    });
                    crossedElement.addClass('crossed');
                }
            }
        }
    });
}

window.convertEmbedLinkToNormalLink = convertEmbedLinkToNormalLink;
window.handleOverflowedContent = handleOverflowedContent;

$(window).on('load', function() {
    convertEmbedLinkToNormalLink();
});

jQuery(document).ready(function($) {
    handleOverflowedContent();
});
