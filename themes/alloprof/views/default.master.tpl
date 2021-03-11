<!DOCTYPE html>
<html lang="{$CurrentLocale.Key}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {asset name="Head"}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,700i" rel="stylesheet">
</head>

{assign
"linkFormat"
"<div class='Navigation-linkContainer'>
        <a href='%url' class='Navigation-link %class'>
            %text
        </a>
    </div>"
}

{capture name="menu"}
    {if $User.SignedIn}
        <div class="Navigation-row NewDiscussion">
            <div class="NewDiscussion mobile">
                {module name="NewDiscussionModule" reorder=$DataDrivenTitleBar}
            </div>
        </div>
    {else}
        {if !$DataDrivenTitleBar}
            <div class="Navigation-row">
                <div class="SignIn mobile">
                    {module name="MeModule"}
                </div>
            </div>
        {/if}
    {/if}

    {if !$DataDrivenTitleBar}
        {activity_link format=$linkFormat}
        {categories_link format=$linkFormat}
        {discussions_link format=$linkFormat}
        {knowledge_link format=$linkFormat}
        {custom_menu format=$linkFormat}

    {/if}
{/capture}
{capture name="navLinks"}
    {if !$DataDrivenTitleBar}
        {activity_link format=$linkFormat}
        {categories_link format=$linkFormat}
        {discussions_link format=$linkFormat}
        {custom_menu format=$linkFormat}
    {/if}
{/capture}
{capture name="submenuLinks"}
    {discussions_link format=$linkFormat}
    {mydiscussions_link format=$linkFormat}
    {followed_link format=$linkFormat}
    {resources_link format=$linkFormat}
{/capture}
{assign var="SectionGroups" value=(isset($Groups) || isset($Group))}
{assign var="TemplateCss" value="
    {if $User.SignedIn}
        UserLoggedIn
    {else}
        UserLoggedOut
    {/if}

    {if inSection('Discussion') and $Page gt 1}
        isNotFirstPage
    {/if}

    {if inSection('Group') && !isset($Group.Icon)}
        noGroupIcon
    {/if}

    locale-{$CurrentLocale.Lang}
"}
<body id="{$BodyID}" class="{$BodyClass}{$TemplateCss|strip:" "}">
<a href="#MainContent" class="Button Primary btn button-skipToContent sr-only SrOnly">{t c="Skip to content"}</a>

    <!--[if lt IE 9]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    <div class="Frame" id="page">
        <div class="Frame-top">
            {if $DataDrivenTitleBar}
                <header id="titleBar" data-react="title-bar-hamburger" style="display: none!important;" data-unhide="true">
                    {$smarty.capture.menu}
                </header>
            {else}
                <div class="Frame-header">
                    <header id="MainHeader" class="Header">
                        <div class="Container">
                            <div class="row">
                                <div class="Header-left">
                                    <div class="Header-left__search">
                                        {searchbox}
                                    </div>
                                </div>
                                <div class="Header-logo">
                                    <div class="Hamburger">
                                        <button class="Hamburger Hamburger-menuXcross" id="menu-button" aria-label="toggle menu">
                                            <span class="Hamburger-menuLines" aria-hidden="true"/>
                                            <span class="Hamburger-visuallyHidden sr-only">
                                                {t c="toggle menu"}
                                            </span>
                                        </button>
                                    </div>
                                    <a href="{home_link format="%url"}" class="Header-logo">
                                        <svg width="147" height="32" viewBox="0 0 147 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M58.4417 28.5418C52.6308 28.5418 47.9027 23.7727 47.9027 17.9115C47.9027 12.0503 52.6308 7.28125 58.4417 7.28125C64.2526 7.28125 68.9808 12.0503 68.9808 17.9115C68.9808 23.7727 64.2526 28.5418 58.4417 28.5418ZM58.4417 10.8125C54.5798 10.8125 51.4398 13.9798 51.4398 17.8751C51.4398 21.7704 54.5798 24.9377 58.4417 24.9377C62.3036 24.9377 65.4437 21.7704 65.4437 17.8751C65.4437 13.9798 62.3036 10.8125 58.4417 10.8125Z" fill="#1A1919"/>
                                            <path d="M123.517 28.5418C117.706 28.5418 112.978 23.7727 112.978 17.9115C112.978 12.0503 117.706 7.28125 123.517 7.28125C129.328 7.28125 134.056 12.0503 134.056 17.9115C134.056 23.7727 129.328 28.5418 123.517 28.5418ZM123.517 10.8125C119.655 10.8125 116.515 13.9798 116.515 17.8751C116.515 21.7704 119.655 24.9377 123.517 24.9377C127.379 24.9377 130.519 21.7704 130.519 17.8751C130.519 13.9798 127.379 10.8125 123.517 10.8125Z" fill="#1A1919"/>
                                            <path d="M31.228 0.328125H27.4383V28.5784H31.228V0.328125Z" fill="#1A1919"/>
                                            <path d="M42.0197 0.328125H38.23V28.5784H42.0197V0.328125Z" fill="#1A1919"/>
                                            <path d="M92.0079 10.3758C90.0228 8.3007 87.352 7.13574 84.5007 7.13574C82.6239 7.13574 80.8192 7.64541 79.2312 8.59194C79.1229 8.66475 79.0146 8.70116 78.9063 8.77397C76.0189 10.667 74.2865 13.9071 74.2865 17.4748V19.2586V27.5954V30.7626V31.964L77.8957 29.8525L81.6854 27.6318C82.7321 27.923 83.8149 28.0686 84.9338 28.0322C90.2755 27.8138 94.6427 23.4452 94.8953 18.0572C95.0036 15.1812 93.993 12.4509 92.0079 10.3758ZM84.7894 24.5009C83.4179 24.5373 82.1547 24.2097 81.0719 23.5544L77.8596 25.4111V19.2586V18.9674V17.584C77.8596 15.3269 78.9785 13.179 80.8553 11.9412C80.9275 11.9048 80.9997 11.8684 81.0719 11.832C82.0825 11.2131 83.2374 10.8855 84.5007 10.8855C88.3265 10.8855 91.4304 14.1255 91.25 18.0208C91.1056 21.5157 88.2543 24.3917 84.7894 24.5009Z" fill="#1A1919"/>
                                            <path d="M0.0078753 18.0937C0.260523 23.4816 4.62773 27.8502 9.96943 28.0686C11.0883 28.105 12.1711 27.9594 13.2178 27.6682L17.0075 29.8889L20.6167 32.0004V30.799V27.6318V19.2586V17.4748C20.6167 13.9071 18.8843 10.667 15.9969 8.77397C15.8886 8.70116 15.7803 8.62835 15.6721 8.59194C14.084 7.64541 12.2433 7.13574 10.4025 7.13574C7.55123 7.13574 4.88038 8.3007 2.89528 10.3758C0.91019 12.4509 -0.100402 15.1812 0.0078753 18.0937ZM3.61713 18.0208C3.43667 14.1255 6.54064 10.8855 10.3665 10.8855C11.6297 10.8855 12.7847 11.2495 13.7952 11.832C13.8674 11.8684 13.9396 11.9048 14.0118 11.9412C15.8886 13.179 17.0075 15.3269 17.0075 17.584V18.9674V19.2586V25.4111L13.7952 23.5544C12.7125 24.2097 11.4492 24.5373 10.0777 24.5009C6.61282 24.3917 3.76151 21.5157 3.61713 18.0208Z" fill="#1A1919"/>
                                            <path d="M146.363 0C141.924 0 139.47 2.40273 139.47 6.80774V28.5779H143.259V12.2685H146.363V8.44596H143.259V6.80774C143.259 5.75199 143.44 5.02389 143.801 4.58703C144.234 4.07736 145.1 3.82253 146.327 3.82253H146.472V0H146.363Z" fill="#1A1919"/>
                                            <path d="M107.889 6.77148C101.717 6.77148 100.778 11.759 100.778 14.7078V28.5781H104.568V14.7442C104.568 12.0502 105.109 10.6304 107.889 10.6304H108.033V6.77148H107.889Z" fill="#1A1919"/>
                                        </svg>
                                    </a>
                                    <a href="{home_link format="%url"}" class="Header-logo mobile">
                                        {mobile_logo}
                                    </a>
                                </div>
                                <div class="Header-right">
                                    {community_chooser buttonType='titleBarLink' buttonClass='Header-desktopCommunityChooser'}
                                    <div class="MeBox-header">
                                        {module name="MeModule" CssClass="FlyoutRight"}
                                    </div>
                                    {if $User.SignedIn}
                                        <button class="mobileMeBox-button">
                                            <span class="Photo PhotoWrap">
                                                <img src="{$User.Photo|escape:'html'}" class="ProfilePhotoSmall" alt="{t c='Avatar'}">
                                            </span>
                                        </button>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <nav class="Navigation needsInitialization js-nav">
                            <div class="Container">
                                {$smarty.capture.navLinks}
                                <div class='Navigation-linkContainer'>
                                    {community_chooser buttonType='reset' fullWidth=true buttonClass='Navigation-link'}
                                </div>
                            </div>
                        </nav>
                        <nav class="mobileMebox js-mobileMebox needsInitialization">
                            <div class="Container">
                                {module name="MeModule"}
                                <button class="mobileMebox-buttonClose Close">
                                    <span>×</span>
                                </button>
                            </div>
                        </nav>
                    </header>
                </div>
                <div class="Frame-banner Banner">
                    {asset name="Banner"}
                </div>
            {/if}
            <div class="Frame-body">
                <div class="Frame-content">
                    <div class="Container">
                        <div class="Frame-contentWrap">
                            <div class="Frame-menubar">
                                {if $User.SignedIn}
                                        {module name="SubMenuModule"}
                                {/if}
                            </div>

                            <div class="Frame-details">
                                {if !$DataDrivenTitleBar}
                                    <div class="Frame-row SearchBoxMobile">
                                        {if !$SectionGroups && !inSection(["SearchResults"])}
                                            <div class="SearchBox js-sphinxAutoComplete" role="search">
                                                {if $hasAdvancedSearch === true}
                                                    {module name="AdvancedSearchModule"}
                                                {else}
                                                    {searchbox}
                                                {/if}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                                <div class="Frame-row">
                                    <div class="sidebar left">
                                        <aside class="Panel Panel-main LeftPanel">
                                            {asset name="Panel"}
                                        </aside>
                                        <div class="Extra">
                                            {asset name="Extra"}
                                        </div>
                                    </div>
                                    <main id="MainContent" class="Content MainContent">
                                        {if inSection("Profile")}
                                            <div class="Profile-header">
                                                <div class="Profile-photo">
                                                    <div class="PhotoLarge">
                                                        {module name="UserPhotoModule"}
                                                    </div>
                                                </div>
                                                <div class="Profile-name">
                                                    <h1 class="Profile-username">
                                                        {$Profile.Name|escape:'html'}
                                                    </h1>
                                                    {if isset($Rank)}
                                                        <span class="Profile-rank">{$Rank.Label|escape:'html'}</span>
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                        {asset name="Content"}
                                    </main>
                                    <div class="sidebar right">
                                        <!-- {module name="ProfileEditModule"} -->
                                        <aside class="Panel Panel-main ProfilePanel">
                                            {asset name="LeftPanel"}
                                        </aside>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="Frame-footer">
            {include file="partials/footer.tpl"}
        </div>
    </div>
    <div id="modals"></div>
    {event name="AfterBody"}
</body>

</html>
