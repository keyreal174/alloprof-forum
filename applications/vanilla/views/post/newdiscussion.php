<?php
    require_once Gdn::controller()->fetchViewLocation('helper_functions', 'Discussions', 'Vanilla');
    $User = val('User', Gdn::controller());
    if (!$User && Gdn::session()->isValid()) {
        $User = Gdn::session()->User;
    }
    $Photo = $User->Photo;
    if ($Photo) {
        $Photo = (isUrl($Photo)) ? $Photo : Gdn_Upload::url(changeBasename($Photo, 'p%s'));
        $PhotoAlt = t('Avatar');
    } else {
        $Photo = UserModel::getDefaultAvatarUrl($User, 'profile');
        $PhotoAlt = t('Default Avatar');
    }

    if ($User->Banned) {
        $BannedPhoto = c('Garden.BannedPhoto', 'https://images.v-cdn.net/banned_large.png');
        if ($BannedPhoto) {
            $Photo = Gdn_Upload::url($BannedPhoto);
        }
    }
?>

<div class="BoxButtons BoxNewDiscussion AskQuestionForm AskQuestionFormPopup">
    <div class="BoxNewDiscussion-header">
        <?php
            $UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Profile.%', 'Profile.');
            $UserName = $UserMetaData['DisplayName'] ?? "";

            $photoClassName = 'user-avatar';
            if (str_contains($Photo, 'avatars/0.svg')) {
                $photoClassName = $photoClassName.' ProfilePhotoDefaultWrapper';
            }
            echo '<span class="'.$photoClassName.'" avatar--first-letter="'.$UserName[0].'">';
            echo img($Photo, ['class' => 'user-avatar', 'alt' => $PhotoAlt]);
            echo '</span>';
        ?>
        <div>
            <?php
                echo "<div class='user-info show'>";
                echo "<div class='username'>".$UserName."</div>";
                echo "<div class='meta'>".t($UserMetaData['Grade'])."</div>";
                echo "</div>";
            ?>
        </div>
        <?php
            if($this->invalid) {
                $Controller = Gdn::controller();
                $Session = Gdn::session();
                $SigninUrl = signInUrl($Controller->SelfUrl);

                echo '<a href="'.url("/entry/jsconnect-redirect?client_id=alloprof").'" class="SignInStudentPopupAgent " rel="nofollow">'.t('What is your question?').'</a>';
            } else echo '<div class="clickToCreate">'.t('What is your question?').'</div>';
        ?>
    </div>
    <div class="close-icon">
        <img src="<?= url('/themes/alloprof/design/images/icons/close.svg') ?>" />
    </div>

    <div id="DiscussionForm1" class="FormTitleWrapper DiscussionForm">
        <?php
            echo '<div class="FormWrapper">';
            echo $this->Form->open();
            echo $this->Form->errors();
        ?>

        <div class="content">

            <?php
                if($this->invalid) {
                    $Controller = Gdn::controller();
                    $Session = Gdn::session();
                    $SigninUrl = signInUrl($Controller->SelfUrl);

                    echo '<a href="'.url($SigninUrl).'" class="SignInPopup" rel="nofollow">';
                    echo '<div class="placeholder">';
                } else echo '<div class="placeholder OpenAskQuestionForm">';
            ?>
                <!-- <div class="icon">
                    <?php /* echo '<img src="'.url("/themes/alloprof/design/images/icons/ask_question.svg").'" />'; */ ?>
                </div> -->

            </div>
            <?php if($this->invalid) echo '</a>';?>
            <?php
                if(!$this->invalid) {
                    $this->fireEvent('BeforeFormInputs');

                    echo '<div class="P">';
                    echo wrap($this->Form->Hidden('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true', 'value' => 'Question']), 'div', ['class' => 'TextBoxWrapper']);
                    echo '</div>';

                    $this->fireEvent('BeforeBodyInput');

                    echo '<div class="P">';
                    echo $this->Form->bodyBox('Body', ['Table' => 'Discussion', 'FileUpload' => true, 'placeholder' => t('Type your question'), 'title' => t('Type your question')]);
                    echo '</div>';
                }
            ?>
        </div>
        <?php
            if(!$this->invalid) {
                echo '<div class="selects">';
                // if ($this->ShowCategorySelector === true) {
                    $Controller = Gdn::controller();
                    if($Controller->data('SelectedCategory')) {
                        $category = $Controller->data('SelectedCategory');
                        $options = ['Value' => $category, 'IncludeNull' => true, 'AdditionalPermissions' => ['PermsDiscussionsAdd']];
                    }

                    $Session = Gdn::session();
                    $DefaultGrade = 0;
                    if ($Session) {
                        $UserID = $Session->UserID;
                        $AuthorMetaData = Gdn::userModel()->getMeta($UserID, 'Profile.%', 'Profile.');
                        if ($AuthorMetaData['Grade']) {
                            $DefaultGrade = $AuthorMetaData['Grade'];
                        }
                    }

                    $fields = c('ProfileExtender.Fields', []);
                    if (!is_array($fields)) {
                        $fields = [];
                    }
                    foreach ($fields as $k => $field) {
                        if ($field['Label'] == "Grade") {
                            $GradeOption = array_filter($field['Options'], function($v) {
                                return preg_match('/(Primaire|Secondaire|Post-secondaire)/', $v);
                            });
                            $GradeOption = array_map(function($val) {
                                return t($val);
                            }, $GradeOption);

                            if ($DefaultGrade && $DefaultGrade !== 0) {
                                $DefaultGrade = array_search(t($DefaultGrade), $GradeOption);
                            }
                        }
                    }

                    echo writeCategoryDropDown($this, 'CategoryID', $options);
                    echo '<span class="space"></span>';
                    echo '<div class="Category rich-select select2 select2-grade">';
                    echo '<div class="pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/grade.svg").'"/></div>';
                    echo $this->Form->dropDown('GradeID', $GradeOption, array('IncludeNull' => true, 'Value' => $DefaultGrade));
                    echo '</div>';
                // }
                echo '</div>';
                echo '<div class="Buttons">';

                $this->fireEvent('BeforeFormButtons');
                if(!Gdn::session()->isValid()) {
                    $Controller = Gdn::controller();
                    $Session = Gdn::session();
                    $SigninUrl = signInUrl($Controller->SelfUrl);

                    // echo '<a data-url="'.url("/entry/jsconnect-redirect?client_id=alloprof&target=").urlencode('/discussions/saveDiscussion').'" class="btn-default btn-shadow signinandsave">'.t('Publish').'</a>';
                    echo anchor(t('Publish'), '/entry/signinstudent?Target=discussions/saveDiscussions', 'btn-default btn-shadow SignInStudentPopupAgent SaveDiscussion');
                    echo anchor(t('Publish'), '/entry/signinstudent?Target=discussions', 'btn-default btn-shadow SignInStudentPopup HiddenImportant');
                } else {
                    echo $this->Form->button((property_exists($this, 'Discussion')) ? t('Save') : t('Publish'), ['class' => 'btn-default btn-shadow']);
                }

                $this->fireEvent('AfterFormButtons');
                echo '</div>';
            }
                echo $this->Form->close();
                echo '</div>';

        ?>
    </div>

