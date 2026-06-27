<script setup>
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Users } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Users";

const endpoints = {
  0: {
    name: "User",
    description: "Get all users",
    method: "get",
    url: "/api/v1/users",
    parameters: [],
    parametersdescription: "",
    responses: {
      data: [
        {
          id: 1,
          name: "Administrator",
          username: "admin",
          email: "admin@example.com",
          role: "Admin",
          email_verified_at: "2022-03-01 00:00:00",
          created_at: "2022-03-01 00:00:00",
          updated_at: "2022-03-01 00:00:00",
        },
        {
          id: 2,
          name: "Network Operator",
          username: "operator",
          email: "operator@example.com",
          role: "Admin",
          email_verified_at: "2022-03-01 00:00:00",
          created_at: "2022-03-01 00:00:00",
          updated_at: "2022-03-01 00:00:00",
        },
      ],
    },
    responsesdescription:
      "Successful response. Results are paginated; use ?perPage= to control page size.",
  },
  1: {
    name: "User",
    description: "Get a single user",
    method: "get",
    url: "/api/v1/users/{id}",
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
        name: "Administrator",
        username: "admin",
        email: "admin@example.com",
        role: "Admin",
        email_verified_at: "2022-03-01 00:00:00",
        created_at: "2022-03-01 00:00:00",
        updated_at: "2022-03-01 00:00:00",
      },
    },
    responsesdescription: "Successful response.",
  },
  2: {
    name: "User",
    description: "Add a user",
    method: "post",
    url: "/api/v1/users",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "name",
        description: "required|string|max:255",
        type: "string",
        example: "John Doe",
      },
      {
        name: "email",
        description: "required|string|email|max:255|unique:users",
        type: "string",
        example: "john.doe@example.com",
      },
      {
        name: "password",
        description: "required|string|min:5",
        type: "string",
        example: "SecurePassword123",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: {
        id: 3,
        name: "John Doe",
        email: "john.doe@example.com",
        role: "Admin",
      },
    },
    responsesdescription: "Successful response.",
  },
  3: {
    name: "User",
    description: "Update a user",
    method: "put",
    url: "/api/v1/users/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "name",
        description: "required|string|max:255",
        type: "string",
        example: "John Smith",
      },
      {
        name: "email",
        description: "optional|string|email|min:1",
        type: "string",
        example: "john.smith@example.com",
      },
      {
        name: "password",
        description: "optional|string|min:5",
        type: "string",
        example: "NewSecurePassword123",
      },
    ],
    parametersdescription:
      "Fields are optional during update. Include only attributes you need to change.",
    responses: {
      success: true,
      data: {
        id: 3,
        name: "John Smith",
        email: "john.smith@example.com",
        role: "Admin",
      },
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "User",
    description: "Delete a user",
    method: "delete",
    url: "/api/v1/users/{id}",
    parameters: [
      {
        name: "id",
        description: "required",
        type: "integer",
        example: "3",
      },
    ],
    parametersUrlOnly: true,
    parametersdescription: "Pass ID integer value in the URL per above.",
    responses: {
      success: true,
      data: "User Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>Users API</CardTitle>
		<CardDescription>Manage system users through the REST API</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<Users class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Working with Users
				</h3>
				<p class="text-muted-foreground mt-1">
					The Users API allows you to manage user accounts in rConfig. You can
					create, view, update, and delete users.
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="User Management"
			message="This section documents the v1 users resource CRUD endpoints. Additional user profile and activity endpoints are available in the authenticated app API namespace."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
