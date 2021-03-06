<?php
/**
 * Discussion filters module
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

/**
 * Renders the discussion filter menu.
 */
class DashboardDiscussionFilterModule extends Gdn_Module {

    public function __construct($grade=-1, $sort='desc', $explanation=false, $verified=false, $subject=-1) {
        $this->_ApplicationFolder = 'dashboard';
        $this->SubjectID = $subject;
        $this->GradeID = $grade;
        $this->Sort = $sort;
        $this->IsExplanation = $explanation;
        $this->IsVerified = $verified;

        parent::__construct();
    }

    public function assetTarget() {
        return 'Panel';
    }

    public function toString() {
        return parent::toString();
    }
}
