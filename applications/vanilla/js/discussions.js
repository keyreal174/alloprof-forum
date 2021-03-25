jQuery(document).ready(function($) {

    // Set up paging
    if ($.morepager) {
        $('.MorePager').not('.Section-Profile .MorePager').morepager({
            pageContainerSelector: 'ul.Discussions:last, ul.Drafts:last',
            afterPageLoaded: function() {
                $(document).trigger('DiscussionPagingComplete');
            }
        });

        // profile/discussions paging
        $('.Section-Profile .Discussions .MorePager, .Section-Profile .Comments .MorePager').morepager({
            pagerInContainer: true,
            afterPageLoaded: function() {
                $(document).trigger('DiscussionPagingComplete');
            }
        });
    }

    if ($('.AdminCheck :checkbox').not(':checked').length == 1) {
        $('.AdminCheck [name="Toggle"]').prop('checked', true).change();
    }

    /* Discussion Checkboxes */
    $(document).on('click', '.AdminCheck [name="Toggle"]', function() {
        if ($(this).prop('checked')) {
            $('.DataList .AdminCheck :checkbox, tbody .AdminCheck :checkbox').prop('checked', true).change();
        } else {
            $('.DataList .AdminCheck :checkbox, tbody .AdminCheck :checkbox').prop('checked', false).change();
        }
    });
    $(document).on('click', '.AdminCheck :checkbox', function() {
        // retrieve all checked ids
        var checkIDs = $('.DataList .AdminCheck :checkbox, tbody .AdminCheck :checkbox');
        var aCheckIDs = new Array();
        checkIDs.each(function() {
            checkID = $(this);

            // jQuery 1.9 removed the old behaviour of checkID.attr('checked') when
            // checking for boolean checked value. It now returns undefined. The
            // correct method to check boolean checked is with checkID.prop('checked');
            // Vanilla would either return the string 'checked' or '', so make
            // sure same return values are generated.
            aCheckIDs[aCheckIDs.length] = {
                'checkId': checkID.val(),
                'checked': checkID.prop('checked') || '' // originally just, wrong: checkID.attr('checked')
            };
        });
        $.ajax({
            type: "POST",
            url: gdn.url('/moderation/checkeddiscussions'),
            data: {'CheckIDs': aCheckIDs, 'DeliveryMethod': 'JSON', 'TransientKey': gdn.definition('TransientKey')},
            dataType: "json",
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                gdn.informMessage(XMLHttpRequest.responseText, {'CssClass': 'Dismissable'});
            },
            success: function(json) {
                gdn.inform(json);
            }
        });
    });

    var restore = function() {
        handleAction('/log/restore');
    };

    var afterSuccess = function(data) {
        // Figure out the IDs that are currently in the view.
        console.log(data);
        window.location.reload();
    }

    $('.RestoreButton').click(function(e) {
        var IDs = $(this).attr('Id');
        console.log(IDs);
        currentAction = restore;

        // Popup the confirm.
        var bar = $.popup({afterSuccess: afterSuccess},
            function(settings) {
                $.post(
                    gdn.url('/log/confirm/restore'),
                    {'DeliveryType': 'VIEW',
                        'Postback': true,
                        'IDs': IDs,
                        'Action': 'Restore',
                        'TransientKey': gdn.definition('TransientKey', '')
                    },
                    function(data) {
                        $.popup.reveal(settings, data);
                    })
            });

        return false;
    });
});
