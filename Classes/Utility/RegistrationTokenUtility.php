<?php
namespace SimonSchaufi\Autologin\Utility;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use TYPO3\CMS\Core\Crypto\Random;

new class RegistrationTokenUtility {

    public static function createToken(string $subject, int $expireTime): string
    {
        
        // TODO: Improve Finding of Base URL via Site, GeneralUtility or Environment
        $baseUrl = $this->contentObjectRenderer->typoLink_URL([
            'parameter' => '/',
            'forceAbsoluteUrl' => true,
        'iat'
        $payload = [
            // subject: thing we use to find the user. unique hash created by the registration form
            'sub' => $subject 
            // audience: this is the current site so we use the base url
            'aud' => $baseUrl,
            // Issued At
            'iat' => time();
            // Expire time
            'exp' => $expireTime
            // Unique identifier: prevent re use by blocking used tokens
            'jti' => (new Random())->generateRandomHexString(32)
            ];
        ];
        return JWT::encode($payload, self::getKey() )
        
    }


    public static function getKey():Key {
        if(!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])){
            throw new \Exception('encrption key not set', 1728390725);
        }
        return new Key(
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . '-JWT-Registration-Token',
            'HS256'
        );
    }

    public static function decode(string $token):array
    {
        $payload = (array)JWT::decode($token, self::getKey());
        //  TODO: verify JTI against used JTI
        return $payload;
        
    } 

    public static function markAsUsed(array $payload):void
    {
        // use expire time and jti to mark the token as used.

        // implementation idea:
        // a database table containing two columns: expire, jti
        // this way expired tokens cann be cleaned be shure to add addtional leeway to the expire time
        // while the jti allows for selecting used tokens.
    }

}
