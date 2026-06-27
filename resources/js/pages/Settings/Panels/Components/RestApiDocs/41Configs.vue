<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { FileText } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Configs";

const endpoints = {
  0: {
    name: "Config",
    description: "Get configs for a device",
    method: "get",
    url: "/api/v1/configs/all-by-deviceid/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass device ID integer value in the URL per above.",
    responses: {
      success: true,
      data: [
        {
          id: 1001,
          device_id: 1001,
          file_id: 5319,
          config_location:
            "\/home\/rconfig\/data\/1001\/2022-03-27\/shrun-09-26-32.txt",
          config_filename: "shrun-09-26-32.txt",
          config_command: "show run",
          config_date: "2022-03-27",
          config_time: "09:26:32",
          config_filesize: 4935,
          created_at: "Mar 27, 2022 9:26AM",
          updated_at: "Mar 27, 2022 9:26AM",
          device: {
            id: 1001,
            device_name: "router1",
            device_ip: "10.1.1.170",
            created_at: "Jan 01, 1970 1:00AM",
          },
        },
        {
          id: 1003,
          device_id: 1001,
          file_id: 5320,
          config_location:
            "\/home\/rconfig\/data\/1001\/2022-03-27\/shver-09-26-32.txt",
          config_filename: "shver-09-26-32.txt",
          config_command: "show version",
          config_date: "2022-03-27",
          config_time: "09:26:32",
          config_filesize: 1349,
          created_at: "Mar 27, 2022 9:26AM",
          updated_at: "Mar 27, 2022 9:26AM",
          device: {
            id: 1001,
            device_name: "router1",
            device_ip: "10.1.1.170",
            created_at: "Jan 01, 1970 1:00AM",
          },
        },
      ],
    },
    responsesdescription:
      "Successful response. Each record includes the device relationship.",
  },
  1: {
    name: "Config",
    description: "Get a specific config",
    method: "get",
    url: "/api/v1/configs/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass config ID integer value in the URL per above.",
    responses: {
      success: true,
      data: {
        id: 1001,
        device_id: 1001,
        file_id: 5319,
        config_location:
          "\/home\/rconfig\/data\/1001\/2022-03-27\/shrun-09-26-32.txt",
        config_filename: "shrun-09-26-32.txt",
        config_command: "show run",
        config_date: "2022-03-27",
        config_time: "09:26:32",
        config_filesize: 4935,
        created_at: "Mar 27, 2022 9:26AM",
        updated_at: "Mar 27, 2022 9:26AM",
        device: {
          id: 1001,
          device_name: "router1",
          device_ip: "10.1.1.170",
          created_at: "Jan 01, 1970 1:00AM",
        },
        configData:
          "Building configuration...\n\nCurrent configuration : 4935 bytes\n!\n! Last configuration change at 12:56:49 UTC Sun Mar 27 2022\n!\nversion 15.7\nservice timestamps debug datetime msec\nservice timestamps log datetime msec\nno service password-encryption\n!\nhostname router1\n!\nboot-start-marker\nboot-end-marker\n!\n!\n!\nno aaa new-model\n!\n[...output truncated for brevity...]",
      },
    },
    responsesdescription:
      "Successful response. The config record includes the configData property which contains the actual configuration text.",
  },
  3: {
    name: "Config",
    description: "Search within configs",
    method: "post",
    url: "/api/v1/configs/search",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "searchTerm",
        description: "required|string|min:3",
        type: "string",
        example: "interface GigabitEthernet",
      },
      {
        name: "devices",
        description:
          "optional|array - If specified, limit search to these device IDs",
        type: "array, without quotes",
        example: "[1001, 1002]",
      },
      {
        name: "categories",
        description:
          "optional|array - If specified, limit search to devices in these categories",
        type: "array, without quotes",
        example: "[1, 2]",
      },
      {
        name: "tags",
        description:
          "optional|array - If specified, limit search to devices with these tags",
        type: "array, without quotes",
        example: "[1, 2]",
      },
      {
        name: "vendors",
        description:
          "optional|array - If specified, limit search to devices with these vendors",
        type: "array, without quotes",
        example: "[1, 2]",
      },
      {
        name: "commands",
        description:
          "optional|array - If specified, limit search to configs from these commands",
        type: "array, without quotes",
        example: "[1, 2]",
      },
      {
        name: "dateFrom",
        description:
          "optional|date - If specified, limit search to configs from this date onwards",
        type: "date string (YYYY-MM-DD)",
        example: "2022-01-01",
      },
      {
        name: "dateTo",
        description:
          "optional|date - If specified, limit search to configs up to this date",
        type: "date string (YYYY-MM-DD)",
        example: "2022-03-31",
      },
    ],
    parametersdescription:
      "At minimum, the searchTerm parameter is required. Other parameters can be used to refine the search.",
    responses: {
      success: true,
      data: [
        {
          id: 1001,
          device_id: 1001,
          file_id: 5319,
          config_location:
            "\/home\/rconfig\/data\/1001\/2022-03-27\/shrun-09-26-32.txt",
          config_filename: "shrun-09-26-32.txt",
          config_command: "show run",
          config_date: "2022-03-27",
          config_time: "09:26:32",
          device: {
            id: 1001,
            device_name: "router1",
            device_ip: "10.1.1.170",
          },
          matches: [
            {
              line_number: 42,
              line_text: "interface GigabitEthernet0/0",
              context: [
                "!",
                "interface GigabitEthernet0/0",
                " ip address 10.1.1.170 255.255.255.0",
                " duplex auto",
                " speed auto",
                "!",
              ],
            },
            {
              line_number: 48,
              line_text: "interface GigabitEthernet0/1",
              context: [
                "!",
                "interface GigabitEthernet0/1",
                " no ip address",
                " shutdown",
                " duplex auto",
                " speed auto",
                "!",
              ],
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. The search results include matched lines with context (surrounding lines) and line numbers.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>Configs API</CardTitle>
		<CardDescription>
			Access and search device configurations through the REST
			API
		</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<FileText class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Working with Configurations
				</h3>
				<p class="text-muted-foreground mt-1">
					The Configs API allows you to retrieve, view, and search through
					device configuration files that have been collected by rConfig. You
					can access configurations by device, specific file, or search within
					them.
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="Deprecated"
			message="The Configs API v1 is deprecated and will be removed in a future release. Please migrate to the v2 endpoints."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
