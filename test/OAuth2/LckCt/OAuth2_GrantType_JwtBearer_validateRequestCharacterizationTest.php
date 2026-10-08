<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/GrantType/JwtBearer.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\GrantType\\JwtBearer');


function OAuth2_GrantType_JwtBearer_validateRequestClaims(array $overrides)
{
    return array_merge(array(
        'iss' => 'client-1',
        'sub' => 'user-1',
        'aud' => 'https://auth.example.test/token',
        'exp' => '1700000100',
        'nbf' => '1699999000',
        'iat' => '1699999000',
        'jti' => 'nonce-1',
    ), $overrides);
}

class OAuth2_GrantType_JwtBearer_validateRequestFakeRequest implements \OAuth2\RequestInterface
{
    public $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function query($name, $default = null)
    {
        return $default;
    }

    public function request($name, $default = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    public function server($name, $default = null)
    {
        return $default;
    }

    public function headers($name, $default = null)
    {
        return $default;
    }

    public function getAllQueryParameters()
    {
        return array();
    }
}

class OAuth2_GrantType_JwtBearer_validateRequestFakeResponse implements \OAuth2\ResponseInterface
{
    public $errors = array();

    public function addParameters(array $parameters)
    {
    }

    public function addHttpHeaders(array $httpHeaders)
    {
    }

    public function setStatusCode($statusCode)
    {
    }

    public function setError($statusCode, $name, $description = null, $uri = null)
    {
        $this->errors[] = array($statusCode, $name, $description, $uri);
    }

    public function setRedirect($statusCode, $url, $state = null, $error = null, $errorDescription = null, $errorUri = null)
    {
    }

    public function getParameter($name)
    {
        return null;
    }
}

class OAuth2_GrantType_JwtBearer_validateRequestFakeStorage implements \OAuth2\Storage\JwtBearerInterface
{
    public $clientKey;
    public $jti;
    public $calls = array();

    public function __construct($clientKey, $jti)
    {
        $this->clientKey = $clientKey;
        $this->jti = $jti;
    }

    public function getClientKey($client_id, $subject)
    {
        $this->calls[] = array('getClientKey', $client_id, $subject);
        return $this->clientKey;
    }

    public function getJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $this->calls[] = array('getJti', $client_id, $subject, $audience, $expiration, $jti);
        return $this->jti;
    }

    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $this->calls[] = array('setJti', $client_id, $subject, $audience, $expiration, $jti);
    }
}

class OAuth2_GrantType_JwtBearer_validateRequestFakeJwt implements \OAuth2\Encryption\EncryptionInterface
{
    public $decoded;
    public $verified;
    public $calls = array();

    public function __construct($decoded, $verified)
    {
        $this->decoded = $decoded;
        $this->verified = $verified;
    }

    public function encode($payload, $key, $algorithm = null)
    {
        return '';
    }

    public function decode($payload, $key, $algorithm = null)
    {
        $this->calls[] = array($payload, $key, $algorithm);
        return $key === null ? $this->decoded : $this->verified;
    }

    public function urlSafeB64Encode($data)
    {
        return $data;
    }

    public function urlSafeB64Decode($b64)
    {
        return $b64;
    }
}

class OAuth2_GrantType_JwtBearer_validateRequestTestable extends \OAuth2\GrantType\JwtBearer
{
    public $fixedNow = 1700000000;

    protected function now()
    {
        return $this->fixedNow;
    }
}

