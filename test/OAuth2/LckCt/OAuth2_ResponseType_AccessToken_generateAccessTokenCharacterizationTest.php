<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/ResponseType/AccessToken.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\ResponseType\\AccessToken');


class OAuth2_ResponseType_AccessToken_generateAccessTokenTestSubject extends \OAuth2\ResponseType\AccessToken
{
    public function __construct()
    {
        $this->tokenStorage = null;
        $this->refreshStorage = null;
        $this->config = array(
            'token_type' => 'bearer',
            'access_lifetime' => 3600,
            'refresh_token_lifetime' => 1209600,
        );
    }

    public function exposeGenerateAccessToken($randomInt = null, $now = null)
    {
        return $this->generateAccessToken($randomInt, $now);
    }
}

class OAuth2_ResponseType_AccessToken_generateAccessTokenFakeCallable
{
    public $calls = 0;
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        $this->calls++;
        return $this->value;
    }
}

function OAuth2_ResponseType_AccessToken_generateAccessTokenShape($token)
{
    return array(
        'type' => gettype($token),
        'length' => strlen((string) $token),
        'lowerHex' => preg_match('/^[0-9a-f]+$/', (string) $token) === 1,
    );
}

class OAuth2_ResponseType_AccessToken_generateAccessTokenCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** generateAccessToken を引数なしで呼んだ場合、最初に使える乱数源から作ったトークンの長さと16進かどうかを返すこと */
    public function testgenerateAccessToken_を引数なしで呼んだ場合_最初に使える乱数源から作ったトークンの長さと16進かどうかを返すこと() {
        $subject = new OAuth2_ResponseType_AccessToken_generateAccessTokenTestSubject();

        $actual = OAuth2_ResponseType_AccessToken_generateAccessTokenShape($subject->exposeGenerateAccessToken());

        $expected = array('type' => 'string', 'length' => 40, 'lowerHex' => true);
        $this->assertEquals($expected, $actual);
    }

    /** generateAccessToken の randomInt と now に固定値を返すフェイクを渡した場合、random_bytes などの乱数源が使える間はフェイクを呼ばずにトークンを作り、その長さと16進かどうかとフェイクの呼び出し回数を返すこと */
    public function testgenerateAccessToken_の_randomInt_と_now_に固定値を返すフェイクを渡した場合_random_bytes_などの乱数源が使える間はフェイクを呼ばずにトークンを作り_その長さと16進かどうかとフェイクの呼び出し回数を返すこと() {
        $subject = new OAuth2_ResponseType_AccessToken_generateAccessTokenTestSubject();
        $randomInt = new OAuth2_ResponseType_AccessToken_generateAccessTokenFakeCallable(12345);
        $now = new OAuth2_ResponseType_AccessToken_generateAccessTokenFakeCallable(1500000000.5);

        $actual = array(OAuth2_ResponseType_AccessToken_generateAccessTokenShape($subject->exposeGenerateAccessToken($randomInt, $now)), $randomInt->calls, $now->calls);

        $expected = array(array('type' => 'string', 'length' => 40, 'lowerHex' => true), 0, 0);
        $this->assertEquals($expected, $actual);
    }
}
