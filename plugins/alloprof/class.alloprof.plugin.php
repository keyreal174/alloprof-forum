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
            ->set();

        $St->table('Comment')
            ->column('GradeID', 'int', true)
            ->column('Published', 'tinyint(1)', '0')
            ->column('DateAccepted', 'datetime', true)
            ->column('AcceptedUserID', 'int', true)
            ->set();

        $St->table('Category')
            ->column('Color', 'varchar(50)', null)
            ->set();
    }

}
