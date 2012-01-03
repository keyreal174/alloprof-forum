<?php if (!defined('APPLICATION')) exit();

/**
 * Theme system
 * 
 * Allows access to theme controls from within views, to give themers a unified
 * toolset for interacting with Vanilla from within views.
 *
 * @author Mark O'Sullivan <markm@vanillaforums.com>
 * @copyright 2003 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 * @since 2.0
 */

class Gdn_Theme {

   protected static $_AssetInfo = array();
   public static function AssetBegin($AssetContainer = 'Panel') {
      self::$_AssetInfo[] = array('AssetContainer' => $AssetContainer);
      ob_start();
   }

   public static function AssetEnd() {
      if (count(self::$_AssetInfo) == 0)
         return;

      $Asset = ob_get_clean();
      $AssetInfo = array_pop(self::$_AssetInfo);

      Gdn::Controller()->AddAsset($AssetInfo['AssetContainer'], $Asset);
   }

   public static function Breadcrumbs($Data, $Format = '<a href="{Url,html}">{Name,html}</a>', $HomeLink = TRUE) {
      $Result = '';

      if ($HomeLink) {
         $Row = array('Name' => $HomeLink, 'Url' => Url('/', TRUE));
         if (!is_string($HomeLink))
            $Row['Name'] = T('Home');
         
         
         $Result .= '<span class="CrumbLabel">'.FormatString($Format, $Row).'</span> ';
      }
      
      $DefaultRoute = ltrim(GetValue('Destination', Gdn::Router()->GetRoute('DefaultController'), ''), '/');

      foreach ($Data as $Row) {
         if (ltrim($Row['Url'], '/') == $DefaultRoute && $HomeLink)
            continue; // don't show default route twice.
         
         $Row['Url'] = Url($Row['Url']);
         $Label = '<span class="CrumbLabel">'.FormatString($Format, $Row).'</span> ';
         $Result = ConcatSep('<span class="Crumb">'.T('Breadcrumbs Crumb', '&raquo;').'</span> ', $Result, $Label);
      }

      $Result ='<span class="Breadcrumbs">'.$Result.'</span>';
      return $Result;
   }
   
