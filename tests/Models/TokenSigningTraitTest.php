<?php
/**
 * @author Chris Chabilall <chris.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 */

namespace VanillaTests\Models;

use VanillaTests\SharedBootstrapTestCase;
use VanillaTests\SiteTestTrait;
use VanillaTests\Fixtures\TokenModel;

/**
 * Test the {@link TokenSigningTrait}.
 * Used to test the token generation and signing utility methods.
 */
class TokenSigningTraitTest extends SharedBootstrapTestCase {
    use SiteTestTrait;

    /**
     * Tests random token generation and signing.
     */
    public function testVerifyRandomTokenSignature() {
        $model = new TokenModel();
        $model->setSecret('ppp');
        $token = $model->randomSignedToken();
        $this->assertEquals(true, $model->verifyTokenSignature($token, $model->tokenIdentifier, true));
    }

    /**
     * An expired token shouldn't verify.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Your nonce has expired.
     */
    public function testExpiryDate() {
        $model = new TokenModel();
        $model->setSecret('ggg');
        $token = $model->randomSignedToken('last month');
        $this->assertEquals(false, $model->verifyTokenSignature($token, $model->tokenIdentifier, true));
    }

    /**
     * An altered token signature shouldn't verify.
     *
     * @expectedException \Exception
     * $expectedExceptionMessage Invalid signature.
     */
    public function testBadSignature() {
        $model = new TokenModel();
        $token = $model->randomSignedToken().'!';
        $this->assertEquals(false, $model->verifyTokenSignature($token, $model->tokenIdentifier, true));
    }

    /**
     * A nonsense token shouldn't verify.
     *
     * @expectedException \Exception
     */
    public function testBadToken() {
        $model = new TokenModel();
        $token = 'a.b.c';
        $this->assertEquals(false, $model->verifyTokenSignature($token, $model->tokenIdentifier, true));
    }

}
