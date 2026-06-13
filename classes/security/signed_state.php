<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_chemillusion\security;

/**
 * HMAC-signed, time-limited state tokens for account-link / launch handoff.
 *
 * The signing secret lives only in server config and never reaches the
 * browser. Tokens are compact "<payload>.<signature>" strings using URL-safe
 * base64. Verification rejects tampered, expired, or unsigned tokens.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signed_state {

    /** @var int Default token lifetime in seconds. */
    const DEFAULT_TTL = 600;

    /**
     * Sign a payload, returning a compact token.
     *
     * @param array $payload Arbitrary JSON-serialisable data (no secrets).
     * @param int $ttl Lifetime in seconds.
     * @return string
     */
    public static function sign(array $payload, $ttl = self::DEFAULT_TTL) {
        $payload['iat'] = time();
        $payload['exp'] = time() + (int) $ttl;
        $payload['nonce'] = bin2hex(random_bytes(8));

        $body = self::b64url(json_encode($payload));
        $sig = self::b64url(self::hmac($body));
        return $body . '.' . $sig;
    }

    /**
     * Verify a token and return its payload, or null when invalid/expired.
     *
     * @param string $token
     * @return array|null
     */
    public static function verify($token) {
        if (!is_string($token) || strpos($token, '.') === false) {
            return null;
        }
        list($body, $sig) = explode('.', $token, 2);

        $expected = self::b64url(self::hmac($body));
        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $payload = json_decode(self::b64url_decode($body), true);
        if (!is_array($payload) || !isset($payload['exp'])) {
            return null;
        }
        if ((int) $payload['exp'] < time()) {
            return null;
        }
        return $payload;
    }

    /**
     * Compute the keyed HMAC for a body string.
     *
     * @param string $body
     * @return string Raw binary HMAC.
     */
    protected static function hmac($body) {
        $secret = (string) get_config('local_chemillusion', 'signing_secret');
        if ($secret === '') {
            // Fall back to site secret so tokens are still unforgeable.
            $secret = get_site_identifier();
        }
        return hash_hmac('sha256', $body, $secret, true);
    }

    /**
     * URL-safe base64 encode.
     *
     * @param string $data
     * @return string
     */
    protected static function b64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL-safe base64 decode.
     *
     * @param string $data
     * @return string
     */
    protected static function b64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
