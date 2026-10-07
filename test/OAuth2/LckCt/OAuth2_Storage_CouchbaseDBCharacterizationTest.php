<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/Storage/CouchbaseDB.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\Storage\\CouchbaseDB');


class OAuth2_Storage_CouchbaseDBTestable extends \OAuth2\Storage\CouchbaseDB
{
    public $store;
    public $calls = array();

    // 親のコンストラクタは Couchbase 接続を生成するため呼ばない。
    // キーは config の表名と id を '-' でつないだもの （例 'client_table-client-a'）。
    public function __construct($store)
    {
        $this->store = $store;
        $this->config = array();
    }

    protected function getObjectByType($name,$id)
    {
        $key = $name . '-' . $id;
        $this->calls[] = array('get', $name, $id);
        return isset($this->store[$key]) ? $this->store[$key] : null;
    }

    protected function setObjectByType($name,$id,$array)
    {
        $array['type'] = $name;
        $this->calls[] = array('set', $name, $id, $array);
        $this->store[$name . '-' . $id] = $array;
        return true;
    }

    protected function deleteObjectByType($name,$id)
    {
        $this->calls[] = array('delete', $name, $id);
        unset($this->store[$name . '-' . $id]);
    }
}

// Couchbase 接続の代わりに get / set / delete の呼び出しを記録するフェイク。
// 実物の get はキーが無いと false を返すので、それに合わせる。
class OAuth2_Storage_CouchbaseDBFakeDb
{
    public $store = array();
    public $calls = array();

    public function get($key)
    {
        $this->calls[] = array('get', $key);
        return isset($this->store[$key]) ? $this->store[$key] : false;
    }

    public function set($key, $value)
    {
        $this->calls[] = array('set', $key, $value);
        $this->store[$key] = $value;
        return true;
    }

    public function delete($key, $cas = "", $persist_to = 0)
    {
        $this->calls[] = array('delete', $key, $cas, $persist_to);
        unset($this->store[$key]);
        return true;
    }
}

// 親のコンストラクタと getObjectByType 等の本体をそのまま通し、
// createCouchbase だけをフェイクの db へ差し替える。
class OAuth2_Storage_CouchbaseDBWithFakeDb extends \OAuth2\Storage\CouchbaseDB
{
    public $createArgs;
    public $fakeDb;

    protected function createCouchbase($servers, $username, $password, $bucket, $persistent)
    {
        $this->createArgs = array($servers, $username, $password, $bucket, $persistent);
        $this->fakeDb = new OAuth2_Storage_CouchbaseDBFakeDb();
        return $this->fakeDb;
    }

    public function configForTest()
    {
        return $this->config;
    }
}