</div>

<div class="information-block newdiscussion show">
    <div>
        <div class="img-box">
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0)">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M56.1207 25.7508C55.9299 21.4319 52.4381 17.9434 48.118 17.757C47.7407 13.4902 44.1352 10.1138 39.7741 10.1138H24.5864C20.7639 10.1138 17.5251 12.7107 16.5282 16.2247C16.5282 16.2247 7.69771 24.9486 7.70769 25.1184C2.4173 26.4793 -0.902516 32.8898 3.31493 38.8642C4.55265 40.6162 6.612 41.6098 8.75734 41.6098H23.2749C25.2149 44.5147 28.5214 46.4398 32.2568 46.4398H41.7469L45.0828 52.8536H50.8098L49.2692 46.4398H51.4156C57.0111 46.4398 61.9742 42.356 62.5573 36.7899C63.0688 31.9011 60.2605 27.565 56.1207 25.7508Z" fill="#FF55C3"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M45.9221 51.4665H49.0478L47.5071 45.0526H51.4128C56.3936 45.0526 60.6815 41.36 61.1753 36.6455C61.6047 32.5479 59.3484 28.6805 55.562 27.0211L54.7709 26.6744L54.7326 25.8123C54.5745 22.2267 51.6419 19.2975 48.0564 19.1427L46.8414 19.0905L46.7343 17.8794C46.4181 14.3028 43.3596 11.5011 39.7718 11.5011H24.5841C21.4773 11.5011 18.7123 13.5993 17.8602 16.6035L17.7603 16.9546L17.5007 17.2115C14.7223 19.956 10.3556 24.3022 9.12457 25.6015L9.15897 26.1768L8.05107 26.4619C5.81419 27.0372 4.00338 28.6755 3.20671 30.843C2.35512 33.1615 2.79507 35.7257 4.44554 38.0646C5.40032 39.4155 7.01141 40.2227 8.75508 40.2227H24.0144L24.426 40.8396C26.188 43.4782 29.1145 45.0526 32.2545 45.0526H42.5862L45.9221 51.4665ZM52.5658 54.2402H44.2368L40.9009 47.8264H32.253C28.4355 47.8264 24.865 46.0344 22.5571 42.9965H8.75354C6.11 42.9965 3.65232 41.7504 2.17827 39.6644C-0.0142308 36.5582 -0.57456 33.0858 0.601577 29.8864C1.66509 26.991 4.02624 24.7735 6.96381 23.8853C7.88641 22.8113 10.09 20.6316 15.3005 15.4843C16.6037 11.4871 20.3673 8.72705 24.5826 8.72705H39.7703C44.3922 8.72705 48.3849 12.0552 49.3247 16.4907C53.5205 17.1836 56.8614 20.606 57.4273 24.8412C61.8672 27.1519 64.4586 31.9091 63.9327 36.9349C63.2925 43.0419 57.7929 47.8264 51.4113 47.8264H51.0251L52.5658 54.2402Z" fill="black"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M22.8426 16.1567H15.7741C11.1639 16.1567 7.39246 19.9281 7.39246 24.5378C7.39246 29.1481 11.1639 32.9195 15.7741 32.9195H25.4001H29.5055C32.4636 32.9195 34.8614 35.3172 34.8614 38.2748" fill="#FF55C3"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M49.5039 23.8962H46.73C46.73 20.3938 43.8801 17.5439 40.3777 17.5439C36.8748 17.5439 34.0255 20.3938 34.0255 23.8962H31.2516C31.2516 18.8643 35.3453 14.77 40.3777 14.77C45.4096 14.77 49.5039 18.8643 49.5039 23.8962ZM44.5242 39.4882H41.7503C41.7503 34.4563 45.8446 30.362 50.8765 30.362H55.8629V33.1359H50.8765C47.3741 33.1359 44.5242 35.9858 44.5242 39.4882ZM45.3871 47.1646H48.1755C52.5788 47.1646 54.6298 45.0742 54.6298 40.5877V38.0485H51.8559V40.5877C51.8559 43.5375 51.0304 44.3907 48.1755 44.3907H45.3871V47.1646ZM33.4649 32.821H34.4283C38.5081 32.821 41.828 29.5017 41.828 25.4218V23.8962H39.054V25.4218C39.054 27.9722 36.9792 30.0471 34.4283 30.0471H29.2838V31.5331H15.774C11.9171 31.5331 8.77928 28.3953 8.77928 24.5384C8.77928 20.6816 11.9171 17.5443 15.774 17.5443H22.8424V14.7704H15.774C10.3876 14.7704 6.00537 19.152 6.00537 24.5384C6.00537 29.5616 9.81661 33.7116 14.6992 34.2483V34.3067C16.6077 34.3067 18.1605 35.8595 18.1605 37.768H20.9344C20.9344 36.4885 20.547 35.2978 19.8834 34.307H29.5054C31.694 34.307 33.4743 36.0873 33.4743 38.2754H36.2482C36.2482 36.0359 35.1505 34.0478 33.4649 32.821ZM20.2166 28.7261C17.3029 28.7261 14.9329 26.3561 14.9329 23.4429H17.7068C17.7068 24.8266 18.8324 25.9522 20.2166 25.9522V28.7261Z" fill="black"/>
            </g>
            <defs>
            <clipPath id="clip0">
            <rect width="64" height="64" fill="white"/>
            </clipPath>
            </defs>
            </svg>
        </div>
        <p><?php echo t('Formulate your question as clearly as possible.') ?></p>
    </div>
    <div>
        <div class="img-box">
            <svg width="64" height="57" viewBox="0 0 64 57" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.57143 0.363525C1.15127 0.363525 0 1.51479 0 2.93495V46.0984C0 47.5186 1.15127 48.6698 2.57143 48.6698L23.6434 48.6698C23.8716 48.6698 24.0905 48.7609 24.2514 48.9228L31.392 56.109C31.7272 56.4463 32.2728 56.4463 32.608 56.109L39.7486 48.9228C39.9095 48.7609 40.1284 48.6698 40.3566 48.6698L61.4286 48.6698C62.8487 48.6698 64 47.5186 64 46.0984V2.93495C64 1.51479 62.8487 0.363525 61.4286 0.363525H2.57143Z" fill="#FF7575"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.90909 3.27262V45.7608L23.6434 45.7607C24.6464 45.7607 25.608 46.1608 26.3149 46.8723L32 52.5937L37.6851 46.8723C38.392 46.1608 39.3536 45.7607 40.3566 45.7607L61.0909 45.7608V3.27262H2.90909ZM0 2.93495C0 1.51479 1.15127 0.363525 2.57143 0.363525H61.4286C62.8487 0.363525 64 1.51479 64 2.93495V46.0984C64 47.5186 62.8487 48.6698 61.4286 48.6698L40.3566 48.6698C40.1284 48.6698 39.9095 48.7609 39.7486 48.9228L32.608 56.109C32.2728 56.4463 31.7272 56.4463 31.392 56.109L24.2514 48.9228C24.0905 48.7609 23.8716 48.6698 23.6434 48.6698L2.57143 48.6698C1.15127 48.6698 0 47.5186 0 46.0984V2.93495Z" fill="black"/>
            <path d="M14.6401 22.3635L14.1241 20.7555L12.0601 21.5835L12.2161 19.3635H10.5241L10.6801 21.5835L8.60413 20.7555L8.08813 22.3635L10.2241 22.9035L8.80813 24.6075L10.1761 25.6035L11.3641 23.7075L12.5521 25.6035L13.9201 24.6075L12.4921 22.9035L14.6401 22.3635Z" fill="black"/>
            <path d="M21.7379 22.6755V23.0355C21.4619 22.6635 21.1019 22.4835 20.5859 22.4835C19.4339 22.4835 18.5819 23.4075 18.5819 24.7035C18.5819 26.0115 19.4339 26.9355 20.6459 26.9355C21.3899 26.9355 21.9659 26.5395 22.2179 25.9155C22.4579 26.3715 22.9859 26.6715 23.6819 26.6715C25.0259 26.6715 25.9139 25.5435 25.9139 23.9595C25.9139 21.5715 23.9699 19.8435 21.2099 19.8435C18.2219 19.8435 16.1459 21.8355 16.1459 24.7035C16.1459 27.5595 18.1859 29.5515 21.1499 29.5515C22.3139 29.5515 23.3699 29.2515 24.3419 28.5915L23.6699 27.2715C22.7339 27.8355 21.9059 28.0755 21.0779 28.0755C19.0619 28.0755 17.7299 26.7195 17.7299 24.7155C17.7299 22.6875 19.0979 21.3075 21.1979 21.3075C23.0579 21.3075 24.4019 22.3995 24.4019 24.1155C24.4019 24.8235 24.1739 25.3035 23.7419 25.3035C23.3579 25.3035 23.2739 24.9315 23.2739 24.6915V22.6755H21.7379ZM21.0419 25.4955C20.5259 25.4955 20.2139 25.1595 20.2139 24.7035C20.2139 24.2475 20.5259 23.9235 21.0179 23.9235C21.3419 23.9235 21.5939 24.0435 21.7379 24.2595V25.1715C21.5939 25.3755 21.3419 25.4955 21.0419 25.4955Z" fill="black"/>
            <path d="M36.4213 23.2875L36.4813 21.8115H34.6453L34.7053 19.9635H33.2053L33.1453 21.8115H31.2133L31.2733 19.9635H29.7853L29.7253 21.8115H27.9253L27.8653 23.2875H29.6773L29.6053 25.0395H27.7813L27.7333 26.5035H29.5573L29.4973 28.3635H30.9853L31.0453 26.5035H32.9773L32.9173 28.3635H34.4173L34.4773 26.5035H36.2893L36.3493 25.0395H34.5253L34.5973 23.2875H36.4213ZM33.0253 25.0395H31.0933L31.1653 23.2875H33.0973L33.0253 25.0395Z" fill="black"/>
            <path d="M40.9837 19.8435C39.7357 19.8435 38.7157 20.2995 38.1037 21.0315L39.2437 22.1835C39.7357 21.6915 40.3117 21.4395 40.8397 21.4395C41.4517 21.4395 41.8357 21.7155 41.8357 22.1595C41.8357 22.7595 41.1757 23.0595 39.9877 23.0715V25.0875H41.7517L41.7757 24.0435C42.9757 23.6835 43.6957 22.9515 43.6957 21.9195C43.6957 20.6595 42.6157 19.8435 40.9837 19.8435ZM40.8397 26.0595C40.0837 26.0595 39.5197 26.5755 39.5197 27.2715C39.5197 27.9555 40.0837 28.4835 40.8397 28.4835C41.5837 28.4835 42.1357 27.9555 42.1357 27.2715C42.1357 26.5755 41.5837 26.0595 40.8397 26.0595Z" fill="black"/>
            <path d="M47.734 19.9635H45.946L45.958 25.0995H47.722L47.734 19.9635ZM46.858 26.0595C46.102 26.0595 45.538 26.5755 45.538 27.2715C45.538 27.9555 46.102 28.4835 46.858 28.4835C47.602 28.4835 48.154 27.9555 48.154 27.2715C48.154 26.5755 47.602 26.0595 46.858 26.0595Z" fill="black"/>
            <path d="M52.7499 19.8435C51.5019 19.8435 50.4819 20.2995 49.8699 21.0315L51.0099 22.1835C51.5019 21.6915 52.0779 21.4395 52.6059 21.4395C53.2179 21.4395 53.6019 21.7155 53.6019 22.1595C53.6019 22.7595 52.9419 23.0595 51.7539 23.0715V25.0875H53.5179L53.5419 24.0435C54.7419 23.6835 55.4619 22.9515 55.4619 21.9195C55.4619 20.6595 54.3819 19.8435 52.7499 19.8435ZM52.6059 26.0595C51.8499 26.0595 51.2859 26.5755 51.2859 27.2715C51.2859 27.9555 51.8499 28.4835 52.6059 28.4835C53.3499 28.4835 53.9019 27.9555 53.9019 27.2715C53.9019 26.5755 53.3499 26.0595 52.6059 26.0595Z" fill="black"/>
            </svg>
        </div>
        <p><?php echo t('Do not publish anything offensive.') ?></p>
    </div>
    <div>
        <div class="img-box">
            <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M50.3889 60C51.524 60 52.5908 59.5578 53.3932 58.7543L58.7563 53.3913C59.5573 52.5918 60 51.5245 60 50.3889C60 49.2533 59.5573 48.1859 58.7533 47.3835L48.4803 37.11C47.8263 36.4574 47.0111 36.0628 46.1629 35.9247C48.0194 32.4221 48.9879 28.5307 48.9879 24.4939C48.9879 10.988 37.9999 0 24.494 0C10.9882 0 0.000164032 10.988 0.000164032 24.4939C0.000164032 37.9999 10.9882 48.9879 24.494 48.9879C28.5298 48.9879 32.4212 48.0194 35.9238 46.1629C36.0614 47.0115 36.4575 47.8262 37.11 48.4793L47.3865 58.7558C48.1874 59.5578 49.2543 60 50.3889 60Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M50.7853 56.1499L56.1498 50.7853C56.369 50.5662 56.369 50.2114 56.1498 49.9922L45.8738 39.7158C45.6547 39.4971 45.2999 39.4971 45.0807 39.7158L39.7162 45.0808C39.497 45.2994 39.497 45.6547 39.7162 45.8734L49.9927 56.1499C50.2114 56.369 50.5666 56.369 50.7853 56.1499Z" fill="#FF55C3"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M24.494 45.3023C35.9862 45.3023 45.3025 35.986 45.3025 24.4938C45.3025 13.0016 35.9862 3.68579 24.494 3.68579C13.0019 3.68579 3.68559 13.0016 3.68559 24.4938C3.68559 35.986 13.0019 45.3023 24.494 45.3023Z" fill="#EEF05D"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M24.4941 40.1178C33.1234 40.1178 40.1184 33.1228 40.1184 24.4939C40.1184 15.8651 33.1234 8.87012 24.4941 8.87012C15.8652 8.87012 8.87027 15.8651 8.87027 24.4939C8.87027 33.1228 15.8652 40.1178 24.4941 40.1178Z" fill="white"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M24.494 4.91425C35.2904 4.91425 44.074 13.6979 44.074 24.4943C44.074 28.7429 42.7137 32.6798 40.4059 35.8924H40.2921L35.8927 40.2923V40.4056C32.68 42.7135 28.7429 44.0738 24.494 44.0738C13.6977 44.0738 4.91406 35.2907 4.91406 24.4943C4.91406 13.6979 13.6977 4.91425 24.494 4.91425ZM46.5309 24.4943C46.5309 29.0042 45.1691 33.202 42.8351 36.6982L44.6589 38.5218C45.3313 38.1768 46.1792 38.2852 46.7424 38.8476L57.0174 49.1232C57.3565 49.4607 57.5432 49.9104 57.5432 50.389C57.5432 50.8676 57.3565 51.3172 57.0184 51.6538L51.6539 57.0188C51.3163 57.3569 50.8672 57.5427 50.389 57.5427C49.9109 57.5427 49.4613 57.3564 49.1242 57.0188L38.8477 46.7424C38.5096 46.4043 38.3234 45.9551 38.3234 45.477C38.3234 45.1874 38.3918 44.9083 38.5211 44.6581L36.6982 42.835C33.2019 45.169 29.004 46.5308 24.494 46.5308C12.3429 46.5308 2.45707 36.6454 2.45707 24.4943C2.45707 12.3431 12.3429 2.45728 24.494 2.45728C36.6451 2.45728 46.5309 12.3431 46.5309 24.4943ZM46.2497 41.8291V41.85L41.8498 46.2495H41.8294L50.389 54.809L54.8091 50.389L46.2497 41.8291ZM40.1987 42.8612L38.6814 41.344C39.6424 40.5335 40.5334 39.6426 41.3438 38.6816L42.8612 40.1987L40.1987 42.8612ZM24.494 10.0986C32.4319 10.0986 38.8898 16.5565 38.8898 24.4939C38.8898 32.4319 32.4319 38.8893 24.494 38.8893C16.5565 38.8893 10.0987 32.4319 10.0987 24.4939C10.0987 16.5565 16.5565 10.0986 24.494 10.0986ZM24.494 41.3462C33.7867 41.3462 41.3468 33.7866 41.3468 24.4939C41.3468 15.2017 33.7867 7.6416 24.494 7.6416C15.2017 7.6416 7.64165 15.2017 7.64165 24.4939C7.64165 33.7866 15.2017 41.3462 24.494 41.3462ZM36.4559 24.4941H33.9989C33.9989 19.2529 29.7351 14.9896 24.4939 14.9896V12.5327C31.0898 12.5327 36.4559 17.8987 36.4559 24.4941Z" fill="black"/>
            </svg>
        </div>
        <p><?php echo t('Make sure that your question hasn’t already been asked.') ?></p>
    </div>
