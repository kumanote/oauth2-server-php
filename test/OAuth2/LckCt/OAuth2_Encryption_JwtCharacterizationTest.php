<?php
// Characterization Test （自動生成）。
// lck-target: src/OAuth2/Encryption/Jwt.php
//
// **期待値は観測値である。** 骨格 （入力 ・ 呼び出し ・ フェイク） は LLM が書き、
// 期待値は骨格を 1 回実行して観測した値を機械的に焼き込んだもので、「あるべき仕様」 の
// 判断は入っていない。バグに見える挙動もそのまま固定されている。
class_exists('OAuth2\\Encryption\\Jwt');


class OAuth2_Encryption_JwtRecordingRsaJwt extends \OAuth2\Encryption\Jwt
{
    public $rsaCalls = array();

    protected function generateRSASignature($input, $key, $algo)
    {
        $this->rsaCalls[] = array($input, $key, $algo);

        return 'fixed-rsa-signature';
    }
}

class OAuth2_Encryption_JwtProbe
{
    public static function capture($callable)
    {
        try {
            return array('value' => call_user_func($callable));
        } catch (\Exception $e) {
            return array('exception' => get_class($e), 'message' => $e->getMessage());
        }
    }
}

class OAuth2_Encryption_JwtRsaKeyPair
{
    private static $pair = null;
    private static $otherPair = null;

    public static function get()
    {
        if (self::$pair === null) {
            self::$pair = self::generate();
        }

        return self::$pair;
    }

    public static function getOther()
    {
        if (self::$otherPair === null) {
            self::$otherPair = self::generate();
        }

        return self::$otherPair;
    }

    private static function generate()
    {
        $resource = openssl_pkey_new(array(
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ));
        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);

        return array('private' => $privateKey, 'public' => $details['key']);
    }
}

class OAuth2_Encryption_JwtCharacterizationTest extends PHPUnit_Framework_TestCase {
    /** encode に配列の payload と文字列の key を渡し、algo を既定の HS256 にした場合、3 セグメントの JWT 文字列を返すこと */
    public function testencode_に配列の_payload_と文字列の_key_を渡し_algo_を既定の_HS256_にした場合_3_セグメントの_JWT_文字列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = $jwt->encode($payload, 'secret-key');

