<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

use Garden\Schema\Schema;
use Garden\Web\Data;
use Garden\Web\Exception\NotFoundException;
use Vanilla\Models\AuthenticatorModel;
use Vanilla\Authenticator\Authenticator;
use Vanilla\Authenticator\SSOAuthenticator;

/**
 * Class AuthenticatorsApiController
 */
class AuthenticatorsApiController extends AbstractApiController  {

    /** @var AuthenticatorModel */
    private $authenticatorModel;

    /** @var Schema */
    private $idParamSchema;

    /** @var Schema */
    private $typeParamSchema;

    /** @var Schema */
    private $fullSchema;

    /**
     * AuthenticatorsApiController constructor.
     *
     * @param AuthenticatorModel $authenticatorModel
     */
    public function __construct(AuthenticatorModel $authenticatorModel) {
        $this->authenticatorModel = $authenticatorModel;
    }

    /**
     * Get an Authenticator.
     *
     * @throws NotFoundException
     * @param string $type
     * @param string $id
     * @return Authenticator
     */
    public function getAuthenticator(string $type, string $id): Authenticator {
        try {
            $authenticator = $this->authenticatorModel->getAuthenticator($type, $id);
        } catch (Exception $e) {
            Logger::log(Logger::DEBUG, 'authenticator_not_found', [
                'authenticatorType' => $type,
                'authenticatorID' => $id,
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);

            throw new NotFoundException('Authenticator');
        }

        return $authenticator;
    }

    /**
     * Get an Authenticator by its ID.
     *
     * @throws NotFoundException
     * @param string $id
     * @return Authenticator
     */
    public function getAuthenticatorByID(string $id): Authenticator {
        try {
            $authenticator = $this->authenticatorModel->getAuthenticatorByID($id);
        } catch (Exception $e) {
            Logger::log(Logger::DEBUG, 'authenticator_not_found', [
                'authenticatorType' => null,
                'authenticatorID' => $id,
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ]);

            throw new NotFoundException('Authenticator');
        }

        return $authenticator;
    }

    /**
     * @return AuthenticatorModel
     */
    public function getAuthenticatorModel() {
        return $this->authenticatorModel;
    }

    /**
     * Get the full Authenticator schema.
     *
     * @return Schema
     */
    public function fullSchema() {
        if (!$this->fullSchema) {
            $this->fullSchema = $this->schema(
                // Use the SSOAuthenticator schema but make the sso attribute optional.
                Schema::parse([
                    'resourceUrl:s' => 'API URL to get the Authenticator',
                    'sso?',
                ])->merge(SSOAuthenticator::getAuthenticatorSchema()),
                'Authenticator'
            );
        }

        return $this->fullSchema;
    }

    /**
     * Get an ID-only Authenticator record schema.
     *
     * @return Schema Returns a schema object.
     */
    public function idParamSchema() {
        if ($this->idParamSchema === null) {
            $this->idParamSchema = Schema::parse([
                'authenticatorID:s' => 'The Authenticator ID.',
            ]);
        }
        return $this->schema($this->idParamSchema, 'in');
    }

    /**
     * Get a Type-only Authenticator record schema.
     *
     * @return Schema Returns a schema object.
     */
    public function typeParamSchema(): Schema {
        if ($this->typeParamSchema === null) {
            $this->typeParamSchema = Schema::parse([
                'type:s' => 'The Authenticator type.',
            ]);
        }
        return $this->schema($this->typeParamSchema, 'in');
    }

    /**
     * GET an Authenticator.
     *
     * @param string $type
     * @param string $id
     * @return array
     */
    public function get(string $type, string $id): array {
        $this->permission('Garden.Setting.Manage');

        $this->typeParamSchema();
        $this->idParamSchema();
        $this->schema([], ['AuthenticatorGet', 'in'])->setDescription('Get an Authenticator.');
        $out = $this->schema($this->fullSchema(), 'out');

        $authenticator = $this->getAuthenticator($type, $id);

        $result = $this->normalizeOutput($authenticator);

        return $out->validate($result);
    }

    /**
     * List authenticators.
     *
     * @return Data
     */
    public function index(): Data {
        $this->permission('Garden.Setting.Manage');

        $this->schema([], ['AuthenticatorIndex', 'in'])->setDescription('List authenticators.');
        $out = $this->schema([':a' => $this->fullSchema()], 'out');

        $authenticators = $this->authenticatorModel->getAuthenticators();
        $result = [];
        foreach ($authenticators as $authenticator) {
            $result[] = $this->normalizeOutput($authenticator);
        }

        $data = $out->validate($result);

        return new Data($data);
    }

    /**
     * Normalize an Authenticator to match the Schema definition.
     *
     * @param Authenticator $authenticator
     * @return array Return a Schema record.
     *
     * @throws \Garden\Schema\ValidationException
     */
    public function normalizeOutput(Authenticator $authenticator): array {
        $record = $authenticator->getAuthenticatorInfo();
        $record['authenticatorID'] = strtolower($record['authenticatorID']);
        $record['type'] = strtolower($record['type']);
        $record['resourceUrl'] = strtolower(url('/api/v2/authenticators/'.$authenticator::getType().'/'.$authenticator->getID()));

        // Convert URLs from relative to absolute.
        foreach (['signInUrl', 'registerUrl', 'signOutUrl', 'ui.photoUrl', 'resourceUrl'] as $field) {
            $value = valr($field, $record, null);
            if ($value !== null) {
                setvalr($field, $record, url($value, true));
            }
        }

        // Not used here specifically but it is used from /authenticate/authenticators.
        if (is_a($authenticator, SSOAuthenticator::class)) {
            /** @var SSOAuthenticator $ssoAuthenticator */
            $ssoAuthenticator = $authenticator;
            if ($this->getSession()->isValid()) {
                $record['isUserLinked'] = $ssoAuthenticator->isUserLinked($this->getSession()->UserID);
            }
        }

        return $record;
    }

    /**
     * Update an Authenticator.
     *
     * @param string $authenticatorID
     * @param array $body
     * @return array
     */
    public function patch(string $authenticatorID, array $body): array {
        $this->permission('Garden.Setting.Manage');

        $this->idParamSchema();
        $in = $this->schema(
            Schema::parse(['isActive'])->add($this->fullSchema()),
            ['AuthenticatorPatch', 'in']
        )->setDescription('Update an Authenticator.');
        $out = $this->schema($this->fullSchema(), 'out');

        $authenticator = $this->getAuthenticatorByID($authenticatorID);

        $body = $in->validate($body);

        $authenticator->setActive($body['isActive']);

        $result = $this->normalizeOutput($authenticator);

        return $out->validate($result);
    }
}
