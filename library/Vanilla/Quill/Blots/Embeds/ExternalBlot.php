<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Quill\Blots\Embeds;

use Gdn;
use Gdn_Controller;
use HeadModule;
use Vanilla\Embeds\EmbedManager;

class ExternalBlot extends AbstractBlockEmbedBlot {

    /** @var EmbedManager */
    private $embedManager;

    /**
     * @inheritdoc
     */
    public function __construct(array $currentOperation, array $previousOperation, array $nextOperation) {
        parent::__construct($currentOperation, $previousOperation, $nextOperation);

        /** @var EmbedManager embedManager */
        $this->embedManager = Gdn::getContainer()->get(EmbedManager::class);
    }

    /**
     * @inheritDoc
     */
    protected static function getInsertKey(): string {
        return "insert.embed-external";
    }

    /**
     * @inheritDoc
     */
    protected function renderContent(array $value): string {
        $data = $value['data'] ?? $value;
        $type = $data['type'] ?? '';
        try {
            $rendered = "<div class='js-embed embedResponsive'>".$this->embedManager->renderData($data)."</div>";
        } catch (\Exception $e) {
            $rendered = ''; // Silently fail.
        }

        return $rendered;
    }
}
