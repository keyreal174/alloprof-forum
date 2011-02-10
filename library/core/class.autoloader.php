<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

/**
 * Vanilla framework autoloader.
 *
 * Handles indexing of class files across the entire framework, as well as bringing those
 * classes into scope as needed.
 *
 * This is a static class that hooks into the SPL autoloader.
 *
 * @author Tim Gunter
 * @copyright 2003 Mark O'Sullivan
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 * @version @@GARDEN-VERSION@@
 * @namespace Garden.Core
 */

class Gdn_Autoloader {

   /**
    * Array of registered maps to search during load requests
    *
    * @var array
    */
   protected static $Maps;
      
   /**
    * Array of pathname prefixes used to namespace similar libraries
    *
    * @var array
    */
   protected static $Prefixes;
   
   /**
    * Array of contexts used to establish search order
    *
    * @var array
    */
   protected static $ContextOrder;
   
   /**
    * Array of maps that pertain to the same CONTEXT+Extension
    *
    * @var array
    */
   protected static $MapGroups;
   
   /**
    * List of priority/preferred CONTEXT+Extension[+MapType] groups for the next lookup 
    *
    * @var array
    */
   protected static $Priorities;
   
   const CONTEXT_CORE            = 'core';
   const CONTEXT_APPLICATION     = 'application';
   const CONTEXT_PLUGIN          = 'plugin';
   const CONTEXT_LOCALE          = 'locale';
   const CONTEXT_THEME           = 'theme';
   
   const MAP_LIBRARY             = 'library';
   const MAP_CONTROLLER          = 'controller';
   const MAP_PLUGIN              = 'plugin';
   const MAP_VENDORS             = 'vendors';
   
   const PRIORITY_TYPE_PREFER    = 'prefer';
   const PRIORITY_TYPE_RESTRICT  = 'restrict';
   
   const PRIORITY_ONCE           = 'once';
   const PRIORITY_PERSIST        = 'persist';
   
   /**
    * Attach mappings for vanilla extension folders
    *
    * @param string $ExtensionType type of extension to map. one of: CONTEXT_THEME, CONTEXT_PLUGIN, CONTEXT_APPLICATION
    */
   public static function Attach($ExtensionType) {
   
      switch ($ExtensionType) {
         case self::CONTEXT_APPLICATION:
         
            if (Gdn::ApplicationManager() instanceof Gdn_ApplicationManager) {
               $EnabledApplications = Gdn::ApplicationManager()->EnabledApplicationFolders();
               
               if (defined('AUTOLOADER') && AUTOLOADER) echo "\nAdding applications folders...\n";
               
               foreach ($EnabledApplications as $EnabledApplication) {
                  $ApplicationPath = CombinePaths(array(PATH_APPLICATIONS."/{$EnabledApplication}"));
                  
                  $AppControllers = CombinePaths(array($ApplicationPath."/controllers"));
                  self::RegisterMap(self::MAP_CONTROLLER, self::CONTEXT_APPLICATION, $AppControllers, array(
                     'SearchSubfolders'      => FALSE,
                     'Extension'             => $EnabledApplication
                  ));
                  
                  $AppModels = CombinePaths(array($ApplicationPath."/models"));
                  self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_APPLICATION, $AppModels, array(
                     'SearchSubfolders'      => FALSE,
                     'Extension'             => $EnabledApplication,
                     'ClassFilter'           => '*model'
                  ));
                  
                  $AppModules = CombinePaths(array($ApplicationPath."/modules"));
                  self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_APPLICATION, $AppModules, array(
                     'SearchSubfolders'      => FALSE,
                     'Extension'             => $EnabledApplication,
                     'ClassFilter'           => '*module'
                  ));
               }
            }
            
         break;
         
         case self::CONTEXT_PLUGIN:

            if (Gdn::PluginManager() instanceof Gdn_PluginManager) {
            
               if (defined('AUTOLOADER') && AUTOLOADER) echo "\nAdding plugin folders...\n";

               foreach (Gdn::PluginManager()->SearchPaths() as $SearchPath => $SearchPathName) {
               
                  if ($SearchPathName === TRUE || $SearchPathName == 1)
                     $SearchPathName = md5($SearchPath);
               
                  // If we have already loaded the plugin manager, use its internal folder list, otherwise scan all subfolders during search
                  if (Gdn::PluginManager()->Started()) {
                     $Folders = Gdn::PluginManager()->EnabledPluginFolders($SearchPath);
                     foreach ($Folders as $PluginFolder) {
                        $FullPluginPath = CombinePaths(array($SearchPath, $PluginFolder));
                        self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_PLUGIN, $FullPluginPath, array(
                           'SearchSubfolders'      => TRUE,
                           'Extension'             => $SearchPathName
                        ));
                     }
                  } else {
                     self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_PLUGIN, $SearchPath, array(
                        'SearchSubfolders'      => TRUE,
                        'Extension'             => $SearchPathName
                     ));
                  }
               }
               
            }

         break;
         
         case self::CONTEXT_THEME:
         
         break;
      }
   
   }
   
   protected static function DoLookup($ClassName, $MapType) {
      // We loop over the caches twice. First, hit only their cached data.
      // If all cache hits miss, search filesystem.
      
      if (defined('AUTOLOADER') && AUTOLOADER) echo '  '.__METHOD__."\n";
      
      if (!is_array(self::$Maps))
         return FALSE;
      
      $Priorities = array();
      
      // Binary flip - cacheonly or cache+fs
      foreach (array(TRUE, FALSE) as $MapOnly) {
      
         $SkipMaps = array(); $ContextType = NULL;
         $SkipTillNextContext = FALSE;
         
         // Drill to the caches associated with this map type
         foreach (self::$Maps as $MapHash => &$Map) {
            if ($Map->MapType() != $MapType) continue;
            
            $MapContext = self::GetContextType($MapHash);
            if ($MapContext != $ContextType) {
               // Hit new context
               $SkipMaps = array();
               $ContextType = $MapContext;
               
               if (!array_key_exists($ContextType, $Priorities))
                  $Priorities[$ContextType] = self::Priorities($ContextType, $MapType);
               
               if (is_array($Priorities[$ContextType]) && sizeof($Priorities[$ContextType]) && defined('AUTOLOADER') && AUTOLOADER) {
                  echo "    Priorities: [{$ContextType} | {$MapType} | ";
                  if (array_key_exists('FAIL_CONTEXT_IF_NOT_FOUND', $Priorities[$ContextType]))
                     echo "restrict]\n";
                  else
                     echo "prefer]\n";
               }
               
               if (array_key_exists($ContextType, $Priorities) && is_array($Priorities[$ContextType])) {
                  foreach ($Priorities[$ContextType] as $PriorityMapHash => $PriorityInfo) {
                  
                     // If we're in a RESTRICT priority and we come to the end, wait till we hit the next context before looking further
                     if ($PriorityMapHash == 'FAIL_CONTEXT_IF_NOT_FOUND') {
                        $SkipTillNextContext = TRUE;
                        break;
                     }
                     $PriorityMap = self::Map($PriorityMapHash);
                     
                     $File = $PriorityMap->Lookup($ClassName, $MapOnly);
                     if ($File !== FALSE) return $File;
                     
                     // Don't check this map again
                     array_push($SkipMaps, $PriorityMapHash);
                  }
               }
            }
            
            // If this map was already checked by a priority, or if we've exhausted a RESTRICT priority, skip maps until the next
            // context level is reached.
            if (in_array($MapHash, $SkipMaps) || $SkipTillNextContext === TRUE) continue;
            
            // Finally, search this map.
            $File = $Map->Lookup($ClassName, $MapOnly);
            if ($File !== FALSE) return $File;
         }
      }
      
      return FALSE;
   }
   
   public static function GetContextType($MapHash) {
      $Matched = preg_match('/^context:(\d+)_.*/', $MapHash, $Matches);
      if ($Matched)
         $ContextIdentifier = GetValue(1, $Matches);
      else
         return FALSE;
         
      return GetValue($ContextIdentifier, self::$ContextOrder, FALSE);
   }
   
   public static function GetMapType($ClassName) {
      // Strip leading 'Gdn_'
      if (substr($ClassName, 0, 4) == 'Gdn_')
         $ClassName = substr($ClassName, 4);
      
      $ClassName = strtolower($ClassName);
      $Length = strlen($ClassName);
      
      if (substr($ClassName, -10) == 'controller' && $Length > 10)
         return self::MAP_CONTROLLER;
      
      return self::MAP_LIBRARY;
   }
   
   public static function Lookup($ClassName, $Options = array()) {
      if (defined('AUTOLOADER') && AUTOLOADER) echo __METHOD__."({$ClassName})\n";
      
      $MapType = self::GetMapType($ClassName);
      if (defined('AUTOLOADER') && AUTOLOADER) echo "  map type: {$MapType}\n";
      
      $DefaultOptions = array(
         'Quiet'              => FALSE,
         'RespectPriorities'  => TRUE
      );
      $Options = array_merge($DefaultOptions, $Options);
      
      $File = self::DoLookup($ClassName, $MapType);
      
      if ($File !== FALSE) {
         if (!GetValue("Quiet", $Options) === TRUE)
            include_once($File);
      }         
      return $File;
   }
   
   public static function Map($MapHash) {
      if (array_key_exists($MapHash, self::$Maps))
         return self::$Maps[$MapHash];
         
      return FALSE;
   }
   
   public static function Priority($ContextType, $Extension, $MapType = NULL, $PriorityType = self::PRIORITY_TYPE_PREFER, $PriorityDuration = self::PRIORITY_ONCE) {
      $MapGroupIdentifier = implode('|',array(
         $ContextType,
         $Extension
      ));
      
      $MapGroupHashes = GetValue($MapGroupIdentifier, self::$MapGroups, array());
      
      foreach ($MapGroupHashes as $MapHash => $Trash) {
         $ThisMapType = self::Map($MapHash)->MapType();
         // We're restricting this priority to a certain maptype, so exclude non matchers
         if (!is_null($MapType) && $ThisMapType != $MapType) continue;
         
         $PriorityHashes[$MapHash] = array(
            'maptype'      => $ThisMapType,
            'duration'     => $PriorityDuration,
            'prioritytype' => $PriorityType
         );
      }
      
      if (!sizeof($PriorityHashes)) return FALSE;
      
      if (!is_array(self::$Priorities)) 
         self::$Priorities = array();
      
      if (!array_key_exists($ContextType,self::$Priorities))
         self::$Priorities[$ContextType] = array(
            self::PRIORITY_TYPE_RESTRICT  => array(),
            self::PRIORITY_TYPE_PREFER    => array()
         );
      
      // Add new priorities to list
      self::$Priorities[$ContextType][$PriorityType] = array_merge(self::$Priorities[$ContextType][$PriorityType], $PriorityHashes);
      
      return TRUE;
   }
   
   public static function Priorities($ContextType, $MapType = NULL) {
      if (!is_array(self::$Priorities) || !array_key_exists($ContextType,self::$Priorities)) 
         return FALSE;
      
      /**
       * First, gather the RESTRICT requirements. If these exist, they are the only hashes that will be sent, and a 'FAIL_IF_NOT_FOUND' 
       * flag will be appended to the list to halt lookups.
       *
       * If there are no RESTRICT priorities, check for PREFER priorities and send those.
       *
       * Always optionally filter on $MapType if provided.
       */
      foreach (array(self::PRIORITY_TYPE_RESTRICT, self::PRIORITY_TYPE_PREFER) as $PriorityType) {
         if (!sizeof(self::$Priorities[$ContextType][$PriorityType])) continue;
         
         $ResultMapHashes = self::$Priorities[$ContextType][$PriorityType];
         $ResponseHashes = array();
         foreach ($ResultMapHashes as $MapHash => $PriorityInfo) {
         
            if (GetValue('duration', $PriorityInfo) == self::PRIORITY_ONCE)
               unset(self::$Priorities[$ContextType][$PriorityType][$MapHash]);
         
            // If this request is being specific about the required maptype, reject anything that doesnt match
            if (!is_null($MapType) && GetValue('maptype', $PriorityInfo) != $MapType)
               continue;
            
            $ResponseHashes[$MapHash] = $PriorityInfo;
         }
         
         if ($PriorityType == self::PRIORITY_TYPE_RESTRICT)
            $ResponseHashes['FAIL_CONTEXT_IF_NOT_FOUND'] = TRUE;
         
         return $ResponseHashes;
      }
      
      return FALSE;
   }
   
   public static function RegisterMap($MapType, $ContextType, $SearchPath, $Options = array()) {
   
      if (defined('AUTOLOADER') && AUTOLOADER) echo __METHOD__."({$MapType}, {$ContextType}, {$SearchPath})\n";
   
      $DefaultOptions = array(
         'SearchSubfolders'      => TRUE,
         'Extension'             => NULL,
         'ContextPrefix'         => NULL,
         'ClassFilter'           => '*'
      );
      if (array_key_exists($ContextType, self::$Prefixes))
         $DefaultOptions['ContextPrefix'] = GetValue($ContextType, self::$Prefixes);
      
      $Options = array_merge($DefaultOptions, $Options);
      
      $Extension = GetValue('Extension', $Options, NULL);
      
      // Determine cache root disk location
      $Hits = 0; str_replace(PATH_LOCAL_ROOT, '', $SearchPath, $Hits);
      if ($Hits) $MapRootLocation = PATH_LOCAL_CACHE;
      else $MapRootLocation = PATH_CACHE;
      
      // Build a unique identifier that refers to this map (same map type, context, extension, and cachefile location)
      $MapIdentifier = implode('|',array(
         $MapType,
         $ContextType,
         $Extension,
         $MapRootLocation
      ));
      $MapHash = md5($MapIdentifier);
      
      // Allow intrinsic ordering / layering of contexts by prefixing them with a context number
      $MapHash = 'context:'.GetValue($ContextType, array_flip(self::$ContextOrder))."_".$MapHash;
      
      if (!is_array(self::$Maps))
         self::$Maps = array();
         
      if (!array_key_exists($MapHash, self::$Maps)) {
         $Map = Gdn_Autoloader_Map::Load($MapType, $ContextType, $MapRootLocation, $Options);
         self::$Maps[$MapHash] = $Map;
      } else {
         if (defined('AUTOLOADER') && AUTOLOADER) echo "  appended path to existing cache\n";
      }
      
      ksort(self::$Maps, SORT_REGULAR);
      
      $AddPathResult = self::$Maps[$MapHash]->AddPath($SearchPath, $Options);
         
      /*
       * Build a unique identifier that refers to this cached list (context and extension)
       *
       * For example, CONTEXT_APPLICATION and 'dashboard' would refer to all maps that store
       * information about the dashboard application: its controllers, models, modules, etc.
       */
      $MapGroupIdentifier = implode('|',array(
         $ContextType,
         $Extension
      ));
      
      if (!is_array(self::$MapGroups))
         self::$MapGroups = array();
      
      if (!array_key_exists($MapGroupIdentifier,self::$MapGroups))
         self::$MapGroups[$MapGroupIdentifier] = array();

      self::$MapGroups[$MapGroupIdentifier][$MapHash] = TRUE;
      
      return $AddPathResult;
   }
   
   /**
    * Register core mappings
    *
    * Set up the autoloader with known searchg directories, hook into the SPL autoloader
    * and load existing caches.
    *
    * @param void
    */
   public static function Start() {
      
      self::$Prefixes = array(
         self::CONTEXT_CORE            => 'c',
         self::CONTEXT_APPLICATION     => 'a',
         self::CONTEXT_PLUGIN          => 'p',
         self::CONTEXT_THEME           => 't'
      );
      self::$ContextOrder = array(
         self::CONTEXT_THEME,
         self::CONTEXT_LOCALE,
         self::CONTEXT_PLUGIN,
         self::CONTEXT_APPLICATION,
         self::CONTEXT_CORE
      );
      
      self::$Maps = array();
      self::$MapGroups = array();
   
      // Register autoloader with the SPL
      spl_autoload_register(array('Gdn_Autoloader', 'Lookup'));
      
      // Configure library/core and library/database
      self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_CORE, PATH_LIBRARY.'/core');
      self::RegisterMap(self::MAP_LIBRARY, self::CONTEXT_CORE, PATH_LIBRARY.'/database');
      
      // Register shutdown function to auto save changed cache files
      register_shutdown_function(array('Gdn_Autoloader', 'Shutdown'));
   }
   
   /**
    * Save current caches
    *
    * This method executes once, just as the framework is shutting down. Its purpose
    * is to save the library maps to disk if they've changed.
    *
    * @param void
    */
   public static function Shutdown() {
      foreach (self::$Maps as $MapHash => &$Map)
         $Map->Shutdown();
   }
   
}

