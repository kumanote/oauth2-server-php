<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/OpenID/ResponseType/IdToken.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\OpenID\\ResponseType\\IdToken');


class OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims implements \OAuth2\OpenID\Storage\UserClaimsInterface
{
    public $claims = array();
    public $calls = array();

    public function getUserClaims($user_id, $scope)
    {
        $this->calls[] = array('getUserClaims', $user_id, $scope);
        return $this->claims;
    }
}

class OAuth2_OpenID_ResponseType_IdTokenFakePublicKey implements \OAuth2\Storage\PublicKeyInterface
{
    public function getPublicKey($client_id = null)
    {
        return 'FAKE_PUBLIC_KEY';
    }

    public function getPrivateKey($client_id = null)
    {
        return 'FAKE_PRIVATE_KEY';
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        return 'RS256';
    }
}

class OAuth2_OpenID_ResponseType_IdTokenFakeEncryption implements \OAuth2\Encryption\EncryptionInterface
{
    public function encode($payload, $key, $algorithm = null)
    {
        return 'FAKE_ENCODED';
    }

    public function decode($payload, $key, $algorithm = null)
    {
        return array();
    }

    public function urlSafeB64Encode($data)
    {
        return 'FAKE_B64';
    }

    public function urlSafeB64Decode($b64)
    {
        return '';
    }
}

class TestableOAuth2_OpenID_ResponseType_IdToken extends \OAuth2\OpenID\ResponseType\IdToken
{
    public $calls = array();

    public function encryptionUtilClass()
    {
        return get_class($this->encryptionUtil);
    }

    protected function createAtHash($access_token, $client_id = null)
    {
        $this->calls[] = array('createAtHash', $access_token, $client_id);
        return 'FIXED_AT_HASH';
    }

    protected function encodeToken(array $token, $client_id = null)
    {
        $this->calls[] = array('encodeToken', $client_id);
        return $this->withTimeNormalized($token);
    }

    // 対象は time() を直接呼ぶため固定値を注入できない。現在時刻に由来する値を
    // 決定的な表現 （iat は 'now'、exp は iat からの差、既定の auth_time は 'now'） に置き換える。
    protected function withTimeNormalized(array $token)
    {
        $now = time();
        $token['exp'] = $token['exp'] - $token['iat'];
        $token['iat'] = (abs($token['iat'] - $now) <= 5) ? 'now' : $token['iat'];
        if (is_int($token['auth_time']) && abs($token['auth_time'] - $now) <= 5) {
            $token['auth_time'] = 'now';
        }
        return $token;
    }
}

function OAuth2_OpenID_ResponseType_IdToken_invokeOrMessage($fn)
{
    try {
        return $fn();
    } catch (\LogicException $e) {
        return 'LogicException: ' . $e->getMessage();
    }
}

function OAuth2_OpenID_ResponseType_IdToken_normalizeTime(array $token)
{
    // 対象は time() を直接呼ぶため固定値を注入できない。iat は 'now'、exp は iat からの差に置き換える。
    $now = time();
    $token['exp'] = $token['exp'] - $token['iat'];
    $token['iat'] = (abs($token['iat'] - $now) <= 5) ? 'now' : $token['iat'];
    if (is_int($token['auth_time']) && abs($token['auth_time'] - $now) <= 5) {
        $token['auth_time'] = 'now';
    }
    return $token;
}

class OAuth2_OpenID_ResponseType_IdTokenRecordingEncryption implements \OAuth2\Encryption\EncryptionInterface
{
    public $calls = array();

    public function encode($payload, $key, $algorithm = null)
    {
        $this->calls[] = array('encode', OAuth2_OpenID_ResponseType_IdToken_normalizeTime($payload), $key, $algorithm);
        return 'RECORDED_ENCODED';
    }

    public function decode($payload, $key, $algorithm = null)
    {
        return array();
    }

    public function urlSafeB64Encode($data)
    {
        // バイナリのハッシュ断片は hex と長さで記録する
        $this->calls[] = array('urlSafeB64Encode', bin2hex($data), strlen($data));
        return 'B64:' . bin2hex($data);
    }

    public function urlSafeB64Decode($b64)
    {
        return '';
    }
}

