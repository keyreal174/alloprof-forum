jQuery(document).ready(function($) {
    function customNotificationToggle() {
        if($('.Flayout-notification').hasClass('open')) {
            $('.Flayout-notification').removeClass('open');
        } else {
            $('.Flayout-notification').addClass('open');
        }
    }

    function toggleNotificationSettingsContent() {
        if($('.notification-settings-content').hasClass('open')) {
            $('.notification-settings-content').removeClass('open');
        } else {
            $('.notification-settings-content').addClass('open');
        }
    }

    function markAllAsRead() {
        $.ajax({
            type: "POST",
            url: gdn.url('/notifications/markAllAsRead'),
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(res) {
                $('.notification-list .mark-not-read').remove();
                $('.notification-count').text(0);
            }
        });
        return false;
    }

    function markSingleRead($id) {
        $.ajax({
            type: "POST",
            url: gdn.url('/notifications/markSingleRead/'+$id),
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(res) {
                console.log(res)
            }
        });
        return false;
    }

    function updateNotificationSettings() {
        var formData = new FormData()

        const preferences = {
            'All': ['Popup.DiscussionComment', 'Popup.Moderation'],
            'ToggleEmail': ['Email.DiscussionComment', 'Email.Moderation'],
            'ToggleExplanation': ['Popup.DiscussionComment'],
            'ToggleModeration': ['Popup.Moderation']
        }

        var settings = {
            'All': $('.notification-settings-content #Form_ToggleAll').is(":checked"),
            'ToggleEmail': $('.notification-settings-content #Form_ToggleEmail').is(":checked"),
            'ToggleExplanation': $('.notification-settings-content #Form_ToggleExplanation').is(":checked"),
            'ToggleModeration': $('.notification-settings-content #Form_ToggleModeration').is(":checked")
        }

        Object.entries(settings).forEach(([key, value]) => {
            preferences[key].map(preference => {
                formData.append('Checkboxes[]', preference);
                if(value)
                    formData.append(preference.replace('.', '-dot-'), 1);
            })
        });

        $.ajax({
            type: "POST",
            url: gdn.url('/profile/preferencesByAjax'),
            data: formData,
            processData: false,
            contentType: false,
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(res) {
                console.log(res)
            }
        });
        return false;
    }

    function initNotification() {
        $(document).on('click', '.ToggleFlyout-notification', function() {
            customNotificationToggle();
        });

        $(window).click(function() {
            $('.Flayout-notification').removeClass('open');
        });

        $('.Flayout-notification').on('click', '.notification-settings', function(e) {
            toggleNotificationSettingsContent();
            e.stopPropagation();
        });

        $('.Flayout-notification').on('click', '.notification-list .Item', function(e) {
            if($(this).attr('id'))
                markSingleRead($(this).attr('id'));
        });

        $('.Flayout-notification').on('click', '.notification-all-read', function(e) {
            markAllAsRead();
            e.stopPropagation();
        });

        $('.Flayout-notification').on('change', '.notification-settings-content input[type="checkbox"]', function() {
            updateNotificationSettings()
        });
    }

    initNotification();
})