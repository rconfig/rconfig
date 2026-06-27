<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Terminal } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Commands";

const endpoints = {
  0: {
    name: "Command",
    description: "Get all commands",
    method: "get",
    url: "/api/v1/commands",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          command: "show version",
          commandDesc: "Show system version",
          created_at: "Jan 01, 1970 1:00AM",
          updated_at: "Jan 01, 1970 1:00AM",
          templates: [
            {
              id: 1,
              templateName: "Cisco IOS - TELNET - No Enable",
              pivot: {
                command_id: 1,
                template_id: 1,
              },
            },
            {
              id: 2,
              templateName: "Cisco IOS - SSH - No Enable",
              pivot: {
                command_id: 1,
                template_id: 2,
              },
            },
          ],
        },
        {
          id: 2,
          command: "show run",
          commandDesc: "Show running configuration",
          created_at: "Jan 01, 1970 1:00AM",
          updated_at: "Jan 01, 1970 1:00AM",
          templates: [
            {
              id: 1,
              templateName: "Cisco IOS - TELNET - No Enable",
              pivot: {
                command_id: 2,
                template_id: 1,
              },
            },
            {
              id: 2,
              templateName: "Cisco IOS - SSH - No Enable",
              pivot: {
                command_id: 2,
                template_id: 2,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a command comes with the templates relationship by default.",
  },
  1: {
    name: "Command",
    description: "Get a single command",
    method: "get",
    url: "/api/v1/commands/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1",
      },
    ],
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          command: "show version",
          commandDesc: "Show system version",
          created_at: "Jan 01, 1970 1:00AM",
          updated_at: "Jan 01, 1970 1:00AM",
          templates: [
            {
              id: 1,
              templateName: "Cisco IOS - TELNET - No Enable",
              pivot: {
                command_id: 1,
                template_id: 1,
              },
            },
            {
              id: 2,
              templateName: "Cisco IOS - SSH - No Enable",
              pivot: {
                command_id: 1,
                template_id: 2,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a command comes with the templates relationship by default.",
  },
  2: {
    name: "Command",
    description: "Add a command",
    method: "post",
    url: "/api/v1/commands",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "command",
        description: "required|unique:commands|max:255|min:2",
        type: "string",
        example: "show interfaces",
      },
      {
        name: "commandDesc",
        description: "optional|max:255",
        type: "string",
        example: "Show all interfaces",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Command added successfully",
    },
    responsesdescription: "Successful response.",
  },
  3: {
    name: "Command",
    description: "Update a given command",
    method: "put",
    url: "/api/v1/commands/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "command",
        description: "required|unique:commands|max:255|min:2",
        type: "string",
        example: "show interfaces",
      },
      {
        name: "commandDesc",
        description: "optional|max:255",
        type: "string",
        example: "Show all interfaces",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Command updated successfully",
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "Command",
    description: "Delete a given command",
    method: "delete",
    url: "/api/v1/commands/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1",
      },
    ],
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: "Command Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>Commands API</CardTitle>
		<CardDescription>
			Manage device commands through the rConfig API
		</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<Terminal class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Commands Overview
				</h3>
				<p class="text-muted-foreground mt-1">
					Manage device commands through the rConfig API
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="Template Relationships"
			message="Commands have relationships with templates. When retrieving commands, the associated templates are included in the response."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
