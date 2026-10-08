<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/Storage/Redis.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\Storage\\Redis');


class TestableOAuth2_Storage_Redis extends \OAuth2\Storage\Redis
{
    public $store = array();
    public $calls = array();
    public $expireResult = 1;

    protected function getValue($key)
    {
        $this->calls[] = array('getValue', $key);
        if (isset($this->store[$key])) {
            return $this->store[$key];
        }

        return false;
    }

    protected function setValue($key, $value, $expire=0)
    {
        $this->calls[] = array('setValue', $key, $value, $expire);
        $this->store[$key] = $value;

        return true;
    }

    protected function expireValue($key)
    {
        $this->calls[] = array('expireValue', $key);
        unset($this->store[$key]);

        return $this->expireResult;
    }
}

function OAuth2_Storage_Redis_captureException($callable)
{
    try {
        return array('returned', call_user_func($callable));
    } catch (\Exception $e) {
        return array(get_class($e), $e->getMessage());
    }
}

class OAuth2_Storage_RedisFakeClient
{
    public $values = array();
    public $calls = array();
    public $setResult = true;
    public $delResult = 1;

    public function get($key)
    {
        $this->calls[] = array('get', $key);
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        return null;
    }

    public function set($key, $str)
    {
        $this->calls[] = array('set', $key, $str);

        return $this->setResult;
    }

    // setex の第 2 引数は対象が $expire - time() で算出し実行時刻に依存するため、記録に含めない
    public function setex($key, $seconds, $str)
    {
        $this->calls[] = array('setex', $key, $str);

        return $this->setResult;
    }

    public function del($key)
    {
        $this->calls[] = array('del', $key);

        return $this->delResult;
    }
}

class OAuth2_Storage_RedisFakeStatus
{
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}

