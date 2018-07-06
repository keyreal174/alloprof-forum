<?php

/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

/**
 * Handles access tokens.
 *
 * When using this model you should be using the {@link AccessTokenModel::issue()} and {@link AccessTokenModel::verify()}
 * methods most of the time.
 */
class AccessTokenModel extends Gdn_Model {
    use \Vanilla\PrunableTrait , \Vanilla\TokenSigningTrait;

    private $secret;
    protected static $tokenIdentifier = "access token";

    /**
     * Construct an {@link AccessToken} object.
     *
     * @param string $secret The secret used to sign access tokens for the client.
     */
    public function __construct($secret = '') {
        parent::__construct('AccessToken');
        $this->PrimaryKey = 'AccessTokenID';
        $this->secret = $secret ?: c('Garden.Cookie.Salt');

        $this->setPruneAfter('1 day')
            ->setPruneField('DateExpires');
    }

    /**
     * Issue an access token.
     *
     * @param int $userID The user ID the token is issued to.
     * @param mixed $expires The date the token expires. This can be a string relative date.
     * @param string $type The type of token. Pass a string that you define here. This will usually be the name of an addon.
     * @param array $scope The permission scope of the token. Leave blank to inherit the user's permissions.
     * @return string Returns a signed access token.
     */
    public function issue($userID, $expires = '1 month', $type = 'system', $scope = []) {
        if ($expires instanceof  DateTimeInterface) {
            $expireDate = $expires->format(MYSQL_DATE_FORMAT);
        } else {
            $expireDate = Gdn_Format::toDateTime($this->toTimestamp($expires));
        }
        $token = $this->insert([
            'UserID' => $userID,
            'Type' => $type,
            'DateExpires' => $expireDate,
            'Scope' => $scope
        ]);

        if (!$token) {
            throw new Gdn_UserException($this->Validation->resultsText(), 400);
        }

        $accessToken = $this->signToken($token, $expireDate);
        return $accessToken;
    }

    /**
     * Revoke an already issued token.
     *
     * @param string|int $token The token, access or numeric ID token to revoke.
     * @return bool Returns true if the token was revoked or false otherwise.
     */
    public function revoke($token) {
        $id = false;
        if (filter_var($token, FILTER_VALIDATE_INT)) {
            $id = $token;
        } else {
            $token = $this->trim($token);
            $row = $this->getToken($token);
            if ($row) {
                $id = $row['AccessTokenID'];
            }
        }

        $this->setField($id, [
            'DateExpires' => Gdn_Format::toDateTime(strtotime('-1 hour'))
        ]);
        $this->setAttribute($id, 'revoked', true);
        return $this->Database->LastInfo['RowCount'] > 0;
    }

    /**
     * Get an access token by its numeric ID.
     *
     * @param int $accessTokenID
     * @param string $datasetType
     * @param array $options
     * @return array|bool
     */
    public function getID($accessTokenID, $datasetType = DATASET_TYPE_ARRAY, $options = []) {
        $row = $this->getWhere(['AccessTokenID' => $accessTokenID])->firstRow($datasetType);
        return $row;
    }

    /**
     * Fetch an access token row using the token.
     *
     * @param mixed $token
     * @return array|bool
     */
    public function getToken($token) {
        $row = $this->getWhere(['Token' => $token])->firstRow(DATASET_TYPE_ARRAY);
        return $row;
    }

    /**
     * Unserialize a row from the database for API consumption.
     *
     * @param array &$row The row to decode.
     */
    protected function decodeRow(&$row) {
        $isObject = false;
        if (is_object($row) && !$row instanceof ArrayAccess) {
            $isObject = true;
            $row = (array)$row;
        }

        $row['InsertIPAddress'] = ipDecode($row['InsertIPAddress']);

        foreach (['Scope', 'Attributes'] as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = json_decode($row[$field], true);
            }
        }

