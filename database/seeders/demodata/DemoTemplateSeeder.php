<?php

namespace Database\Seeders\demodata;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DemoTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->templates() as $template) {
            Template::create($template);
        }
        foreach ($this->templateUrls() as $template) {
            if (! File::exists(templates_path().basename($template['fileName']))) {
                $response = Http::get($template['url']);
                File::put(templates_path().basename($template['fileName']), $response->body());
            }
        }
    }

    public function templateUrls()
    {
        return [
            [
                'name' => 'Template 1',
                'fileName' => '/app/rconfig/templates/brocade.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Brocade/brocade.yml',
            ],
            [
                'name' => 'Template 2',
                'fileName' => '/app/rconfig/templates/CheckpointGaiaOS_NoEnable.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Checkpoint/CheckpointGaiaOS_NoEnable.yml',
            ],
            [
                'name' => 'Template 3',
                'fileName' => '/app/rconfig/templates/dell-s4048-ssh-noenable.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Dell/dell-s4048-ssh-noenable.yml',
            ],
            [
                'name' => 'Template 4',
                'fileName' => '/app/rconfig/templates/hp-procurve-ssh-noenable-v2.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/HP/hp-procurve-ssh-noenable-v2.yml',
            ],
            [
                'name' => 'Template 5',
                'fileName' => '/app/rconfig/templates/Sonicwall-ssh-no-enable.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Palo%20Alto%20Networks/panos-ssh.yml',
            ],
            [
                'name' => 'Template 6',
                'fileName' => '/app/rconfig/templates/panos-ssh.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Palo%20Alto%20Networks/panos-ssh.yml',
            ],
            [
                'name' => 'Template 7',
                'fileName' => '/app/rconfig/templates/Sonicwall-ssh-no-enable.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/Sonicwall/Sonicwall-ssh-no-enable.yml',
            ],
            [
                'name' => 'Template 8',
                'fileName' => '/app/rconfig/templates/centos-7-ssh.yml',
                'url' => 'https://raw.githubusercontent.com/rconfig/rConfig-templates/master/linux/centos-7-ssh.yml',
            ],
        ];
    }

    public function templates()
    {
        return [
            [
                'id' => 5,
                'fileName' => '/app/rconfig/templates/brocade.yml',
                'templateName' => 'Brocade_Devices',
                'description' => 'Brocade Connection Teamplate',
                'created_at' => '2022-04-18 11:36:07',
                'updated_at' => '2022-04-18 11:36:07',
            ],
            [
                'id' => 6,
                'fileName' => '/app/rconfig/templates/CheckpointGaiaOS_NoEnable.yml',
                'templateName' => 'Checkpoint Gaia',
                'description' => 'Checkpoint Gaia connection template',
                'created_at' => '2022-04-18 11:36:15',
                'updated_at' => '2022-04-18 11:36:15',
            ],
            [
                'id' => 7,
                'fileName' => '/app/rconfig/templates/dell-s4048-ssh-noenable.yml',
                'templateName' => 'Dell S4048 ssh noenable',
                'description' => 'Dell S4048 ssh noenable',
                'created_at' => '2022-04-18 11:36:22',
                'updated_at' => '2022-04-18 11:36:22',
            ],
            [
                'id' => 8,
                'fileName' => '/app/rconfig/templates/hp-procurve-ssh-noenable-v2.yml',
                'templateName' => 'HP Procurve SSH noenable v2',
                'description' => 'HP Procurve SSH noenable v2 for v5 users',
                'created_at' => '2022-04-18 11:36:31',
                'updated_at' => '2022-04-18 11:36:31',
            ],
            [
                'id' => 9,
                'fileName' => '/app/rconfig/templates/panos-ssh.yml',
                'templateName' => 'Palo Alto - SSH - No Configure Mode',
                'description' => 'Palo Alto Networks PAN-OS SSH based connection without configuration mode',
                'created_at' => '2022-04-18 11:36:43',
                'updated_at' => '2022-04-18 11:36:43',
            ],
            [
                'id' => 10,
                'fileName' => '/app/rconfig/templates/Sonicwall-ssh-no-enable.yml',
                'templateName' => 'Sonicwall-ssh-no-enable',
                'description' => 'Sonicwall SSH based connection without enable mode',
                'created_at' => '2022-04-18 11:36:53',
                'updated_at' => '2022-04-18 11:36:53',
            ],
            [
                'id' => 11,
                'fileName' => '/app/rconfig/templates/centos-7-ssh.yml',
                'templateName' => 'Centos 7 SSH',
                'description' => 'Centos 7 SSH',
                'created_at' => '2022-04-18 11:37:02',
                'updated_at' => '2022-04-18 11:37:02',
            ],
        ];
    }
}