class OAuth2_GrantType_JwtBearer_validateRequestCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** request の assertion が空の場合、invalid_request を setError して null を返すこと */
    public function testrequest_の_assertion_が空の場合_invalid_request_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array());
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_request', 'Missing parameters: "assertion" required', null)), array(), array());
        $this->assertEquals($expected, $actual);
    }

    /** jwtUtil->decode が false を返した場合、JWT is malformed を setError して null を返すこと */
    public function testjwtUtil_decode_が_false_を返した場合_JWT_is_malformed_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(false, true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_request', 'JWT is malformed', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** decode した claims に iss が無い場合、Invalid issuer を setError して null を返すこと */
    public function testdecode_した_claims_に_iss_が無い場合_Invalid_issuer_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('iss' => null)), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Invalid issuer (iss) provided', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** decode した claims に sub が無い場合、Invalid subject を setError して null を返すこと */
    public function testdecode_した_claims_に_sub_が無い場合_Invalid_subject_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('sub' => null)), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Invalid subject (sub) provided', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** decode した claims に exp が無い場合、Expiration time must be present を setError して null を返すこと */
    public function testdecode_した_claims_に_exp_が無い場合_Expiration_time_must_be_present_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('exp' => null)), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Expiration (exp) time must be present', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** exp が数字だけの文字列ではない場合、exp must be a unix time stamp を setError して null を返すこと */
    public function testexp_が数字だけの文字列ではない場合_exp_must_be_a_unix_time_stamp_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('exp' => '2026-01-01')), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Expiration (exp) time must be a unix time stamp', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** exp が now() 以下の場合、JWT has expired を setError して null を返すこと */
    public function testexp_が_now_以下の場合_JWT_has_expired_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('exp' => '1700000000')), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $grant->fixedNow = 1700000000;
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'JWT has expired', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** nbf が数字だけの文字列ではない場合、nbf must be a unix time stamp を setError して null を返すこと */
    public function testnbf_が数字だけの文字列ではない場合_nbf_must_be_a_unix_time_stamp_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('nbf' => 'soon')), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Not Before (nbf) time must be a unix time stamp', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** nbf が now() より後の場合、cannot be used before nbf を setError して null を返すこと */
    public function testnbf_が_now_より後の場合_cannot_be_used_before_nbf_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('nbf' => '1700000050')), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $grant->fixedNow = 1700000000;
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'JWT cannot be used before the Not Before (nbf) time', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** nbf が null で aud が無い場合、Invalid audience を setError して null を返すこと */
    public function testnbf_が_null_で_aud_が無い場合_Invalid_audience_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('nbf' => null, 'aud' => null)), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Invalid audience (aud)', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** aud がコンストラクタの audience と一致しない場合、Invalid audience を setError して null を返すこと */
    public function testaud_がコンストラクタの_audience_と一致しない場合_Invalid_audience_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('aud' => 'https://other.example.test/token')), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Invalid audience (aud)', null)), array(), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** storage->getJti が expires が now() より後の記録を返した場合、jti has already been used を setError して null を返すこと */
    public function teststorage_getJti_が_expires_が_now_より後の記録を返した場合_jti_has_already_been_used_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', array('expires' => 1700000500));
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $grant->fixedNow = 1700000000;
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'JSON Token Identifier (jti) has already been used', null)), array(array('getJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1')), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** storage->getJti が expires が now() 以前の記録を返した場合、setJti を呼んで検証を続けること */
    public function teststorage_getJti_が_expires_が_now_以前の記録を返した場合_setJti_を呼んで検証を続けること() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', array('expires' => 1699999000));
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $grant->fixedNow = 1700000000;
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(true, array(), array(array('getJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('setJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('getClientKey', 'client-1', 'user-1')), array(array('raw.jwt.token', null, false), array('raw.jwt.token', 'public-key', array('RS256', 'RS384', 'RS512'))));
        $this->assertEquals($expected, $actual);
    }

    /** jti が null の場合、getJti と setJti を呼ばずに検証を続けること */
    public function testjti_が_null_の場合_getJti_と_setJti_を呼ばずに検証を続けること() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array('jti' => null)), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(true, array(), array(array('getClientKey', 'client-1', 'user-1')), array(array('raw.jwt.token', null, false), array('raw.jwt.token', 'public-key', array('RS256', 'RS384', 'RS512'))));
        $this->assertEquals($expected, $actual);
    }

    /** storage->getClientKey が false を返した場合、Invalid issuer or subject を setError して null を返すこと */
    public function teststorage_getClientKey_が_false_を返した場合_Invalid_issuer_or_subject_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage(false, false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'Invalid issuer (iss) or subject (sub) provided', null)), array(array('getJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('setJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('getClientKey', 'client-1', 'user-1')), array(array('raw.jwt.token', null, false)));
        $this->assertEquals($expected, $actual);
    }

    /** 鍵付きの jwtUtil->decode が false を返した場合、JWT failed signature verification を setError して null を返すこと */
    public function test鍵付きの_jwtUtil_decode_が_false_を返した場合_JWT_failed_signature_verification_を_setError_して_null_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), false);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil);
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(null, array(array(400, 'invalid_grant', 'JWT failed signature verification', null)), array(array('getJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('setJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('getClientKey', 'client-1', 'user-1')), array(array('raw.jwt.token', null, false), array('raw.jwt.token', 'public-key', array('RS256', 'RS384', 'RS512'))));
        $this->assertEquals($expected, $actual);
    }

    /** 全ての検証を通過し allowed_algorithms を config で差し替えた場合、true を返し getClientId と getUserId が claims を返すこと */
    public function test全ての検証を通過し_allowed_algorithms_を_config_で差し替えた場合_true_を返し_getClientId_と_getUserId_が_claims_を返すこと() {
        $storage = new OAuth2_GrantType_JwtBearer_validateRequestFakeStorage('public-key', false);
        $jwtUtil = new OAuth2_GrantType_JwtBearer_validateRequestFakeJwt(OAuth2_GrantType_JwtBearer_validateRequestClaims(array()), true);
        $grant = new OAuth2_GrantType_JwtBearer_validateRequestTestable($storage, 'https://auth.example.test/token', $jwtUtil, array('allowed_algorithms' => array('HS256')));
        $request = new OAuth2_GrantType_JwtBearer_validateRequestFakeRequest(array('assertion' => 'raw.jwt.token'));
        $response = new OAuth2_GrantType_JwtBearer_validateRequestFakeResponse();

        $actual = array($grant->validateRequest($request, $response), $grant->getClientId(), $grant->getUserId(), $response->errors, $storage->calls, $jwtUtil->calls);

        $expected = array(true, 'client-1', 'user-1', array(), array(array('getJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('setJti', 'client-1', 'user-1', 'https://auth.example.test/token', '1700000100', 'nonce-1'), array('getClientKey', 'client-1', 'user-1')), array(array('raw.jwt.token', null, false), array('raw.jwt.token', 'public-key', array('HS256'))));
        $this->assertEquals($expected, $actual);
    }
}
