<?php if (!defined('APPLICATION')) exit();

if (!function_exists('timeElapsedString'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

if (!function_exists('translate'))
    include($this->fetchViewLocation('helper_functions', 'activity', 'dashboard'));
?>
<ul class="PopList Activities">
    <li class="Item Title"><?php
        $prefix = '';
        if (preg_match('/zonedentraide/i', $_SERVER['REQUEST_URI'])) {
            $prefix = '/zonedentraide/messages/inbox';
        } else {
            $prefix = '/helpzone/messages/inbox';
        }
        $prefix = url('/messages/inbox');
        $inboxWithCircle = '<svg width="27" height="24" viewBox="0 0 27 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23 19 12 9 1 19" stroke="#000"/>
            <path d="m23 5-11 8L1 5" fill="#fff"/>
            <path d="m23 5-11 8L1 5" stroke="#000"/>
            <path d="m23.5 4.5-.009 15H.5v-15h23z" stroke="#000"/>
            <circle cx="23" cy="4" r="4" fill="#F95928"/>
        </svg>';
        $inbox = '<svg width="27" height="24" viewBox="0 0 27 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23 19 12 9 1 19" stroke="#000"/>
            <path d="m23 5-11 8L1 5" fill="#fff"/>
            <path d="m23 5-11 8L1 5" stroke="#000"/>
            <path d="m23.5 4.5-.009 15H.5v-15h23z" stroke="#000"/>
        </svg>';
        echo '<a href="#" class="Close d-mobile">×</a>';
        if ($this->data('UnreadConvNotifications') && $this->data('UnreadConvNotifications') > 0)  {
            echo '
                <a href="'.$prefix.'" class="notification-inbox d-desktop" style="right: 32px">
                    '.$inboxWithCircle.'
                </a>
            ';
        } else {
            echo '
                <a href="'.$prefix.'" class="notification-inbox d-desktop" style="right: 32px">
                    '.$inbox.'
                </a>
            ';
        }
        echo '
            <a class="notification-settings d-desktop">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" d="M12 4.5c.34 0 .66.03.98.07l1.65-2.11c.15-.19.42-.24.64-.12l3.46 2c.22.12.31.38.22.61l-1 2.49c.39.52.73 1.08.98 1.69l2.65.38c.24.03.42.24.42.49v4c0 .25-.18.46-.42.49l-2.65.38c-.25.61-.58 1.17-.98 1.69l1 2.49c.08.22 0 .49-.22.61l-3.46 2c-.22.12-.49.07-.64-.12l-1.65-2.11c-.32.04-.65.07-.98.07-.33 0-.66-.03-.98-.07l-1.65 2.11c-.15.19-.42.25-.64.12l-3.46-2c-.22-.12-.31-.38-.22-.61l1-2.49c-.39-.52-.73-1.08-.98-1.69l-2.65-.38A.488.488 0 0 1 2 14v-4c0-.25.18-.46.42-.49l2.65-.38c.25-.61.58-1.17.98-1.69l-1-2.49c-.08-.22 0-.49.22-.61l3.46-2c.22-.12.49-.07.64.12l1.65 2.11c.32-.04.64-.07.98-.07zm0 11c1.93 0 3.5-1.57 3.5-3.5S13.93 8.5 12 8.5 8.5 10.07 8.5 12s1.57 3.5 3.5 3.5z" stroke="#000"/>
                </svg>
            </a>
        ';

        echo '<strong>'.t('Notifications').' (<span class="notification-count">'.($this->data('UnreadNotifications')).'</span>)</strong>';
        ?>
    </li>
    <li class="d-mobile">
        <div class="actions">
            <?php
            if (count($this->data('Activities')))
                echo '<div class="notification-all-read d-mobile">'.t('Mark all as read').'</div>';
            echo '<div style="display: flex; align-items: center;">';
            if ($this->data('UnreadConvNotifications'))  {
                echo '
                    <a href="'.$prefix.'" class="notification-inbox d-mobile InboxPopup" style="margin-right: 8px; display: block !important;">
                        '.$inboxWithCircle.'
                    </a>
                ';
            } else {
                echo '
                    <a href="'.$prefix.'" class="notification-inbox d-mobile InboxPopup" style="margin-right: 8px; display: block !important;">
                        '.$inbox.'
                    </a>
                ';
            }
            echo '
                <div class="notification-settings d-mobile" style="display: block !important; padding: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" d="M12 4.5c.34 0 .66.03.98.07l1.65-2.11c.15-.19.42-.24.64-.12l3.46 2c.22.12.31.38.22.61l-1 2.49c.39.52.73 1.08.98 1.69l2.65.38c.24.03.42.24.42.49v4c0 .25-.18.46-.42.49l-2.65.38c-.25.61-.58 1.17-.98 1.69l1 2.49c.08.22 0 .49-.22.61l-3.46 2c-.22.12-.49.07-.64-.12l-1.65-2.11c-.32.04-.65.07-.98.07-.33 0-.66-.03-.98-.07l-1.65 2.11c-.15.19-.42.25-.64.12l-3.46-2c-.22-.12-.31-.38-.22-.61l1-2.49c-.39-.52-.73-1.08-.98-1.69l-2.65-.38A.488.488 0 0 1 2 14v-4c0-.25.18-.46.42-.49l2.65-.38c.25-.61.58-1.17.98-1.69l-1-2.49c-.08-.22 0-.49.22-.61l3.46-2c.22-.12.49-.07.64.12l1.65 2.11c.32-.04.64-.07.98-.07zm0 11c1.93 0 3.5-1.57 3.5-3.5S13.93 8.5 12 8.5 8.5 10.07 8.5 12s1.57 3.5 3.5 3.5z" stroke="#000"/>
                    </svg>
                </div>
                </div>
            ';
        ?>
        </div>
        </div>
    <li class="notification-settings-content FilterMenu">
        <div>
            <?php
                if ($this->data('Preferences')['Email.CustomNotification']) {
                    echo Gdn::controller()->Form->toggle('ToggleEmail', t('Email notifications'), [ 'checked' => true ]);
                } else {
                    echo Gdn::controller()->Form->toggle('ToggleEmail', t('Email notifications'));
                }
            ?>
        </div>
        <div>
            <?php
                if ($this->data('Preferences')['Popup.Moderation']) {
                    echo Gdn::controller()->Form->toggle('ToggleModeration', t('Question published or declined'), [ 'checked' => true ]);
                } else {
                    echo Gdn::controller()->Form->toggle('ToggleModeration', t('Question published or declined'));
                }
            ?>
        </div>
        <div>
            <?php
                if ($this->data('Preferences')['Popup.DiscussionComment']) {
                    echo Gdn::controller()->Form->toggle('ToggleExplanation', t('Answer received'), [ 'checked' => true ]);
                } else {
                    echo Gdn::controller()->Form->toggle('ToggleExplanation', t('Answer received'));
                }
            ?>
        </div>
    </li>
    <?php
        if (count($this->data('Activities')))
            echo '<div class="notification-all-read-wrapper"><a class="notification-all-read d-desktop">'.t('Mark all as read').'</a></div>';
    ?>
    <div class="notification-list">
        <li class="Item Empty Center jquery-empty-section" style="display: none">
            <?php echo t('Notifications will appear here.', t('You do not have any notifications yet.')); ?>
        </li>
        <?php
        if (count($this->data('Activities'))):
            $convCount = 0;
            $PostActivities = array_filter($this->data('Activities'), function($k) {
                return $k['ActivityTypeID'] != '21';
            });
            $ConvActivities = array_slice(array_filter($this->data('Activities'), function($k) {
                return $k['ActivityTypeID'] == '21' && $k['Notified'] == '3' ;
            }), 0, 3);
            if (count($ConvActivities)):
                echo  '<div class="conversaton-notification-list">';
                foreach ($ConvActivities as $Activity): ?>
        <?php
                    $rel = !empty($Activity['Route']) ? ' rel="'.url($Activity['Route']).'"' : null;
                    $id = ($Activity['Notified'] == ActivityModel::SENT_PENDING)?' id="'.$Activity['ActivityID'].'"':null;
                ?>
        <li class="Item" <?php echo $rel, $id?>>
            <?php
                        if ($Activity['Photo']) {
                            if (str_contains($Activity['Photo'], 'avatars/0.svg')) {
                                $ClassName = 'ProfilePhotoDefaultWrapper';
                            }

                            $firstLetter = getFirstLetter($Activity['ActivityUserID']);

                            $PhotoAnchor = anchor(
                                img($Activity['Photo'], ['class' => 'ProfilePhoto PhotoWrapMedium']),
                                $Activity['PhotoUrl'], 'PhotoWrap PhotoWrapMedium '.$ClassName, ["avatar--first-letter" => $firstLetter]);
                        } else {
                            $PhotoAnchor = '';
                        }
                    ?>
            <div class="Author Photo">
                <?php echo $PhotoAnchor; ?>
            </div>

            <div class="ItemContent Activity">
                <?php
                            $verified = $Activity['Verified'] && $Activity['ActivityTypeID'] == 30;
                        ?>
                <h6 class="<?php echo $verified?'verified':'' ?>">
                    <?php echo t('New message!'); ?>
                </h6>
                <p>
                    <?php
                                echo t('You have a new message from a moderator.');
                            ?>
                </p>
                <div class="Meta">
                    <span class="MItem DateCreated"><?php echo timeElapsedString($Activity['DateUpdated']); ?></span>
                </div>
                <?php
                            if($Activity['Notified'] == ActivityModel::SENT_PENDING) echo '<span class="mark-not-read"></span>'
                        ?>
                <span></span>
            </div>
        </li>
        <?php
                endforeach;
            echo '</div>';
            endif;

            if (count($PostActivities)):
                echo  '<div class="post-notification-list">';
                foreach ($PostActivities as $Activity): ?>
        <?php
                        $rel = !empty($Activity['Route']) ? ' rel="'.url($Activity['Route']).'"' : null;
                        $id = ($Activity['Notified'] == ActivityModel::SENT_PENDING)?' id="'.$Activity['ActivityID'].'"':null;
                    ?>
        <li class="Item" <?php echo $rel, $id?>>
            <?php
                        if ($Activity['Photo']) {
                            if (str_contains($Activity['Photo'], 'avatars/0.svg')) {
                                $ClassName = 'ProfilePhotoDefaultWrapper';
                            }

                            $firstLetter = getFirstLetter($Activity['ActivityUserID']);

                            $PhotoAnchor = anchor(
                                img($Activity['Photo'], ['class' => 'ProfilePhoto PhotoWrapMedium']),
                                $Activity['PhotoUrl'], 'PhotoWrap PhotoWrapMedium '.$ClassName, ["avatar--first-letter" => $firstLetter]);
                        } else {
                            $PhotoAnchor = '';
                        }
                        ?>
            <div class="Author Photo">
                <?php echo $PhotoAnchor; ?>
            </div>

            <div class="ItemContent Activity">
                <?php
                                        $verified = $Activity['Verified'] && $Activity['ActivityTypeID'] == 30;
                                    ?>
                <h6 class="<?php echo $verified?'verified':'' ?>">
                    <b><?php echo t($Activity['Headline']); ?></b>
                    <?php if($verified) {?>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1.25 9C1.25 4.71979 4.71979 1.25 9 1.25C11.0554 1.25 13.0267 2.06652 14.4801 3.51992C15.9335 4.97333 16.75 6.94457 16.75 9C16.75 13.2802 13.2802 16.75 9 16.75C4.71979 16.75 1.25 13.2802 1.25 9Z"
                            fill="#05BF8E" stroke="#05BF8E" stroke-width="2.5" />
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.02851 8.40338C5.35801 8.07387 5.89224 8.07387 6.22175 8.40338L8.01161 10.1932L12.188 6.01689C12.5175 5.68739 13.0517 5.68739 13.3812 6.01689C13.7107 6.3464 13.7107 6.88063 13.3812 7.21014L8.60823 11.9831C8.27873 12.3126 7.7445 12.3126 7.41499 11.9831L5.02851 9.59662C4.699 9.26712 4.699 8.73288 5.02851 8.40338Z"
                            fill="white" />
                    </svg>
                    <?php } ?>
                </h6>
                <p>
                    <?php
                                            $excerpt = '';
                                            $story = t($Activity['Story']);
                                            $format = $Activity['Format'] ?? Vanilla\Formatting\Formats\HtmlFormat::FORMAT_KEY;
                                            $excerpt = htmlspecialchars($story ? Gdn::formatService()->renderExcerpt($story, $format) : $excerpt);

                                            echo t(trim($excerpt)); ?>
                </p>
                <div class="Meta">
                    <span class="MItem DateCreated"><?php echo timeElapsedString($Activity['DateUpdated']); ?></span>
                </div>
                <?php
                                        if($Activity['Notified'] == ActivityModel::SENT_PENDING) echo '<span class="mark-not-read"></span>'
                                    ?>
                <span></span>
            </div>
        </li>
        <?php
                endforeach;
            echo  '</div>';
            endif;
            ?>
        <?php else: ?>
        <li class="Item Empty Center">
            <?php echo t('Notifications will appear here.', t('You do not have any notifications yet.')); ?></li>
        <?php endif; ?>
    </div>
</ul>