class Gdn_Autoloader_Map {
   
   /**
    * Sprintf format string that describes the on-disk name of the mapping caches
    * 
    * @const string
    */
   const DISK_MAP_NAME_FORMAT = '%s/%s_map.ini';
   
   const LOOKUP_CLASS_MASK = 'class.%s.php';
   const LOOKUP_INTERFACE_MASK = 'interface.%s.php';
   
   protected $MapInfo;
   protected $Map;
   protected $Ignore;
   protected $Paths;
   
   private function __construct($MapType, $ContextType, $MapRootLocation, $Options) {
      $this->Map = NULL;
      $this->Ignore = array('.','..');
      $this->Paths = array();
      
      $ExtensionName = GetValue('Extension', $Options, NULL);
      $Recursive = GetValue('SearchSubfolders', $Options, TRUE);
      $ContextPrefix = GetValue('ContextPrefix', $Options, NULL);
      
      $MapName = $MapType;
      if (!is_null($ExtensionName))
         $MapName = $ExtensionName.'_'.$MapName;
         
      if (!is_null($ContextPrefix))
         $MapName = $ContextPrefix.'_'.$MapName;
      
      $OnDiskMapFile = sprintf(self::DISK_MAP_NAME_FORMAT, $MapRootLocation, strtolower($MapName));
      
      if (defined('AUTOLOADER') && AUTOLOADER) echo "  cache started: {$MapName} - {$OnDiskMapFile}\n";
      
      $this->MapInfo = array(
         'ondisk'       => $OnDiskMapFile,
         'root'         => $MapRootLocation,
         'name'         => $MapName,
         'maptype'      => $MapType,
         'contexttype'  => $ContextType,
         'extension'    => $ExtensionName,
         'dirty'        => FALSE
      );
   }
   