class OAuth2_OpenID_ResponseType_IdTokenCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** config に issuer が無い配列を渡してコンストラクタを呼んだ場合、LogicException を投げること */
    public function testconfig_に_issuer_が無い配列を渡してコンストラクタを呼んだ場合_LogicException_を投げること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $fn = function () use ($claims, $keys) {
            return new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array());
        };

        $actual = OAuth2_OpenID_ResponseType_IdToken_invokeOrMessage($fn);

        $expected = 'LogicException: config parameter "issuer" must be set';
        $this->assertEquals($expected, $actual);
    }

    /** encryptionUtil に null を渡して生成し createIdToken を呼んだ場合、Jwt が既定の encryptionUtil になり token を組み立てること */
    public function testencryptionUtil_に_null_を渡して生成し_createIdToken_を呼んだ場合_Jwt_が既定の_encryptionUtil_になり_token_を組み立てること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), null);

        $actual = array($target->createIdToken('client-a', 'user-1'), $target->encryptionUtilClass());

        $expected = array(array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now'), 'OAuth2\\Encryption\\Jwt');
        $this->assertEquals($expected, $actual);
    }

    /** userInfo にスカラーを渡し nonce ・ userClaims ・ access_token を省略して createIdToken を呼んだ場合、sub に userInfo が入り auth_time が現在時刻になり nonce ・ at_hash を含まないこと */
    public function testuserInfo_にスカラーを渡し_nonce_userClaims_access_token_を省略して_createIdToken_を呼んだ場合_sub_に_userInfo_が入り_auth_time_が現在時刻になり_nonce_at_hash_を含まないこと() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);

        $actual = array($target->createIdToken('client-a', 'user-1'), $target->calls);

        $expected = array(array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now'), array(array('encodeToken', 'client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** userInfo に user_id と auth_time を持つ配列を渡して createIdToken を呼んだ場合、sub に user_id が入り auth_time に渡した値がそのまま入ること */
    public function testuserInfo_に_user_id_と_auth_time_を持つ配列を渡して_createIdToken_を呼んだ場合_sub_に_user_id_が入り_auth_time_に渡した値がそのまま入ること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $userInfo = array('user_id' => 'user-2', 'auth_time' => 1000);

        $actual = $target->createIdToken('client-a', $userInfo);

        $expected = array('iss' => 'https://issuer.example', 'sub' => 'user-2', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 1000);
        $this->assertEquals($expected, $actual);
    }

    /** userInfo に user_id だけを持ち auth_time の無い配列を渡して createIdToken を呼んだ場合、auth_time が現在時刻で補われること */
    public function testuserInfo_に_user_id_だけを持ち_auth_time_の無い配列を渡して_createIdToken_を呼んだ場合_auth_time_が現在時刻で補われること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $userInfo = array('user_id' => 'user-3');

        $actual = $target->createIdToken('client-a', $userInfo);

        $expected = array('iss' => 'https://issuer.example', 'sub' => 'user-3', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now');
        $this->assertEquals($expected, $actual);
    }

    /** userInfo に user_id の無い配列を渡して createIdToken を呼んだ場合、LogicException を投げること */
    public function testuserInfo_に_user_id_の無い配列を渡して_createIdToken_を呼んだ場合_LogicException_を投げること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $userInfo = array('auth_time' => 1000);
        $fn = function () use ($target, $userInfo) {
            return $target->createIdToken('client-a', $userInfo);
        };

        $actual = OAuth2_OpenID_ResponseType_IdToken_invokeOrMessage($fn);

        $expected = 'LogicException: if $user_id argument is an array, user_id index must be set';
        $this->assertEquals($expected, $actual);
    }

    /** nonce に空でない文字列を渡して createIdToken を呼んだ場合、token に nonce が追加されること */
    public function testnonce_に空でない文字列を渡して_createIdToken_を呼んだ場合_token_に_nonce_が追加されること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);

        $actual = $target->createIdToken('client-a', 'user-1', 'nonce-123');

        $expected = array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now', 'nonce' => 'nonce-123');
        $this->assertEquals($expected, $actual);
    }

    /** userClaims に空でない配列を渡して createIdToken を呼んだ場合、token に userClaims の要素が追加されること */
    public function testuserClaims_に空でない配列を渡して_createIdToken_を呼んだ場合_token_に_userClaims_の要素が追加されること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $userClaims = array('email' => 'user@example.com', 'sub' => 'claims-sub');

        $actual = $target->createIdToken('client-a', 'user-1', null, $userClaims);

        $expected = array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now', 'email' => 'user@example.com');
        $this->assertEquals($expected, $actual);
    }

    /** access_token に空でない文字列を渡して createIdToken を呼んだ場合、createAtHash が access_token と client_id で呼ばれ token に at_hash が入ること */
    public function testaccess_token_に空でない文字列を渡して_createIdToken_を呼んだ場合_createAtHash_が_access_token_と_client_id_で呼ばれ_token_に_at_hash_が入ること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);

        $actual = array($target->createIdToken('client-a', 'user-1', null, null, 'access-token-xyz'), $target->calls);

        $expected = array(array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now', 'at_hash' => 'FIXED_AT_HASH'), array(array('createAtHash', 'access-token-xyz', 'client-a'), array('encodeToken', 'client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** params に state を含めずに getAuthorizeResponse を呼んだ場合、fragment に id_token だけが入り getUserClaims が user_id と scope で呼ばれること */
    public function testparams_に_state_を含めずに_getAuthorizeResponse_を呼んだ場合_fragment_に_id_token_だけが入り_getUserClaims_が_user_id_と_scope_で呼ばれること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $claims->claims = array('email' => 'user@example.com');
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $params = array('client_id' => 'client-a', 'redirect_uri' => 'https://client.example/cb', 'scope' => 'openid email');

        $actual = array($target->getAuthorizeResponse($params, 'user-1'), $claims->calls);

        $expected = array(array('https://client.example/cb', array('query' => array(), 'fragment' => array('id_token' => array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 'now', 'email' => 'user@example.com')))), array(array('getUserClaims', 'user-1', 'openid email')));
        $this->assertEquals($expected, $actual);
    }

    /** params に state を含めて getAuthorizeResponse を呼んだ場合、fragment に id_token と state が入ること */
    public function testparams_に_state_を含めて_getAuthorizeResponse_を呼んだ場合_fragment_に_id_token_と_state_が入ること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $enc = new OAuth2_OpenID_ResponseType_IdTokenFakeEncryption();
        $target = new TestableOAuth2_OpenID_ResponseType_IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $enc);
        $params = array('client_id' => 'client-a', 'redirect_uri' => 'https://client.example/cb', 'state' => 'state-abc', 'nonce' => 'nonce-123');
        $userInfo = array('user_id' => 'user-2', 'auth_time' => 1000);

        $actual = $target->getAuthorizeResponse($params, $userInfo);

        $expected = array('https://client.example/cb', array('query' => array(), 'fragment' => array('id_token' => array('iss' => 'https://issuer.example', 'sub' => 'user-2', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 1000, 'nonce' => 'nonce-123'), 'state' => 'state-abc')));
        $this->assertEquals($expected, $actual);
    }

    /** createAtHash ・ encodeToken を override せず、publicKeyStorage が RS256 を返すフェイクと記録する encryptionUtil を注入して access_token 付きで createIdToken を呼んだ場合、sha256 ハッシュの前半が urlSafeB64Encode に渡され、token と getPrivateKey の鍵と RS256 が encode に渡されること */
    public function testcreateAtHash_encodeToken_を_override_せず_publicKeyStorage_が_RS256_を返すフェイクと記録する_encryptionUtil_を注入して_access_token_付きで_createIdToken_を呼んだ場合_sha256_ハッシュの前半が_urlSafeB64Encode_に渡され_token_と_getPrivateKey_の鍵と_RS256_が_encode_に渡されること() {
        $claims = new OAuth2_OpenID_ResponseType_IdTokenFakeUserClaims();
        $keys = new OAuth2_OpenID_ResponseType_IdTokenFakePublicKey();
        $encryption = new OAuth2_OpenID_ResponseType_IdTokenRecordingEncryption();
        $target = new \OAuth2\OpenID\ResponseType\IdToken($claims, $keys, array('issuer' => 'https://issuer.example'), $encryption);

        $actual = array($target->createIdToken('client-a', array('user_id' => 'user-1', 'auth_time' => 1700000000), null, null, 'access-token-xyz'), $encryption->calls);

        $expected = array('RECORDED_ENCODED', array(array('urlSafeB64Encode', 'e2622d5b7f612eeb8c0f469ccf2567e1', 16), array('encode', array('iss' => 'https://issuer.example', 'sub' => 'user-1', 'aud' => 'client-a', 'iat' => 'now', 'exp' => 3600, 'auth_time' => 1700000000, 'at_hash' => 'B64:e2622d5b7f612eeb8c0f469ccf2567e1'), 'FAKE_PRIVATE_KEY', 'RS256')));
        $this->assertEquals($expected, $actual);
    }
}
