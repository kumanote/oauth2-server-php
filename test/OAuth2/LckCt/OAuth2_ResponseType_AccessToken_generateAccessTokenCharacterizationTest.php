<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/ResponseType/AccessToken.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\ResponseType\\AccessToken');


class OAuth2_ResponseType_AccessToken_generateAccessTokenExposed extends \OAuth2\ResponseType\AccessToken
{
    // 親のコンストラクタはストレージを要求するが、generateAccessToken は使わないので呼ばない
    public function __construct()
    {
        $this->config = array(
            'token_type'             => 'bearer',
            'access_lifetime'        => 3600,
            'refresh_token_lifetime' => 1209600,
        );
    }

    public function exposeGenerateAccessToken($mtRand = null, $now = null)
    {
        return $this->generateAccessToken($mtRand, $now);
    }
}

function OAuth2_ResponseType_AccessToken_generateAccessToken_fixedMtRand()
{
    return 42;
}

// 乱数そのものは固定できないので、分岐が決める長さと文字種だけを観測する
function OAuth2_ResponseType_AccessToken_generateAccessToken_shape($token)
{
    return array(
        'type' => gettype($token),
        'length' => strlen($token),
        'isLowerHex' => preg_match('/^[0-9a-f]{40}$/', $token) === 1,
    );
}

class OAuth2_ResponseType_AccessToken_generateAccessTokenCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** mtRand と now を渡さずに generateAccessToken を呼んだ場合、利用可能な乱数源から 20 バイトを得て 40 桁の 16 進文字列を返すこと */
    public function testmtRand_と_now_を渡さずに_generateAccessToken_を呼んだ場合_利用可能な乱数源から_20_バイトを得て_40_桁の_16_進文字列を返すこと() {
        $target = new OAuth2_ResponseType_AccessToken_generateAccessTokenExposed();

        $actual = OAuth2_ResponseType_AccessToken_generateAccessToken_shape($target->exposeGenerateAccessToken());

        $expected = array('type' => 'string', 'length' => 40, 'isLowerHex' => true);
        $this->assertEquals($expected, $actual);
    }

    /** mtRand に固定値を返す関数、now に固定の時刻を渡して generateAccessToken を呼んだ場合、乱数源が先に成功するので mtRand と now は使われず 40 桁の 16 進文字列を返すこと */
    public function testmtRand_に固定値を返す関数_now_に固定の時刻を渡して_generateAccessToken_を呼んだ場合_乱数源が先に成功するので_mtRand_と_now_は使われず_40_桁の_16_進文字列を返すこと() {
        $target = new OAuth2_ResponseType_AccessToken_generateAccessTokenExposed();

        $actual = OAuth2_ResponseType_AccessToken_generateAccessToken_shape($target->exposeGenerateAccessToken('OAuth2_ResponseType_AccessToken_generateAccessToken_fixedMtRand', 1234567890.5));

        $expected = array('type' => 'string', 'length' => 40, 'isLowerHex' => true);
        $this->assertEquals($expected, $actual);
    }
}
