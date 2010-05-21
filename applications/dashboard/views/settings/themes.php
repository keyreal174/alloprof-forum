<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
$AddonUrl = Gdn::Config('Garden.AddonUrl');
?>
<h1><?php echo T('Manage Themes'); ?></h1>
<?php
if ($AddonUrl != '')
   echo '<div class="FilterMenu">',
      Anchor('Get More Themes', $AddonUrl),
      '</div>';
         
?>
<div class="Info">
<?php
printf(
   T('ThemeHelp'),
   '<code>'.PATH_THEMES.'</code>'
);
?></div>
<?php echo $this->Form->Errors(); ?>
<div class="Messages Errors TestAddonErrors Hidden">
   <ul>
      <li><?php echo T('The addon could not be enabled because it generated a fatal error: <pre>%s</pre>'); ?></li>
   </ul>
</div>
<div class="CurrentTheme">
   <h3><?php echo T('Current Theme'); ?></h3>
   <?php
   $Version = GetValue('Version', $this->EnabledTheme, '');
   $ThemeUrl = GetValue('Url', $this->EnabledTheme, '');
   $Author = GetValue('Author', $this->EnabledTheme, '');
   $AuthorUrl = GetValue('AuthorUrl', $this->EnabledTheme, '');   
   $NewVersion = GetValue('NewVersion', $this->EnabledTheme, '');
   $Upgrade = $NewVersion != '' && version_compare($NewVersion, $Version, '>');
   $PreviewImage = SafeGlob(PATH_THEMES . DS . $this->EnabledThemeFolder . DS . "screenshot.*");
   $PreviewImage = count($PreviewImage) > 0 ? basename($PreviewImage[0]) : FALSE;
   if ($PreviewImage && in_array(strtolower(pathinfo($PreviewImage, PATHINFO_EXTENSION)), array('gif','jpg','png')))
      echo Img('/themes/'.$this->EnabledThemeFolder.'/'.$PreviewImage, array('alt' => $this->EnabledThemeName, 'height' => '112', 'width' => '150'));
   
   echo '<h4>';
      echo $ThemeUrl != '' ? Url($this->EnabledThemeName, $ThemeUrl) : $this->EnabledThemeName;
      if ($Version != '')
         echo '<span class="Version">'.sprintf(T('version %s'), $Version).'</span>';
         
      if ($Author != '')
         echo '<span class="Author">'.sprintf('by %s', $AuthorUrl != '' ? Anchor($Author, $AuthorUrl) : $Author).'</span>';
   
   echo '</h4>';
   echo '<div class="Description">'.GetValue('Description', $this->EnabledTheme, '').'</div>';
	$this->FireEvent('AfterCurrentTheme');
   
   $RequiredApplications = GetValue('RequiredApplications', $this->EnabledTheme, FALSE);
   if (is_array($RequiredApplications)) {
      echo '<div class="Requirements">'.T('Requires: ');

      $i = 0;
      if ($i > 0)
         echo ', ';
      
      foreach ($RequiredApplications as $RequiredApplication => $VersionInfo) {   
         printf(T('%1$s Version %2$s'), $RequiredApplication, $VersionInfo);
         ++$i;
      }
      echo '</div>';
   }
   
   if ($Upgrade) {
      echo '<div class="Alert">';
      echo Url(
            sprintf(T('%1$s version %2$s is available.'), $this->EnabledThemeName, $NewVersion),
            CombinePaths(array($AddonUrl, 'find', urlencode($this->EnabledThemeName)), '/')
         );
      echo '</div>';
   }
   ?>
