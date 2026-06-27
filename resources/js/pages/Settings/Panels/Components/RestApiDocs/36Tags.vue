<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Tag } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Tags";

const endpoints = {
  0: {
    name: "Tag",
    description: "Get all tags",
    method: "get",
    url: "/api/v1/tags",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          tagname: "Routers",
          tagDescription: "A Tag for Routers",
          created_at: "Jul 16, 2022 5:51AM",
          updated_at: "Jul 16, 2022 5:51AM",
        },
        {
          id: 2,
          tagname: "Switches",
          tagDescription: "A Tag for Switches",
          created_at: "Jul 16, 2022 5:51AM",
          updated_at: "Jul 16, 2022 5:51AM",
        },
      ],
    },
    responsesdescription: "Successful response.",
  },
  1: {
    name: "Tag",
    description: "Get a single tag",
    method: "get",
    url: "/api/v1/tags/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: {
        id: 2,
        tagname: "Switches",
        tagDescription: "A Tag for Switches",
        created_at: "Jul 16, 2022 5:51AM",
        updated_at: "Jul 16, 2022 5:51AM",
      },
    },
    responsesdescription: "Successful response.",
  },
  2: {
    name: "Tag",
    description: "Add a tag",
    method: "post",
    url: "/api/v1/tags",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "tagname",
        description: "required|alpha_dash|min:3|max:255| unique:tags",
        type: "string",
        example: "RoutersTag",
      },
      {
        name: "tagDescription",
        description: "not required|max:255",
        type: "string",
        example: "A description for RoutersTag",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Tag with ID:1013 created successfully",
    },
    responsesdescription: "Successful response.",
  },
  3: {
    name: "Tag",
    description: "Update a given tag",
    method: "put",
    url: "/api/v1/tags/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "tagname",
        description: "required|alpha_dash|min:3|max:255| unique:tags",
        type: "string",
        example: "RoutersTagNew",
      },
      {
        name: "tagDescription",
        description: "not required|max:255",
        type: "string",
        example: "A description for RoutersTagNew",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Tag with ID:1013 updated successfully",
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "Tag",
    description: "Delete a given tag",
    method: "delete",
    url: "/api/v1/tags/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "1001",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: "Tag Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
  <CardHeader>
    <CardTitle>Tags API</CardTitle>
    <CardDescription>Manage device tags through the REST API</CardDescription>
  </CardHeader>

  <div class="px-6 pb-4">
    <div class="flex items-start gap-2 mb-6">
      <Tag class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
      <div>
        <h3 class="text-base font-medium">Working with Tags</h3>
        <p class="text-muted-foreground mt-1">
          The Tags API allows you to label and group devices using flexible
          taxonomies. Tags provide a way to organize devices independently of
          categories or vendors.
        </p>
      </div>
    </div>

    <AlertInfo
      class="mt-4 mb-6"
      variant="dark"
      title="Device Labeling"
      message="Tags can be assigned to multiple devices and a device can have multiple tags, allowing for flexible organization of your network inventory."
    />
  </div>

  <ApiDocsTemplate :pagename="pagename" :endpoints="endpoints" />
</template>
