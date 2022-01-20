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
        if ($this->data('UnreadConvNotifications'))  {
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
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M21.087 9.61125L19.2442 8.997L20.112 7.2615C20.2923 6.88921 20.218 6.4437 19.9267 6.15L17.85 4.07325C17.5546 3.77755 17.1036 3.70303 16.7287 3.888L14.9932 4.75575L14.379 2.913C14.246 2.51926 13.8781 2.25309 13.4625 2.25H10.5375C10.1183 2.24892 9.74538 2.51587 9.61125 2.913L8.997 4.75575L7.2615 3.888C6.88921 3.70773 6.4437 3.78198 6.15 4.07325L4.07325 6.15C3.77755 6.44541 3.70303 6.89643 3.888 7.27125L4.75575 9.00675L2.913 9.621C2.51926 9.75398 2.25309 10.1219 2.25 10.5375V13.4625C2.24892 13.8817 2.51587 14.2546 2.913 14.3887L4.75575 15.003L3.888 16.7385C3.70773 17.1108 3.78198 17.5563 4.07325 17.85L6.15 19.9267C6.44541 20.2224 6.89643 20.297 7.27125 20.112L9.00675 19.2442L9.621 21.087C9.75513 21.4841 10.1281 21.7511 10.5472 21.75H13.4722C13.8914 21.7511 14.2644 21.4841 14.3985 21.087L15.0127 19.2442L16.7482 20.112C17.118 20.2876 17.558 20.2136 17.85 19.9267L19.9267 17.85C20.2224 17.5546 20.297 17.1036 20.112 16.7287L19.2442 14.9932L21.087 14.379C21.4807 14.246 21.7469 13.8781 21.75 13.4625V10.5375C21.7511 10.1183 21.4841 9.74538 21.087 9.61125ZM19.7999 12.7604L18.6299 13.1504C18.0854 13.327 17.646 13.734 17.4282 14.2634C17.2104 14.7928 17.2363 15.3912 17.4989 15.8999L18.0547 17.0114L16.9822 18.0839L15.8999 17.4989C15.394 17.2468 14.8037 17.2267 14.2818 17.4437C13.7599 17.6607 13.3578 18.0934 13.1797 18.6299L12.7897 19.7999H11.2394L10.8494 18.6299C10.6728 18.0853 10.2658 17.6459 9.73641 17.4281C9.20698 17.2104 8.60861 17.2362 8.09995 17.4989L6.98845 18.0546L5.91595 16.9821L6.50095 15.8999C6.76359 15.3912 6.78945 14.7928 6.57167 14.2634C6.3539 13.734 5.91448 13.327 5.36995 13.1504L4.19995 12.7604V11.2394L5.36995 10.8494C5.91448 10.6727 6.3539 10.2657 6.57167 9.73632C6.78945 9.2069 6.76359 8.60852 6.50095 8.09986L5.9452 7.01761L7.0177 5.94511L8.09995 6.50086C8.60861 6.7635 9.20698 6.78936 9.73641 6.57159C10.2658 6.35381 10.6728 5.9144 10.8494 5.36986L11.2394 4.19986H12.7604L13.1504 5.36986C13.3271 5.9144 13.7341 6.35381 14.2635 6.57159C14.7929 6.78936 15.3913 6.7635 15.8999 6.50086L17.0114 5.94511L18.0839 7.01761L17.4989 8.09986C17.2469 8.60577 17.2268 9.19615 17.4438 9.71805C17.6607 10.24 18.0935 10.642 18.6299 10.8201L19.7999 11.2101V12.7604ZM12 8.09998C9.84606 8.09998 8.09997 9.84607 8.09997 12C8.09997 14.1539 9.84606 15.9 12 15.9C14.1539 15.9 15.9 14.1539 15.9 12C15.9 10.9656 15.4891 9.97366 14.7577 9.24227C14.0263 8.51087 13.0343 8.09998 12 8.09998ZM12 13.9499C10.923 13.9499 10.05 13.0768 10.05 11.9999C10.05 10.9229 10.923 10.0499 12 10.0499C13.0769 10.0499 13.95 10.9229 13.95 11.9999C13.95 13.0768 13.0769 13.9499 12 13.9499Z" fill="black"/>
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
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M21.087 9.61125L19.2442 8.997L20.112 7.2615C20.2923 6.88921 20.218 6.4437 19.9267 6.15L17.85 4.07325C17.5546 3.77755 17.1036 3.70303 16.7287 3.888L14.9932 4.75575L14.379 2.913C14.246 2.51926 13.8781 2.25309 13.4625 2.25H10.5375C10.1183 2.24892 9.74538 2.51587 9.61125 2.913L8.997 4.75575L7.2615 3.888C6.88921 3.70773 6.4437 3.78198 6.15 4.07325L4.07325 6.15C3.77755 6.44541 3.70303 6.89643 3.888 7.27125L4.75575 9.00675L2.913 9.621C2.51926 9.75398 2.25309 10.1219 2.25 10.5375V13.4625C2.24892 13.8817 2.51587 14.2546 2.913 14.3887L4.75575 15.003L3.888 16.7385C3.70773 17.1108 3.78198 17.5563 4.07325 17.85L6.15 19.9267C6.44541 20.2224 6.89643 20.297 7.27125 20.112L9.00675 19.2442L9.621 21.087C9.75513 21.4841 10.1281 21.7511 10.5472 21.75H13.4722C13.8914 21.7511 14.2644 21.4841 14.3985 21.087L15.0127 19.2442L16.7482 20.112C17.118 20.2876 17.558 20.2136 17.85 19.9267L19.9267 17.85C20.2224 17.5546 20.297 17.1036 20.112 16.7287L19.2442 14.9932L21.087 14.379C21.4807 14.246 21.7469 13.8781 21.75 13.4625V10.5375C21.7511 10.1183 21.4841 9.74538 21.087 9.61125ZM19.7999 12.7604L18.6299 13.1504C18.0854 13.327 17.646 13.734 17.4282 14.2634C17.2104 14.7928 17.2363 15.3912 17.4989 15.8999L18.0547 17.0114L16.9822 18.0839L15.8999 17.4989C15.394 17.2468 14.8037 17.2267 14.2818 17.4437C13.7599 17.6607 13.3578 18.0934 13.1797 18.6299L12.7897 19.7999H11.2394L10.8494 18.6299C10.6728 18.0853 10.2658 17.6459 9.73641 17.4281C9.20698 17.2104 8.60861 17.2362 8.09995 17.4989L6.98845 18.0546L5.91595 16.9821L6.50095 15.8999C6.76359 15.3912 6.78945 14.7928 6.57167 14.2634C6.3539 13.734 5.91448 13.327 5.36995 13.1504L4.19995 12.7604V11.2394L5.36995 10.8494C5.91448 10.6727 6.3539 10.2657 6.57167 9.73632C6.78945 9.2069 6.76359 8.60852 6.50095 8.09986L5.9452 7.01761L7.0177 5.94511L8.09995 6.50086C8.60861 6.7635 9.20698 6.78936 9.73641 6.57159C10.2658 6.35381 10.6728 5.9144 10.8494 5.36986L11.2394 4.19986H12.7604L13.1504 5.36986C13.3271 5.9144 13.7341 6.35381 14.2635 6.57159C14.7929 6.78936 15.3913 6.7635 15.8999 6.50086L17.0114 5.94511L18.0839 7.01761L17.4989 8.09986C17.2469 8.60577 17.2268 9.19615 17.4438 9.71805C17.6607 10.24 18.0935 10.642 18.6299 10.8201L19.7999 11.2101V12.7604ZM12 8.09998C9.84606 8.09998 8.09997 9.84607 8.09997 12C8.09997 14.1539 9.84606 15.9 12 15.9C14.1539 15.9 15.9 14.1539 15.9 12C15.9 10.9656 15.4891 9.97366 14.7577 9.24227C14.0263 8.51087 13.0343 8.09998 12 8.09998ZM12 13.9499C10.923 13.9499 10.05 13.0768 10.05 11.9999C10.05 10.9229 10.923 10.0499 12 10.0499C13.0769 10.0499 13.95 10.9229 13.95 11.9999C13.95 13.0768 13.0769 13.9499 12 13.9499Z" fill="black"/>
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
                return $k['ActivityTypeID'] == '21';
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
                                            $story = translate($Activity['Story']);
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