</div>
<?php if (count($this->AvailableThemes) > 1) { ?>
<div class="BrowseThemes">
   <h3><?php echo T('Other Themes'); ?></h3>
   <table class="SelectionGrid Themes">
      <tbody>
   <?php
   $Alt = FALSE;
   $Cols = 3;
   $Col = 0;
   foreach ($this->AvailableThemes as $ThemeName => $ThemeInfo) {
      $ScreenName = GetValue('Name', $ThemeInfo, $ThemeName);
      $ThemeFolder = GetValue('Folder', $ThemeInfo, '');
      $Active = $ThemeFolder == $this->EnabledThemeFolder;
      if (!$Active) {
         $Version = GetValue('Version', $ThemeInfo, '');
         $ThemeUrl = GetValue('Url', $ThemeInfo, '');
         $Author = GetValue('Author', $ThemeInfo, '');
         $AuthorUrl = GetValue('AuthorUrl', $ThemeInfo, '');   
         $NewVersion = GetValue('NewVersion', $ThemeInfo, '');
         $Upgrade = $NewVersion != '' && version_compare($NewVersion, $Version, '>');
         $PreviewImage = SafeGlob(PATH_THEMES . DS . $ThemeFolder . DS . "screenshot.*", GLOB_BRACE);
         $PreviewImage = count($PreviewImage) > 0 ? basename($PreviewImage[0]) : FALSE;
         if($PreviewImage && !in_array(strtolower(pathinfo($PreviewImage, PATHINFO_EXTENSION)), array('gif','jpg','png')))
				$PreviewImage = FALSE;
         $Col++;
         if ($Col == 1) {
            $ColClass = 'FirstCol';
            echo '<tr>';
         } elseif ($Col == 2) {
            $ColClass = 'MiddleCol';      
         } else {
            $ColClass = 'LastCol';
            $Col = 0;
         }
         $ColClass .= $Active ? ' Enabled' : '';
         $ColClass .= $PreviewImage ? ' HasPreview' : '';
         ?>
            <td class="<?php echo $ColClass; ?>">
               <?php
                  echo '<h4>';
                     echo $ThemeUrl != '' ? Url($ScreenName, $ThemeUrl) : $ScreenName;
                     if ($Version != '')
                        $Info = sprintf(T('Version %s'), $Version);
                        
                     if ($Author != '')
                        $Info .= sprintf('by %s', $AuthorUrl != '' ? Anchor($Author, $AuthorUrl) : $Author);
      
                  echo '</h4>';
                  
                  if ($PreviewImage) {
                     echo Anchor(Img('/themes/'.$ThemeFolder.'/'.$PreviewImage, array('alt' => $ScreenName, 'height' => '112', 'width' => '150')),
                        'dashboard/settings/previewtheme/'.$ThemeFolder,
                        '',
                        array('target' => '_top')
                     );
                  }

                  echo '<div class="Buttons">';
                  echo Anchor('Apply', 'dashboard/settings/themes/'.$ThemeFolder.'/'.$Session->TransientKey(), 'SmallButton EnableAddon', array('target' => '_top'));
                  echo Anchor('Preview', 'dashboard/settings/previewtheme/'.$ThemeFolder, 'SmallButton PreviewAddon', array('target' => '_top'));
                  echo '</div>';

                  $Description = GetValue('Description', $ThemeInfo);
                  if ($Description)
                     echo '<em>'.$Description.'</em>';
                     
                  $RequiredApplications = GetValue('RequiredApplications', $ThemeInfo, FALSE);
                  if (is_array($RequiredApplications)) {
                     echo '<dl>
                        <dt>'.T('Requires').'</dt>
                        <dd>';

                     $i = 0;
                     foreach ($RequiredApplications as $RequiredApplication => $VersionInfo) {   
                        if ($i > 0)
                           echo ', ';
                           
                        printf(T('%1$s %2$s'), $RequiredApplication, $VersionInfo);
                        ++$i;
                     }
                     echo '</dl>';
                  }
                  
                  if ($Upgrade) {
                     echo '<div class="Alert">';
                     echo Anchor(
                           sprintf(T('%1$s version %2$s is available.'), $ScreenName, $NewVersion),
                           CombinePaths(array($AddonUrl, 'find', urlencode($ThemeName)), '/')
                        );
                     echo '</div>';
                  }
               ?>
            </td>
            <?php
         if ($Col == 0)
            echo '</tr>';
      }
   }
   // Close the row if it wasn't a full row.
   if ($Col > 0)
      echo '<td class="LastCol EmptyCol"'.($Col == 1 ? ' colspan="2"' : '').'>&nbsp;</td></tr>';
   ?>
      </tbody>
   </table>
</div>
<?php
}