<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Server } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Devices";

const endpoints = {
  0: {
    name: "Device",
    description: "Get all devices",
    method: "get",
    url: "/api/v1/devices",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1004,
          integration_host_id: null,
          device_name: "router4",
          device_ip: "10.1.1.170",
          device_default_creds_on: 0,
          device_username: "cisco1",
          device_password: "********",
          device_enable_password: "********",
          ssh_key_id: null,
          device_main_prompt: "router1#",
          device_enable_prompt: "router1>",
          device_category_id: 1,
          device_template: 4,
          device_model: "CSR1000v",
          device_version: "",
          device_added_by: "1",
          created_at: "Jan 01, 1970 1:00AM",
          updated_at: "Mar 27, 2022 0:03AM",
          status: 0,
          last_seen: "Mar 27, 2022 0:03AM",
          vendor: [
            {
              id: 1,
              vendorName: "Aruba",
              created_at: "Jun 07, 2018 13:42PM",
              updated_at: "Jun 07, 2018 13:42PM",
              pivot: {
                device_id: 1004,
                vendor_id: 1,
              },
            },
          ],
          category: [
            {
              id: 1,
              categoryName: "Routers",
              categoryDescription: null,
              badgeColor: "badge-primary",
              created_at: "Jun 06, 2018 22:20PM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                device_id: 1004,
                category_id: 1,
              },
            },
          ],
          tag: [],
          template: [],
        },
      ],
    },
    responsesdescription:
      'Successful response, but partially truncated for brevity. Note: a device comes with the "vendor", "tag", "template" and "Command Group" relationships.',
  },
  1: {
    name: "Device",
    description: "Get a single device",
    method: "get",
    url: "/api/v1/devices/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: [
        {
          id: 1001,
          integration_host_id: null,
          device_name: "router1",
          device_ip: "10.1.1.170",
          device_default_creds_on: 0,
          device_username: "cisco",
          device_password: "********",
          device_enable_password: "********",
          ssh_key_id: null,
          device_main_prompt: "router1#",
          device_enable_prompt: "router1>",
          device_category_id: 1,
          device_template: 1,
          device_model: "CSR1000v",
          device_version: null,
          device_added_by: "1",
          created_at: "Jan 01, 1970 1:00AM",
          updated_at: "Mar 27, 2022 0:02AM",
          status: 1,
          last_seen: "Mar 27, 2022 0:02AM",
          vendor: [
            {
              id: 1,
              vendorName: "Aruba",
              created_at: "Jun 07, 2018 13:42PM",
              updated_at: "Jun 07, 2018 13:42PM",
              pivot: {
                device_id: 1001,
                vendor_id: 1,
              },
            },
          ],
          category: [
            {
              id: 1,
              categoryName: "Routers",
              categoryDescription: null,
              badgeColor: "badge-primary",
              created_at: "Jun 06, 2018 22:20PM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                device_id: 1001,
                category_id: 1,
              },
            },
          ],
          tag: [
            {
              id: 1001,
              tagname: "devtag1",
              tagDescription: "test tag description 1",
              created_at: "Jan 01, 1970 1:00AM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                device_id: 1001,
                tag_id: 1001,
              },
            },
          ],
          template: [
            {
              id: 1,
              fileName: "/app/rconfig/templates/ios-telnet-noenable.yml",
              templateName: "Cisco IOS - TELNET - No Enable",
              description:
                "Cisco IOS TELNET based connection without enable mode",
              created_at: "Feb 27, 2018 12:09PM",
              updated_at: "Jan 01, 1970 1:00AM",
              pivot: {
                device_id: 1001,
                template_id: 1,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      'Successful response. Note: a device comes with the "vendor", "tag", "template" and "category" relationships.',
  },
  2: {
    name: "Device",
    description: "Add a device",
    method: "post",
    url: "/api/v1/devices",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "integration_host_id",
        description:
          "optional|integer - can be used for reference to upstream tools or platforms such as CMDB",
        type: "string",
        example: "123456",
      },
      {
        name: "device_name",
        description: "required|unique:devices|min:5|max:255|alpha_dash",
        type: "string",
        example: "router1",
      },
      {
        name: "device_ip",
        description: "required|ip",
        type: "string",
        example: "192.168.1.2",
      },
      {
        name: "device_vendor",
        description: "required",
        type: "string",
        example: "1",
      },
      {
        name: "device_model",
        description: "required|max:255|min:2",
        type: "string",
        example: "Cisco Catalyst 2960",
      },
      {
        name: "device_category",
        description: "required|max:255",
        type: "integer",
        example: 1,
      },
      {
        name: "device_tags",
        description: "required",
        type: "array, without quotes",
        example: "[1, 2]",
      },
      {
        name: "device_cred_id",
        description:
          "Not required. Can be null, 0 or an actual credential ID. If set to a valid value, it will override the credentials passed in the request.",
        type: "integer",
        example: 1,
      },
      {
        name: "device_username",
        description: "max:255|min:2. Not required if device_cred_id is set",
        type: "string",
        example: "cisco",
      },
      {
        name: "device_password",
        description: "min:2. Not required if device_cred_id is set",
        type: "string",
        example: "ExamplePassword",
      },
      {
        name: "device_enable_password",
        description: "Not required.",
        type: "string",
        example: "ExamplePassword",
      },
      {
        name: "device_template",
        description: "required",
        type: "integer",
        example: 1,
      },
      {
        name: "device_main_prompt",
        description: "required",
        type: "string",
        example: "router1#",
      },
      {
        name: "device_enable_prompt",
        description: "optional",
        type: "string",
        example: "router1>",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Device with ID:66369 created successfully",
    },
    responsesdescription:
      "Successful response. Once a device is added, a download job will start immediately. Check the queue manager and activity logs for jobs status.",
  },
  3: {
    name: "Device",
    description: "Update a given device",
    method: "put",
    url: "/api/v1/devices/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "device_name",
        description: "required|unique:devices|min:5|max:255|alpha_dash",
        type: "string",
        example: "router1",
      },
      {
        name: "device_ip",
        description: "required|ip",
        type: "string",
        example: "192.168.1.2",
      },
      {
        name: "device_vendor",
        description: "required",
        type: "string",
        example: "1",
      },
      {
        name: "device_model",
        description: "required|max:255|min:2",
        type: "string",
        example: "Cisco Catalyst 2960",
      },
      {
        name: "device_category",
        description: "required|max:255",
        type: "integer",
        example: 1,
      },
      {
        name: "device_tags",
        description: "required",
        type: "array",
        example: "[1, 2]",
      },
      {
        name: "device_cred_id",
        description:
          "Not required. Can be null, 0 or an actual credential ID. If set to a valid value, it will override the credentials passed in the request.",
        type: "integer",
        example: 1,
      },
      {
        name: "device_username",
        description:
          "required|max:255|min:2. Required even if device_cred_id is set.",
        type: "string",
        example: "cisco",
      },
      {
        name: "device_password",
        description: "required|min:2. Required even if device_cred_id is set.",
        type: "string",
        example: "ExamplePassword",
      },
      {
        name: "device_enable_password",
        description: "Not required.",
        type: "string",
        example: "ExamplePassword",
      },
      {
        name: "device_template",
        description: "required",
        type: "integer",
        example: 1,
      },
      {
        name: "device_main_prompt",
        description: "required",
        type: "string",
        example: "router1#",
      },
      {
        name: "device_enable_prompt",
        description: "optional",
        type: "string",
        example: "router1>",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Device with ID:66369 updated successfully",
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "Device",
    description: "Delete a given device",
    method: "delete",
    url: "/api/v1/devices/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: "Device Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
  <CardHeader>
    <CardTitle>Devices</CardTitle>
    <CardDescription>Manage network devices through the API</CardDescription>
  </CardHeader>

  <div class="px-6 pb-4">
    <div class="flex items-start gap-2 mb-6">
      <Server class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
      <div>
        <h3 class="text-base font-medium">Overview</h3>
        <p class="text-muted-foreground mt-1">
          Manage network devices through the API
        </p>
      </div>
    </div>

    <AlertInfo
      class="mt-4 mb-6"
      variant="dark"
      title="Deprecated"
      message="The Devices API v1 is deprecated and will be removed in a future release. Please migrate to the v2 endpoints."
    />
  </div>

  <ApiDocsTemplate :pagename="pagename" :endpoints="endpoints" />
</template>
