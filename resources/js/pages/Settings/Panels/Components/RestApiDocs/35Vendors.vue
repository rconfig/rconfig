<script setup>
import { ref } from "vue";
import { CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import ApiDocsTemplate from "./ApiDocsTemplate.vue";
import { Building } from "lucide-vue-next";
import AlertInfo from "@/pages/Shared/Alerts/AlertInfo.vue";

const pagename = "Vendors";

const endpoints = {
  0: {
    name: "Vendor",
    description: "Get all vendors",
    method: "get",
    url: "/api/v1/vendors",
    parameters: [],
    parametersdescription: "",
    responses: {
      success: true,
      data: [
        {
          id: 1,
          vendorName: "Aruba",
          created_at: "Jun 07, 2018 13:42PM",
          updated_at: "Jun 07, 2018 13:42PM",
          devices: [
            {
              id: 1001,
              device_name: "router1",
              device_ip: "10.1.1.170",
              pivot: {
                vendor_id: 1,
                device_id: 1001,
              },
            },
            {
              id: 1002,
              device_name: "router2",
              device_ip: "192.168.1.171",
              pivot: {
                vendor_id: 1,
                device_id: 1002,
              },
            },
          ],
        },
        {
          id: 2,
          vendorName: "Cisco",
          created_at: "Jun 07, 2018 13:42PM",
          updated_at: "Jun 07, 2018 13:42PM",
          devices: [
            {
              id: 1003,
              device_name: "switch1",
              device_ip: "192.168.1.172",
              pivot: {
                vendor_id: 2,
                device_id: 1003,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a vendor comes with the devices relationship by default.",
  },
  1: {
    name: "Vendor",
    description: "Get a single vendor",
    method: "get",
    url: "/api/v1/vendors/{id}",
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
          vendorName: "Aruba",
          created_at: "Jun 07, 2018 13:42PM",
          updated_at: "Jun 07, 2018 13:42PM",
          devices: [
            {
              id: 1001,
              device_name: "router1",
              device_ip: "10.1.1.170",
              pivot: {
                vendor_id: 1,
                device_id: 1001,
              },
            },
            {
              id: 1002,
              device_name: "router2",
              device_ip: "192.168.1.171",
              pivot: {
                vendor_id: 1,
                device_id: 1002,
              },
            },
          ],
        },
      ],
    },
    responsesdescription:
      "Successful response. Note: a vendor comes with the devices relationship by default.",
  },
  2: {
    name: "Vendor",
    description: "Add a vendor",
    method: "post",
    url: "/api/v1/vendors",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "vendorName",
        description: "required|unique:vendors|max:255|min:2|alpha_dash",
        type: "string",
        example: "Juniper",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Vendor added successfully",
    },
    responsesdescription: "Successful response.",
  },
  3: {
    name: "Vendor",
    description: "Update a given vendor",
    method: "put",
    url: "/api/v1/vendors/{id}",
    parametersUrlOnly: false,
    parameters: [
      {
        name: "vendorName",
        description: "required|unique:vendors|max:255|min:2|alpha_dash",
        type: "string",
        example: "Juniper",
      },
    ],
    parametersdescription: "",
    responses: {
      success: true,
      data: "Vendor updated successfully",
    },
    responsesdescription: "Successful response.",
  },
  4: {
    name: "Vendor",
    description: "Delete a given vendor",
    method: "delete",
    url: "/api/v1/vendors/{id}",
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
      data: "Vendor Deleted",
    },
    responsesdescription: "Successful response.",
  },
};
</script>

<template>
	<CardHeader>
		<CardTitle>{{ pagename }} API</CardTitle>
		<CardDescription>
			Manage device vendors through the REST API
		</CardDescription>
	</CardHeader>

	<div class="px-6 pb-4">
		<div class="flex items-start gap-2 mb-6">
			<Building class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
			<div>
				<h3 class="text-base font-medium">
					Working with Vendors
				</h3>
				<p class="text-muted-foreground mt-1">
					The Vendors API allows you to manage equipment manufacturers for your
					network devices. Vendors help organize and categorize devices by their
					manufacturer.
				</p>
			</div>
		</div>

		<AlertInfo
			class="mt-4 mb-6"
			variant="dark"
			title="Device Relationships"
			message="Vendors have relationships with devices. When retrieving vendors, the associated devices are included in the response."
		/>
	</div>

	<ApiDocsTemplate
		:pagename="pagename"
		:endpoints="endpoints"
	/>
</template>
