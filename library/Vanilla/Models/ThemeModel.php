<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Models;

use Gdn_Session;
use Vanilla\Database\Operation\CurrentUserFieldProcessor;
use Vanilla\Database\Operation\CurrentDateFieldProcessor;
use Vanilla\Theme\VariablesProviderInterface;

/**
 * Handle custom themes.
 */
class ThemeModel extends PipelineModel {

    /** @var VariablesProviderInterface[] */
    private $variableProviders = [];

    /** @var Gdn_Session */
    private $session;

    /**
     * ThemeModel constructor.
     *
     * @param Gdn_Session $session
     */
    public function __construct(Gdn_Session $session) {
        parent::__construct("theme");
        $this->session = $session;

        $dateProcessor = new CurrentDateFieldProcessor();
        $dateProcessor->setInsertFields(["dateInserted", "dateUpdated"])
            ->setUpdateFields(["dateUpdated"]);
        $this->addPipelineProcessor($dateProcessor);

        $userProcessor = new CurrentUserFieldProcessor($this->session);
        $userProcessor->setInsertFields(["insertUserID", "updateUserID"])
            ->setUpdateFields(["updateUserID"]);
        $this->addPipelineProcessor($userProcessor);
    }

    /**
     * Add a theme-variable provider.
     *
     * @param VariablesProviderInterface $provider
     */
    public function addVariableProvider(VariablesProviderInterface $provider) {
        $this->variableProviders[] = $provider;
    }

    /**
     * Get all configured theme-variable providers.
     *
     * @return array
     */
    public function getVariableProviders(): array {
        return $this->variableProviders;
    }
}
