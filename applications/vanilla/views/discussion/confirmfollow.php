<?php
    use Vanilla\Utility\HtmlUtils;

    if (!Gdn::session()->isValid()) {
        return '';
    }

    $discussion = $this->data('Discussion');

    $popupClass = "";
    $popupLink = "/discussion/confirmFollow/".$discussion->DiscussionID;

    $hasFollowedTeacher = false;
    if ($this->getUserRole() == 'Teacher') {
        $data = Gdn::sql()
                ->select('*')
                ->from('UserDiscussion')
                ->where('DiscussionID', $discussion->DiscussionID)
                ->where('Bookmarked', 1)
                ->get();
        foreach ($data as $row) {
            $followedUserID = val('UserID', $row);
            if ($followedUserID != Gdn::session()->UserID) {
                if ($this->getUserRole($followedUserID) == 'Teacher') {
                    $hasFollowedTeacher = true;
                    break;
                }
            }
        }

        if ($hasFollowedTeacher) {
            $popupClass = " OptionsLink Popup";
        }
    }

    // Bookmark link
    $isBookmarked = $discussion->Bookmarked == '1';

    // Bookmark link
    $title = t($isBookmarked ? 'UnFollow' : 'Follow');

    $accessibleLabel= HtmlUtils::accessibleLabel('%s for discussion: "%s"', [t($isBookmarked? 'UnFollow' : 'Follow'), is_array($discussion) ? $discussion["Name"] : $discussion->Name]);

    $icon_following = <<<EOT
    <svg width="21" height="24" viewBox="0 0 21 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.5 3.75C0.5 2.09315 1.84315 0.75 3.5 0.75H17.8077C19.4645 0.75 20.8077 2.09315 20.8077 3.75V22.4894C20.8077 23.3576 19.7774 23.8134 19.135 23.2294L10.9902 15.825C10.7995 15.6516 10.5082 15.6516 10.3175 15.825L2.17267 23.2294C1.5303 23.8134 0.5 23.3576 0.5 22.4894V3.75Z" fill="black"/>
    </svg>
    EOT;

    $icon_follow = <<<EOT
    <svg width="19" height="21" viewBox="0 0 19 21" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.75049 3.5C1.75049 2.5335 2.53399 1.75 3.50049 1.75H15.2697C16.2362 1.75 17.0197 2.5335 17.0197 3.5V18.6743L10.5623 12.8039C9.89479 12.1971 8.87541 12.1971 8.20792 12.8039L1.75049 18.6743V3.5Z" stroke="black" stroke-width="2.5"/>
    </svg>
    EOT;

    $icon = $isBookmarked ? $icon_following : $icon_follow;


?>

<div class="BoxConfirmFollow">
    <img src="<?= url('/themes/alloprof/design/images/confirmfollow-bot.svg') ?>" />
    <h2><?php echo t("Oops! The question is already followed") ?></h2>

    <p><?php echo t("A teacher has already indicated that he wants to answer this question.") ?></p>
    <p><?php echo t("Do you still want to add the question to your list?") ?></p>

    <div class="BoxConfirmFollow-options">
        <?php
            echo '<button id="'.$discussion->DiscussionID.'" after-title="'.t('UnFollow').'" class="btn-default followButton Option-Icon'.($isBookmarked ? ' TextColor isFollowing' : '').'" title="'.$title.'" data-url="'.'/discussion/bookmark/'.$discussion->DiscussionID.'/'.Gdn::session()->transientKey().'">'.t("Add to my list")."</button>";
        ?>
        <a class="btn-default btn-shadow close-link Close">
            <span> <?php echo t("Cancel") ?> </span>
        </a>
    </div>
</div>