class OAuth2_Storage_CouchbaseDBCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** コンストラクタに配列でも Couchbase でもない文字列を渡した場合、InvalidArgumentException を投げること */
    public function testコンストラクタに配列でも_Couchbase_でもない文字列を渡した場合_InvalidArgumentException_を投げること() {
        $actual = call_user_func(function () { try { new \OAuth2\Storage\CouchbaseDB('not-an-array'); return 'no exception'; } catch (\InvalidArgumentException $e) { return get_class($e); } });

        $expected = 'InvalidArgumentException';
        $this->assertEquals($expected, $actual);
    }

    /** client_table に client-a があり checkClientCredentials に一致する client_secret を渡した場合、一致結果を返すこと */
    public function testclient_table_に_client_a_があり_checkClientCredentials_に一致する_client_secret_を渡した場合_一致結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => 'secret-a')));

        $actual = array($storage->checkClientCredentials('client-a', 'secret-a'), $storage->calls);

        $expected = array(true, array(array('get', 'client_table', 'client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** client_table に client-a があり checkClientCredentials に一致しない client_secret を渡した場合、不一致結果を返すこと */
    public function testclient_table_に_client_a_があり_checkClientCredentials_に一致しない_client_secret_を渡した場合_不一致結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => 'secret-a')));

        $actual = $storage->checkClientCredentials('client-a', 'wrong-secret');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に存在しない client_id を checkClientCredentials に渡した場合、false を返すこと */
    public function testclient_table_に存在しない_client_id_を_checkClientCredentials_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->checkClientCredentials('missing-client', 'secret-a');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に存在しない client_id を isPublicClient に渡した場合、false を返すこと */
    public function testclient_table_に存在しない_client_id_を_isPublicClient_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->isPublicClient('missing-client');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table の client-a の client_secret が空文字で isPublicClient を呼んだ場合、empty の判定結果を返すこと */
    public function testclient_table_の_client_a_の_client_secret_が空文字で_isPublicClient_を呼んだ場合_empty_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => '')));

        $actual = $storage->isPublicClient('client-a');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** client_table の client-a に client_secret があり isPublicClient を呼んだ場合、empty の判定結果を返すこと */
    public function testclient_table_の_client_a_に_client_secret_があり_isPublicClient_を呼んだ場合_empty_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => 'secret-a')));

        $actual = $storage->isPublicClient('client-a');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に client-a があり getClientDetails を呼んだ場合、保存された配列と getObjectByType の呼び出し記録を返すこと */
    public function testclient_table_に_client_a_があり_getClientDetails_を呼んだ場合_保存された配列と_getObjectByType_の呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => 'secret-a', 'scope' => 'read')));

        $actual = array($storage->getClientDetails('client-a'), $storage->calls);

        $expected = array(array('client_id' => 'client-a', 'client_secret' => 'secret-a', 'scope' => 'read'), array(array('get', 'client_table', 'client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** client_table に存在しない client_id を getClientDetails に渡した場合、false を返すこと */
    public function testclient_table_に存在しない_client_id_を_getClientDetails_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getClientDetails('missing-client');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に client-a が既にある状態で setClientDetails を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testclient_table_に_client_a_が既にある状態で_setClientDetails_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'client_secret' => 'secret-a')));

        $actual = array($storage->setClientDetails('client-a', 'secret-b', 'http://example.test/cb', 'password', 'read', 'user-1'), $storage->calls);

        $expected = array(true, array(array('get', 'client_table', 'client-a'), array('set', 'client_table', 'client-a', array('client_id' => 'client-a', 'client_secret' => 'secret-b', 'redirect_uri' => 'http://example.test/cb', 'grant_types' => 'password', 'scope' => 'read', 'user_id' => 'user-1', 'type' => 'client_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** client_table に client-new が無い状態で setClientDetails を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testclient_table_に_client_new_が無い状態で_setClientDetails_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = array($storage->setClientDetails('client-new', 'secret-n'), $storage->calls);

        $expected = array(true, array(array('get', 'client_table', 'client-new'), array('set', 'client_table', 'client-new', array('client_id' => 'client-new', 'client_secret' => 'secret-n', 'redirect_uri' => null, 'grant_types' => null, 'scope' => null, 'user_id' => null, 'type' => 'client_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** client-a の grant_types に含まれる grant_type を checkRestrictedGrantType に渡した場合、in_array の判定結果を返すこと */
    public function testclient_a_の_grant_types_に含まれる_grant_type_を_checkRestrictedGrantType_に渡した場合_in_array_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'grant_types' => 'password client_credentials')));

        $actual = $storage->checkRestrictedGrantType('client-a', 'password');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** client-a の grant_types に含まれない grant_type を checkRestrictedGrantType に渡した場合、in_array の判定結果を返すこと */
    public function testclient_a_の_grant_types_に含まれない_grant_type_を_checkRestrictedGrantType_に渡した場合_in_array_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'grant_types' => 'password client_credentials')));

        $actual = $storage->checkRestrictedGrantType('client-a', 'refresh_token');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client-a に grant_types が無い状態で checkRestrictedGrantType を呼んだ場合、true を返すこと */
    public function testclient_a_に_grant_types_が無い状態で_checkRestrictedGrantType_を呼んだ場合_true_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a')));

        $actual = $storage->checkRestrictedGrantType('client-a', 'password');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に存在しない client_id を checkRestrictedGrantType に渡した場合、true を返すこと */
    public function testclient_table_に存在しない_client_id_を_checkRestrictedGrantType_に渡した場合_true_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->checkRestrictedGrantType('missing-client', 'password');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** access_token_table に token-a があり getAccessToken を呼んだ場合、保存された配列と呼び出し記録を返すこと */
    public function testaccess_token_table_に_token_a_があり_getAccessToken_を呼んだ場合_保存された配列と呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('access_token_table-token-a' => array('access_token' => 'token-a', 'client_id' => 'client-a', 'expires' => 1000)));

        $actual = array($storage->getAccessToken('token-a'), $storage->calls);

        $expected = array(array('access_token' => 'token-a', 'client_id' => 'client-a', 'expires' => 1000), array(array('get', 'access_token_table', 'token-a')));
        $this->assertEquals($expected, $actual);
    }

    /** access_token_table に存在しない access_token を getAccessToken に渡した場合、false を返すこと */
    public function testaccess_token_table_に存在しない_access_token_を_getAccessToken_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getAccessToken('missing-token');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** access_token_table に token-a が既にある状態で setAccessToken を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testaccess_token_table_に_token_a_が既にある状態で_setAccessToken_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('access_token_table-token-a' => array('access_token' => 'token-a', 'client_id' => 'client-a', 'expires' => 1000)));

        $actual = array($storage->setAccessToken('token-a', 'client-a', 'user-1', 2000, 'read'), $storage->calls);

        $expected = array(true, array(array('get', 'access_token_table', 'token-a'), array('set', 'access_token_table', 'token-a', array('access_token' => 'token-a', 'client_id' => 'client-a', 'expires' => 2000, 'user_id' => 'user-1', 'scope' => 'read', 'type' => 'access_token_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** access_token_table に token-new が無い状態で setAccessToken を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testaccess_token_table_に_token_new_が無い状態で_setAccessToken_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = array($storage->setAccessToken('token-new', 'client-a', 'user-1', 2000), $storage->calls);

        $expected = array(true, array(array('get', 'access_token_table', 'token-new'), array('set', 'access_token_table', 'token-new', array('access_token' => 'token-new', 'client_id' => 'client-a', 'expires' => 2000, 'user_id' => 'user-1', 'scope' => null, 'type' => 'access_token_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** code_table に code-a があり getAuthorizationCode を呼んだ場合、保存された配列と呼び出し記録を返すこと */
    public function testcode_table_に_code_a_があり_getAuthorizationCode_を呼んだ場合_保存された配列と呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('code_table-code-a' => array('authorization_code' => 'code-a', 'client_id' => 'client-a', 'expires' => 1000)));

        $actual = array($storage->getAuthorizationCode('code-a'), $storage->calls);

        $expected = array(array('authorization_code' => 'code-a', 'client_id' => 'client-a', 'expires' => 1000), array(array('get', 'code_table', 'code-a')));
        $this->assertEquals($expected, $actual);
    }

    /** code_table に存在しない code を getAuthorizationCode に渡した場合、false を返すこと */
    public function testcode_table_に存在しない_code_を_getAuthorizationCode_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getAuthorizationCode('missing-code');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** code_table に code-a が既にある状態で setAuthorizationCode を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testcode_table_に_code_a_が既にある状態で_setAuthorizationCode_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('code_table-code-a' => array('authorization_code' => 'code-a', 'client_id' => 'client-a', 'expires' => 1000)));

        $actual = array($storage->setAuthorizationCode('code-a', 'client-a', 'user-1', 'http://example.test/cb', 2000, 'read', 'id-token-1'), $storage->calls);

        $expected = array(true, array(array('get', 'code_table', 'code-a'), array('set', 'code_table', 'code-a', array('authorization_code' => 'code-a', 'client_id' => 'client-a', 'user_id' => 'user-1', 'redirect_uri' => 'http://example.test/cb', 'expires' => 2000, 'scope' => 'read', 'id_token' => 'id-token-1', 'type' => 'code_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** code_table に code-new が無い状態で setAuthorizationCode を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testcode_table_に_code_new_が無い状態で_setAuthorizationCode_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = array($storage->setAuthorizationCode('code-new', 'client-a', 'user-1', 'http://example.test/cb', 2000), $storage->calls);

        $expected = array(true, array(array('get', 'code_table', 'code-new'), array('set', 'code_table', 'code-new', array('authorization_code' => 'code-new', 'client_id' => 'client-a', 'user_id' => 'user-1', 'redirect_uri' => 'http://example.test/cb', 'expires' => 2000, 'scope' => null, 'id_token' => null, 'type' => 'code_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** expireAuthorizationCode に code-a を渡した場合、true と deleteObjectByType の呼び出し記録を返すこと */
    public function testexpireAuthorizationCode_に_code_a_を渡した場合_true_と_deleteObjectByType_の呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('code_table-code-a' => array('authorization_code' => 'code-a')));

        $actual = array($storage->expireAuthorizationCode('code-a'), $storage->calls);

        $expected = array(true, array(array('delete', 'code_table', 'code-a')));
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-a があり checkUserCredentials に一致する password を渡した場合、checkPassword の判定結果を返すこと */
    public function testuser_table_に_user_a_があり_checkUserCredentials_に一致する_password_を渡した場合_checkPassword_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('user_table-user-a' => array('username' => 'user-a', 'password' => 'pass-a')));

        $actual = $storage->checkUserCredentials('user-a', 'pass-a');

        $expected = true;
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-a があり checkUserCredentials に一致しない password を渡した場合、checkPassword の判定結果を返すこと */
    public function testuser_table_に_user_a_があり_checkUserCredentials_に一致しない_password_を渡した場合_checkPassword_の判定結果を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('user_table-user-a' => array('username' => 'user-a', 'password' => 'pass-a')));

        $actual = $storage->checkUserCredentials('user-a', 'wrong-pass');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** user_table に存在しない username を checkUserCredentials に渡した場合、false を返すこと */
    public function testuser_table_に存在しない_username_を_checkUserCredentials_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->checkUserCredentials('missing-user', 'pass-a');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-a があり getUserDetails を呼んだ場合、username を user_id に写した配列を返すこと */
    public function testuser_table_に_user_a_があり_getUserDetails_を呼んだ場合_username_を_user_id_に写した配列を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('user_table-user-a' => array('username' => 'user-a', 'password' => 'pass-a', 'first_name' => 'A')));

        $actual = $storage->getUserDetails('user-a');

        $expected = array('username' => 'user-a', 'password' => 'pass-a', 'first_name' => 'A', 'user_id' => 'user-a');
        $this->assertEquals($expected, $actual);
    }

    /** user_table に存在しない username を getUserDetails に渡した場合、getUser の結果をそのまま返すこと */
    public function testuser_table_に存在しない_username_を_getUserDetails_に渡した場合_getUser_の結果をそのまま返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getUserDetails('missing-user');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** refresh_token_table に refresh-a があり getRefreshToken を呼んだ場合、保存された配列と呼び出し記録を返すこと */
    public function testrefresh_token_table_に_refresh_a_があり_getRefreshToken_を呼んだ場合_保存された配列と呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('refresh_token_table-refresh-a' => array('refresh_token' => 'refresh-a', 'client_id' => 'client-a', 'expires' => 1000)));

        $actual = array($storage->getRefreshToken('refresh-a'), $storage->calls);

        $expected = array(array('refresh_token' => 'refresh-a', 'client_id' => 'client-a', 'expires' => 1000), array(array('get', 'refresh_token_table', 'refresh-a')));
        $this->assertEquals($expected, $actual);
    }

    /** refresh_token_table に存在しない refresh_token を getRefreshToken に渡した場合、false を返すこと */
    public function testrefresh_token_table_に存在しない_refresh_token_を_getRefreshToken_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getRefreshToken('missing-refresh');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** setRefreshToken に refresh-a と scope を渡した場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testsetRefreshToken_に_refresh_a_と_scope_を渡した場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = array($storage->setRefreshToken('refresh-a', 'client-a', 'user-1', 2000, 'read'), $storage->calls);

        $expected = array(true, array(array('set', 'refresh_token_table', 'refresh-a', array('refresh_token' => 'refresh-a', 'client_id' => 'client-a', 'user_id' => 'user-1', 'expires' => 2000, 'scope' => 'read', 'type' => 'refresh_token_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** unsetRefreshToken に refresh-a を渡した場合、true と deleteObjectByType の呼び出し記録を返すこと */
    public function testunsetRefreshToken_に_refresh_a_を渡した場合_true_と_deleteObjectByType_の呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('refresh_token_table-refresh-a' => array('refresh_token' => 'refresh-a')));

        $actual = array($storage->unsetRefreshToken('refresh-a'), $storage->calls);

        $expected = array(true, array(array('delete', 'refresh_token_table', 'refresh-a')));
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-a があり getUser を呼んだ場合、保存された配列と呼び出し記録を返すこと */
    public function testuser_table_に_user_a_があり_getUser_を呼んだ場合_保存された配列と呼び出し記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('user_table-user-a' => array('username' => 'user-a', 'password' => 'pass-a')));

        $actual = array($storage->getUser('user-a'), $storage->calls);

        $expected = array(array('username' => 'user-a', 'password' => 'pass-a'), array(array('get', 'user_table', 'user-a')));
        $this->assertEquals($expected, $actual);
    }

    /** user_table に存在しない username を getUser に渡した場合、false を返すこと */
    public function testuser_table_に存在しない_username_を_getUser_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getUser('missing-user');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-a が既にある状態で setUser を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testuser_table_に_user_a_が既にある状態で_setUser_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('user_table-user-a' => array('username' => 'user-a', 'password' => 'pass-a')));

        $actual = array($storage->setUser('user-a', 'pass-b', 'First', 'Last'), $storage->calls);

        $expected = array(true, array(array('get', 'user_table', 'user-a'), array('set', 'user_table', 'user-a', array('username' => 'user-a', 'password' => 'pass-b', 'first_name' => 'First', 'last_name' => 'Last', 'type' => 'user_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** user_table に user-new が無い状態で setUser を呼んだ場合、true と setObjectByType に渡された配列の記録を返すこと */
    public function testuser_table_に_user_new_が無い状態で_setUser_を呼んだ場合_true_と_setObjectByType_に渡された配列の記録を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = array($storage->setUser('user-new', 'pass-n'), $storage->calls);

        $expected = array(true, array(array('get', 'user_table', 'user-new'), array('set', 'user_table', 'user-new', array('username' => 'user-new', 'password' => 'pass-n', 'first_name' => null, 'last_name' => null, 'type' => 'user_table'))));
        $this->assertEquals($expected, $actual);
    }

    /** jwt_table に存在しない client_id を getClientKey に渡した場合、false を返すこと */
    public function testjwt_table_に存在しない_client_id_を_getClientKey_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getClientKey('missing-client', 'subject-a');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** jwt_table の client-a の subject が getClientKey に渡した subject と一致する場合、key を返すこと */
    public function testjwt_table_の_client_a_の_subject_が_getClientKey_に渡した_subject_と一致する場合_key_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('jwt_table-client-a' => array('client_id' => 'client-a', 'subject' => 'subject-a', 'key' => 'public-key-a')));

        $actual = $storage->getClientKey('client-a', 'subject-a');

        $expected = 'public-key-a';
        $this->assertEquals($expected, $actual);
    }

    /** jwt_table の client-a の subject が getClientKey に渡した subject と一致しない場合、false を返すこと */
    public function testjwt_table_の_client_a_の_subject_が_getClientKey_に渡した_subject_と一致しない場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('jwt_table-client-a' => array('client_id' => 'client-a', 'subject' => 'subject-a', 'key' => 'public-key-a')));

        $actual = $storage->getClientKey('client-a', 'subject-other');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** jwt_table の client-a に subject が無い状態で getClientKey を呼んだ場合、false を返すこと */
    public function testjwt_table_の_client_a_に_subject_が無い状態で_getClientKey_を呼んだ場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('jwt_table-client-a' => array('client_id' => 'client-a', 'key' => 'public-key-a')));

        $actual = $storage->getClientKey('client-a', 'subject-a');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table に存在しない client_id を getClientScope に渡した場合、false を返すこと */
    public function testclient_table_に存在しない_client_id_を_getClientScope_に渡した場合_false_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = $storage->getClientScope('missing-client');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** client_table の client-a に scope があり getClientScope を呼んだ場合、scope を返すこと */
    public function testclient_table_の_client_a_に_scope_があり_getClientScope_を呼んだ場合_scope_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a', 'scope' => 'read write')));

        $actual = $storage->getClientScope('client-a');

        $expected = 'read write';
        $this->assertEquals($expected, $actual);
    }

    /** client_table の client-a に scope が無い状態で getClientScope を呼んだ場合、null を返すこと */
    public function testclient_table_の_client_a_に_scope_が無い状態で_getClientScope_を呼んだ場合_null_を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array('client_table-client-a' => array('client_id' => 'client-a')));

        $actual = $storage->getClientScope('client-a');

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** getJti を呼んだ場合、未実装の例外を投げること */
    public function testgetJti_を呼んだ場合_未実装の例外を投げること() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = call_user_func(function () use ($storage) { try { $storage->getJti('client-a', 'subject-a', 'audience-a', 1000, 'jti-a'); return 'no exception'; } catch (\Exception $e) { return array(get_class($e), $e->getMessage()); } });

        $expected = array('Exception', 'getJti() for the Couchbase driver is currently unimplemented.');
        $this->assertEquals($expected, $actual);
    }

    /** setJti を呼んだ場合、未実装の例外を投げること */
    public function testsetJti_を呼んだ場合_未実装の例外を投げること() {
        $storage = new OAuth2_Storage_CouchbaseDBTestable(array());

        $actual = call_user_func(function () use ($storage) { try { $storage->setJti('client-a', 'subject-a', 'audience-a', 1000, 'jti-a'); return 'no exception'; } catch (\Exception $e) { return array(get_class($e), $e->getMessage()); } });

        $expected = array('Exception', 'setJti() for the Couchbase driver is currently unimplemented.');
        $this->assertEquals($expected, $actual);
    }

    /** コンストラクタに servers と bucket だけを持つ配列を渡し username と password を省いた場合、createCouchbase に空文字の username と password を渡し config に既定の表名を入れること */
    public function testコンストラクタに_servers_と_bucket_だけを持つ配列を渡し_username_と_password_を省いた場合_createCouchbase_に空文字の_username_と_password_を渡し_config_に既定の表名を入れること() {
        $connection = array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth');

        $actual = call_user_func(function () use ($connection) { $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb($connection); return array($storage->createArgs, $storage->configForTest()); });

        $expected = array(array(array('127.0.0.1:8091'), '', '', 'oauth', false), array('client_table' => 'oauth_clients', 'access_token_table' => 'oauth_access_tokens', 'refresh_token_table' => 'oauth_refresh_tokens', 'code_table' => 'oauth_authorization_codes', 'user_table' => 'oauth_users', 'jwt_table' => 'oauth_jwt'));
        $this->assertEquals($expected, $actual);
    }

    /** コンストラクタに username と password を含む配列と client_table を上書きする config を渡した場合、createCouchbase に渡した username と password を渡し config の client_table を上書きした配列を保持すること */
    public function testコンストラクタに_username_と_password_を含む配列と_client_table_を上書きする_config_を渡した場合_createCouchbase_に渡した_username_と_password_を渡し_config_の_client_table_を上書きした配列を保持すること() {
        $connection = array('servers' => array('127.0.0.1:8091', '127.0.0.2:8091'), 'username' => 'cb-user', 'password' => 'cb-pass', 'bucket' => 'oauth');
        $config = array('client_table' => 'custom_clients', 'extra_key' => 'extra');

        $actual = call_user_func(function () use ($connection, $config) { $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb($connection, $config); return array($storage->createArgs, $storage->configForTest()); });

        $expected = array(array(array('127.0.0.1:8091', '127.0.0.2:8091'), 'cb-user', 'cb-pass', 'oauth', false), array('client_table' => 'custom_clients', 'access_token_table' => 'oauth_access_tokens', 'refresh_token_table' => 'oauth_refresh_tokens', 'code_table' => 'oauth_authorization_codes', 'user_table' => 'oauth_users', 'jwt_table' => 'oauth_jwt', 'extra_key' => 'extra'));
        $this->assertEquals($expected, $actual);
    }

    /** コンストラクタに servers が配列でない配列を渡した場合、InvalidArgumentException を投げること */
    public function testコンストラクタに_servers_が配列でない配列を渡した場合_InvalidArgumentException_を投げること() {
        $actual = call_user_func(function () { try { new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => 'not-an-array', 'bucket' => 'oauth')); return 'no exception'; } catch (\InvalidArgumentException $e) { return get_class($e); } });

        $expected = 'InvalidArgumentException';
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの db に oauth_clients-client-a の JSON があり getClientDetails を呼んだ場合、JSON を連想配列に復元して返し db の get に表名と id をつないだキーを渡すこと */
    public function testフェイクの_db_に_oauth_clients_client_a_の_JSON_があり_getClientDetails_を呼んだ場合_JSON_を連想配列に復元して返し_db_の_get_に表名と_id_をつないだキーを渡すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth'));
        $storage->fakeDb->store['oauth_clients-client-a'] = json_encode(array('client_id' => 'client-a', 'client_secret' => 'secret-a', 'scope' => 'read', 'type' => 'client_table'));

        $actual = array($storage->getClientDetails('client-a'), $storage->fakeDb->calls);

        $expected = array(array('client_id' => 'client-a', 'client_secret' => 'secret-a', 'scope' => 'read', 'type' => 'client_table'), array(array('get', 'oauth_clients-client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** config で client_table を custom_clients に変えフェイクの db に custom_clients-client-a の JSON がある状態で getClientDetails を呼んだ場合、config の表名を使ったキーで db の get を呼び復元した配列を返すこと */
    public function testconfig_で_client_table_を_custom_clients_に変えフェイクの_db_に_custom_clients_client_a_の_JSON_がある状態で_getClientDetails_を呼んだ場合_config_の表名を使ったキーで_db_の_get_を呼び復元した配列を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth'), array('client_table' => 'custom_clients'));
        $storage->fakeDb->store['custom_clients-client-a'] = json_encode(array('client_id' => 'client-a', 'client_secret' => 'secret-a'));

        $actual = array($storage->getClientDetails('client-a'), $storage->fakeDb->calls);

        $expected = array(array('client_id' => 'client-a', 'client_secret' => 'secret-a'), array(array('get', 'custom_clients-client-a')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの db に oauth_access_tokens-missing-token が無い状態で getAccessToken を呼んだ場合、db の get の戻り値を json_decode した結果に応じた値を返すこと */
    public function testフェイクの_db_に_oauth_access_tokens_missing_token_が無い状態で_getAccessToken_を呼んだ場合_db_の_get_の戻り値を_json_decode_した結果に応じた値を返すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth'));

        $actual = array($storage->getAccessToken('missing-token'), $storage->fakeDb->calls);

        $expected = array(false, array(array('get', 'oauth_access_tokens-missing-token')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの db を持つ対象で setRefreshToken に refresh-a を渡した場合、true を返し db の set に oauth_refresh_tokens-refresh-a と type を加えた JSON を渡すこと */
    public function testフェイクの_db_を持つ対象で_setRefreshToken_に_refresh_a_を渡した場合_true_を返し_db_の_set_に_oauth_refresh_tokens_refresh_a_と_type_を加えた_JSON_を渡すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth'));

        $actual = array($storage->setRefreshToken('refresh-a', 'client-a', 'user-1', 2000, 'read'), $storage->fakeDb->calls);

        $expected = array(true, array(array('set', 'oauth_refresh_tokens-refresh-a', '{"refresh_token":"refresh-a","client_id":"client-a","user_id":"user-1","expires":2000,"scope":"read","type":"refresh_token_table"}')));
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの db に oauth_refresh_tokens-refresh-a がある状態で unsetRefreshToken を呼んだ場合、true を返し db の delete にキーと空文字と 1 を渡すこと */
    public function testフェイクの_db_に_oauth_refresh_tokens_refresh_a_がある状態で_unsetRefreshToken_を呼んだ場合_true_を返し_db_の_delete_にキーと空文字と_1_を渡すこと() {
        $storage = new OAuth2_Storage_CouchbaseDBWithFakeDb(array('servers' => array('127.0.0.1:8091'), 'bucket' => 'oauth'));
        $storage->fakeDb->store['oauth_refresh_tokens-refresh-a'] = json_encode(array('refresh_token' => 'refresh-a'));

        $actual = array($storage->unsetRefreshToken('refresh-a'), $storage->fakeDb->calls);

        $expected = array(true, array(array('delete', 'oauth_refresh_tokens-refresh-a', '', 1)));
        $this->assertEquals($expected, $actual);
    }
}
