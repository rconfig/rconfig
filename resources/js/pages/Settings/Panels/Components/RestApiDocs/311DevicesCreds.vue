<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Key } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Device Credentials";

const endpoints = {
  0: {
    name: "DeviceCred",
    description: "Get all device credentials",
    method: "get",
    url: "/api/v1/device-credentials",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          deviceCredName: "Cisco IOS SSH",
          username: "admin",
          password: "********",
          enableMode: 1,
          enablePassword: "********",
          protocol: "ssh",
          sshPort: 22,
          description: "Cisco IOS SSH credentials",
          created_at: "2022-01-15 10:30:00",
          updated_at: "2022-01-15 10:30:00",
          devices: [
            {
              id: 1001,
              device_name: "router1",
              device_ip: "10.1.1.170",
              pivot: {
                devicecred_id: 1,
                device_id: 1001,
              },
            },
            {
              id: 1002,
              device_name: "router2",
              device_ip: "192.168.1.171",
              pivot: {
                devicecred_id: 1,
                device_id: 1002,
              },
            },
          ],
        },
        {
          id: 2,
          deviceCredName: "Cisco IOS Telnet",
          username: "admin",
          password: "********",
          enableMode: 1,
          enablePassword: "********",
          protocol: "telnet",
          sshPort: null,
          description: "Cisco IOS Telnet credentials",
          created_at: "2022-01-15 10:35:00",
          updated_at: "2022-01-15 10:35:00",
          devices: [],
        },
      ],
    },
    responsesdescription:
      "Successful response. If credential masking is enabled, password values are masked. If credential masking is disabled, password values are returned as plain text. Each credential includes associated devices.",
  },
  1: {
    name: "DeviceCred",
    description: "Get a single device credential",
    method: "get",
    url: "/api/v1/device-credentials/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: {
        id: 1,
        deviceCredName: "Cisco IOS SSH",
        username: "admin",
        password: "********",
        enableMode: 1,
        enablePassword: "********",
        protocol: "ssh",
        sshPort: 22,
        description: "Cisco IOS SSH credentials",
        created_at: "2022-01-15 10:30:00",
        updated_at: "2022-01-15 10:30:00",
        devices: [
          {
            id: 1001,
            device_name: "router1",
            device_ip: "10.1.1.170",
            pivot: {
              devicecred_id: 1,
              device_id: 1001,
            },
          },
          {
            id: 1002,
            device_name: "router2",
            device_ip: "192.168.1.171",
            pivot: {
              devicecred_id: 1,
              device_id: 1002,
            },
          },
        ],
      },
    },
    responsesdescription:
      "Successful response. If credential masking is enabled, password values are masked. If credential masking is disabled, password values are returned as plain text. Includes associated devices.",
  },
  2: {
    name: "DeviceCred",
    description: "Delete a device credential",
    method: "delete",
    url: "/api/v1/device-credentials/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "3",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription:
      "Pass ID integer value in the URL per above. Note: You cannot delete credentials that are in use by devices.",
    responses: {
      success: true,
      data: "Device credential deleted successfully",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
  <CardHeader>
    <CardTitle>Device Credentials API</CardTitle>
    <CardDescription
      >Manage device credentials through the rConfig API</CardDescription
    >
  </CardHeader>

  <div class="px-6 pb-4">
    <div class="flex items-start gap-2 mb-6">
      <Key class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
      <div>
        <h3 class="text-base font-medium">Device Credentials Overview</h3>
        <p class="text-muted-foreground mt-1">
          Manage device credentials through the rConfig API
        </p>
      </div>
    </div>

    <AlertInfo
      class="mt-4 mb-6"
      variant="dark"
      title="Security Note"
      message="If credential masking is enabled, credential password fields are masked in API responses. If credential masking is disabled, credential password fields are returned as plain text."
    />
  </div>

  <ApiDocsTemplate :pagename="pagename" :endpoints="endpoints" />
</template>