   public function AddPath($SearchPath, $Options) {
      $this->Paths[$SearchPath] = array(
         'path'         => $SearchPath,
         'recursive'    => (bool)GetValue('SearchSubfolders', $Options),
         'filter'       => GetValue('ClassFilter', $Options)
      );
   }
   
   public function ContextType() {
      return GetValue('contexttype', $this->MapInfo);
   }
   
   public function Extension() {
      return GetValue('extension', $this->MapInfo);
   }
   
   protected function FindFile($Path, $SearchFiles, $Recursive) {
      if (!is_array($SearchFiles))
         $SearchFiles = array($SearchFiles);
      
      if (!is_dir($Path)) return FALSE;
      if (defined('AUTOLOADER') && AUTOLOADER) echo "        - findfile: {$Path}\n";
      $Files = scandir($Path);
      foreach ($Files as $FileName) {
         if (in_array($FileName, $this->Ignore)) continue;
         $FullPath = CombinePaths(array($Path, $FileName));
         
         // If this is a folder...
         if (is_dir($FullPath)) {
            if ($Recursive) {
               $File = $this->FindFile($FullPath, $SearchFiles, $Recursive);
               if ($File !== FALSE) return $File;
               continue;
            }
            else {
               continue;
            }
         }
         
         if (in_array($FileName, $SearchFiles)) return $FullPath;
      }
      return FALSE;
   }
   
