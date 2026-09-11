<?php

use App\Casts\EncryptStringCast;
use App\Models\Device;
use Illuminate\Support\Facades\Crypt;

/**
 * Regression cover for the stored PHP object injection reported in rconfig/rconfig#369.
 *
 * EncryptStringCast::get() decrypts a stored secret and then unserialize()s it. Before
 * the fix, that unserialize (and the probing one inside is_serialized()) ran without an
 * allowed_classes allowlist, so an attacker-supplied serialized POP chain stored in any
 * encrypted field (device_password, cred_password, mail_password, ...) was instantiated
 * on the next read, reaching a system() gadget in the bundled framework: authenticated RCE.
 *
 * The fix passes ['allowed_classes' => false] to both unserialize() calls. These tests pin
 * that: an object payload must never be brought to life, while legacy serialized scalars
 * (the reason the cast unserializes at all, bug #100) must still decode.
 */

/**
 * Canary "gadget". If EncryptStringCast ever instantiates a serialized object, __wakeup()
 * (or __destruct() on a real instance) flips the static flag and the security test fails.
 */
class EncryptStringCastCanary
{
    public static bool $awoke = false;

    public function __wakeup(): void
    {
        self::$awoke = true;
    }

    public function __destruct()
    {
        self::$awoke = true;
    }
}

beforeEach(function () {
    EncryptStringCastCanary::$awoke = false;
});

test('a serialized object payload is never instantiated on read', function () {
    $ciphertext = Crypt::encryptString(serialize(new EncryptStringCastCanary));
    EncryptStringCastCanary::$awoke = false;

    $result = (new EncryptStringCast)->get(new Device, 'device_password', $ciphertext, []);

    expect(EncryptStringCastCanary::$awoke)->toBeFalse('EncryptStringCast instantiated an attacker object');
    expect($result)->not->toBeInstanceOf(EncryptStringCastCanary::class);
});

test('a normal password round-trips through set and get', function () {
    $cast = new EncryptStringCast;
    $stored = $cast->set(new Device, 'device_password', 'SuperSecret99', []);

    $value = $cast->get(new Device, 'device_password', $stored['device_password'], []);

    expect($value)->toBe('SuperSecret99');
});

test('a legacy serialized scalar still decodes', function () {
    $ciphertext = Crypt::encryptString(serialize('legacyPassword'));

    $value = (new EncryptStringCast)->get(new Device, 'device_password', $ciphertext, []);

    expect($value)->toBe('legacyPassword');
});

test('null decodes to null and whitespace keeps its pre-existing blank quirk', function () {
    $cast = new EncryptStringCast;

    expect($cast->get(new Device, 'device_password', null, []))->toBeNull();

    // A blank/whitespace secret is nulled, then probed by is_serialized() and decodes to
    // false. This quirk predates #369 and is unchanged by the allowed_classes fix; the
    // masking layer tolerates it (see DeviceCredentialsApiV1MaskingTest). Pin it so the
    // security fix is not blamed for it and it does not silently drift.
    $spaces = Crypt::encryptString('   ');
    expect($cast->get(new Device, 'device_password', $spaces, []))->toBeFalse();
});

test('set encrypts non-null and passes null through', function () {
    $cast = new EncryptStringCast;

    expect($cast->set(new Device, 'device_password', null, []))->toBe(['device_password' => null]);
    expect(Crypt::decryptString($cast->set(new Device, 'device_password', 'x', [])['device_password']))->toBe('x');
});
