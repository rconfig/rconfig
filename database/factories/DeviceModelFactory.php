<?php

namespace Database\Factories;

use App\Models\DeviceModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceModel>
 */
class DeviceModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DeviceModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = [
            'iPhone',
            'Samsung Galaxy',
            'Google Pixel',
            'OnePlus',
            'Huawei P',
            'iPad',
            'MacBook',
            'Surface Pro',
            'ThinkPad',
            'Dell XPS',
            'Router',
            'Switch',
            'Firewall',
            'Access Point',
            'Modem',
            'Printer',
            'Scanner',
            'Monitor',
            'Projector',
            'Webcam',
        ];

        $suffixes = [
            '14',
            '15',
            '16',
            'Pro',
            'Max',
            'Plus',
            'Mini',
            'Air',
            'S23',
            'S24',
            'Ultra',
            '7',
            '8',
            '9',
            '10',
            '11',
            '2023',
            '2024',
            '2025',
            'Gen 4',
            'Gen 5',
        ];

        $name = $this->faker->randomElement($deviceTypes) . ' ' . $this->faker->randomElement($suffixes);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.7)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a device model with a specific name
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    /**
     * Create a device model with a description
     */
    public function withDescription(?string $description = null): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description ?? $this->faker->paragraph(),
        ]);
    }

    /**
     * Create a device model without a description
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Create mobile device models
     */
    public function mobile(): static
    {
        $mobileDevices = [
            'iPhone 14',
            'iPhone 15',
            'iPhone 15 Pro',
            'iPhone 15 Pro Max',
            'Samsung Galaxy S23',
            'Samsung Galaxy S24',
            'Samsung Galaxy S24 Ultra',
            'Google Pixel 7',
            'Google Pixel 8',
            'Google Pixel 8 Pro',
            'OnePlus 11',
            'OnePlus 12',
            'Huawei P60',
            'Xiaomi 13',
        ];

        $name = $this->faker->randomElement($mobileDevices);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Mobile device: ' . $name,
        ]);
    }

    /**
     * Create laptop device models
     */
    public function laptop(): static
    {
        $laptopDevices = [
            'MacBook Air M2',
            'MacBook Pro 14"',
            'MacBook Pro 16"',
            'Dell XPS 13',
            'Dell XPS 15',
            'ThinkPad X1 Carbon',
            'Surface Laptop 5',
            'HP Spectre x360',
            'ASUS ZenBook',
        ];

        $name = $this->faker->randomElement($laptopDevices);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Laptop computer: ' . $name,
        ]);
    }

    /**
     * Create network device models
     */
    public function network(): static
    {
        $networkDevices = [
            'Cisco Catalyst 9300',
            'Cisco ASR 1000',
            'Juniper EX4300',
            'Aruba 2930F',
            'Fortinet FortiGate 60F',
            'SonicWall TZ470',
            'Ubiquiti UniFi AP',
            'Netgear Nighthawk',
            'TP-Link Archer',
        ];

        $name = $this->faker->randomElement($networkDevices);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Network device: ' . $name,
        ]);
    }

    /**
     * Create printer device models
     */
    public function printer(): static
    {
        $printerDevices = [
            'HP LaserJet Pro 400',
            'Canon PIXMA TR8620',
            'Epson EcoTank ET-2720',
            'Brother HL-L2350DW',
            'Xerox WorkCentre 6515',
            'Ricoh SP 330DN',
        ];

        $name = $this->faker->randomElement($printerDevices);

        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Printer: ' . $name,
        ]);
    }

    /**
     * Create a sequence of popular device models
     */
    public function popular(): static
    {
        return $this->sequence(
            ['name' => 'iPhone 15', 'slug' => 'iphone-15'],
            ['name' => 'Samsung Galaxy S24', 'slug' => 'samsung-galaxy-s24'],
            ['name' => 'MacBook Pro M3', 'slug' => 'macbook-pro-m3'],
            ['name' => 'Dell XPS 13', 'slug' => 'dell-xps-13'],
            ['name' => 'iPad Pro', 'slug' => 'ipad-pro'],
        );
    }
}