   /**
    * Autoloader cache static constructor
    *
    * @return Gdn_Autoloader_Map
    */
   public static function Load($MapType, $ContextType, $MapRootLocation, $Options) {
      return new Gdn_Autoloader_Map($MapType, $ContextType, $MapRootLocation, $Options);
   }
   
   public function Lookup($ClassName, $MapOnly = TRUE) {
      $MapName = GetValue('name', $this->MapInfo);
      if (defined('AUTOLOADER') && AUTOLOADER) echo "    ".__METHOD__." [{$MapName}] ({$ClassName}, ".(($MapOnly) ? 'cache': 'cache+fs').")\n";
      
      // Lazyload cache data
      if (is_null($this->Map)) {
         $this->Map = array();
         $OnDiskMapFile = GetValue('ondisk', $this->MapInfo);
         
         if (defined('AUTOLOADER') && AUTOLOADER) echo "      load from disk: {$OnDiskMapFile}... ";
         // Loading cache data from disk
         if (file_exists($OnDiskMapFile)) {
            if (defined('AUTOLOADER') && AUTOLOADER) echo "exists\n";
            $MapContents = parse_ini_file($OnDiskMapFile, FALSE);
            if ($MapContents != FALSE && is_array($MapContents)) {
               $this->Map = $MapContents;
            } else
               @unlink($OnDiskMapFile);
         } else {
            if (defined('AUTOLOADER') && AUTOLOADER) echo "missing\n";
         }
      }
   
      $ClassName = strtolower($ClassName);
      if (array_key_exists($ClassName, $this->Map)) {
         if (defined('AUTOLOADER') && AUTOLOADER) echo "      cache hit\n";
         return GetValue($ClassName, $this->Map);
      }
      // Look at the filesystem, too
      if (!$MapOnly) {
         if (substr($ClassName, 0, 4) == 'gdn_')
            $FSClassName = substr($ClassName, 4);
         else
            $FSClassName = $ClassName;
         
         $Files = array(
            sprintf(self::LOOKUP_CLASS_MASK, $FSClassName),
            sprintf(self::LOOKUP_INTERFACE_MASK, $FSClassName)
         );
         if (defined('AUTOLOADER') && AUTOLOADER) echo "      find: {$Files[0]}\n";
         if (defined('AUTOLOADER') && AUTOLOADER) echo "      find: {$Files[1]}\n";
         
         foreach ($this->Paths as $Path => $PathOptions) {
            $ClassFilter = GetValue('filter', $PathOptions);
            if (!fnmatch($ClassFilter, $ClassName)) continue;
            
            $Recursive = GetValue('recursive', $PathOptions);
            if (defined('AUTOLOADER') && AUTOLOADER) echo "      scan: '{$Path}' recurse: ".(($Recursive) ? 'y': 'n')."\n";
   
            $File = $this->FindFile($Path, $Files, $Recursive);
            
            if ($File !== FALSE) {
               $this->Map[$ClassName] = $File;
               $this->MapInfo['dirty'] = TRUE;
               
               if (defined('AUTOLOADER') && AUTOLOADER) echo "      found {$ClassName} @ {$File}. added back to cache {$MapName}\n";
               return $File;
            }
         }
      }
      
      return FALSE;
   }
   
   public function MapType() {
      return GetValue('maptype', $this->MapInfo);
   }
   
   public function Shutdown() {
      
      if (!GetValue('dirty', $this->MapInfo)) return FALSE;
      
      if (!sizeof($this->Map))
         return FALSE;
         
      if (defined('AUTOLOADER') && AUTOLOADER) echo __METHOD__."\n";
      $MapName = GetValue('name', $this->MapInfo);
      $OnDisk = GetValue('ondisk', $this->MapInfo);
      if (defined('AUTOLOADER') && AUTOLOADER) echo "  saving cache [{$MapName}] @ {$OnDisk}\n";
      
      $FileName = GetValue('ondisk', $this->MapInfo);
      
      $MapContents = "[cache]\n";
      foreach ($this->Map as $ClassName => $Location) {
         $MapContents .= "{$ClassName} = \"{$Location}\"\n";
      }
      try {
         Gdn_FileSystem::SaveFile($FileName, $MapContents, LOCK_EX);
      }
      catch (Exception $e) { return FALSE; }
      
      return TRUE;
   }
   
}



