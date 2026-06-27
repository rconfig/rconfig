<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { FileCode } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Templates";

const endpoints = {
  0: {
    name: "Template",
    description: "Get all templates",
    method: "get",
    url: "/api/v1/templates",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          fileName: "/app/rconfig/templates/ios-telnet-noenable.yml",
          templateName: "Cisco IOS - TELNET - No Enable",
          description: "Cisco IOS TELNET based connection without enable mode",
          created_at: "Feb 27, 2018 12:09PM",
          updated_at: "Jan 01, 1970 1:00AM",
          commands: [
            {
              id: 1,
              command: "show version",
              commandDesc: "Show system version",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 1,
                command_id: 1,
              },
            },
            {
              id: 2,
              command: "show run",
              commandDesc: "Show running configuration",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 1,
                command_id: 2,
              },
            },
          ],
        },
        {
          id: 2,
          fileName: "/app/rconfig/templates/ios-ssh-noenable.yml",
          templateName: "Cisco IOS - SSH - No Enable",
          description: "Cisco IOS SSH based connection without enable mode",
          created_at: "Feb 27, 2018 12:10PM",
          updated_at: "Jan 01, 1970 1:00AM",
          commands: [
            {
              id: 1,
              command: "show version",
              commandDesc: "Show system version",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 2,
                command_id: 1,
              },
            },
            {
              id: 2,
              command: "show run",
              commandDesc: "Show running configuration",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 2,
                command_id: 2,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a template comes with the commands relationship by default.",
  },
  1: {
    name: "Template",
    description: "Get a single template",
    method: "get",
    url: "/api/v1/templates/{id}",
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
          fileName: "/app/rconfig/templates/ios-telnet-noenable.yml",
          templateName: "Cisco IOS - TELNET - No Enable",
          description: "Cisco IOS TELNET based connection without enable mode",
          created_at: "Feb 27, 2018 12:09PM",
          updated_at: "Jan 01, 1970 1:00AM",
          commands: [
            {
              id: 1,
              command: "show version",
              commandDesc: "Show system version",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 1,
                command_id: 1,
              },
            },
            {
              id: 2,
              command: "show run",
              commandDesc: "Show running configuration",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                template_id: 1,
                command_id: 2,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a template comes with the commands relationship by default.",
  },
  2: {
    name: "Template",
    description: "Add a template",
    method: "post",
    url: "/api/v1/templates",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "templateName",
        description: "required|unique:templates|max:255|min:2|alpha_dash",
        type: "string",
        example: "Cisco IOS - SSH - With Enable",
      },
      {
        name: "description",
        description: "required|max:255|min:2",
        type: "string",
        example: "Cisco IOS SSH based connection with enable mode",
      },
      {
        name: "commands",
        description: "required|array",
        type: "array, without quotes",
        example: "[1, 2, 3]",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Template added successfully",
    },
    responsesdescription: "Successful response.",
  },
  3: {
    name: "Template",
    description: "Update a given template",
    method: "put",
    url: "/api/v1/templates/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "templateName",
        description: "required|unique:templates|max:255|min:2|alpha_dash",
        type: "string",
        example: "Cisco IOS - SSH - With Enable",
      },
      {
        name: "description",
        description: "required|max:255|min:2",
        type: "string",
        example: "Cisco IOS SSH based connection with enable mode",
      },
      {
        name: "commands",
        description: "required|array",
        type: "array, without quotes",
        example: "[1, 2, 3]",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Template updated successfully",
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "Template",
    description: "Delete a given template",
    method: "delete",
    url: "/api/v1/templates/{id}",
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
      data: "Template Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>Templates API</CardTitle>
		<CardDescription>
			Manage device templates through the rConfig API
		</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<FileCode class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Templates Overview
				</h3>
				<p class="text-muted-foreground mt-1">
					Manage device templates through the rConfig API
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="Command Relationships"
			message="Templates have relationships with commands. When retrieving templates, the associated commands are included in the response."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
