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
class DiscussionFilterModule extends Gdn_Module {

    public function __construct($grade=-1, $sort='desc', $explanation=false, $verified=false, $subject=-1) {
        parent::__construct();
        $this->SubjectID = $subject;
        $this->GradeID = $grade;
        $this->Sort = $sort;
        $this->IsExplanation = $explanation;
        $this->IsVerified = $verified;
    }

    public function assetTarget() {
        return 'Panel';
    }

    public function toString() {
        return parent::toString();
    }
}