</div>
<script >
    function formatState (state) {
        var data = $(state.element).data();
        if (!state.id) { return state.text; }
        var icon = '<div class="category-img"></div>';

        if(data && data['img_src']){
            icon = '<div class="category-img"><img src="'+data['img_src'] + '"/></div>';
        }
        var $state = $(
          '<span class="image-option">'+ icon + state.text + '</span>'
       );
       return $state;
    };

    function selectCategoryImg (obj) {
        if(obj) {

            var data = $(obj.element).data();
            var parent = $(obj.element).parent().parent().parent();

            if(data && data['img_src']){
                parent.find('.category-selected-img').html('<img src="'+data['img_src']+'"/>');
            }

            if(data && data['img_src'] === '') {
                parent.find('.category-selected-img').html('');
            }
        }
    }

    var selectGradePlaceholder = 'Niveau';
    if (gdn.meta.siteSection.contentLocale == 'en') {
        selectGradePlaceholder = 'Level';
    }

    $('.scrollToAskQuestionFormPopup .select2-grade select').select2({
        minimumResultsForSearch: -1,
        placeholder: selectGradePlaceholder,
    });

    var selectCategoryPlaceholder = 'Matière';
    if (gdn.meta.siteSection.contentLocale == 'en') {
        selectCategoryPlaceholder = 'Topic';
    }

    $('.scrollToAskQuestionFormPopup .select2-category select').select2({
        placeholder: selectCategoryPlaceholder,
        minimumResultsForSearch: -1,
        templateResult: formatState
    }).on('select2:select', function (e) {
        var data = e.params.data;
        selectCategoryImg(data);
    });

    selectCategoryImg({element: $('.FilterMenu .select2-category option:selected')});
    selectCategoryImg({element: $('.EditDiscussionDetail .select2-category option:selected')});
</script>