        if ($isObject) {
            $row = (object)$row;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function insert($fields) {
        if (empty($fields['Token'])) {
            $fields['Token'] = $this->randomToken();
        }

        $this->encodeRow($fields);
        parent::insert($fields);
        if (!empty($this->Database->LastInfo['RowCount'])) {
            $this->prune();
            $result = $fields['Token'];
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update($fields, $where = false, $limit = false) {
        $this->encodeRow($fields);
        return parent::update($fields, $where, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function setField($rowID, $property, $value = false) {
        if (!is_array($property)) {
            $property = [$property => $value];
        }
        $this->encodeRow($property);
        parent::setField($rowID, $property);
    }

    /**
     * Generate and sign a token.
     *
     * @param string $expires When the token expires.
     * @return string
     */
    public function randomSignedToken($expires = '2 months') {
        return $this->signToken($this->randomToken(), $expires);
    }

    /**
     * Sign a token row.
     *
     * @param array $row The database row of the token.
     * @return string Returns a signed token.
     */
    public function signTokenRow($row) {
        $token = val('Token', $row);
        $expires = val('DateExpires', $row);

        return $this->signToken($token, $expires);
    }

    /**
     * Verify an access token.
     *
     * @param string $accessToken An access token issued from {@link AccessTokenModel::issue()}.
     * @param bool $throw Whether or not to throw an exception on a verification error.
     * @return array|false Returns the valid access token row or **false**.
     * @throws \Exception Throws an exception if the token is invalid and {@link $throw} is **true**.
     */
    public function verify($accessToken, $throw = false) {
        // First verify the token without going to the database.
        if (!$this->verifyTokenSignature($accessToken, self::$tokenIdentifier, $throw)) {
            return false;
        }

        $token = $this->trim($accessToken);

        $row = $this->getToken($token);

        if (!$row) {
            return $this->tokenError('Access token not found.', 401, $throw);
        }

        if (!empty($row['Attributes']['revoked'])) {
            return $this->tokenError('Your access token was revoked.', 401, $throw);
        }

        // Check the expiry date from the database.
        $dbExpires = $this->toTimestamp($row['DateExpires']);
        if ($dbExpires === 0) {

        } elseif ($dbExpires < time()) {
            return $this->tokenError('Your access token has expired.', 401, $throw);
        }

        return $row;
    }

    /**
     * Base 64 encode a date.
     *
     * @param mixed $dt A timestamp or date string.
     * @return string Returns the encoded date.
     */
    private function encodeDate($dt) {
        $timestamp = $this->toTimestamp($dt);
        $result = self::base64urlEncode(pack('I', $timestamp));
        return $result;
    }

    /**
     * Trim the expiry date and signature off of a token.
     *
     * @param string $accessToken The access token to trim.
     */
    public function trim($accessToken) {
        if (strpos($accessToken, '.') !== false) {
            list($_, $token) = explode('.', $accessToken);
            return $token;
        }
        return $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhere($where = false, $orderFields = '', $orderDirection = 'asc', $limit = false, $offset = false) {
        $result = parent::getWhere($where, $orderFields, $orderDirection, $limit, $offset);
        array_walk($result->result(), [$this, 'decodeRow']);

        return $result;
    }

    /**
     * Base 64 decode a date.
     *
     * @param string $str An encoded date.
     * @return int Returns a timestamp.
     */
    private function decodeDate($str) {
        $arr = unpack('I*', self::base64urlDecode($str));
        if (empty($arr[1]) || !is_int($arr[1])) {
            return null;
        }
        return $arr[1];
    }



    /**
     * Save an attribute on an access token row.
     *
     * @param int $accessTokenID
     * @param string $key
     * @param mixed $value
     * @return array|bool
     */
    public function setAttribute($accessTokenID, $key, $value) {
        $row = $this->getID($accessTokenID, DATASET_TYPE_ARRAY);
        $result = false;
        if ($row) {
            $attributes = array_key_exists('Attributes', $row) ? $row['Attributes'] : [];
            $attributes[$key] = $value;
            $this->update(
                ['Attributes' => $attributes],
                ['AccessTokenID' => $accessTokenID],
            1);
            $result = $this->getID($accessTokenID);
        }
        return $result;
    }
}
