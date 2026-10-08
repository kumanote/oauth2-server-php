<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/ResponseType/AccessToken.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\ResponseType\\AccessToken');


class OAuth2_ResponseType_AccessToken_generateAccessToken_2Target extends \OAuth2\ResponseType\AccessToken
{
    public function __construct($tokenStorage = null, array $config = array())
    {
        $this->tokenStorage = $tokenStorage;
        $this->refreshStorage = null;
        $this->config = array_merge(array(
            'token_type' => 'bearer',
            'access_lifetime' => 3600,
            'refresh_token_lifetime' => 1209600,
        ), $config);
    }

    public function exposeGenerateAccessToken($mtRand = null, $now = null, $uniqid = null)
    {
        return $this->generateAccessToken($mtRand, $now, $uniqid);
    }
}

class OAuth2_ResponseType_AccessToken_generateAccessToken_2Recorder
{
    public $calls = array();

    public function mtRand()
    {
        $this->calls[] = 'mt_rand';
        return 7;
    }

    public function uniqid($prefix, $moreEntropy)
    {
        $this->calls[] = 'uniqid';
        return 'fixed-uniqid';
    }
}

class OAuth2_ResponseType_AccessToken_generateAccessToken_2FakeTokenStorage
{
    public $calls = array();

    public function setAccessToken($token, $clientId, $userId, $expires, $scope = null)
    {
        $this->calls[] = array($token, $clientId, $userId, $expires, $scope);
    }

    public function getAccessToken($token)
    {
        return null;
    }
}

function OAuth2_ResponseType_AccessToken_generateAccessToken_2Shape($result)
{
    if (is_array($result)) {
        $token = $result['access_token'];
        return array(
            strlen($token),
            ctype_xdigit($token),
            $result['expires_in'],
            $result['token_type'],
            $result['scope'],
            array_key_exists('refresh_token', $result),
        );
    }
    return array(strlen($result), ctype_xdigit($result));
}

class OAuth2_ResponseType_AccessToken_generateAccessToken_2CharacterizationTest extends PHPUnit_Framework_TestCase {
    /** 引数を渡さずに generateAccessToken を呼んだ場合、40 文字の 16 進文字列を返すこと */
    public function test引数を渡さずに_generateAccessToken_を呼んだ場合_40_文字の_16_進文字列を返すこと() {
        $target = new OAuth2_ResponseType_AccessToken_generateAccessToken_2Target();

        $actual = OAuth2_ResponseType_AccessToken_generateAccessToken_2Shape($target->exposeGenerateAccessToken());

        $expected = array(40, true);
        $this->assertEquals($expected, $actual);
    }

    /** mtRand ・ now ・ uniqid にフェイクを渡した場合、random_bytes の分岐で返り、フェイクの mtRand ・ uniqid は呼ばれないこと */
    public function testmtRand_now_uniqid_にフェイクを渡した場合_random_bytes_の分岐で返り_フェイクの_mtRand_uniqid_は呼ばれないこと() {
        $target = new OAuth2_ResponseType_AccessToken_generateAccessToken_2Target();
        $recorder = new OAuth2_ResponseType_AccessToken_generateAccessToken_2Recorder();

        $actual = array(strlen($target->exposeGenerateAccessToken(array($recorder, 'mtRand'), 1234567890.5, array($recorder, 'uniqid'))), $recorder->calls);

        $expected = array(40, array());
        $this->assertEquals($expected, $actual);
    }

    /** createAccessToken から呼ばれ tokenStorage にフェイクを差した場合、生成した 40 文字の access_token が setAccessToken に渡されること */
    public function testcreateAccessToken_から呼ばれ_tokenStorage_にフェイクを差した場合_生成した_40_文字の_access_token_が_setAccessToken_に渡されること() {
        $storage = new OAuth2_ResponseType_AccessToken_generateAccessToken_2FakeTokenStorage();
        $target = new OAuth2_ResponseType_AccessToken_generateAccessToken_2Target($storage, array('access_lifetime' => 0));

        $actual = OAuth2_ResponseType_AccessToken_generateAccessToken_2Shape($target->createAccessToken('client-1', 'user-1', 'read', false));

        $expected = array(40, true, 0, 'bearer', 'read', false);
        $this->assertEquals($expected, $actual);
    }
}
