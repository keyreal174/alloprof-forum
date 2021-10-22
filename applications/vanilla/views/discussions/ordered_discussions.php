<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
if (!function_exists('writeDiscussion'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

if (!function_exists('writeDiscussionDetail'))
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

function reOrderDiscussion($body) {
    $texts = [];
    $images = [];
    $rowArray = json_decode($body, true);
    if(is_array($rowArray)) {
        foreach ($rowArray as $row) {
            if(isset($row['insert'])) {
                if(gettype($row['insert']) == 'string')
                    array_push($texts, $row);
                else if(gettype($row['insert']) == 'array') {
                    if(isset($row['insert']['embed-external']))
                        array_push($images, $row);
                    else array_push($texts, $row);
                }
            } else  {
                array_push($texts, $row);
            }
        }
    }

    return json_encode(array_merge($texts, $images));
}

foreach ($this->DiscussionData->result() as $Discussion) {
    $Discussion->Body = reOrderDiscussion($Discussion->Body);
    writeDiscussionDetail($Discussion, $this, $Session);
}
