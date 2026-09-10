<?php

use App\Models\Device;
use App\Models\User;

/**
 * DB-backed regression cover for the stored PHP object injection in rconfig/rconfig#369.
 *
 * The attack is stored: a low-privileged user submits a serialized POP chain as
 * device_password (POST /api/devices stores it, encrypted), and the next read of the
 * device — the routine GET /api/devices/{id} an admin opens — decrypts and unserialize()s
 * it through EncryptStringCast, instantiating the gadget and reaching system().
 *
 * These tests store an object payload the way the attacker would and then read it back,
 * both directly off the model and through the API, asserting the gadget is never woken.
 * The unit-level proof lives in tests/Unit/Casts/EncryptStringCastTest.php.
 */

/**
 * Canary "gadget" — woken only if the cast instantiates a serialized object. Guarded so it
 * does not clash with the same-named class in the unit test when the whole suite runs.
 */
if (! class_exists('DevicePwnCanary')) {
    class DevicePwnCanary
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
}

beforeEach(function () {
    $this->beginTransaction();
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    DevicePwnCanary::$awoke = false;
});

test('an object payload stored in device_password is not instantiated when the model is read', function () {
    $device = Device::factory()->create([
        'device_password' => serialize(new DevicePwnCanary),
    ]);

    DevicePwnCanary::$awoke = false;

    // Read back from the database, forcing the cast to decrypt + unserialize.
    $reloaded = Device::findOrFail($device->id);
    $password = $reloaded->device_password;

    expect(DevicePwnCanary::$awoke)->toBeFalse('reading device_password instantiated an attacker object');
    expect($password)->not->toBeInstanceOf(DevicePwnCanary::class);
});

test('GET /api/devices/{id} does not instantiate a stored object payload', function () {
    $device = Device::factory()->create([
        'device_password' => serialize(new DevicePwnCanary),
    ]);

    DevicePwnCanary::$awoke = false;

    $response = $this->get('/api/devices/' . $device->id);

    $response->assertStatus(200);
    expect(DevicePwnCanary::$awoke)->toBeFalse('the device read endpoint instantiated an attacker object');
});

test('a normal device password still round-trips through the api', function () {
    $device = Device::factory()->create(['device_password' => 'PlainSecret42']);

    $response = $this->get('/api/devices/' . $device->id);

    $response->assertStatus(200);
    $response->assertJsonFragment(['device_password' => 'PlainSecret42']);
});