   public static function Link($Path, $Text = FALSE, $Format = NULL, $Options = array()) {
      $Session = Gdn::Session();
      $Class = GetValue('class', $Options, '');
      $WithDomain = GetValue('WithDomain', $Options);
      $Target = GetValue('Target', $Options, '');
      
      if (is_null($Format))
         $Format = '<a href="%url" class="%class">%text</a>';

      switch ($Path) {
         case 'activity':
            TouchValue('Permissions', $Options, 'Garden.Activity.View');
            break;
         case 'category':
            $Breadcrumbs = Gdn::Controller()->Data('Breadcrumbs');
            if (is_array($Breadcrumbs) && count($Breadcrumbs) > 0) {
               $Last = array_pop($Breadcrumbs);
               $Path = GetValue('Url', $Last);
               $DefaultText = GetValue('Name', $Last, T('Back'));
            } else {
               $Path = '/';
               $DefaultText = C('Garden.Title', T('Back'));
            }
            if (!$Text)
               $Text = $DefaultText;
            break;
         case 'dashboard':
            $Path = 'dashboard/settings';
            TouchValue('Permissions', $Options, array('Garden.Settings.Manage','Garden.Settings.View'));
            if (!$Text)
               $Text = T('Dashboard');
            break;
         case 'home':
            $Path = '/';
            if (!$Text)
               $Text = T('Home');
            break;
         case 'inbox':
            $Path = 'messages/inbox';
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text)
               $Text = T('Inbox');
            if ($Session->IsValid() && $Session->User->CountUnreadConversations) {
               $Class = trim($Class.' HasCount');
               $Text .= ' <span class="Alert">'.$Session->User->CountUnreadConversations.'</span>';
            }
            break;
         case 'forumroot':
            $Route = Gdn::Router()->GetDestination('DefaultForumRoot');
            if (is_null($Route))
               $Path = '/';
            else
               $Path = CombinePaths (array('/',$Route));
            break;
         case 'profile':
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text && $Session->IsValid())
               $Text = $Session->User->Name;
            if ($Session->IsValid() && $Session->User->CountNotifications) {
               $Class = trim($Class.' HasCount');
               $Text .= ' <span class="Alert">'.$Session->User->CountNotifications.'</span>';
            }
            break;
         case 'user':
            $Path = 'profile';
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text && $Session->IsValid())
               $Text = $Session->User->Name;

            break;
         case 'photo':
            $Path = 'profile';
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text && $Session->IsValid()) {
               $IsFullPath = strtolower(substr($Session->User->Photo, 0, 7)) == 'http://' || strtolower(substr($Session->User->Photo, 0, 8)) == 'https://';
               $PhotoUrl = ($IsFullPath) ? $Session->User->Photo : Gdn_Upload::Url(ChangeBasename($Session->User->Photo, 'n%s'));
               $Text = Img($PhotoUrl, array('alt' => htmlspecialchars($Session->User->Name)));
            }

            break;
         case 'drafts':
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text)
               $Text = T('My Drafts');
            if ($Session->IsValid() && $Session->User->CountDrafts) {
               $Class = trim($Class.' HasCount');
               $Text .= ' <span class="Alert">'.$Session->User->CountDrafts.'</span>';
            }
            break;
         case 'discussions/bookmarked':
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text)
               $Text = T('My Bookmarks');
            if ($Session->IsValid() && $Session->User->CountBookmarks) {
               $Class = trim($Class.' HasCount');
               $Text .= ' <span class="Count">'.$Session->User->CountBookmarks.'</span>';
            }
            break;
         case 'discussions/mine':
            TouchValue('Permissions', $Options, 'Garden.SignIn.Allow');
            if (!$Text)
               $Text = T('My Discussions');
            if ($Session->IsValid() && $Session->User->CountDiscussions) {
               $Class = trim($Class.' HasCount');
               $Text .= ' <span class="Count">'.$Session->User->CountDiscussions.'</span>';
            }
            break;
         case 'signin':
         case 'signinout':
            // The destination is the signin/signout toggle link.
            if ($Session->IsValid()) {
               if(!$Text)
                  $Text = T('Sign Out');
               $Path =  SignOutUrl($Target);
               $Class = ConcatSep(' ', $Class, 'SignOut');
            } else {
               if(!$Text)
                  $Text = T('Sign In');
               $Attribs = array();

               $Path = SignInUrl($Target);
               if (SignInPopup() && strpos(Gdn::Request()->Url(), 'entry') === FALSE)
                  $Class = ConcatSep(' ', $Class, 'SignInPopup');
            }
            break;
      }

      if (GetValue('Permissions', $Options) && !$Session->CheckPermission($Options['Permissions'], FALSE))
         return '';

      $Url = Gdn::Request()->Url($Path, $WithDomain);
      
      if ($TK = GetValue('TK', $Options)) {
         if (in_array($TK, array(1, 'true')))
            $TK = 'TransientKey';
         $Url .= (strpos($Url, '?') === FALSE ? '?' : '&').$TK.'='.urlencode(Gdn::Session()->TransientKey());
      }

      if (strcasecmp(trim($Path, '/'), Gdn::Request()->Path()) == 0)
         $Class = ConcatSep(' ', $Class, 'Selected');

      // Build the final result.
      $Result = $Format;
      $Result = str_replace('%url', $Url, $Result);
      $Result = str_replace('%text', $Text, $Result);
      $Result = str_replace('%class', $Class, $Result);

      return $Result;
   }

   /**
    * Renders the banner logo, or just the banner title if the logo is not defined.
    */
   public static function Logo() {
      $Logo = C('Garden.Logo');
      if ($Logo) {
         $Logo = ltrim($Logo, '/');
         // Fix the logo path.
         if (StringBeginsWith($Logo, 'uploads/'))
            $Logo = substr($Logo, strlen('uploads/'));
      }
      $Title = C('Garden.Title', 'Title');
      echo $Logo ? Img(Gdn_Upload::Url($Logo), array('alt' => $Title)) : $Title;
   }

   public static function Module($Name) {
      try {
         if (!class_exists($Name)) {
            $Result = "Error: $Name doesn't exist";
         } else {
               $Module = new $Name(Gdn::Controller(), '');
               $Result = $Module->ToString();

         }
      } catch (Exception $Ex) {
         if (Debug())
            $Result = '<pre class="Exception">'.htmlspecialchars($Ex->getMessage()."\n".$Ex->getTraceAsString()).'</pre>';
         else
            $Result = $Ex->getMessage();
      }
      return $Result;
   }
   
   public static function Pagename() {
      $Application = Gdn::Dispatcher()->Application();
      $Controller = Gdn::Dispatcher()->Controller();
      switch ($Controller) {
         case 'discussions':
         case 'discussion':
         case 'post':
            return 'discussions';
            
         case 'inbox':
            return 'inbox';
            
         case 'activity':
            return 'activity';
            
         case 'profile':
            $Args = Gdn::Dispatcher()->ControllerArguments();
            if (!sizeof($Args) || (sizeof($Args) && $Args[0] == Gdn::Session()->UserID))
               return 'profile';
            break;
      }
      
      return 'unknown';
   }

   public static function Text($Code, $Default) {
      return C("ThemeOption.{$Code}", T('Theme_'.$Code, $Default));
   }
}