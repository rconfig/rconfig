<script setup>
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import Spinner from '@/pages/Shared/Icon/Spinner.vue';
import { useSystemSettingsEmail } from '@/pages/Settings/Panels/Components/useSystemSettingsEmail';
const { settings, test1Loading, test2Loading, testEmail, updateEmail } = useSystemSettingsEmail();

const props = defineProps({});
</script>

<template>
  <div class="grid w-full max-w-full items-center gap-1.5">
    <h3 class="mb-2 text-2xl font-semibold leading-7 tracking-tight font-inter">Email</h3>
    <p class="text-sm text-muted-foreground">Configure the email settings for the system notifications.</p>

    <div class="grid gap-2 py-4">
      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="mail_host"
          class="text-right">
          Mail Host
          <span class="text-red-600">*</span>
        </Label>
        <Input
          id="mail_host"
          v-model="settings.mail_host"
          autocomplete="off"
          class="col-span-3" />
      </div>

      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="mail_port"
          class="text-right">
          Mail Port
          <span class="text-red-600">*</span>
        </Label>
        <Input
          id="mail_port"
          type="number"
          v-model="settings.mail_port"
          autocomplete="off"
          class="col-span-3" />
      </div>

      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="mail_from_email"
          class="text-right">
          Mail From Email
          <span class="text-red-600">*</span>
        </Label>
        <Input
          id="mail_from_email"
          v-model="settings.mail_from_email"
          autocomplete="off"
          class="col-span-3" />
      </div>

      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="mail_to_email"
          class="text-right">
          Mail To Email
          <span class="text-red-600">*</span>
        </Label>
        <Input
          id="mail_to_email"
          v-model="settings.mail_to_email"
          autocomplete="off"
          class="col-span-3" />
      </div>

      <div class="grid items-center grid-cols-4 gap-4">
        <Label
          for="mail_authcheck"
          class="text-right">
          Mail Auth Check
          <span class="text-red-600">*</span>
        </Label>
        <input
          type="checkbox"
          value=""
          class="sr-only peer" />
        <label class="inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            value=""
            :checked="settings.mail_authcheck === 1 ? true : false"
            v-model="settings.mail_authcheck"
            class="sr-only peer" />
          <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-0 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
        </label>
      </div>

      <transition name="fade">
        <div
          class="grid gap-2 pt-2"
          v-if="settings.mail_authcheck">
          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="mail_username"
              class="text-right">
              Mail Username
              <span class="text-red-600">*</span>
            </Label>
            <Input
              id="mail_username"
              v-model="settings.mail_username"
              autocomplete="off"
              class="col-span-3" />
          </div>

          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="mail_password"
              class="text-right">
              Mail Password
              <span class="text-red-600">*</span>
            </Label>
            <Input
              id="mail_password"
              v-model="settings.mail_password"
              autocomplete="off"
              class="col-span-3" />
          </div>

          <div class="grid items-center grid-cols-4 gap-4">
            <Label
              for="mail_encryption"
              class="text-right">
              Mail Encryption
              <span class="text-red-600">*</span>
            </Label>

            <Select v-model="settings.mail_encryption">
              <SelectTrigger class="w-full focus:outline-none focus:ring-0">
                <SelectValue placeholder="Select an encryption option.." />
              </SelectTrigger>
              <SelectContent class="">
                <SelectGroup>
                  <SelectLabel>-- Select an option --</SelectLabel>
                  <SelectItem value="tls">TLS</SelectItem>
                  <SelectItem value="ssl">SSL</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>
      </transition>
    </div>

    <div class="flex justify-between">
      <div class="flex justify-end jap-2">
        <Button
          variant="outline"
          class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800"
          @click.prevent="testEmail('email')"
          size="sm">
          <Spinner :state="test1Loading" />
          <Icon
            v-if="!test1Loading"
            icon="fluent-color:mail-multiple-32"
            class="w-4 h-4 mr-2" />
          Send Test Email
        </Button>
        <Button
          variant="outline"
          class="px-2 py-1 ml-2 text-sm bg-rcgray-900 hover:bg-rcgray-800"
          @click.prevent="testEmail('notification')"
          size="sm">
          <Spinner :state="test2Loading" />
          <Icon
            v-if="!test2Loading"
            icon="fluent-color:alert-32"
            class="w-4 h-4 mr-2" />
          Send Test Notification
        </Button>
      </div>
      <div class="flex justify-end jap-2">
        <Button
          variant="outline"
          class="px-2 py-1 ml-2 text-sm bg-blue-600 hover:bg-blue-700"
          @click.prevent="updateEmail()"
          size="sm">
          Save
        </Button>
      </div>
    </div>
  </div>
  <Separator class="my-6" />
</template>
