<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/Storage/Cassandra.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\Storage\\Cassandra');


class TestableOAuth2_Storage_Cassandra extends \OAuth2\Storage\Cassandra
{
    public $store = array();
    public $calls = array();
    public $setResult = true;

    protected function createConnectionPool($keyspace, $servers)
    {
        return null;
    }

    protected function getValue($key)
    {
        $this->calls[] = array('getValue', $key);
        if (isset($this->store[$key])) {
            return $this->store[$key];
        }

        return false;
    }

    protected function setValue($key, $value, $expire = 0)
    {
        $this->calls[] = array('setValue', $key, $value, $expire);

        return $this->setResult;
    }

    protected function expireValue($key)
    {
        $this->calls[] = array('expireValue', $key);
        $existed = isset($this->store[$key]);
        unset($this->store[$key]);

        return $existed;
    }
}

function OAuth2_Storage_Cassandra_catchException($callable)
{
    try {
        return $callable();
    } catch (\Exception $e) {
        return get_class($e);
    }
}

class OAuth2_Storage_CassandraCacheOnly extends \OAuth2\Storage\Cassandra
{
    protected function createConnectionPool($keyspace, $servers)
    {
        return null;
    }
}

function OAuth2_Storage_Cassandra_seedCache($storage, array $cache)
{
    $property = new \ReflectionProperty('OAuth2\Storage\Cassandra', 'cache');
    $property->setAccessible(true);
    $property->setValue($storage, $cache);
}

