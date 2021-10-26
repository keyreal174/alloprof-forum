<?php
    require_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussions', 'Vanilla');
    $Session = Gdn::session();
    if (!Gdn::session()->isValid()) {
        $additionalClass = 'invalid';
    } else {
        $additionalClass = 'valid';
    }
?>
<nav class="Question-submenu <?php echo $additionalClass ?>">
    <?php if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
        $followedDiscussionsCount = Gdn::sql()
            ->select('d.DiscussionID', 'count', 'CountDiscussions')
            ->from('UserDiscussion d')
            ->where(['d.UserID' => Gdn::session()->UserID, 'd.Bookmarked' => 1])
            ->get()
            ->firstRow()
            ->CountDiscussions;
    ?>
        <div class='Navigation-linkContainer mobile'>
        <?php echo Gdn_Theme::link('discussions', sprite('Home').' <span style="margin-top: 1px;">'.t('Home').'</span>', '<a href="%url" class="%class Navigation-link"><svg width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.25 11.5178L10.5 2.26777L19.75 11.5178V20.25H13.146V16.3809C13.146 15.4144 12.3625 14.6309 11.396 14.6309H9.6665C8.70001 14.6309 7.9165 15.4144 7.9165 16.3809V20.25H1.25V11.5178Z" stroke="black" stroke-width="2.5"/>
</svg><br/>
%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('discussions/bookmarked', sprite('Home').' '.'<span class="desktop">'.t('Follow ups').($followedDiscussionsCount > 0 ? "<span class='realcount Count'>".$followedDiscussionsCount."</span>" : '').'</span>'.'<span class="mobile">'.t('Follow ups'), '<a href="%url" class="%class Navigation-link"><br/>
%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer mobile'>
            <?php echo Gdn_Theme::link('/search/mobile', t('Explore'), '<a href="%url" class="%class Navigation-link SearchPopup"><svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="9.78105" cy="9.78105" r="8.28105" stroke="black" stroke-width="2"/>
                <path d="M16.25 16.25L20 20" stroke="black" stroke-width="2" stroke-linecap="round"/>
                </svg><br/>%text</a>'); ?>
        </div>

    <?php } else { ?>
        <div class='Navigation-linkContainer mobile'>
            <?php echo Gdn_Theme::link('discussions', sprite('Home').' <span style="margin-top: 1px;">'.t('Home').'</span>', '<a href="%url" class="%class Navigation-link"><svg width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M1.25 11.5178L10.5 2.26777L19.75 11.5178V20.25H13.146V16.3809C13.146 15.4144 12.3625 14.6309 11.396 14.6309H9.6665C8.70001 14.6309 7.9165 15.4144 7.9165 16.3809V20.25H1.25V11.5178Z" stroke="black" stroke-width="2.5"/>
</svg><br/>
%text</a>'); ?>
        </div>
        <?php
            if(Gdn::session()->isValid()){
                $discussionsCount = Gdn::sql()
                    ->select('d.DiscussionID', 'count', 'CountDiscussions')
                    ->from('Discussion d')
                    ->where(['d.InsertUserID' => Gdn::session()->UserID])
                    ->get()
                    ->firstRow()
                    ->CountDiscussions;

                echo "<div class='Navigation-linkContainer'>";
                echo Gdn_Theme::link('discussions/mine', sprite('Home').' '.t('My Questions'), '<a href="%url" class="%class Navigation-link"><svg class="d-mobile" width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5853 3.61017C15.1032 3.90423 15.7614 3.72517 16.0589 3.20926C16.3022 2.77995 16.8048 2.56923 17.2815 2.69669C17.7582 2.82415 18.0886 3.2576 18.0851 3.75103C18.0851 4.34945 17.6 4.83457 17.0016 4.83457C16.4032 4.83457 15.9181 5.31969 15.9181 5.91811C15.9181 6.51653 16.4032 7.00165 17.0016 7.00165C18.5788 7.00063 19.9278 5.86752 20.201 4.31413C20.4742 2.76073 19.5929 1.23535 18.1106 0.696217C16.6284 0.157085 14.973 0.759795 14.1844 2.12572C14.0397 2.37551 14.0006 2.6727 14.0759 2.95138C14.1512 3.23007 14.3345 3.4672 14.5853 3.61017ZM19.2446 11.3356C18.6524 11.2588 18.1096 11.6755 18.031 12.2675C17.5605 16.0683 14.3303 18.9221 10.5004 18.9204H4.44342L5.14772 18.2161C5.56783 17.7935 5.56783 17.1109 5.14772 16.6883C2.98737 14.5195 2.34256 11.2646 3.51288 8.43589C4.6832 5.60722 7.4392 3.75936 10.5004 3.75086C11.0988 3.75086 11.5839 3.26574 11.5839 2.66732C11.5839 2.0689 11.0988 1.58378 10.5004 1.58378C6.76216 1.59937 3.36183 3.75059 1.74656 7.12188C0.131296 10.4932 0.585408 14.4911 2.91563 17.4143L1.06278 19.2346C0.755331 19.5462 0.665557 20.0122 0.835236 20.4157C1.00145 20.8203 1.39466 21.0853 1.83209 21.0875H10.5004C15.4133 21.0881 19.5596 17.434 20.1764 12.56C20.2164 12.2739 20.1405 11.9836 19.9655 11.7537C19.7906 11.5238 19.531 11.3733 19.2446 11.3356ZM17.4133 8.16115C17.216 8.07388 16.9972 8.04747 16.7849 8.08531L16.5898 8.15032L16.3948 8.24784L16.2322 8.3887C16.1348 8.48854 16.0575 8.60628 16.0047 8.73543C15.9406 8.87058 15.9109 9.01945 15.918 9.16885C15.9149 9.31335 15.9407 9.45703 15.9939 9.59143C16.0499 9.72147 16.1309 9.83927 16.2322 9.93816C16.4367 10.141 16.7136 10.2541 17.0016 10.2524C17.6 10.2524 18.0851 9.76727 18.0851 9.16885C18.0888 9.02671 18.0591 8.88569 17.9984 8.7571C17.882 8.49673 17.6737 8.28842 17.4133 8.17199V8.16115Z" fill="black"/>
                </svg><br/>
                %text'. ($discussionsCount > 0 ? "<span class='realcount Count'>".$discussionsCount."</span>" : '') .'</a>');
                echo '</div>';
            }
        ?>
        <div class='Navigation-linkContainer mobile'>
            <?php 
            $className = $Session->isValid()?'QuestionPopup':'QuestionPopup SignInStudentPopupAgent';
            echo '<a href="'.url('/post/newQuestionPopup').'" class="'.$className.'"><svg width="49" height="48" viewBox="0 0 49 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.5 24C0.5 10.7452 11.2452 0 24.5 0C37.7548 0 48.5 10.7452 48.5 24C48.5 37.2548 37.7548 48 24.5 48C11.2452 48 0.5 37.2548 0.5 24Z" fill="#05BF8E"/>
                <path d="M34.0863 19L21.8298 31.2565L16.88 31.9636L17.5871 27.0139L29.8436 14.7574L34.0863 19Z" stroke="white" stroke-width="2"/>
                <path d="M31.7293 21.8284L27.0153 17.1144" stroke="white" stroke-width="2"/>
                </svg><br/></a>'; ?>
        </div>
        <?php
            if(Gdn::session()->isValid()){
                $followedDiscussionsCount = Gdn::sql()
                ->select('d.DiscussionID', 'count', 'CountDiscussions')
                ->from('UserDiscussion d')
                ->where(['d.UserID' => Gdn::session()->UserID, 'd.Bookmarked' => 1])
                ->get()
                ->firstRow()
                ->CountDiscussions;

                echo "<div class='Navigation-linkContainer'>";
                echo Gdn_Theme::link('discussions/bookmarked', sprite('Home').' '.'<span>'.t('Follow ups').'</span>', '<a href="%url" class="%class Navigation-link"><svg class="d-mobile" width="19" height="21" viewBox="0 0 19 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.75049 3.5C1.75049 2.5335 2.53399 1.75 3.50049 1.75H15.2697C16.2362 1.75 17.0197 2.5335 17.0197 3.5V18.6743L10.5623 12.8039C9.89479 12.1971 8.87541 12.1971 8.20792 12.8039L1.75049 18.6743V3.5Z" stroke="black" stroke-width="2.5"/>
                    </svg><br/>
                    %text'. ($followedDiscussionsCount > 0 ? "<span class='realcount Count'>".$followedDiscussionsCount."</span>" : '') .'</a>');
                echo '</div>';
        }?>
        <div class='Navigation-linkContainer mobile'>
            <?php echo Gdn_Theme::link('/search/mobile', t('Explore'), '<a href="%url" class="%class Navigation-link SearchPopup"><svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="9.78105" cy="9.78105" r="8.28105" stroke="black" stroke-width="2"/>
                <path d="M16.25 16.25L20 20" stroke="black" stroke-width="2" stroke-linecap="round"/>
                </svg><br/>%text</a>'); ?>
        </div>

    <?php }
    ?>
     <div class='Navigation-linkContainer d-desktop'>
        <?php
               echo '<a class="Navigation-link ToggleFlyout Flayout-Subject Subject-toggle" rel="/categories/subjectdropdown">';
               echo t("Topics");
               echo '<div class="Flyout FlyoutMenu Flyout-withFrame Flayout-Subject"></div></a>';
        ?>
     </div>
</nav>
