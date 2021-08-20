<?php
/**
 *
 * Changes:
 *  1.0     Release
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license Proprietary
 */

/**
 * Class AlloprofPlugin
 */
class AlloprofPlugin extends Gdn_Plugin {

    /**
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Run once on enable.
     */
    public function setup() {
        $this->structure();
    }

    /**
     * Database updates.
     */
    public function structure() {
        $St = Gdn::structure();
        $Sql = Gdn::sql();

        /**
         * Add grade to discussion and comment
         * Add publish feature
         * Add verify feature
         */

        $St->table('Discussion')
            ->column('GradeID', 'int', true)
            ->column('Published', 'tinyint(1)', '0')
            ->column('DateAccepted', 'datetime', true)
            ->column('AcceptedUserID', 'int', true)
            ->column('Resolved', 'tinyint(1)', 0)
            ->column('Cycle', 'int', 0)
            ->column('Language', 'varchar(5)', ['Null' => false, 'Default' => 'fr'])
            ->set();

        $St->table('Comment')
            ->column('GradeID', 'int', true)
            ->column('Published', 'tinyint(1)', '0')
            ->column('DateAccepted', 'datetime', true)
            ->column('AcceptedUserID', 'int', true)
            ->column('Language', 'varchar(5)', ['Null' => false, 'Default' => 'fr'])
            ->set();

        $St->table('Category')
            ->column('Color', 'varchar(50)', null)
            ->column('Language', 'varchar(5)', ['Null' => false, 'Default' => 'fr'])
            ->column('LinkedCategoryID', 'int', ['Null' => true])
            ->set();

        $St->table('User')
            ->column('ProfileLanguage', 'varchar(5)', ['Null' => false, 'Default' => 'fr'])
            ->set();

        $St->table('Log')
            ->column('Language', 'varchar(5)', ['Null' => true])
            ->set();

        // NOTE: disabled as was one-time script to fix existing users
        //// Set default preferences for all users
        //
        //$preferences = '{"Email.CustomNotification": "1", "Email.ConversationMessage":false, "Email.BookmarkComment":false, "Popup.DiscussionComment":"1","Popup.Moderation":"1"}';
        //
        //$Sql->update('User')
        //   ->set('Preferences', $preferences)
        //   ->put();

        // NOTE: disabled as was one-time script to fix existing users
        //// Set Published as true in comments table for the records which are not in the log model

        $data = $Sql->query("SELECT COUNT(GDN_Log.LogID) AS Cnt, GDN_Comment.CommentID FROM GDN_Comment
        LEFT JOIN GDN_Log on GDN_Comment.CommentID = GDN_Log.RecordID AND GDN_Log.RecordType = 'Comment'
        WHERE GDN_Comment.Published IS false
        GROUP BY GDN_Comment.CommentID")->resultArray();

        foreach ($data as $row) {
            if ($row["Cnt"] == 0) {
                $Sql->update('Comment')
                    ->set('Published', TRUE)
                    ->where('CommentID', $row["CommentID"])
                    ->put();
            }
        }

        $Sql->query("UPDATE vanilla_dev.GDN_Log
        LEFT JOIN vanilla_dev.GDN_Discussion ON vanilla_dev.GDN_Log.RecordID = vanilla_dev.GDN_Discussion.DiscussionID
        SET vanilla_dev.GDN_Log.Language = vanilla_dev.GDN_Discussion.Language
        WHERE vanilla_dev.GDN_Log.RecordType = 'Discussion';");

        $Sql->query("UPDATE vanilla_dev.GDN_Log
        LEFT JOIN vanilla_dev.GDN_Comment ON vanilla_dev.GDN_Log.RecordID = vanilla_dev.GDN_Comment.CommentID
        SET vanilla_dev.GDN_Log.Language = vanilla_dev.GDN_Comment.Language
        WHERE vanilla_dev.GDN_Log.RecordType = 'Comment';");
    }
}
