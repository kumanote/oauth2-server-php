<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/ResponseType/AuthorizationCode.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\ResponseType\\AuthorizationCode');


class OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeFakeStorage implements \OAuth2\Storage\AuthorizationCodeInterface
{
    public $calls = array();

    public function getAuthorizationCode($code)
    {
        return null;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        $this->calls[] = array(
            'code' => $code,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'redirect_uri' => $redirect_uri,
            'expires' => $expires,
            'scope' => $scope,
        );
    }

    public function expireAuthorizationCode($code)
    {
    }
}

// 乱数と時刻の Seam を固定する。呼ぶたびに違う bytes を返すのは、
// random_bytes が無い環境でも 2 回の code が同じにならないようにするため。
class OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeSubject extends \OAuth2\ResponseType\AuthorizationCode
{
    public $seamCalls = array();
    private $counter = 0;

    protected function opensslRandomPseudoBytes($length)
    {
        $this->counter++;
        $this->seamCalls[] = array('opensslRandomPseudoBytes', $length);
        return str_pad((string) $this->counter, $length, 'a');
    }

    protected function mtRand()
    {
        $this->counter++;
        $this->seamCalls[] = array('mtRand');
        return 12345 + $this->counter;
    }

    protected function microtimeNow()
    {
        $this->seamCalls[] = array('microtimeNow');
        return 1700000000.5;
    }
}

// 戻り値の code は環境によって乱数源が変わるので、値そのものではなく
// 長さと storage へ渡した内容との一致だけを観測する。
function OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeObserve($subject, $storage, $client_id, $user_id, $redirect_uri, $scope)
{
    $code = $subject->createAuthorizationCode($client_id, $user_id, $redirect_uri, $scope);
    $call = $storage->calls[0];
    return array(
        'code_length' => strlen($code),
        'stored_same_code' => $call['code'] === $code,
        'stored_client_id' => $call['client_id'],
        'stored_user_id' => $call['user_id'],
        'stored_redirect_uri' => $call['redirect_uri'],
        'stored_scope' => $call['scope'],
        'store_call_count' => count($storage->calls),
    );
}

class OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** 乱数の Seam を固定した FakeStorage 付きの subject で createAuthorizationCode を 1 回呼んだ場合、生成した code の長さと、同じ code と引数が storage の setAuthorizationCode に渡ること */
    public function test乱数の_Seam_を固定した_FakeStorage_付きの_subject_で_createAuthorizationCode_を_1_回呼んだ場合_生成した_code_の長さと_同じ_code_と引数が_storage_の_setAuthorizationCode_に渡ること() {
        $storage = new OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeFakeStorage();
        $subject = new OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeSubject($storage, array('auth_code_lifetime' => 60));

        $actual = OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeObserve($subject, $storage, 'client-1', 'user-1', 'http://example.com/callback', 'read write');

        $expected = array('code_length' => 40, 'stored_same_code' => true, 'stored_client_id' => 'client-1', 'stored_user_id' => 'user-1', 'stored_redirect_uri' => 'http://example.com/callback', 'stored_scope' => 'read write', 'store_call_count' => 1);
        $this->assertEquals($expected, $actual);
    }

    /** 同じ subject で createAuthorizationCode を 2 回続けて呼んだ場合、2 回の code が互いに異なること */
    public function test同じ_subject_で_createAuthorizationCode_を_2_回続けて呼んだ場合_2_回の_code_が互いに異なること() {
        $storage = new OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeFakeStorage();
        $subject = new OAuth2_ResponseType_AuthorizationCode_generateAuthorizationCodeSubject($storage);

        $actual = $subject->createAuthorizationCode('client-1', 'user-1', 'http://example.com/callback', null) !== $subject->createAuthorizationCode('client-1', 'user-1', 'http://example.com/callback', null);

        $expected = true;
        $this->assertEquals($expected, $actual);
    }
}