class OAuth2_Storage_RedisCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** code_key に続く code の値が store にある状態で getAuthorizationCode を呼んだ場合、store の値をそのまま返し getValue を code_key 付きのキーで 1 回呼ぶこと */
    public function testcode_key_に続く_code_の値が_store_にある状態で_getAuthorizationCode_を呼んだ場合_store_の値をそのまま返し_getValue_を_code_key_付きのキーで_1_回呼ぶこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_authorization_codes:code-1'] = array('authorization_code' => 'code-1', 'client_id' => 'client-a');

        $actual = array($storage->getAuthorizationCode('code-1'), $storage->calls);

        $expected = array(array('authorization_code' => 'code-1', 'client_id' => 'client-a'), array(array('getValue', 'oauth_authorization_codes:code-1')));
        $this->assertEquals($expected, $actual);
    }

    /** setAuthorizationCode に scope と id_token を省略して呼んだ場合、setValue に compact した配列と expires を渡した結果を返すこと */
    public function testsetAuthorizationCode_に_scope_と_id_token_を省略して呼んだ場合_setValue_に_compact_した配列と_expires_を渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setAuthorizationCode('code-1', 'client-a', 'user-1', 'http://localhost/cb', 1700000000), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_authorization_codes:code-1', array('authorization_code' => 'code-1', 'client_id' => 'client-a', 'user_id' => 'user-1', 'redirect_uri' => 'http://localhost/cb', 'expires' => 1700000000, 'scope' => null, 'id_token' => null), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireAuthorizationCode を呼んだ場合、expireValue を code_key 付きのキーで呼びその戻り値を返すこと */
    public function testexpireAuthorizationCode_を呼んだ場合_expireValue_を_code_key_付きのキーで呼びその戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_authorization_codes:code-1'] = array('authorization_code' => 'code-1');

        $actual = array($storage->expireAuthorizationCode('code-1'), $storage->calls, $storage->store);

        $expected = array(1, array(array('expireValue', 'oauth_authorization_codes:code-1')), array());
        $this->assertEquals($expected, $actual);
    }

    /** user_key に続く username が store に無い状態で checkUserCredentials を呼んだ場合、getUser が false を返すので false を返すこと */
    public function testuser_key_に続く_username_が_store_に無い状態で_checkUserCredentials_を呼んだ場合_getUser_が_false_を返すので_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->checkUserCredentials('alice', 'secret'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_users:alice')));
        $this->assertEquals($expected, $actual);
    }

    /** store の password と同じ文字列で checkUserCredentials を呼んだ場合、true を返すこと */
    public function teststore_の_password_と同じ文字列で_checkUserCredentials_を呼んだ場合_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'secret');

        $actual = array($storage->checkUserCredentials('alice', 'secret'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_users:alice')));
        $this->assertEquals($expected, $actual);
    }

    /** store の password と異なる文字列で checkUserCredentials を呼んだ場合、false を返すこと */
    public function teststore_の_password_と異なる文字列で_checkUserCredentials_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'secret');

        $actual = array($storage->checkUserCredentials('alice', 'wrong'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_users:alice')));
        $this->assertEquals($expected, $actual);
    }

    /** user_key に続く username が store にある状態で getUserDetails を呼んだ場合、user_id を username で補った配列を返すこと */
    public function testuser_key_に続く_username_が_store_にある状態で_getUserDetails_を呼んだ場合_user_id_を_username_で補った配列を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'secret', 'first_name' => 'Alice');

        $actual = array($storage->getUserDetails('alice'), $storage->calls);

        $expected = array(array('user_id' => 'alice', 'username' => 'alice', 'password' => 'secret', 'first_name' => 'Alice'), array(array('getValue', 'oauth_users:alice')));
        $this->assertEquals($expected, $actual);
    }

    /** store の値に user_id が含まれる状態で getUser を呼んだ場合、array_merge の結果を返すこと */
    public function teststore_の値に_user_id_が含まれる状態で_getUser_を呼んだ場合_array_merge_の結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_users:alice'] = array('user_id' => 'stored-id', 'username' => 'alice');

        $actual = array($storage->getUser('alice'), $storage->calls);

        $expected = array(array('user_id' => 'stored-id', 'username' => 'alice'), array(array('getValue', 'oauth_users:alice')));
        $this->assertEquals($expected, $actual);
    }

    /** setUser に first_name と last_name を省略して呼んだ場合、setValue に compact した配列を expire 既定値で渡した結果を返すこと */
    public function testsetUser_に_first_name_と_last_name_を省略して呼んだ場合_setValue_に_compact_した配列を_expire_既定値で渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setUser('alice', 'secret'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_users:alice', array('username' => 'alice', 'password' => 'secret', 'first_name' => null, 'last_name' => null), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** client_key に続く client_id が store に無い状態で checkClientCredentials を呼んだ場合、false を返すこと */
    public function testclient_key_に続く_client_id_が_store_に無い状態で_checkClientCredentials_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->checkClientCredentials('client-a', 'sec'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret と同じ文字列で checkClientCredentials を呼んだ場合、true を返すこと */
    public function teststore_の_client_secret_と同じ文字列で_checkClientCredentials_を呼んだ場合_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'client_secret' => 'sec');

        $actual = array($storage->checkClientCredentials('client-a', 'sec'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client に client_secret のキーが無い状態で checkClientCredentials を呼んだ場合、isset が偽になり false を返すこと */
    public function teststore_の_client_に_client_secret_のキーが無い状態で_checkClientCredentials_を呼んだ場合_isset_が偽になり_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a');

        $actual = array($storage->checkClientCredentials('client-a', 'sec'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret と異なる文字列で checkClientCredentials を呼んだ場合、false を返すこと */
    public function teststore_の_client_secret_と異なる文字列で_checkClientCredentials_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'client_secret' => 'sec');

        $actual = array($storage->checkClientCredentials('client-a', 'other'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** client_key に続く client_id が store に無い状態で isPublicClient を呼んだ場合、false を返すこと */
    public function testclient_key_に続く_client_id_が_store_に無い状態で_isPublicClient_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->isPublicClient('client-a'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret が空文字の状態で isPublicClient を呼んだ場合、empty が真になり true を返すこと */
    public function teststore_の_client_secret_が空文字の状態で_isPublicClient_を呼んだ場合_empty_が真になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'client_secret' => '');

        $actual = array($storage->isPublicClient('client-a'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret に文字列が入っている状態で isPublicClient を呼んだ場合、false を返すこと */
    public function teststore_の_client_secret_に文字列が入っている状態で_isPublicClient_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'client_secret' => 'sec');

        $actual = array($storage->isPublicClient('client-a'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** setClientDetails に全引数を渡して呼んだ場合、setValue に compact した配列を expire 既定値で渡した結果を返すこと */
    public function testsetClientDetails_に全引数を渡して呼んだ場合_setValue_に_compact_した配列を_expire_既定値で渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setClientDetails('client-a', 'sec', 'http://localhost/cb', 'password refresh_token', 'read', 'user-1'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_clients:client-a', array('client_id' => 'client-a', 'client_secret' => 'sec', 'redirect_uri' => 'http://localhost/cb', 'grant_types' => 'password refresh_token', 'scope' => 'read', 'user_id' => 'user-1'), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** store の grant_types に含まれる grant_type で checkRestrictedGrantType を呼んだ場合、in_array が真になり true を返すこと */
    public function teststore_の_grant_types_に含まれる_grant_type_で_checkRestrictedGrantType_を呼んだ場合_in_array_が真になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'grant_types' => 'password refresh_token');

        $actual = array($storage->checkRestrictedGrantType('client-a', 'refresh_token'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の grant_types に含まれない grant_type で checkRestrictedGrantType を呼んだ場合、in_array が偽になり false を返すこと */
    public function teststore_の_grant_types_に含まれない_grant_type_で_checkRestrictedGrantType_を呼んだ場合_in_array_が偽になり_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'grant_types' => 'password refresh_token');

        $actual = array($storage->checkRestrictedGrantType('client-a', 'client_credentials'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client に grant_types のキーが無い状態で checkRestrictedGrantType を呼んだ場合、制限なしとして true を返すこと */
    public function teststore_の_client_に_grant_types_のキーが無い状態で_checkRestrictedGrantType_を呼んだ場合_制限なしとして_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a');

        $actual = array($storage->checkRestrictedGrantType('client-a', 'client_credentials'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** client_key に続く client_id が store に無い状態で checkRestrictedGrantType を呼んだ場合、getClientDetails が false を返し isset が偽になり true を返すこと */
    public function testclient_key_に続く_client_id_が_store_に無い状態で_checkRestrictedGrantType_を呼んだ場合_getClientDetails_が_false_を返し_isset_が偽になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->checkRestrictedGrantType('client-a', 'password'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** refresh_token_key に続く refresh_token が store にある状態で getRefreshToken を呼んだ場合、store の値をそのまま返すこと */
    public function testrefresh_token_key_に続く_refresh_token_が_store_にある状態で_getRefreshToken_を呼んだ場合_store_の値をそのまま返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_refresh_tokens:rt-1'] = array('refresh_token' => 'rt-1', 'client_id' => 'client-a');

        $actual = array($storage->getRefreshToken('rt-1'), $storage->calls);

        $expected = array(array('refresh_token' => 'rt-1', 'client_id' => 'client-a'), array(array('getValue', 'oauth_refresh_tokens:rt-1')));
        $this->assertEquals($expected, $actual);
    }

    /** setRefreshToken に scope を省略して呼んだ場合、setValue に compact した配列と expires を渡した結果を返すこと */
    public function testsetRefreshToken_に_scope_を省略して呼んだ場合_setValue_に_compact_した配列と_expires_を渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setRefreshToken('rt-1', 'client-a', 'user-1', 1700000000), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_refresh_tokens:rt-1', array('refresh_token' => 'rt-1', 'client_id' => 'client-a', 'user_id' => 'user-1', 'expires' => 1700000000, 'scope' => null), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が 1 を返す状態で unsetRefreshToken を呼んだ場合、result > 0 が真になり true を返すこと */
    public function testexpireValue_が_1_を返す状態で_unsetRefreshToken_を呼んだ場合_result_0_が真になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->expireResult = 1;

        $actual = array($storage->unsetRefreshToken('rt-1'), $storage->calls);

        $expected = array(true, array(array('expireValue', 'oauth_refresh_tokens:rt-1')));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が 0 を返す状態で unsetRefreshToken を呼んだ場合、result > 0 が偽になり false を返すこと */
    public function testexpireValue_が_0_を返す状態で_unsetRefreshToken_を呼んだ場合_result_0_が偽になり_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->expireResult = 0;

        $actual = array($storage->unsetRefreshToken('rt-1'), $storage->calls);

        $expected = array(false, array(array('expireValue', 'oauth_refresh_tokens:rt-1')));
        $this->assertEquals($expected, $actual);
    }

    /** access_token_key に続く access_token が store にある状態で getAccessToken を呼んだ場合、store の値をそのまま返すこと */
    public function testaccess_token_key_に続く_access_token_が_store_にある状態で_getAccessToken_を呼んだ場合_store_の値をそのまま返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_access_tokens:at-1'] = array('access_token' => 'at-1', 'client_id' => 'client-a');

        $actual = array($storage->getAccessToken('at-1'), $storage->calls);

        $expected = array(array('access_token' => 'at-1', 'client_id' => 'client-a'), array(array('getValue', 'oauth_access_tokens:at-1')));
        $this->assertEquals($expected, $actual);
    }

    /** setAccessToken に scope を渡して呼んだ場合、setValue に compact した配列と expires を渡した結果を返すこと */
    public function testsetAccessToken_に_scope_を渡して呼んだ場合_setValue_に_compact_した配列と_expires_を渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setAccessToken('at-1', 'client-a', 'user-1', 1700000000, 'read write'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_access_tokens:at-1', array('access_token' => 'at-1', 'client_id' => 'client-a', 'user_id' => 'user-1', 'expires' => 1700000000, 'scope' => 'read write'), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が 1 を返す状態で unsetAccessToken を呼んだ場合、result > 0 が真になり true を返すこと */
    public function testexpireValue_が_1_を返す状態で_unsetAccessToken_を呼んだ場合_result_0_が真になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->expireResult = 1;

        $actual = array($storage->unsetAccessToken('at-1'), $storage->calls);

        $expected = array(true, array(array('expireValue', 'oauth_access_tokens:at-1')));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が 0 を返す状態で unsetAccessToken を呼んだ場合、result > 0 が偽になり false を返すこと */
    public function testexpireValue_が_0_を返す状態で_unsetAccessToken_を呼んだ場合_result_0_が偽になり_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->expireResult = 0;

        $actual = array($storage->unsetAccessToken('at-1'), $storage->calls);

        $expected = array(false, array(array('expireValue', 'oauth_access_tokens:at-1')));
        $this->assertEquals($expected, $actual);
    }

    /** supported:global の全スコープに含まれる scope で scopeExists を呼んだ場合、array_diff が空になり true を返すこと */
    public function testsupported_global_の全スコープに含まれる_scope_で_scopeExists_を呼んだ場合_array_diff_が空になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_scopes:supported:global'] = 'read write profile';

        $actual = array($storage->scopeExists('read profile'), $storage->calls);

        $expected = array(true, array(array('getValue', 'oauth_scopes:supported:global')));
        $this->assertEquals($expected, $actual);
    }

    /** supported:global に含まれないスコープを含む scope で scopeExists を呼んだ場合、array_diff が空にならず false を返すこと */
    public function testsupported_global_に含まれないスコープを含む_scope_で_scopeExists_を呼んだ場合_array_diff_が空にならず_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_scopes:supported:global'] = 'read write';

        $actual = array($storage->scopeExists('read admin'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_scopes:supported:global')));
        $this->assertEquals($expected, $actual);
    }

    /** supported:global が store に無く getValue が false を返す状態で scopeExists を呼んだ場合、false を文字列化した空文字との差分で判定した結果を返すこと */
    public function testsupported_global_が_store_に無く_getValue_が_false_を返す状態で_scopeExists_を呼んだ場合_false_を文字列化した空文字との差分で判定した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->scopeExists('read'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_scopes:supported:global')));
        $this->assertEquals($expected, $actual);
    }

    /** client_id を省略して getDefaultScope を呼んだ場合、default:global の値を返すこと */
    public function testclient_id_を省略して_getDefaultScope_を呼んだ場合_default_global_の値を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_scopes:default:global'] = 'read';

        $actual = array($storage->getDefaultScope(), $storage->calls);

        $expected = array('read', array(array('getValue', 'oauth_scopes:default:global')));
        $this->assertEquals($expected, $actual);
    }

    /** default:client-a が store にある状態で client_id を渡して getDefaultScope を呼んだ場合、default:client-a の値を返し default:global は読まないこと */
    public function testdefault_client_a_が_store_にある状態で_client_id_を渡して_getDefaultScope_を呼んだ場合_default_client_a_の値を返し_default_global_は読まないこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_scopes:default:client-a'] = 'write';
        $storage->store['oauth_scopes:default:global'] = 'read';

        $actual = array($storage->getDefaultScope('client-a'), $storage->calls);

        $expected = array('write', array(array('getValue', 'oauth_scopes:default:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** default:client-a が store に無い状態で client_id を渡して getDefaultScope を呼んだ場合、default:global の値へ戻して返すこと */
    public function testdefault_client_a_が_store_に無い状態で_client_id_を渡して_getDefaultScope_を呼んだ場合_default_global_の値へ戻して返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_scopes:default:global'] = 'read';

        $actual = array($storage->getDefaultScope('client-a'), $storage->calls);

        $expected = array('read', array(array('getValue', 'oauth_scopes:default:client-a'), array('getValue', 'oauth_scopes:default:global')));
        $this->assertEquals($expected, $actual);
    }

    /** type に 'default' と 'supported' 以外の文字列を渡して setScope を呼んだ場合、InvalidArgumentException を投げること */
    public function testtype_に_default_と_supported_以外の文字列を渡して_setScope_を呼んだ場合_InvalidArgumentException_を投げること() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $callable = function () use ($storage) {
            return $storage->setScope('read', null, 'other');
        };

        $actual = array(OAuth2_Storage_Redis_captureException($callable), $storage->calls);

        $expected = array(array('InvalidArgumentException', '"$type" must be one of "default", "supported"'), array());
        $this->assertEquals($expected, $actual);
    }

    /** client_id を省略して setScope を呼んだ場合、type:global のキーで setValue を呼んだ結果を返すこと */
    public function testclient_id_を省略して_setScope_を呼んだ場合_type_global_のキーで_setValue_を呼んだ結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setScope('read write'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_scopes:supported:global', 'read write', 0)));
        $this->assertEquals($expected, $actual);
    }

    /** client_id と type に 'default' を渡して setScope を呼んだ場合、default:client_id のキーで setValue を呼んだ結果を返すこと */
    public function testclient_id_と_type_に_default_を渡して_setScope_を呼んだ場合_default_client_id_のキーで_setValue_を呼んだ結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setScope('read', 'client-a', 'default'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_scopes:default:client-a', 'read', 0)));
        $this->assertEquals($expected, $actual);
    }

    /** jwt_key に続く client_id が store に無い状態で getClientKey を呼んだ場合、false を返すこと */
    public function testjwt_key_に続く_client_id_が_store_に無い状態で_getClientKey_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->getClientKey('client-a', 'subject-1'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_jwt:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の subject と同じ subject で getClientKey を呼んだ場合、store の key を返すこと */
    public function teststore_の_subject_と同じ_subject_で_getClientKey_を呼んだ場合_store_の_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_jwt:client-a'] = array('key' => 'public-key-pem', 'subject' => 'subject-1');

        $actual = array($storage->getClientKey('client-a', 'subject-1'), $storage->calls);

        $expected = array('public-key-pem', array(array('getValue', 'oauth_jwt:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の subject と異なる subject で getClientKey を呼んだ場合、null を返すこと */
    public function teststore_の_subject_と異なる_subject_で_getClientKey_を呼んだ場合_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_jwt:client-a'] = array('key' => 'public-key-pem', 'subject' => 'subject-1');

        $actual = array($storage->getClientKey('client-a', 'subject-2'), $storage->calls);

        $expected = array(null, array(array('getValue', 'oauth_jwt:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の jwt に subject のキーが無い状態で getClientKey を呼んだ場合、isset が偽になり null を返すこと */
    public function teststore_の_jwt_に_subject_のキーが無い状態で_getClientKey_を呼んだ場合_isset_が偽になり_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_jwt:client-a'] = array('key' => 'public-key-pem');

        $actual = array($storage->getClientKey('client-a', 'subject-1'), $storage->calls);

        $expected = array(null, array(array('getValue', 'oauth_jwt:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** setClientKey に subject を省略して呼んだ場合、jwt_key のキーで key と subject の配列を setValue に渡した結果を返すこと */
    public function testsetClientKey_に_subject_を省略して呼んだ場合_jwt_key_のキーで_key_と_subject_の配列を_setValue_に渡した結果を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->setClientKey('client-a', 'public-key-pem'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_jwt:client-a', array('key' => 'public-key-pem', 'subject' => null), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** client_key に続く client_id が store に無い状態で getClientScope を呼んだ場合、false を返すこと */
    public function testclient_key_に続く_client_id_が_store_に無い状態で_getClientScope_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);

        $actual = array($storage->getClientScope('client-a'), $storage->calls);

        $expected = array(false, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client に scope がある状態で getClientScope を呼んだ場合、store の scope を返すこと */
    public function teststore_の_client_に_scope_がある状態で_getClientScope_を呼んだ場合_store_の_scope_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a', 'scope' => 'read write');

        $actual = array($storage->getClientScope('client-a'), $storage->calls);

        $expected = array('read write', array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** store の client に scope のキーが無い状態で getClientScope を呼んだ場合、null を返すこと */
    public function teststore_の_client_に_scope_のキーが無い状態で_getClientScope_を呼んだ場合_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $storage->store['oauth_clients:client-a'] = array('client_id' => 'client-a');

        $actual = array($storage->getClientScope('client-a'), $storage->calls);

        $expected = array(null, array(array('getValue', 'oauth_clients:client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** getJti を呼んだ場合、未実装の Exception を投げること */
    public function testgetJti_を呼んだ場合_未実装の_Exception_を投げること() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $callable = function () use ($storage) {
            return $storage->getJti('client-a', 'subject-1', 'audience', 1700000000, 'jti-1');
        };

        $actual = OAuth2_Storage_Redis_captureException($callable);

        $expected = array('Exception', 'getJti() for the Redis driver is currently unimplemented.');
        $this->assertEquals($expected, $actual);
    }

    /** setJti を呼んだ場合、未実装の Exception を投げること */
    public function testsetJti_を呼んだ場合_未実装の_Exception_を投げること() {
        $storage = new TestableOAuth2_Storage_Redis(null);
        $callable = function () use ($storage) {
            return $storage->setJti('client-a', 'subject-1', 'audience', 1700000000, 'jti-1');
        };

        $actual = OAuth2_Storage_Redis_captureException($callable);

        $expected = array('Exception', 'setJti() for the Redis driver is currently unimplemented.');
        $this->assertEquals($expected, $actual);
    }

    /** config で access_token_key を上書きして getAccessToken を呼んだ場合、array_merge で上書きしたキーで getValue を呼ぶこと */
    public function testconfig_で_access_token_key_を上書きして_getAccessToken_を呼んだ場合_array_merge_で上書きしたキーで_getValue_を呼ぶこと() {
        $storage = new TestableOAuth2_Storage_Redis(null, array('access_token_key' => 'custom_at:'));
        $storage->store['custom_at:at-1'] = array('access_token' => 'at-1');

        $actual = array($storage->getAccessToken('at-1'), $storage->calls);

        $expected = array(array('access_token' => 'at-1'), array(array('getValue', 'custom_at:at-1')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイク redis の get が JSON 文字列を返す状態で getAccessToken を呼んだ場合、getValue が cache を経由せず redis->get を呼び json_decode した配列を返すこと */
    public function testフェイク_redis_の_get_が_JSON_文字列を返す状態で_getAccessToken_を呼んだ場合_getValue_が_cache_を経由せず_redis_get_を呼び_json_decode_した配列を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->values['oauth_access_tokens:at-1'] = '{"access_token":"at-1","client_id":"client-a","expires":1700000000}';
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->getAccessToken('at-1'), $redis->calls);

        $expected = array(array('access_token' => 'at-1', 'client_id' => 'client-a', 'expires' => 1700000000), array(array('get', 'oauth_access_tokens:at-1')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイク redis の get が null を返す状態で getAccessToken を呼んだ場合、getValue の isset が偽になり false を返すこと */
    public function testフェイク_redis_の_get_が_null_を返す状態で_getAccessToken_を呼んだ場合_getValue_の_isset_が偽になり_false_を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->getAccessToken('at-missing'), $redis->calls);

        $expected = array(false, array(array('get', 'oauth_access_tokens:at-missing')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイク redis の set が bool の true を返す状態で expire 無しの setClientDetails を呼んだ場合、setValue が redis->set に json_encode した文字列を渡し is_bool が真なのでその戻り値をそのまま返すこと */
    public function testフェイク_redis_の_set_が_bool_の_true_を返す状態で_expire_無しの_setClientDetails_を呼んだ場合_setValue_が_redis_set_に_json_encode_した文字列を渡し_is_bool_が真なのでその戻り値をそのまま返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = true;
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setClientDetails('client-a', 'sec'), $redis->calls);

        $expected = array(true, array(array('set', 'oauth_clients:client-a', '{"client_id":"client-a","client_secret":"sec","redirect_uri":null,"grant_types":null,"scope":null,"user_id":null}')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイク redis の set が getPayload が 'OK' を返すオブジェクトを返す状態で setClientDetails を呼んだ場合、setValue の is_bool が偽になり getPayload と 'OK' の比較結果を返すこと */
    public function testフェイク_redis_の_set_が_getPayload_が_OK_を返すオブジェクトを返す状態で_setClientDetails_を呼んだ場合_setValue_の_is_bool_が偽になり_getPayload_と_OK_の比較結果を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = new OAuth2_Storage_RedisFakeStatus('OK');
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setClientDetails('client-a', 'sec'), $redis->calls);

        $expected = array(true, array(array('set', 'oauth_clients:client-a', '{"client_id":"client-a","client_secret":"sec","redirect_uri":null,"grant_types":null,"scope":null,"user_id":null}')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイク redis の set が getPayload が 'ERR' を返すオブジェクトを返す状態で setClientDetails を呼んだ場合、setValue の getPayload と 'OK' の比較が偽になりその結果を返すこと */
    public function testフェイク_redis_の_set_が_getPayload_が_ERR_を返すオブジェクトを返す状態で_setClientDetails_を呼んだ場合_setValue_の_getPayload_と_OK_の比較が偽になりその結果を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = new OAuth2_Storage_RedisFakeStatus('ERR');
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setClientDetails('client-a', 'sec'), $redis->calls);

        $expected = array(false, array(array('set', 'oauth_clients:client-a', '{"client_id":"client-a","client_secret":"sec","redirect_uri":null,"grant_types":null,"scope":null,"user_id":null}')));
        $this->assertEquals($expected, $actual);
    }

    /** expires に正の値を渡して setAccessToken を呼んだ場合、setValue の expire > 0 が真になり redis->setex に json_encode した文字列を渡した結果を返すこと */
    public function testexpires_に正の値を渡して_setAccessToken_を呼んだ場合_setValue_の_expire_0_が真になり_redis_setex_に_json_encode_した文字列を渡した結果を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = true;
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setAccessToken('at-1', 'client-a', 'user-1', 1700000000, 'read'), $redis->calls);

        $expected = array(true, array(array('setex', 'oauth_access_tokens:at-1', '{"access_token":"at-1","client_id":"client-a","user_id":"user-1","expires":1700000000,"scope":"read"}')));
        $this->assertEquals($expected, $actual);
    }

    /** setClientDetails の直後に同じ client_id で getClientDetails を呼んだ場合、getValue の cache の isset が真になり redis->get を呼ばず setValue で cache に入れた配列を返すこと */
    public function testsetClientDetails_の直後に同じ_client_id_で_getClientDetails_を呼んだ場合_getValue_の_cache_の_isset_が真になり_redis_get_を呼ばず_setValue_で_cache_に入れた配列を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = true;
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setClientDetails('client-a', 'sec'), $storage->getClientDetails('client-a'), $redis->calls);

        $expected = array(true, array('client_id' => 'client-a', 'client_secret' => 'sec', 'redirect_uri' => null, 'grant_types' => null, 'scope' => null, 'user_id' => null), array(array('set', 'oauth_clients:client-a', '{"client_id":"client-a","client_secret":"sec","redirect_uri":null,"grant_types":null,"scope":null,"user_id":null}')));
        $this->assertEquals($expected, $actual);
    }

    /** setAccessToken の後に unsetAccessToken を呼びさらに同じ access_token で getAccessToken を呼んだ場合、expireValue が cache を unset して redis->del の戻り値を返し続く getValue は redis->get を呼んで false を返すこと */
    public function testsetAccessToken_の後に_unsetAccessToken_を呼びさらに同じ_access_token_で_getAccessToken_を呼んだ場合_expireValue_が_cache_を_unset_して_redis_del_の戻り値を返し続く_getValue_は_redis_get_を呼んで_false_を返すこと() {
        $redis = new OAuth2_Storage_RedisFakeClient();
        $redis->setResult = true;
        $redis->delResult = 1;
        $storage = new \OAuth2\Storage\Redis($redis);

        $actual = array($storage->setAccessToken('at-1', 'client-a', 'user-1', 1700000000), $storage->unsetAccessToken('at-1'), $storage->getAccessToken('at-1'), $redis->calls);

        $expected = array(true, true, false, array(array('setex', 'oauth_access_tokens:at-1', '{"access_token":"at-1","client_id":"client-a","user_id":"user-1","expires":1700000000,"scope":null}'), array('del', 'oauth_access_tokens:at-1'), array('get', 'oauth_access_tokens:at-1')));
        $this->assertEquals($expected, $actual);
    }
}
