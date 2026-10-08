<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/Request.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\Request');


class OAuth2_Request_createFromGlobalsFakeGlobals extends \OAuth2\RequestGlobals
{
    public $getData = array();
    public $postData = array();
    public $cookieData = array();
    public $filesData = array();
    public $serverData = array();

    public function get()
    {
        return $this->getData;
    }

    public function post()
    {
        return $this->postData;
    }

    public function cookie()
    {
        return $this->cookieData;
    }

    public function files()
    {
        return $this->filesData;
    }

    public function server()
    {
        return $this->serverData;
    }
}

class OAuth2_Request_createFromGlobalsState
{
    // setGlobals(null) の経路で $_GET を書き換えるので、teardown で元へ戻すための退避先
    public static $savedGet = null;
}

function OAuth2_Request_createFromGlobals_makeGlobals($contentType, $method, $post)
{
    $globals = new OAuth2_Request_createFromGlobalsFakeGlobals();
    $globals->postData = $post;
    $globals->serverData = array('CONTENT_TYPE' => $contentType, 'REQUEST_METHOD' => $method);
    return $globals;
}

function OAuth2_Request_createFromGlobals_snapshot(\OAuth2\Request $request)
{
    return array(
        'class' => get_class($request),
        'query' => $request->query,
        'request' => $request->request,
        'attributes' => $request->attributes,
        'cookies' => $request->cookies,
        'files' => $request->files,
        'server' => $request->server,
        'headers' => $request->headers,
    );
}

class OAuth2_Request_createFromGlobalsCharacterizationTest extends PHPUnit_Framework_TestCase {
    /**
     * 各テストの後で、テストが差し替えた状態を本番の既定へ戻す。
     *
     * @after
     */
    #[\PHPUnit\Framework\Attributes\After]
    public function lckRestoreState() {
        \OAuth2\Request::setGlobals(null);
        if (\OAuth2_Request_createFromGlobalsState::$savedGet !== null) {
            $_GET = \OAuth2_Request_createFromGlobalsState::$savedGet;
            \OAuth2_Request_createFromGlobalsState::$savedGet = null;
        }
    }

    /** setGlobals(null) のまま $_GET に client_id と scope を入れた場合、既定の RequestGlobals が $_GET を読んで query に入れること */
    public function testsetGlobals_null_のまま__GET_に_client_id_と_scope_を入れた場合_既定の_RequestGlobals_が__GET_を読んで_query_に入れること() {
        \OAuth2\Request::setGlobals(null);
        \OAuth2_Request_createFromGlobalsState::$savedGet = $_GET;
        $_GET = array('client_id' => 'ct-client', 'scope' => 'read');

        $actual = \OAuth2\Request::createFromGlobals()->query;

        $expected = array('client_id' => 'ct-client', 'scope' => 'read');
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を application/x-www-form-urlencoded、REQUEST_METHOD を小文字の put で返す場合、post の値を捨てて php://input を parse_str した結果を request に入れること */
    public function testフェイクの_server_が_CONTENT_TYPE_を_application_x_www_form_urlencoded_REQUEST_METHOD_を小文字の_put_で返す場合_post_の値を捨てて_php_input_を_parse_str_した結果を_request_に入れること() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('application/x-www-form-urlencoded; charset=UTF-8', 'put', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array();
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を application/x-www-form-urlencoded、REQUEST_METHOD を DELETE で返す場合、post の値を捨てて php://input を parse_str した結果を request に入れること */
    public function testフェイクの_server_が_CONTENT_TYPE_を_application_x_www_form_urlencoded_REQUEST_METHOD_を_DELETE_で返す場合_post_の値を捨てて_php_input_を_parse_str_した結果を_request_に入れること() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('application/x-www-form-urlencoded', 'DELETE', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array();
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を application/x-www-form-urlencoded、REQUEST_METHOD を POST で返す場合、post の値をそのまま request に保つこと */
    public function testフェイクの_server_が_CONTENT_TYPE_を_application_x_www_form_urlencoded_REQUEST_METHOD_を_POST_で返す場合_post_の値をそのまま_request_に保つこと() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('application/x-www-form-urlencoded', 'POST', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array('from_post' => '1');
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を application/json、REQUEST_METHOD を小文字の post で返す場合、空の php://input を json_decode した結果を request に入れること */
    public function testフェイクの_server_が_CONTENT_TYPE_を_application_json_REQUEST_METHOD_を小文字の_post_で返す場合_空の_php_input_を_json_decode_した結果を_request_に入れること() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('application/json; charset=UTF-8', 'post', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = null;
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を application/json、REQUEST_METHOD を GET で返す場合、post の値をそのまま request に保つこと */
    public function testフェイクの_server_が_CONTENT_TYPE_を_application_json_REQUEST_METHOD_を_GET_で返す場合_post_の値をそのまま_request_に保つこと() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('application/json', 'GET', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array('from_post' => '1');
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server が CONTENT_TYPE を text/plain、REQUEST_METHOD を PUT で返す場合、post の値をそのまま request に保つこと */
    public function testフェイクの_server_が_CONTENT_TYPE_を_text_plain_REQUEST_METHOD_を_PUT_で返す場合_post_の値をそのまま_request_に保つこと() {
        \OAuth2\Request::setGlobals(\OAuth2_Request_createFromGlobals_makeGlobals('text/plain', 'PUT', array('from_post' => '1')));

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array('from_post' => '1');
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの server に CONTENT_TYPE と REQUEST_METHOD が無い場合、既定値の空文字と GET で判定し、post の値をそのまま request に保つこと */
    public function testフェイクの_server_に_CONTENT_TYPE_と_REQUEST_METHOD_が無い場合_既定値の空文字と_GET_で判定し_post_の値をそのまま_request_に保つこと() {
        $globals = new \OAuth2_Request_createFromGlobalsFakeGlobals();
        $globals->postData = array('from_post' => '1');
        $globals->serverData = array();
        \OAuth2\Request::setGlobals($globals);

        $actual = \OAuth2\Request::createFromGlobals()->request;

        $expected = array('from_post' => '1');
        $this->assertEquals($expected, $actual);
    }

    /** フェイクの get ・ post ・ cookie ・ files ・ server にそれぞれ値を入れた場合、各フェイクの値が query ・ request ・ cookies ・ files ・ server ・ headers に渡ること */
    public function testフェイクの_get_post_cookie_files_server_にそれぞれ値を入れた場合_各フェイクの値が_query_request_cookies_files_server_headers_に渡ること() {
        $globals = new \OAuth2_Request_createFromGlobalsFakeGlobals();
        $globals->getData = array('q' => 'query-value');
        $globals->postData = array('p' => 'post-value');
        $globals->cookieData = array('session' => 'cookie-value');
        $globals->filesData = array('upload' => array('name' => 'file.txt'));
        $globals->serverData = array('REQUEST_METHOD' => 'GET', 'HTTP_X_CUSTOM' => 'custom-header');
        \OAuth2\Request::setGlobals($globals);

        $actual = \OAuth2_Request_createFromGlobals_snapshot(\OAuth2\Request::createFromGlobals());

        $expected = array('class' => 'OAuth2\\Request', 'query' => array('q' => 'query-value'), 'request' => array('p' => 'post-value'), 'attributes' => array(), 'cookies' => array('session' => 'cookie-value'), 'files' => array('upload' => array('name' => 'file.txt')), 'server' => array('REQUEST_METHOD' => 'GET', 'HTTP_X_CUSTOM' => 'custom-header'), 'headers' => array('X_CUSTOM' => 'custom-header'));
        $this->assertEquals($expected, $actual);
    }
}