class OAuth2_Storage_CassandraCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** コンストラクタの第1引数に ConnectionPool でも配列でもない文字列 'bad' を渡した場合、InvalidArgumentException を投げること */
    public function testコンストラクタの第1引数に_ConnectionPool_でも配列でもない文字列_bad_を渡した場合_InvalidArgumentException_を投げること() {
        $actual = OAuth2_Storage_Cassandra_catchException(function () { return new TestableOAuth2_Storage_Cassandra('bad'); });

        $expected = 'InvalidArgumentException';
        $this->assertEquals($expected, $actual);
    }

    /** getValue が code_key と code を連結したキーで配列を返す状態で getAuthorizationCode を呼んだ場合、getValue の結果をそのまま返し、呼ばれたキーを記録すること */
    public function testgetValue_が_code_key_と_code_を連結したキーで配列を返す状態で_getAuthorizationCode_を呼んだ場合_getValue_の結果をそのまま返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_authorization_codes:code1'] = array('authorization_code' => 'code1', 'client_id' => 'client1');

        $actual = array($storage->getAuthorizationCode('code1'), $storage->calls);

        $expected = array(array('authorization_code' => 'code1', 'client_id' => 'client1'), array(array('getValue', 'oauth_authorization_codes:code1')));
        $this->assertEquals($expected, $actual);
    }

    /** setValue が true を返す状態で setAuthorizationCode に expires 1700000000 を渡した場合、compact した配列と expires を setValue へ渡し、その戻り値を返すこと */
    public function testsetValue_が_true_を返す状態で_setAuthorizationCode_に_expires_1700000000_を渡した場合_compact_した配列と_expires_を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setAuthorizationCode('code1', 'client1', 'user1', 'http://example.com/cb', 1700000000, 'read', 'idtoken1'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_authorization_codes:code1', array('authorization_code' => 'code1', 'client_id' => 'client1', 'user_id' => 'user1', 'redirect_uri' => 'http://example.com/cb', 'expires' => 1700000000, 'scope' => 'read', 'id_token' => 'idtoken1'), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が該当キーの存在を true で返す状態で expireAuthorizationCode を呼んだ場合、expireValue の戻り値を返し、呼ばれたキーを記録すること */
    public function testexpireValue_が該当キーの存在を_true_で返す状態で_expireAuthorizationCode_を呼んだ場合_expireValue_の戻り値を返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_authorization_codes:code1'] = array('authorization_code' => 'code1');

        $actual = array($storage->expireAuthorizationCode('code1'), $storage->calls);

        $expected = array(true, array(array('expireValue', 'oauth_authorization_codes:code1')));
        $this->assertEquals($expected, $actual);
    }

    /** getUser が false を返す （ユーザーが store に無い） 状態で checkUserCredentials を呼んだ場合、checkPassword を呼ばずに false を返すこと */
    public function testgetUser_が_false_を返す_ユーザーが_store_に無い_状態で_checkUserCredentials_を呼んだ場合_checkPassword_を呼ばずに_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->checkUserCredentials('alice', 'secret');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store の password が sha1('secret') で、checkUserCredentials に 'secret' を渡した場合、checkPassword の結果として true を返すこと */
    public function teststore_の_password_が_sha1_secret_で_checkUserCredentials_に_secret_を渡した場合_checkPassword_の結果として_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => sha1('secret'));

        $actual = $storage->checkUserCredentials('alice', 'secret');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** store の password が sha1('secret') で、checkUserCredentials に 'wrong' を渡した場合、checkPassword の結果として false を返すこと */
    public function teststore_の_password_が_sha1_secret_で_checkUserCredentials_に_wrong_を渡した場合_checkPassword_の結果として_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => sha1('secret'));

        $actual = $storage->checkUserCredentials('alice', 'wrong');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** getValue が user_key のキーで配列を返す状態で getUserDetails を呼んだ場合、user_id に username を入れた配列と getValue の結果をマージして返すこと */
    public function testgetValue_が_user_key_のキーで配列を返す状態で_getUserDetails_を呼んだ場合_user_id_に_username_を入れた配列と_getValue_の結果をマージして返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'hashed', 'first_name' => 'Alice', 'last_name' => null);

        $actual = $storage->getUserDetails('alice');

        $expected = array('user_id' => 'alice', 'username' => 'alice', 'password' => 'hashed', 'first_name' => 'Alice', 'last_name' => null);
        $this->assertEquals($expected, $actual);
    }

    /** getValue が false を返す状態で getUserDetails を呼んだ場合、false を返すこと */
    public function testgetValue_が_false_を返す状態で_getUserDetails_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getUserDetails('nobody');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** setUser に平文の password 'secret' を渡した場合、hashPassword で sha1 にした password を含む配列を setValue へ渡し、その戻り値を返すこと */
    public function testsetUser_に平文の_password_secret_を渡した場合_hashPassword_で_sha1_にした_password_を含む配列を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setUser('alice', 'secret', 'Alice', 'Smith'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_users:alice', array('username' => 'alice', 'password' => 'e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4', 'first_name' => 'Alice', 'last_name' => 'Smith'), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** getClientDetails が false を返す状態で checkClientCredentials を呼んだ場合、false を返すこと */
    public function testgetClientDetails_が_false_を返す状態で_checkClientCredentials_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->checkClientCredentials('client1', 'sec');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret が 'sec' で、checkClientCredentials に 'sec' を渡した場合、true を返すこと */
    public function teststore_の_client_secret_が_sec_で_checkClientCredentials_に_sec_を渡した場合_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'client_secret' => 'sec');

        $actual = $storage->checkClientCredentials('client1', 'sec');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** store のクライアント配列に client_secret キーが無い状態で checkClientCredentials を呼んだ場合、isset が偽になり false を返すこと */
    public function teststore_のクライアント配列に_client_secret_キーが無い状態で_checkClientCredentials_を呼んだ場合_isset_が偽になり_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1');

        $actual = $storage->checkClientCredentials('client1', 'sec');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** getClientDetails が false を返す状態で isPublicClient を呼んだ場合、false を返すこと */
    public function testgetClientDetails_が_false_を返す状態で_isPublicClient_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->isPublicClient('client1');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret が空文字の状態で isPublicClient を呼んだ場合、empty の結果として true を返すこと */
    public function teststore_の_client_secret_が空文字の状態で_isPublicClient_を呼んだ場合_empty_の結果として_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'client_secret' => '');

        $actual = $storage->isPublicClient('client1');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** store の client_secret が 'sec' の状態で isPublicClient を呼んだ場合、empty の結果として false を返すこと */
    public function teststore_の_client_secret_が_sec_の状態で_isPublicClient_を呼んだ場合_empty_の結果として_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'client_secret' => 'sec');

        $actual = $storage->isPublicClient('client1');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** setClientDetails に全引数を渡した場合、client_key のキーと compact した配列を setValue へ渡し、その戻り値を返すこと */
    public function testsetClientDetails_に全引数を渡した場合_client_key_のキーと_compact_した配列を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setClientDetails('client1', 'sec', 'http://example.com/cb', 'authorization_code', 'read', 'user1'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_clients:client1', array('client_id' => 'client1', 'client_secret' => 'sec', 'redirect_uri' => 'http://example.com/cb', 'grant_types' => 'authorization_code', 'scope' => 'read', 'user_id' => 'user1'), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** store の grant_types が 'authorization_code password' で、checkRestrictedGrantType に 'password' を渡した場合、in_array の結果として true を返すこと */
    public function teststore_の_grant_types_が_authorization_code_password_で_checkRestrictedGrantType_に_password_を渡した場合_in_array_の結果として_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'grant_types' => 'authorization_code password');

        $actual = $storage->checkRestrictedGrantType('client1', 'password');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** store の grant_types が 'authorization_code password' で、checkRestrictedGrantType に 'implicit' を渡した場合、in_array の結果として false を返すこと */
    public function teststore_の_grant_types_が_authorization_code_password_で_checkRestrictedGrantType_に_implicit_を渡した場合_in_array_の結果として_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'grant_types' => 'authorization_code password');

        $actual = $storage->checkRestrictedGrantType('client1', 'implicit');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store のクライアント配列に grant_types が無い状態で checkRestrictedGrantType を呼んだ場合、制限なしとして true を返すこと */
    public function teststore_のクライアント配列に_grant_types_が無い状態で_checkRestrictedGrantType_を呼んだ場合_制限なしとして_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1');

        $actual = $storage->checkRestrictedGrantType('client1', 'implicit');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** getValue が refresh_token_key のキーで配列を返す状態で getRefreshToken を呼んだ場合、getValue の結果をそのまま返し、呼ばれたキーを記録すること */
    public function testgetValue_が_refresh_token_key_のキーで配列を返す状態で_getRefreshToken_を呼んだ場合_getValue_の結果をそのまま返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_refresh_tokens:rt1'] = array('refresh_token' => 'rt1', 'client_id' => 'client1');

        $actual = array($storage->getRefreshToken('rt1'), $storage->calls);

        $expected = array(array('refresh_token' => 'rt1', 'client_id' => 'client1'), array(array('getValue', 'oauth_refresh_tokens:rt1')));
        $this->assertEquals($expected, $actual);
    }

    /** setRefreshToken に expires 1700000000 を渡した場合、compact した配列と expires を setValue へ渡し、その戻り値を返すこと */
    public function testsetRefreshToken_に_expires_1700000000_を渡した場合_compact_した配列と_expires_を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setRefreshToken('rt1', 'client1', 'user1', 1700000000, 'read'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_refresh_tokens:rt1', array('refresh_token' => 'rt1', 'client_id' => 'client1', 'user_id' => 'user1', 'expires' => 1700000000, 'scope' => 'read'), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が該当キーの存在を true で返す状態で unsetRefreshToken を呼んだ場合、expireValue の戻り値を返し、呼ばれたキーを記録すること */
    public function testexpireValue_が該当キーの存在を_true_で返す状態で_unsetRefreshToken_を呼んだ場合_expireValue_の戻り値を返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_refresh_tokens:rt1'] = array('refresh_token' => 'rt1');

        $actual = array($storage->unsetRefreshToken('rt1'), $storage->calls);

        $expected = array(true, array(array('expireValue', 'oauth_refresh_tokens:rt1')));
        $this->assertEquals($expected, $actual);
    }

    /** getValue が access_token_key のキーで配列を返す状態で getAccessToken を呼んだ場合、getValue の結果をそのまま返し、呼ばれたキーを記録すること */
    public function testgetValue_が_access_token_key_のキーで配列を返す状態で_getAccessToken_を呼んだ場合_getValue_の結果をそのまま返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_access_tokens:at1'] = array('access_token' => 'at1', 'client_id' => 'client1');

        $actual = array($storage->getAccessToken('at1'), $storage->calls);

        $expected = array(array('access_token' => 'at1', 'client_id' => 'client1'), array(array('getValue', 'oauth_access_tokens:at1')));
        $this->assertEquals($expected, $actual);
    }

    /** setAccessToken に expires 1700000000 を渡した場合、compact した配列と expires を setValue へ渡し、その戻り値を返すこと */
    public function testsetAccessToken_に_expires_1700000000_を渡した場合_compact_した配列と_expires_を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setAccessToken('at1', 'client1', 'user1', 1700000000, 'read'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_access_tokens:at1', array('access_token' => 'at1', 'client_id' => 'client1', 'user_id' => 'user1', 'expires' => 1700000000, 'scope' => 'read'), 1700000000)));
        $this->assertEquals($expected, $actual);
    }

    /** expireValue が該当キーの不存在を false で返す状態で unsetAccessToken を呼んだ場合、expireValue の戻り値を返し、呼ばれたキーを記録すること */
    public function testexpireValue_が該当キーの不存在を_false_で返す状態で_unsetAccessToken_を呼んだ場合_expireValue_の戻り値を返し_呼ばれたキーを記録すること() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->unsetAccessToken('at1'), $storage->calls);

        $expected = array(false, array(array('expireValue', 'oauth_access_tokens:at1')));
        $this->assertEquals($expected, $actual);
    }

    /** supported:global が 'read write delete' で、scopeExists に 'read write' を渡した場合、array_diff が空になり true を返すこと */
    public function testsupported_global_が_read_write_delete_で_scopeExists_に_read_write_を渡した場合_array_diff_が空になり_true_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_scopes:supported:global'] = 'read write delete';

        $actual = $storage->scopeExists('read write');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** supported:global が 'read write delete' で、scopeExists に 'read admin' を渡した場合、array_diff が空にならず false を返すこと */
    public function testsupported_global_が_read_write_delete_で_scopeExists_に_read_admin_を渡した場合_array_diff_が空にならず_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_scopes:supported:global'] = 'read write delete';

        $actual = $storage->scopeExists('read admin');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** getValue が supported:global に false を返す状態で scopeExists に 'read' を渡した場合、false を文字列化した空文字と比較して false を返すこと */
    public function testgetValue_が_supported_global_に_false_を返す状態で_scopeExists_に_read_を渡した場合_false_を文字列化した空文字と比較して_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->scopeExists('read');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** getDefaultScope に client_id を渡さなかった場合、default:global の値を返し、client 別キーを参照しないこと */
    public function testgetDefaultScope_に_client_id_を渡さなかった場合_default_global_の値を返し_client_別キーを参照しないこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_scopes:default:global'] = 'read';
        $storage->store['oauth_scopes:default:client1'] = 'write';

        $actual = array($storage->getDefaultScope(), $storage->calls);

        $expected = array('read', array(array('getValue', 'oauth_scopes:default:global')));
        $this->assertEquals($expected, $actual);
    }

    /** default:client1 が store にある状態で getDefaultScope に 'client1' を渡した場合、default:client1 の値を返し、global を参照しないこと */
    public function testdefault_client1_が_store_にある状態で_getDefaultScope_に_client1_を渡した場合_default_client1_の値を返し_global_を参照しないこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_scopes:default:global'] = 'read';
        $storage->store['oauth_scopes:default:client1'] = 'write';

        $actual = array($storage->getDefaultScope('client1'), $storage->calls);

        $expected = array('write', array(array('getValue', 'oauth_scopes:default:client1')));
        $this->assertEquals($expected, $actual);
    }

    /** default:client2 が store に無い状態で getDefaultScope に 'client2' を渡した場合、default:global の値へフォールバックして返すこと */
    public function testdefault_client2_が_store_に無い状態で_getDefaultScope_に_client2_を渡した場合_default_global_の値へフォールバックして返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_scopes:default:global'] = 'read';

        $actual = array($storage->getDefaultScope('client2'), $storage->calls);

        $expected = array('read', array(array('getValue', 'oauth_scopes:default:client2'), array('getValue', 'oauth_scopes:default:global')));
        $this->assertEquals($expected, $actual);
    }

    /** setScope の type に 'invalid' を渡した場合、InvalidArgumentException を投げること */
    public function testsetScope_の_type_に_invalid_を渡した場合_InvalidArgumentException_を投げること() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = OAuth2_Storage_Cassandra_catchException(function () use ($storage) { return $storage->setScope('read', null, 'invalid'); });

        $expected = 'InvalidArgumentException';
        $this->assertEquals($expected, $actual);
    }

    /** setScope に client_id を null、type を 'supported' で渡した場合、supported:global のキーで setValue を呼び、その戻り値を返すこと */
    public function testsetScope_に_client_id_を_null_type_を_supported_で渡した場合_supported_global_のキーで_setValue_を呼び_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setScope('read write', null, 'supported'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_scopes:supported:global', 'read write', 0)));
        $this->assertEquals($expected, $actual);
    }

    /** setScope に client_id を 'client1'、type を 'default' で渡した場合、default:client1 のキーで setValue を呼び、その戻り値を返すこと */
    public function testsetScope_に_client_id_を_client1_type_を_default_で渡した場合_default_client1_のキーで_setValue_を呼び_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setScope('read', 'client1', 'default'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_scopes:default:client1', 'read', 0)));
        $this->assertEquals($expected, $actual);
    }

    /** getValue が jwt_key のキーに false を返す状態で getClientKey を呼んだ場合、false を返すこと */
    public function testgetValue_が_jwt_key_のキーに_false_を返す状態で_getClientKey_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getClientKey('client1', 'subject1');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store の jwt の subject が 'subject1' で、getClientKey に 'subject1' を渡した場合、jwt の key を返すこと */
    public function teststore_の_jwt_の_subject_が_subject1_で_getClientKey_に_subject1_を渡した場合_jwt_の_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_jwt:client1'] = array('key' => 'pubkey1', 'subject' => 'subject1');

        $actual = $storage->getClientKey('client1', 'subject1');

        $expected = 'pubkey1';
        $this->assertEquals($expected, $actual);
    }

    /** store の jwt の subject が 'subject1' で、getClientKey に 'other' を渡した場合、null を返すこと */
    public function teststore_の_jwt_の_subject_が_subject1_で_getClientKey_に_other_を渡した場合_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_jwt:client1'] = array('key' => 'pubkey1', 'subject' => 'subject1');

        $actual = $storage->getClientKey('client1', 'other');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** setClientKey に key と subject を渡した場合、jwt_key のキーと key ・ subject の配列を setValue へ渡し、その戻り値を返すこと */
    public function testsetClientKey_に_key_と_subject_を渡した場合_jwt_key_のキーと_key_subject_の配列を_setValue_へ渡し_その戻り値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = array($storage->setClientKey('client1', 'pubkey1', 'subject1'), $storage->calls);

        $expected = array(true, array(array('setValue', 'oauth_jwt:client1', array('key' => 'pubkey1', 'subject' => 'subject1'), 0)));
        $this->assertEquals($expected, $actual);
    }

    /** getClientDetails が false を返す状態で getClientScope を呼んだ場合、false を返すこと */
    public function testgetClientDetails_が_false_を返す状態で_getClientScope_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getClientScope('client1');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store のクライアント配列に scope 'read write' がある状態で getClientScope を呼んだ場合、scope の値を返すこと */
    public function teststore_のクライアント配列に_scope_read_write_がある状態で_getClientScope_を呼んだ場合_scope_の値を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1', 'scope' => 'read write');

        $actual = $storage->getClientScope('client1');

        $expected = 'read write';
        $this->assertEquals($expected, $actual);
    }

    /** store のクライアント配列に scope キーが無い状態で getClientScope を呼んだ場合、null を返すこと */
    public function teststore_のクライアント配列に_scope_キーが無い状態で_getClientScope_を呼んだ場合_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_clients:client1'] = array('client_id' => 'client1');

        $actual = $storage->getClientScope('client1');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** getJti を呼んだ場合、未実装として Exception を投げること */
    public function testgetJti_を呼んだ場合_未実装として_Exception_を投げること() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = OAuth2_Storage_Cassandra_catchException(function () use ($storage) { return $storage->getJti('client1', 'subject1', 'aud', 1700000000, 'jti1'); });

        $expected = 'Exception';
        $this->assertEquals($expected, $actual);
    }

    /** setJti を呼んだ場合、未実装として Exception を投げること */
    public function testsetJti_を呼んだ場合_未実装として_Exception_を投げること() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = OAuth2_Storage_Cassandra_catchException(function () use ($storage) { return $storage->setJti('client1', 'subject1', 'aud', 1700000000, 'jti1'); });

        $expected = 'Exception';
        $this->assertEquals($expected, $actual);
    }

    /** client1 向けの public key 配列が store にある状態で getPublicKey に 'client1' を渡した場合、client1 向けの public_key を返すこと */
    public function testclient1_向けの_public_key_配列が_store_にある状態で_getPublicKey_に_client1_を渡した場合_client1_向けの_public_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:client1'] = array('public_key' => 'pub-client1', 'private_key' => 'priv-client1', 'encryption_algorithm' => 'HS256');
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getPublicKey('client1');

        $expected = 'pub-client1';
        $this->assertEquals($expected, $actual);
    }

    /** client2 向けの配列が無く public_key_key 単独の配列だけが store にある状態で getPublicKey に 'client2' を渡した場合、全体向けの public_key を返すこと */
    public function testclient2_向けの配列が無く_public_key_key_単独の配列だけが_store_にある状態で_getPublicKey_に_client2_を渡した場合_全体向けの_public_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getPublicKey('client2');

        $expected = 'pub-global';
        $this->assertEquals($expected, $actual);
    }

    /** getValue が client 別にも全体向けにも false を返す状態で getPublicKey を呼んだ場合、return に到達せず null を返すこと */
    public function testgetValue_が_client_別にも全体向けにも_false_を返す状態で_getPublicKey_を呼んだ場合_return_に到達せず_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getPublicKey('client2');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** client1 向けの public key 配列が store にある状態で getPrivateKey に 'client1' を渡した場合、client1 向けの private_key を返すこと */
    public function testclient1_向けの_public_key_配列が_store_にある状態で_getPrivateKey_に_client1_を渡した場合_client1_向けの_private_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:client1'] = array('public_key' => 'pub-client1', 'private_key' => 'priv-client1', 'encryption_algorithm' => 'HS256');
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getPrivateKey('client1');

        $expected = 'priv-client1';
        $this->assertEquals($expected, $actual);
    }

    /** client2 向けの配列が無く public_key_key 単独の配列だけが store にある状態で getPrivateKey に 'client2' を渡した場合、全体向けの private_key を返すこと */
    public function testclient2_向けの配列が無く_public_key_key_単独の配列だけが_store_にある状態で_getPrivateKey_に_client2_を渡した場合_全体向けの_private_key_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getPrivateKey('client2');

        $expected = 'priv-global';
        $this->assertEquals($expected, $actual);
    }

    /** getValue が client 別にも全体向けにも false を返す状態で getPrivateKey を呼んだ場合、return に到達せず null を返すこと */
    public function testgetValue_が_client_別にも全体向けにも_false_を返す状態で_getPrivateKey_を呼んだ場合_return_に到達せず_null_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getPrivateKey('client2');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** client1 向けの public key 配列が store にある状態で getEncryptionAlgorithm に 'client1' を渡した場合、client1 向けの encryption_algorithm を返すこと */
    public function testclient1_向けの_public_key_配列が_store_にある状態で_getEncryptionAlgorithm_に_client1_を渡した場合_client1_向けの_encryption_algorithm_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:client1'] = array('public_key' => 'pub-client1', 'private_key' => 'priv-client1', 'encryption_algorithm' => 'HS256');
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getEncryptionAlgorithm('client1');

        $expected = 'HS256';
        $this->assertEquals($expected, $actual);
    }

    /** client2 向けの配列が無く public_key_key 単独の配列だけが store にある状態で getEncryptionAlgorithm に 'client2' を渡した場合、全体向けの encryption_algorithm を返すこと */
    public function testclient2_向けの配列が無く_public_key_key_単独の配列だけが_store_にある状態で_getEncryptionAlgorithm_に_client2_を渡した場合_全体向けの_encryption_algorithm_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_public_keys:'] = array('public_key' => 'pub-global', 'private_key' => 'priv-global', 'encryption_algorithm' => 'ES256');

        $actual = $storage->getEncryptionAlgorithm('client2');

        $expected = 'ES256';
        $this->assertEquals($expected, $actual);
    }

    /** getValue が client 別にも全体向けにも false を返す状態で getEncryptionAlgorithm を呼んだ場合、既定の 'RS256' を返すこと */
    public function testgetValue_が_client_別にも全体向けにも_false_を返す状態で_getEncryptionAlgorithm_を呼んだ場合_既定の_RS256_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getEncryptionAlgorithm('client2');

        $expected = 'RS256';
        $this->assertEquals($expected, $actual);
    }

    /** getUserDetails が false を返す状態で getUserClaims を呼んだ場合、false を返すこと */
    public function testgetUserDetails_が_false_を返す状態で_getUserClaims_を呼んだ場合_false_を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();

        $actual = $storage->getUserClaims('nobody', 'profile email');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** store のユーザーに name ・ given_name ・ email ・ email_verified 'true' がある状態で getUserClaims に 'profile email' を渡した場合、profile と email の各 claim 値を集めた配列を返し、email_verified を真偽値にすること */
    public function teststore_のユーザーに_name_given_name_email_email_verified_true_がある状態で_getUserClaims_に_profile_email_を渡した場合_profile_と_email_の各_claim_値を集めた配列を返し_email_verified_を真偽値にすること() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'hashed', 'name' => 'Alice Smith', 'given_name' => 'Alice', 'email' => 'alice@example.com', 'email_verified' => 'true');

        $actual = $storage->getUserClaims('alice', 'profile email');

        $expected = array('name' => 'Alice Smith', 'family_name' => null, 'given_name' => 'Alice', 'middle_name' => null, 'nickname' => null, 'preferred_username' => null, 'profile' => null, 'picture' => null, 'website' => null, 'gender' => null, 'birthdate' => null, 'zoneinfo' => null, 'locale' => null, 'updated_at' => null, 'email' => 'alice@example.com', 'email_verified' => true);
        $this->assertEquals($expected, $actual);
    }

    /** store のユーザーの email_verified が 'false' の状態で getUserClaims に 'email' を渡した場合、email_verified を false にした配列を返すこと */
    public function teststore_のユーザーの_email_verified_が_false_の状態で_getUserClaims_に_email_を渡した場合_email_verified_を_false_にした配列を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'hashed', 'email' => 'alice@example.com', 'email_verified' => 'false');

        $actual = $storage->getUserClaims('alice', 'email');

        $expected = array('email' => 'alice@example.com', 'email_verified' => false);
        $this->assertEquals($expected, $actual);
    }

    /** store のユーザーに address の配列がある状態で getUserClaims に 'address' を渡した場合、address キーの下に address の各 claim 値を入れた配列を返すこと */
    public function teststore_のユーザーに_address_の配列がある状態で_getUserClaims_に_address_を渡した場合_address_キーの下に_address_の各_claim_値を入れた配列を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'hashed', 'address' => array('formatted' => 'Tokyo, Japan', 'locality' => 'Tokyo', 'country' => 'JP'));

        $actual = $storage->getUserClaims('alice', 'address');

        $expected = array('address' => array('formatted' => 'Tokyo, Japan', 'street_address' => null, 'locality' => 'Tokyo', 'region' => null, 'postal_code' => null, 'country' => 'JP'));
        $this->assertEquals($expected, $actual);
    }

    /** store のユーザーに phone_number だけがある状態で getUserClaims に 'phone' を渡した場合、phone の claim 値を集め、無い項目を null にした配列を返すこと */
    public function teststore_のユーザーに_phone_number_だけがある状態で_getUserClaims_に_phone_を渡した場合_phone_の_claim_値を集め_無い項目を_null_にした配列を返すこと() {
        $storage = new TestableOAuth2_Storage_Cassandra();
        $storage->store['oauth_users:alice'] = array('username' => 'alice', 'password' => 'hashed', 'phone_number' => '+81-3-0000-0000');

        $actual = $storage->getUserClaims('alice', 'phone');

        $expected = array('phone_number' => '+81-3-0000-0000', 'phone_number_verified' => null);
        $this->assertEquals($expected, $actual);
    }

    /** getValue を override しないサブクラスの cache に code_key と 'code1' を連結したキーの配列を入れた状態で getAuthorizationCode を呼んだ場合、ColumnFamily を生成せずに cache の配列をそのまま返すこと */
    public function testgetValue_を_override_しないサブクラスの_cache_に_code_key_と_code1_を連結したキーの配列を入れた状態で_getAuthorizationCode_を呼んだ場合_ColumnFamily_を生成せずに_cache_の配列をそのまま返すこと() {
        $storage = new OAuth2_Storage_CassandraCacheOnly(array());
        OAuth2_Storage_Cassandra_seedCache($storage, array('oauth_authorization_codes:code1' => array('client_id' => 'client1', 'user_id' => 'user1', 'scope' => 'read')));

        $actual = $storage->getAuthorizationCode('code1');

        $expected = array('client_id' => 'client1', 'user_id' => 'user1', 'scope' => 'read');
        $this->assertEquals($expected, $actual);
    }
}
