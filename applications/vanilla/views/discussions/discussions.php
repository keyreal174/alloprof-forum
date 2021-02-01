<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
if (!function_exists('writeDiscussion'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

if (!function_exists('formatBody'))
    include($this->fetchViewLocation('helper_functions', 'discussion', 'vanilla'));

if (!function_exists('formatMeAction'))
    include($this->fetchViewLocation('helper_functions', 'discussion', 'vanilla'));

if (property_exists($this, 'AnnounceData') && is_object($this->AnnounceData)) {
    foreach ($this->AnnounceData->result() as $Discussion) {
        // writeDiscussion($Discussion, $this, $Session);
    }
}

foreach ($this->DiscussionData->result() as $Discussion) {
    // writeDiscussion($Discussion, $this, $Session);
    writeDiscussionDetail($Discussion, $this, $Session);
}
