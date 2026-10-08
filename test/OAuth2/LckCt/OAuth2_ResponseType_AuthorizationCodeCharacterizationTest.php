<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/ResponseType/AuthorizationCode.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\ResponseType\\AuthorizationCode');


class OAuth2_ResponseType_AuthorizationCodeFakeStorage implements \OAuth2\Storage\AuthorizationCodeInterface
{
    public $calls = array();

    public function getAuthorizationCode($code)
    {
        $this->calls[] = array('getAuthorizationCode', $code);
        return null;
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        // expires は time() 由来で毎回変わるため記録しない
        $this->calls[] = array('setAuthorizationCode', $code, $client_id, $user_id, $redirect_uri, $scope);
    }

    public function expireAuthorizationCode($code)
    {
        $this->calls[] = array('expireAuthorizationCode', $code);
    }
}

class TestableOAuth2_ResponseType_AuthorizationCode extends \OAuth2\ResponseType\AuthorizationCode
{
    public $calls = array();

    protected function generateAuthorizationCode()
    {
        $this->calls[] = array('generateAuthorizationCode');
        return 'fixed-auth-code-0123456789abcdef01234567';
    }

    protected function opensslRandomPseudoBytes($length)
    {
        $this->calls[] = array('opensslRandomPseudoBytes', $length);
        return str_repeat('a', $length);
    }

    protected function mtRand()
    {
        $this->calls[] = array('mtRand');
        return 12345;
    }

    protected function microtimeNow()
    {
        $this->calls[] = array('microtimeNow');
        return 1700000000.5;
    }
}

class OAuth2_ResponseType_AuthorizationCodeRealGenerator extends \OAuth2\ResponseType\AuthorizationCode
{
    public $calls = array();

    // generateAuthorizationCode は override せず本番の実装を通す。
    // random_bytes が存在する環境では乱数が毎回変わるため、act では長さだけを観測する。
    protected function opensslRandomPseudoBytes($length)
    {
        $this->calls[] = array('opensslRandomPseudoBytes', $length);
        return str_repeat('b', $length);
    }

    protected function mtRand()
    {
        $this->calls[] = array('mtRand');
        return 54321;
    }

    protected function microtimeNow()
    {
        $this->calls[] = array('microtimeNow');
        return 1700000001.5;
    }
}

class OAuth2_ResponseType_AuthorizationCodeCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** params に state を含めて getAuthorizeResponse を呼んだ場合、redirect_uri と、code と state を持つ query の組を返すこと */
    public function testparams_に_state_を含めて_getAuthorizeResponse_を呼んだ場合_redirect_uri_と_code_と_state_を持つ_query_の組を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = $target->getAuthorizeResponse(array('client_id' => 'client-1', 'redirect_uri' => 'https://example.com/cb', 'state' => 'xyz'), 'user-1');

        $expected = array('https://example.com/cb', array('query' => array('code' => 'fixed-auth-code-0123456789abcdef01234567', 'state' => 'xyz')));
        $this->assertEquals($expected, $actual);
    }

    /** params に state を含めずに getAuthorizeResponse を呼んだ場合、query に state を含めず code だけを持つ組を返すこと */
    public function testparams_に_state_を含めずに_getAuthorizeResponse_を呼んだ場合_query_に_state_を含めず_code_だけを持つ組を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = $target->getAuthorizeResponse(array('client_id' => 'client-1', 'redirect_uri' => 'https://example.com/cb'), 'user-1');

        $expected = array('https://example.com/cb', array('query' => array('code' => 'fixed-auth-code-0123456789abcdef01234567')));
        $this->assertEquals($expected, $actual);
    }

    /** params に scope を含めて getAuthorizeResponse を呼んだ場合、storage の setAuthorizationCode に scope を渡して code を返すこと */
    public function testparams_に_scope_を含めて_getAuthorizeResponse_を呼んだ場合_storage_の_setAuthorizationCode_に_scope_を渡して_code_を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = array($target->getAuthorizeResponse(array('client_id' => 'client-1', 'redirect_uri' => 'https://example.com/cb', 'scope' => 'read write', 'state' => 'st'), 'user-1'), $storage->calls);

        $expected = array(array('https://example.com/cb', array('query' => array('code' => 'fixed-auth-code-0123456789abcdef01234567', 'state' => 'st'))), array(array('setAuthorizationCode', 'fixed-auth-code-0123456789abcdef01234567', 'client-1', 'user-1', 'https://example.com/cb', 'read write')));
        $this->assertEquals($expected, $actual);
    }

    /** scope を渡して createAuthorizationCode を呼んだ場合、generateAuthorizationCode の値を storage に保存して返すこと */
    public function testscope_を渡して_createAuthorizationCode_を呼んだ場合_generateAuthorizationCode_の値を_storage_に保存して返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = array($target->createAuthorizationCode('client-1', 'user-1', 'https://example.com/cb', 'read'), $storage->calls, $target->calls);

        $expected = array('fixed-auth-code-0123456789abcdef01234567', array(array('setAuthorizationCode', 'fixed-auth-code-0123456789abcdef01234567', 'client-1', 'user-1', 'https://example.com/cb', 'read')), array(array('generateAuthorizationCode')));
        $this->assertEquals($expected, $actual);
    }

    /** scope を省略して createAuthorizationCode を呼んだ場合、storage の setAuthorizationCode に scope として null を渡すこと */
    public function testscope_を省略して_createAuthorizationCode_を呼んだ場合_storage_の_setAuthorizationCode_に_scope_として_null_を渡すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = array($target->createAuthorizationCode('client-1', 'user-1', 'https://example.com/cb'), $storage->calls);

        $expected = array('fixed-auth-code-0123456789abcdef01234567', array(array('setAuthorizationCode', 'fixed-auth-code-0123456789abcdef01234567', 'client-1', 'user-1', 'https://example.com/cb', null)));
        $this->assertEquals($expected, $actual);
    }

    /** config を渡さずに生成して enforceRedirect を呼んだ場合、既定の enforce_redirect を返すこと */
    public function testconfig_を渡さずに生成して_enforceRedirect_を呼んだ場合_既定の_enforce_redirect_を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage);

        $actual = $target->enforceRedirect();

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** config で enforce_redirect に true を渡して enforceRedirect を呼んだ場合、渡した enforce_redirect を返すこと */
    public function testconfig_で_enforce_redirect_に_true_を渡して_enforceRedirect_を呼んだ場合_渡した_enforce_redirect_を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new TestableOAuth2_ResponseType_AuthorizationCode($storage, array('enforce_redirect' => true));

        $actual = $target->enforceRedirect();

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** generateAuthorizationCode を override しないサブクラスで createAuthorizationCode を呼んだ場合、sha512 のハッシュを tokenLen の 40 文字に切り詰めた code を返すこと */
    public function testgenerateAuthorizationCode_を_override_しないサブクラスで_createAuthorizationCode_を呼んだ場合_sha512_のハッシュを_tokenLen_の_40_文字に切り詰めた_code_を返すこと() {
        $storage = new OAuth2_ResponseType_AuthorizationCodeFakeStorage();
        $target = new OAuth2_ResponseType_AuthorizationCodeRealGenerator($storage);

        $actual = strlen($target->createAuthorizationCode('client-1', 'user-1', 'https://example.com/cb', 'read'));

        $expected = 40;
        $this->assertEquals($expected, $actual);
    }
}
