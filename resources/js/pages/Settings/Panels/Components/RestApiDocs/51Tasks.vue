<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Calendar } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Tasks";

const endpoints = {
  0: {
    name: "Task",
    description: "Get all tasks",
    method: "get",
    url: "/api/v1/tasks",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          task_name: "Daily Router Backup",
          task_desc: "Download configurations from all routers every day",
          task_command: "getconfigs",
          task_categories: null,
          task_devices: null,
          task_tags: "1,2",
          task_cron: "0 0 * * *",
          task_enabled: 1,
          next_run: "2022-03-29 00:00:00",
          last_run: "2022-03-28 00:00:00",
          created_at: "2022-03-01 08:30:00",
          updated_at: "2022-03-28 00:00:01",
          runtime_stats: {
            average_duration: "180 seconds",
            last_duration: "175 seconds",
            success_rate: "98%",
            total_runs: 28,
          },
        },
        {
          id: 2,
          task_name: "Weekly Switch Backup",
          task_desc: "Download configurations from all switches every week",
          task_command: "getconfigs",
          task_categories: "2",
          task_devices: null,
          task_tags: null,
          task_cron: "0 0 * * 0",
          task_enabled: 1,
          next_run: "2022-04-03 00:00:00",
          last_run: "2022-03-27 00:00:00",
          created_at: "2022-03-01 08:45:00",
          updated_at: "2022-03-27 00:00:01",
          runtime_stats: {
            average_duration: "120 seconds",
            last_duration: "118 seconds",
            success_rate: "100%",
            total_runs: 4,
          },
        },
      ],
    },
    responsesdescription:
      "Successful response. Each task includes runtime statistics.",
  },
  1: {
    name: "Task",
    description: "Get a single task",
    method: "get",
    url: "/api/v1/tasks/{id}",
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
        task_name: "Daily Router Backup",
        task_desc: "Download configurations from all routers every day",
        task_command: "getconfigs",
        task_categories: null,
        task_devices: null,
        task_tags: "1,2",
        task_cron: "0 0 * * *",
        task_enabled: 1,
        next_run: "2022-03-29 00:00:00",
        last_run: "2022-03-28 00:00:00",
        created_at: "2022-03-01 08:30:00",
        updated_at: "2022-03-28 00:00:01",
        runtime_stats: {
          average_duration: "180 seconds",
          last_duration: "175 seconds",
          success_rate: "98%",
          total_runs: 28,
        },
        execution_history: [
          {
            run_date: "2022-03-28 00:00:00",
            status: "success",
            duration: "175 seconds",
            devices_total: 45,
            devices_success: 44,
            devices_failed: 1,
          },
          {
            run_date: "2022-03-27 00:00:00",
            status: "success",
            duration: "182 seconds",
            devices_total: 45,
            devices_success: 45,
            devices_failed: 0,
          },
        ],
      },
    },
    responsesdescription:
      "Successful response. A single task includes runtime statistics and recent execution history.",
  },
  4: {
    name: "Task",
    description: "Delete a task",
    method: "delete",
    url: "/api/v1/tasks/{id}",
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
      data: "Task deleted successfully",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
  <CardHeader>
    <CardTitle>Tasks API</CardTitle>
    <CardDescription
      >Manage scheduled tasks through the REST API</CardDescription
    >
  </CardHeader>

  <div class="px-6 pb-4">
    <div class="flex items-start gap-2 mb-6">
      <Calendar class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
      <div>
        <h3 class="text-base font-medium">Working with Scheduled Tasks</h3>
        <p class="text-muted-foreground mt-1">
          The Tasks API allows you to manage scheduled operations in rConfig.
          You can create, view, update, and delete tasks that run on a schedule
          defined by a cron expression. Tasks can be applied to specific
          devices, categories, or tags.
        </p>
      </div>
    </div>

    <AlertInfo
      class="mt-4 mb-6"
      variant="dark"
      title="Task Commands"
      message="The v1 token API currently supports listing, viewing, and deleting tasks. Task create/update/execute helpers are available in the authenticated app API namespace."
    />
  </div>

  <ApiDocsTemplate :pagename="pagename" :endpoints="endpoints" />
</template>
