<?php

namespace Tests\Fasttests\ControllersTests\Api;

use App\Models\Config;
use App\Models\User;
use Tests\TestCase;

class ConfigSearchControllerTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        /** @var mixed $this->user */
        $this->actingAs($this->user, 'api');
    }

    public function test_a_search_requires_a_category()
    {
        $response = $this->json('post', '/api/configs/search', ['category' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('category', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_a_search_requires_a_search_string()
    {
        $response = $this->json('post', '/api/configs/search', ['search_string' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('search_string', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_a_search_requires_a_line_count()
    {
        $response = $this->json('post', '/api/configs/search', ['line_count' => null]);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('line_count', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_search_fails_on_incorrect_category_value()
    {
        $response = $this->json('post', '/api/configs/search', ['category' => 'sss', 'search_string' => 'null', 'line_count' => '0']);

        $response->assertJson(['errors' => true]);
        $this->assertArrayHasKey('category', $response['errors']);
        $response->assertStatus(422);
    }

    public function test_search_returns_valid_result_globalsearch()
    {
        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['category' => 1, 'search_string' => 'snmp', 'line_count' => '0', 'latestOnly' => false]);

        $this->assertStringContainsString(' snmp-server host 1.1.1.1 TESTCOMMUNITY10', $response->getContent());
    }

    public function test_search_returns_valid_result_latestOnly()
    {
        Config::truncate();
        $this->fakeConfigInserts();

        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['category' => 1, 'search_string' => 'snmp', 'line_count' => '5', 'latestOnly' => true]);

        $this->assertStringContainsString('ip ssh rsa keypair-name sshkeys', $response->getContent());
        $this->assertStringContainsString('snmp-server host 1.1.1.2 TESTCOMMUNITY10', $response->getContent());
        $this->assertStringContainsString('control-plane', $response->getContent());
        Config::truncate();
    }

    public function test_can_search_and_return_single_line_result_correctly()
    {
        Config::truncate();
        $this->fakeConfigInserts();

        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['category' => 1, 'search_string' => '9FM4W37HHOV', 'line_count' => '0', 'latestOnly' => true]);

        $this->assertStringContainsString('Processor board ID 9FM4W37HHOV', $response->getContent());
        Config::truncate();
    }

    public function test_can_search_and_return_multi_line_result_correctly()
    {
        Config::truncate();
        $this->fakeConfigInserts();

        $this->assertDatabaseHas('categories', ['id' => 1]);
        $response = $this->post('/api/configs/search', ['category' => 1, 'search_string' => '9FM4W37HHOV', 'line_count' => '5', 'latestOnly' => true]);

        $this->assertStringContainsString('License Level: ax', $response->getContent());
        $this->assertStringContainsString('Processor board ID 9FM4W37HHOV', $response->getContent());
        $this->assertStringContainsString('0K bytes of WebUI ODM Files at webui:.', $response->getContent());
        Config::truncate();
    }

    public function fakeConfigInserts()
    {
        $configs = [
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:05:47',
                'end_time' => '2022-01-15 17:05:48',
                'duration' => 1,
                'created_at' => '2022-01-15 17:05:48',
                'updated_at' => '2022-01-15 17:05:48',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:07:08',
                'end_time' => '2022-01-15 17:07:09',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:09',
                'updated_at' => '2022-01-15 17:07:09',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:07:39',
                'end_time' => '2022-01-15 17:07:40',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:40',
                'updated_at' => '2022-01-15 17:07:40',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-15 17:07:39',
                'end_time' => '2022-01-15 17:07:40',
                'duration' => 1,
                'created_at' => '2022-01-15 17:07:40',
                'updated_at' => '2022-01-15 17:07:40',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:17:26',
                'end_time' => '2022-01-15 17:17:27',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:27',
                'updated_at' => '2022-01-15 17:17:27',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:26',
                'end_time' => '2022-01-15 17:17:27',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:27',
                'updated_at' => '2022-01-15 17:17:27',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1002,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 0,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2353,
                'start_time' => '2022-01-15 17:17:33',
                'end_time' => '2022-01-15 17:17:34',
                'duration' => 1,
                'created_at' => '2022-01-15 17:17:34',
                'updated_at' => '2022-01-15 17:17:34',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_versions.txt',
                'config_filename' => 'test_show_versions.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:08:22',
                'end_time' => '2022-01-17 17:08:23',
                'duration' => 1,
                'created_at' => '2022-01-17 17:08:23',
                'updated_at' => '2022-01-17 17:08:23',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:09:53',
                'end_time' => '2022-01-17 17:09:54',
                'duration' => 1,
                'created_at' => '2022-01-17 17:09:54',
                'updated_at' => '2022-01-17 17:09:54',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 33,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 2369,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show version',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_version.txt',
                'config_filename' => 'test_show_version.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show clock',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_show_clock.txt',
                'config_filename' => 'test_show_clock.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1001,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1002,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1003,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
            [
                'device_id' => 1004,
                'device_name' => 'router1',
                'device_category' => 'Routers',
                'command' => 'show run',
                'type' => 'device_download',
                'download_status' => 1,
                'report_id' => null,
                'config_location' => rconfig_appdir_path() . '/tests/storage/test_router_showrun_must_not_be_deleted.txt',
                'config_filename' => 'test_router_showrun_must_not_be_deleted.txt',
                'config_filesize' => 4472,
                'start_time' => '2022-01-17 17:10:02',
                'end_time' => '2022-01-17 17:10:03',
                'duration' => 1,
                'created_at' => '2022-01-17 17:10:03',
                'updated_at' => '2022-01-17 17:10:03',
            ],
        ];
        Config::insert($configs);
    }
}
