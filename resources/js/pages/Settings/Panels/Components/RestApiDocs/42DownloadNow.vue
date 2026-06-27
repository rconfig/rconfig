<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Download } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Download Now";

const endpoints = {
  0: {
    name: "Download",
    description: "Download configurations for a device",
    method: "get",
    url: "/api/v1/download-now/{id}",
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
      data: "Download job queued successfully for device 1001",
    },
    responsesdescription:
      "Successful response. The download job is queued and will be processed asynchronously. Check the job status in the Queue Manager.",
  },
  1: {
    name: "Download",
    description: "Download configurations by API collection host ID",
    method: "get",
    url: "/api/v1/api-collections/download-now/{id}",
    parametersUrlOnly: true,
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersdescription: "Pass the API collection host ID in the URL.",
    responses: {
      success: true,
      message: "Download started for device id: 1001",
    },
    responsesdescription:
      "Successful response. The download job is queued and processed asynchronously.",
  },
  2: {
    name: "Download",
    description: "Download configurations by integration host ID",
    method: "get",
    url: "/api/v1/integrations/download-now/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "90009000",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass the integration host ID in the URL.",
    responses: {
      success: true,
      message: "Download started for device id: 90009000",
    },
    responsesdescription:
      "Successful response. The integration download job is queued and processed asynchronously.",
  },
};
</script>

<template>
  <CardHeader>
    <CardTitle>Download Now API</CardTitle>
    <CardDescription
      >Trigger immediate configuration downloads through the REST
      API</CardDescription
    >
  </CardHeader>

  <div class="px-6 pb-4">
    <div class="flex items-start gap-2 mb-6">
      <Download class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
      <div>
        <h3 class="text-base font-medium">On-Demand Configuration Downloads</h3>
        <p class="text-muted-foreground mt-1">
          The Download Now API allows you to trigger immediate configuration
          downloads from your network devices. You can download configurations
          for individual devices, multiple specific devices, or all devices in a
          category or with a specific tag.
        </p>
      </div>
    </div>

    <AlertInfo
      class="mt-4 mb-6"
      variant="dark"
      title="Asynchronous Processing"
      message="Download requests are queued and processed asynchronously. The v1 endpoints here are host-ID driven and return queue-start confirmation messages."
    />
  </div>

  <ApiDocsTemplate :pagename="pagename" :endpoints="endpoints" />
</template>
