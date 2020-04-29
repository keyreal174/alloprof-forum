<?php
/**
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Library\Jobs;

use Garden\Schema\Schema;
use Vanilla\Scheduler\Job\JobExecutionStatus;
use Vanilla\Scheduler\Job\JobPriority;
use Vanilla\Scheduler\Job\LocalJobInterface;

/**
 * Update category discussion count.
 */
class UpdateDiscussionCount implements LocalJobInterface {

    /** @var \DiscussionModel */
    private $discussionModel;

    /** @var int */
    private $categoryID;

    /**
     * Initial job setup.
     *
     * @param \DiscussionModel $discussionModel
     */
    public function __construct(\DiscussionModel $discussionModel) {
        $this->discussionModel = $discussionModel;
    }

    /**
     * Validate the message against the schema.
     *
     * @return Schema
     */
    private function messageSchema(): Schema {
        $schema = Schema::parse(["categoryID" => ["type" => "integer"]]);
        return $schema;
    }

    /**
     * Update category discussion count.
     */
    public function run(): JobExecutionStatus {
        if (!is_int($this->categoryID)) {
            return JobExecutionStatus::abandoned();
        }
        $this->discussionModel->updateDiscussionCount($this->categoryID);
        return JobExecutionStatus::complete();
    }

    /**
     * Set job Message
     *
     * @param array $message
     */
    public function setMessage(array $message) {
        $message = $this->messageSchema()->validate($message);
        $this->categoryID = $message["categoryID"];
    }

    /**
     * Set job priority
     *
     * @param JobPriority $priority
     * @return void
     */
    public function setPriority(JobPriority $priority) {
    }

    /**
     * Set job execution delay
     *
     * @param int $seconds
     * @return void
     */
    public function setDelay(int $seconds) {
    }
}
