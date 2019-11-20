<!DOCTYPE html>
<html lang="{$CurrentLocale.Key}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {asset name="Head"}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i" rel="stylesheet">
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
    <nav class="Navigation needsInitialization js-nav">
        <div class="Container">
            {if $User.SignedIn}
                <div class="Navigation-row NewDiscussion">
                    <div class="NewDiscussion mobile">
                        {module name="NewDiscussionModule"}
                    </div>
                </div>
            {else}
                <div class="Navigation-row">
                    <div class="SignIn mobile">
                        {module name="MeModule"}
                    </div>
                </div>
            {/if}
            {categories_link format=$linkFormat}
            {discussions_link format=$linkFormat}
            {activity_link format=$linkFormat}
            {custom_menu format=$linkFormat}
            <div class='Navigation-linkContainer'>
                {community_chooser buttonType='reset' fullWidth=true buttonClass='Navigation-link'}
            </div>
        </div>
    </nav>
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

    <!--[if lt IE 9]>
      <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    <div class="Frame" id="page">
        <div class="Frame-top">
            {if has_data_driven_title_bar === true}
                <div data-react="title-bar-hamburger" style="display: none!important;">
                    {$smarty.capture.menu}
                </div>
             { else }
                <div class="Frame-header">
                    <header id="MainHeader" class="Header">
                        <div class="Container">
                            <div class="row">
                                <div class="Hamburger">
                                    <button class="Hamburger Hamburger-menuXcross" id="menu-button" aria-label="toggle menu">
                                        <span class="Hamburger-menuLines" aria-hidden="true"/>
                                        <span class="Hamburger-visuallyHidden sr-only">
                                            {t c="toggle menu"}
                                        </span>
                                    </button>
                                </div>
                                <a href="{home_link format="%url"}" class="Header-logo">
                                    {logo}
                                </a>
                                <a href="{home_link format="%url"}" class="Header-logo mobile">
                                    {mobile_logo}
                                </a>
                                <nav class="Header-desktopNav">
                                    {categories_link format=$linkFormat}
                                    {discussions_link format=$linkFormat}
                                    {custom_menu format=$linkFormat}
                                </nav>
                                <div class="Header-flexSpacer"></div>
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
                        {$smarty.capture.menu}
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
            {/if}
            <div class="Frame-body">
                <div class="Frame-content">
                    <div class="Container">
                        <div class="Frame-contentWrap">
                            <div class="Frame-details">
                                {if !$isHomepage}
                                    <div class="Frame-row">
                                        <nav class="BreadcrumbsBox">
                                            {breadcrumbs}
                                        </nav>
                                    </div>
                                {/if}
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
                                <div class="Frame-row">
                                    <main class="Content MainContent">
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
                                    <aside class="Panel Panel-main">
                                        {if !$SectionGroups}
                                            <div class="SearchBox js-sphinxAutoComplete" role="search">
                                                {searchbox}
                                            </div>
                                        {/if}
                                        {asset name="Panel"}
                                    </aside>
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