        $expected = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.vdHX7sQLw_U18swp7rMVOcJW_a-1I4Jz40ncOAb7TjU';
        $this->assertEquals($expected, $actual);
    }

    /** encode に algo = 'HS384' を渡した場合、HS384 の HMAC 署名を付けた JWT 文字列を返すこと */
    public function testencode_に_algo_HS384_を渡した場合_HS384_の_HMAC_署名を付けた_JWT_文字列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = $jwt->encode($payload, 'secret-key', 'HS384');

        $expected = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.NS1TGnz3mfx5Hiwk7lURPB0nb0qGv9151yTYVe0236tCHHbFEnE8p8a8MFK8N7TL';
        $this->assertEquals($expected, $actual);
    }

    /** encode に algo = 'HS512' を渡した場合、HS512 の HMAC 署名を付けた JWT 文字列を返すこと */
    public function testencode_に_algo_HS512_を渡した場合_HS512_の_HMAC_署名を付けた_JWT_文字列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = $jwt->encode($payload, 'secret-key', 'HS512');

        $expected = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.pIkAOi9-vUlNtmaxS_DXi-HO0I7pxg89Pwp8qCnzCJvh-vw75f1z4sjyBDD0mQLWpxMNYVy-NBKCCaDMbd1mpA';
        $this->assertEquals($expected, $actual);
    }

    /** generateRSASignature を固定値を返すフェイクに差し替え、encode に algo = 'RS256' を渡した場合、フェイクの署名を使った JWT 文字列と、generateRSASignature に渡された引数の記録を返すこと */
    public function testgenerateRSASignature_を固定値を返すフェイクに差し替え_encode_に_algo_RS256_を渡した場合_フェイクの署名を使った_JWT_文字列と_generateRSASignature_に渡された引数の記録を返すこと() {
        $jwt = new OAuth2_Encryption_JwtRecordingRsaJwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = array($jwt->encode($payload, 'rsa-private-key', 'RS256'), $jwt->rsaCalls);

        $expected = array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.Zml4ZWQtcnNhLXNpZ25hdHVyZQ', array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9', 'rsa-private-key', 7)));
        $this->assertEquals($expected, $actual);
    }

    /** generateRSASignature を固定値を返すフェイクに差し替え、encode に algo = 'RS384' を渡した場合、フェイクの署名を使った JWT 文字列と、generateRSASignature に渡された引数の記録を返すこと */
    public function testgenerateRSASignature_を固定値を返すフェイクに差し替え_encode_に_algo_RS384_を渡した場合_フェイクの署名を使った_JWT_文字列と_generateRSASignature_に渡された引数の記録を返すこと() {
        $jwt = new OAuth2_Encryption_JwtRecordingRsaJwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = array($jwt->encode($payload, 'rsa-private-key', 'RS384'), $jwt->rsaCalls);

        $expected = array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzM4NCJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.Zml4ZWQtcnNhLXNpZ25hdHVyZQ', array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzM4NCJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9', 'rsa-private-key', 8)));
        $this->assertEquals($expected, $actual);
    }

    /** generateRSASignature を固定値を返すフェイクに差し替え、encode に algo = 'RS512' を渡した場合、フェイクの署名を使った JWT 文字列と、generateRSASignature に渡された引数の記録を返すこと */
    public function testgenerateRSASignature_を固定値を返すフェイクに差し替え_encode_に_algo_RS512_を渡した場合_フェイクの署名を使った_JWT_文字列と_generateRSASignature_に渡された引数の記録を返すこと() {
        $jwt = new OAuth2_Encryption_JwtRecordingRsaJwt();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = array($jwt->encode($payload, 'rsa-private-key', 'RS512'), $jwt->rsaCalls);

        $expected = array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9.Zml4ZWQtcnNhLXNpZ25hdHVyZQ', array(array('eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzUxMiJ9.eyJzdWIiOiJ1c2VyLTEiLCJleHAiOjE3MDAwMDAwMDB9', 'rsa-private-key', 9)));
        $this->assertEquals($expected, $actual);
    }

    /** encode に sign が知らない algo = 'none' を渡した場合、sign が Exception を投げること */
    public function testencode_に_sign_が知らない_algo_none_を渡した場合_sign_が_Exception_を投げること() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $payload = array('sub' => 'user-1');

        $actual = OAuth2_Encryption_JwtProbe::capture(function () use ($jwt, $payload) { return $jwt->encode($payload, 'secret-key', 'none'); });

        $expected = array('exception' => 'Exception', 'message' => 'Unsupported or invalid signing algorithm.');
        $this->assertEquals($expected, $actual);
    }

    /** encode に algo = 'RS256' と秘密鍵として解釈できない文字列の key を渡した場合、openssl_sign が失敗し generateRSASignature が Exception を投げること */
    public function testencode_に_algo_RS256_と秘密鍵として解釈できない文字列の_key_を渡した場合_openssl_sign_が失敗し_generateRSASignature_が_Exception_を投げること() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $payload = array('sub' => 'user-1');

        $actual = OAuth2_Encryption_JwtProbe::capture(function () use ($jwt, $payload) { return @$jwt->encode($payload, 'not-a-private-key', 'RS256'); });

        $expected = array('exception' => 'Exception', 'message' => 'Unable to sign data.');
        $this->assertEquals($expected, $actual);
    }

    /** decode に '.' を含まない文字列を渡した場合、false を返すこと */
    public function testdecode_に_を含まない文字列を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();

        $actual = $jwt->decode('no-dots-here', 'secret-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に先頭が '.' で始まる 3 セグメントの文字列を渡した場合、strpos が 0 を返すため false を返すこと */
    public function testdecode_に先頭が_で始まる_3_セグメントの文字列を渡した場合_strpos_が_0_を返すため_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();

        $actual = $jwt->decode('.eyJhbGciOiJIUzI1NiJ9.c2ln', 'secret-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に '.' 区切りが 2 セグメントしかない文字列を渡した場合、false を返すこと */
    public function testdecode_に_区切りが_2_セグメントしかない文字列を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();

        $actual = $jwt->decode('aGVhZGVy.cGF5bG9hZA', 'secret-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に header セグメントが JSON として復号できない 3 セグメントの文字列を渡した場合、false を返すこと */
    public function testdecode_に_header_セグメントが_JSON_として復号できない_3_セグメントの文字列を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = 'xx.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('sig');

        $actual = $jwt->decode($token, 'secret-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に header は正しいが payload セグメントが JSON として復号できない文字列を渡した場合、false を返すこと */
    public function testdecode_に_header_は正しいが_payload_セグメントが_JSON_として復号できない文字列を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256'))) . '.xx.' . $jwt->urlSafeB64Encode('sig');

        $actual = $jwt->decode($token, 'secret-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に allowedAlgorithms = false を渡し、署名セグメントが不正な JWT を渡した場合、署名検証を行わず payload の配列を返すこと */
    public function testdecode_に_allowedAlgorithms_false_を渡し_署名セグメントが不正な_JWT_を渡した場合_署名検証を行わず_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1', 'exp' => 1700000000))) . '.' . $jwt->urlSafeB64Encode('bogus-signature');

        $actual = $jwt->decode($token, 'secret-key', false);

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** decode に allowedAlgorithms = array() （空配列） を渡し、署名セグメントが不正な JWT を渡した場合、(bool) 変換で false になり署名検証を行わず payload の配列を返すこと */
    public function testdecode_に_allowedAlgorithms_array_空配列_を渡し_署名セグメントが不正な_JWT_を渡した場合_bool_変換で_false_になり署名検証を行わず_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1', 'exp' => 1700000000))) . '.' . $jwt->urlSafeB64Encode('bogus-signature');

        $actual = $jwt->decode($token, 'secret-key', array());

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** decode に allowedAlgorithms = true を渡し、header に alg が無い JWT を渡した場合、false を返すこと */
    public function testdecode_に_allowedAlgorithms_true_を渡し_header_に_alg_が無い_JWT_を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('sig');

        $actual = $jwt->decode($token, 'secret-key', true);

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に allowedAlgorithms = array('HS256') を渡し、header の alg が HS512 の JWT を渡した場合、false を返すこと */
    public function testdecode_に_allowedAlgorithms_array_HS256_を渡し_header_の_alg_が_HS512_の_JWT_を渡した場合_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode(array('sub' => 'user-1', 'exp' => 1700000000), 'secret-key', 'HS512');

        $actual = $jwt->decode($token, 'secret-key', array('HS256'));

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に HS256 で署名した JWT と署名時と異なる key を渡した場合、verifySignature が不一致となり false を返すこと */
    public function testdecode_に_HS256_で署名した_JWT_と署名時と異なる_key_を渡した場合_verifySignature_が不一致となり_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode(array('sub' => 'user-1', 'exp' => 1700000000), 'secret-key');

        $actual = $jwt->decode($token, 'other-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に HS256 で署名した JWT と署名時と同じ key を渡した場合、payload の配列を返すこと */
    public function testdecode_に_HS256_で署名した_JWT_と署名時と同じ_key_を渡した場合_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode(array('sub' => 'user-1', 'exp' => 1700000000), 'secret-key');

        $actual = $jwt->decode($token, 'secret-key');

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** decode に HS384 で署名した JWT と同じ key と allowedAlgorithms = array('HS384') を渡した場合、payload の配列を返すこと */
    public function testdecode_に_HS384_で署名した_JWT_と同じ_key_と_allowedAlgorithms_array_HS384_を渡した場合_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode(array('sub' => 'user-1', 'exp' => 1700000000), 'secret-key', 'HS384');

        $actual = $jwt->decode($token, 'secret-key', array('HS384'));

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** decode に HS512 で署名した JWT と同じ key と allowedAlgorithms = array('HS256', 'HS512') を渡した場合、payload の配列を返すこと */
    public function testdecode_に_HS512_で署名した_JWT_と同じ_key_と_allowedAlgorithms_array_HS256_HS512_を渡した場合_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->encode(array('sub' => 'user-1', 'exp' => 1700000000), 'secret-key', 'HS512');

        $actual = $jwt->decode($token, 'secret-key', array('HS256', 'HS512'));

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** decode に header の alg が RS256 で署名が不正な JWT と公開鍵として解釈できない key を渡した場合、openssl_verify が 1 を返さず false を返すこと */
    public function testdecode_に_header_の_alg_が_RS256_で署名が不正な_JWT_と公開鍵として解釈できない_key_を渡した場合_openssl_verify_が_1_を返さず_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'RS256'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('bogus-signature');

        $actual = @$jwt->decode($token, 'not-a-public-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に header の alg が RS384 で署名が不正な JWT と公開鍵として解釈できない key を渡した場合、openssl_verify が 1 を返さず false を返すこと */
    public function testdecode_に_header_の_alg_が_RS384_で署名が不正な_JWT_と公開鍵として解釈できない_key_を渡した場合_openssl_verify_が_1_を返さず_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'RS384'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('bogus-signature');

        $actual = $jwt->decode($token, 'not-a-public-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に header の alg が RS512 で署名が不正な JWT と公開鍵として解釈できない key を渡した場合、openssl_verify が 1 を返さず false を返すこと */
    public function testdecode_に_header_の_alg_が_RS512_で署名が不正な_JWT_と公開鍵として解釈できない_key_を渡した場合_openssl_verify_が_1_を返さず_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'RS512'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('bogus-signature');

        $actual = $jwt->decode($token, 'not-a-public-key');

        $expected = false;
        $this->assertEquals($expected, $actual);
    }

    /** decode に header の alg が verifySignature の知らない 'none' の JWT を allowedAlgorithms = true で渡した場合、verifySignature が InvalidArgumentException を投げること */
    public function testdecode_に_header_の_alg_が_verifySignature_の知らない_none_の_JWT_を_allowedAlgorithms_true_で渡した場合_verifySignature_が_InvalidArgumentException_を投げること() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $token = $jwt->urlSafeB64Encode(json_encode(array('typ' => 'JWT', 'alg' => 'none'))) . '.' . $jwt->urlSafeB64Encode(json_encode(array('sub' => 'user-1'))) . '.' . $jwt->urlSafeB64Encode('sig');

        $actual = OAuth2_Encryption_JwtProbe::capture(function () use ($jwt, $token) { return $jwt->decode($token, 'secret-key', true); });

        $expected = array('exception' => 'InvalidArgumentException', 'message' => 'Unsupported or invalid signing algorithm.');
        $this->assertEquals($expected, $actual);
    }

    /** 実際の RSA 秘密鍵で encode に algo = 'RS256' を渡し、対になる公開鍵で decode した場合、openssl_sign が成功して generateRSASignature が署名を返し、openssl_verify が 1 を返して payload の配列を返すこと */
    public function test実際の_RSA_秘密鍵で_encode_に_algo_RS256_を渡し_対になる公開鍵で_decode_した場合_openssl_sign_が成功して_generateRSASignature_が署名を返し_openssl_verify_が_1_を返して_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $keys = OAuth2_Encryption_JwtRsaKeyPair::get();
        $payload = array('sub' => 'user-1', 'exp' => 1700000000);

        $actual = $jwt->decode($jwt->encode($payload, $keys['private'], 'RS256'), $keys['public'], array('RS256'));

        $expected = array('sub' => 'user-1', 'exp' => 1700000000);
        $this->assertEquals($expected, $actual);
    }

    /** 実際の RSA 秘密鍵で encode に algo = 'RS384' を渡し、対になる公開鍵で decode した場合、generateRSASignature が署名を返し、RS384 の openssl_verify が 1 を返して payload の配列を返すこと */
    public function test実際の_RSA_秘密鍵で_encode_に_algo_RS384_を渡し_対になる公開鍵で_decode_した場合_generateRSASignature_が署名を返し_RS384_の_openssl_verify_が_1_を返して_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $keys = OAuth2_Encryption_JwtRsaKeyPair::get();
        $payload = array('sub' => 'user-2', 'scope' => 'read');

        $actual = $jwt->decode($jwt->encode($payload, $keys['private'], 'RS384'), $keys['public'], array('RS384'));

        $expected = array('sub' => 'user-2', 'scope' => 'read');
        $this->assertEquals($expected, $actual);
    }

    /** 実際の RSA 秘密鍵で encode に algo = 'RS512' を渡し、対になる公開鍵で decode した場合、generateRSASignature が署名を返し、RS512 の openssl_verify が 1 を返して payload の配列を返すこと */
    public function test実際の_RSA_秘密鍵で_encode_に_algo_RS512_を渡し_対になる公開鍵で_decode_した場合_generateRSASignature_が署名を返し_RS512_の_openssl_verify_が_1_を返して_payload_の配列を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $keys = OAuth2_Encryption_JwtRsaKeyPair::get();
        $payload = array('sub' => 'user-3', 'iat' => 1600000000);

        $actual = $jwt->decode($jwt->encode($payload, $keys['private'], 'RS512'), $keys['public'], array('RS512'));

        $expected = array('sub' => 'user-3', 'iat' => 1600000000);
        $this->assertEquals($expected, $actual);
    }

    /** 実際の RSA 秘密鍵で encode に algo = 'RS256' を渡し、別の鍵対の公開鍵で decode した場合、generateRSASignature は署名を返すが openssl_verify が 0 を返し false を返すこと */
    public function test実際の_RSA_秘密鍵で_encode_に_algo_RS256_を渡し_別の鍵対の公開鍵で_decode_した場合_generateRSASignature_は署名を返すが_openssl_verify_が_0_を返し_false_を返すこと() {
        $jwt = new \OAuth2\Encryption\Jwt();
        $keys = OAuth2_Encryption_JwtRsaKeyPair::get();
        $otherKeys = OAuth2_Encryption_JwtRsaKeyPair::getOther();
        $payload = array('sub' => 'user-4');

        $actual = $jwt->decode($jwt->encode($payload, $keys['private'], 'RS256'), $otherKeys['public'], array('RS256'));

        $expected = false;
        $this->assertEquals($expected, $actual);
    }
}
