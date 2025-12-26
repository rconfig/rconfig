<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceBulkUpdateController extends ApiBaseController
{
    public $model;

    public function __construct(Device $model)
    {
        $this->model = $model;
    }

    public function bulkUpdate($type, Request $request)
    {
        if ($type === 'bulk-delete') {
            return $this->bulkDelete($request);
        }

        $method = 'update' . ucfirst($type);

        if (! method_exists($this, $method)) {
            return $this->respondNotFound('Invalid bulk update type');
        }

        return $this->$method($request);
    }

    public function bulkDelete(Request $request)
    {

        $this->validate(request(), [
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Delete the devices
            $this->model->whereIn('id', $device_ids)->delete();
            // delete related configs
            DB::table('configs')->whereIn('device_id', $device_ids)->delete();
            DB::table('device_role')->whereIn('device_id', $device_ids)->delete();
            DB::table('device_task')->whereIn('device_id', $device_ids)->delete();
            DB::table('device_tag')->whereIn('device_id', $device_ids)->delete();
            DB::table('device_vendor')->whereIn('device_id', $device_ids)->delete();
            DB::table('category_device')->whereIn('device_id', $device_ids)->delete();
            DB::table('device_template')->whereIn('device_id', $device_ids)->delete();

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices deleted successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to delete devices' . $e->getMessage());
        }
    }

    public function updateCategory(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'category_id' => 'required|integer|exists:categories,id',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $category_id = request('category_id');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_category_id field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_category_id' => $category_id]);

            // Delete the existing relationships in the category_device table
            DB::table('category_device')->whereIn('device_id', $device_ids)->delete();

            // Insert the new relationships in the category_device table
            foreach ($device_ids as $device_id) {
                DB::table('category_device')->insert(['device_id' => $device_id, 'category_id' => $category_id]);
            }

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateTemplate(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'template_id' => 'required|integer|exists:templates,id',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $template_id = request('template_id');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_template_id field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_template' => $template_id]);

            // Delete the existing relationships in the template_device table
            DB::table('device_template')->whereIn('device_id', $device_ids)->delete();

            // Insert the new relationships in the template_device table
            foreach ($device_ids as $device_id) {
                DB::table('device_template')->insert(['device_id' => $device_id, 'template_id' => $template_id]);
            }

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateVendor()
    {
        $this->validate(request(), [
            'vendor_id' => 'required|exists:vendors,id',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $vendor_id = request('vendor_id');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Delete the existing relationships in the device_vendor table
            DB::table('device_vendor')->whereIn('device_id', $device_ids)->delete();

            // Insert the new relationships in the device_vendor table
            foreach ($device_ids as $device_id) {
                DB::table('device_vendor')->insert(['device_id' => $device_id, 'vendor_id' => $vendor_id]);
            }

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateTags()
    {
        $this->validate(request(), [
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
            'append' => 'boolean', // Optional append flag
        ]);

        $tag_ids = request('tag_ids');
        $device_ids = request('device_ids');
        $append = request('append', false); // Default to false if append is not provided

        // Start the transaction
        DB::beginTransaction();

        try {

            if (! $append) {
                // If append is false, delete existing relationships in the device_tag table
                DB::table('device_tag')->whereIn('device_id', $device_ids)->delete();
            }

            // Insert the new relationships in the device_tag table
            foreach ($device_ids as $device_id) {
                $device = Device::find($device_id);

                if ($append) {
                    // Attach only tags that aren't already attached
                    $existingTags = $device->tag()->pluck('id')->toArray();
                    $newTags = array_diff($tag_ids, $existingTags); // Filter out existing tags
                    $device->tag()->attach($newTags);
                } else {
                    // If not appending, attach all tags
                    $device->tag()->attach($tag_ids);
                }
            }

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices tags updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices: ' . $e->getMessage());
        }
    }

    public function updateCredentials()
    {
        $this->validate(request(), [
            'cred_id' => 'required|integer',
            'cred_id.*' => 'exists:device_credentials,id',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $cred_id = request('cred_id');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_cred_id field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_cred_id' => $cred_id]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateDevice_main_prompt()
    {
        $this->validate(request(), [
            'device_main_prompt' => 'required|string',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $device_main_prompt = request('device_main_prompt');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_main_prompt field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_main_prompt' => $device_main_prompt]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateDevice_enable_prompt()
    {
        $this->validate(request(), [
            'device_enable_prompt' => 'required|string',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $device_enable_prompt = request('device_enable_prompt');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_enable_prompt field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_enable_prompt' => $device_enable_prompt]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }

    public function updateModel()
    {
        $this->validate(request(), [
            'device_model' => 'required|string',
            'device_ids' => 'required|array',
            'device_ids.*' => 'exists:devices,id',
        ]);

        $device_model = request('device_model');
        $device_ids = request('device_ids');

        // Start the transaction
        DB::beginTransaction();

        try {
            // Update the device_model field in the devices table
            $this->model->whereIn('id', $device_ids)->update(['device_model' => $device_model]);

            // Commit the transaction
            DB::commit();

            return $this->successResponse('Devices updated successfully', $device_ids);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            DB::rollBack();

            // Handle the error, e.g., return an error response or rethrow the exception
            return $this->failureResponse('Failed to update devices' . $e->getMessage());
        }
    }
}
