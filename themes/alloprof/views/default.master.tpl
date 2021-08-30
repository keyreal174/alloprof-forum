<!DOCTYPE html>
<html lang="{$CurrentLocale.Key}">

<head>
    {literal}
    <script src="https://ap-prod-frontend-observer.firebaseapp.com/main.js?f4f1755908a75cde0200"></script>
    <link rel="stylesheet" href="https://appa-staging--pr129-update-connection-wi-2llgv1vn.web.app/styles.css"/>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MF69GGC');</script>
    <!-- End Google Tag Manager -->
    {/literal}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    {asset name="Head"}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,700,700i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clamp-js/0.7.0/clamp.js" integrity="sha512-TnePtmz3HL4p8nFS2lR46u0iHrwObVnUednDASZK/qS9btkd09xKs1PeCt1kpS4a0gWNQx1AF+WnDHDK+xWcAw==" crossorigin="anonymous"></script>
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
{literal}
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MF69GGC"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
{/literal}
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
                        <div class="row">
                            <div class="Header-left">

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
                                <a href="/" class="Header-logo">
                                    <svg width="115" height="25" viewBox="0 0 147 32" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                            </div>
                            <div class="Header-right">
                                {community_chooser buttonType='titleBarLink' buttonClass='Header-desktopCommunityChooser'}

                                <div class="MeBox-header">
                                    {module name="MeModule" CssClass="FlyoutRight"}
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
                    </header>

                    <div class="Header" id="SubHeader">
                        <div class="row">
                            <div class="Header-left">
                                <div class="MobileHeader">
                                    {asset name="MobileHeader"}
                                </div>
                                <div class="Frame-menubar">
                                    {module name="SubMenuModule"}
                                </div>
                            </div>
                            <div class="Header-right">
                                <div class="Header-left__search">
                                    {searchbox}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="Frame-banner Banner">
                    {asset name="Banner"}
                </div>
            {/if}
            <div class="Frame-body">
                <div class="Frame-content">
                    <div class="Container">
                        <div class="Frame-contentWrap">
                            <div class="Frame-details">

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
            <div class="footer-partner">
                <div class="Footer-Section CustomFooter">
                    {module name="CustomFooterModule"}
                </div>
            </div>
        </div>
    </div>
    <a href="{url('/discussions/filter')}" class="discussion-filters-mobile FilterPopup">
        <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.49997 4.79997H5.29997V1.19999C5.29997 0.537255 4.76272 0 4.09998 0C3.43724 0 2.89999 0.537255 2.89999 1.19999V4.79997H1.69999C1.03726 4.79997 0.5 5.33723 0.5 5.99997C0.5 6.6627 1.03726 7.19996 1.69999 7.19996H6.49997C7.1627 7.19996 7.69996 6.6627 7.69996 5.99997C7.69996 5.33723 7.1627 4.79997 6.49997 4.79997ZM4.09991 9.5998C3.43718 9.5998 2.89992 10.1371 2.89992 10.7998V22.7997C2.89992 23.4625 3.43718 23.9997 4.09991 23.9997C4.76265 23.9997 5.29991 23.4625 5.29991 22.7997V10.7998C5.29991 10.1371 4.76265 9.5998 4.09991 9.5998ZM12.4999 19.2C11.8372 19.2 11.3 19.7373 11.3 20.4V22.8C11.3 23.4628 11.8372 24 12.4999 24C13.1627 24 13.6999 23.4628 13.6999 22.8V20.4C13.6999 19.7373 13.1627 19.2 12.4999 19.2ZM23.2998 9.59994H22.0998V1.19999C22.0998 0.537255 21.5626 0 20.8998 0C20.2371 0 19.6998 0.537255 19.6998 1.19999V9.59994H18.4998C17.8371 9.59994 17.2998 10.1372 17.2998 10.7999C17.2998 11.4627 17.8371 11.9999 18.4998 11.9999H23.2998C23.9625 11.9999 24.4998 11.4627 24.4998 10.7999C24.4998 10.1372 23.9625 9.59994 23.2998 9.59994ZM20.8999 14.3999C20.2372 14.3999 19.6999 14.9372 19.6999 15.5999V22.7999C19.6999 23.4626 20.2372 23.9999 20.8999 23.9999C21.5627 23.9999 22.0999 23.4626 22.0999 22.7999V15.5999C22.0999 14.9372 21.5627 14.3999 20.8999 14.3999ZM14.8999 14.3999H13.7V1.19999C13.7 0.537255 13.1627 0 12.5 0C11.8372 0 11.3 0.537255 11.3 1.19999V14.3999H10.1C9.43723 14.3999 8.89998 14.9372 8.89998 15.5999C8.89998 16.2626 9.43723 16.7999 10.1 16.7999H14.8999C15.5627 16.7999 16.0999 16.2626 16.0999 15.5999C16.0999 14.9372 15.5627 14.3999 14.8999 14.3999Z" fill="black"/>
        </svg>
    </a>
    <div id="modals"></div>
    {event name="AfterBody"}
    <alloprof-appa></alloprof-appa>
    <div class="modal-backdrop"></div>
 
{literal}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js"></script>
<script>
  var firebaseConfig = {
    apiKey: "AIzaSyACxE0KuvExUdFyx0gx6z_rBP3Nnhkstlc",
    authDomain: "alloprof-production.firebaseapp.com",
    projectId: "alloprof-production",
    appId: "1:811980803247:web:e68aceb268f2ddf3e4185a"
  };
  firebase.initializeApp(firebaseConfig);
  var auth = firebase.auth();
</script>
<script src="https://appa-staging--pr129-update-connection-wi-2llgv1vn.web.app/alloprof-profile.js"></script>
{/literal}
</body>

</html>
