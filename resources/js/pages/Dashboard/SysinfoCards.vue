<script setup>
import { useClipboard } from '@vueuse/core';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { ref } from 'vue';

const hoverIcons = ref({});
const activeIcons = ref({});
const { text, copy, copied, isSupported } = useClipboard();
const emit = defineEmits(['refresh']);

const copyItem = async (key, value) => {
  try {
    copy(value);
    activeIcons.value[key] = true;
    setTimeout(() => {
      activeIcons.value[key] = false;
    }, 1500);
  } catch (e) {
    console.error('Failed to copy:', e);
  }
};

const handleMouseOver = key => {
  hoverIcons.value[key] = true;
};

const handleMouseLeave = key => {
  hoverIcons.value[key] = false;
};

defineProps({
  sysinfo: {
    type: Object,
    required: true
  },
  isLoadingSysinfo: {
    type: Boolean,
    required: true
  }
});

function refresh() {
  emit('refresh');
}
</script>

<template>
  <div>
    <Card class="overflow-hidden">
      <CardHeader class="flex flex-row items-start bg-muted/50">
        <div class="grid gap-0.5">
          <CardTitle class="flex items-center gap-2 text-lg group">System Details</CardTitle>
          <CardDescription>Useful system information for support</CardDescription>
        </div>
        <div class="flex items-center gap-1 ml-auto">
          <Button
            @click="refresh()"
            size="sm"
            variant="outline"
            class="gap-1 hover:bg-rcgray-800">
            <Icon
              icon="flat-color-icons:refresh"
              class="hover:animate-pulse" />
          </Button>
        </div>
      </CardHeader>
      <CardContent class="text-sm">
        <div
          class="flex items-start w-full space-x-4"
          v-if="isLoadingSysinfo">
          <Skeleton class="w-12 h-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
            <Skeleton class="w-[400px] h-4" />
          </div>
        </div>

        <div
          class="grid gap-3"
          v-if="!isLoadingSysinfo">
          <dl class="grid gap-3">
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">OSVersion</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.OSVersion }}
                <Icon
                  :icon="activeIcons['OSVersion'] ? 'material-symbols:check-circle-outline' : hoverIcons['OSVersion'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['OSVersion'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('OSVersion', sysinfo.OSVersion)"
                  @mouseover="handleMouseOver('OSVersion')"
                  @mouseleave="handleMouseLeave('OSVersion')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">localIp</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.localIp }}
                <Icon
                  :icon="activeIcons['localIp'] ? 'material-symbols:check-circle-outline' : hoverIcons['localIp'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['localIp'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('localIp', sysinfo.localIp)"
                  @mouseover="handleMouseOver('localIp')"
                  @mouseleave="handleMouseLeave('localIp')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">PublicIP</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.PublicIP }}
                <Icon
                  :icon="activeIcons['PublicIP'] ? 'material-symbols:check-circle-outline' : hoverIcons['PublicIP'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['PublicIP'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('PublicIP', sysinfo.PublicIP)"
                  @mouseover="handleMouseOver('PublicIP')"
                  @mouseleave="handleMouseLeave('PublicIP')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">ServerName</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.ServerName }}
                <Icon
                  :icon="activeIcons['ServerName'] ? 'material-symbols:check-circle-outline' : hoverIcons['ServerName'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['ServerName'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('ServerName', sysinfo.ServerName)"
                  @mouseover="handleMouseOver('ServerName')"
                  @mouseleave="handleMouseLeave('ServerName')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">PHPVersion</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.PHPVersion }}
                <Icon
                  :icon="activeIcons['PHPVersion'] ? 'material-symbols:check-circle-outline' : hoverIcons['PHPVersion'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['PHPVersion'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('PHPVersion', sysinfo.PHPVersion)"
                  @mouseover="handleMouseOver('PHPVersion')"
                  @mouseleave="handleMouseLeave('PHPVersion')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">RedisVersion</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.RedisVersion }}
                <Icon
                  :icon="activeIcons['RedisVersion'] ? 'material-symbols:check-circle-outline' : hoverIcons['RedisVersion'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['RedisVersion'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('RedisVersion', sysinfo.RedisVersion)"
                  @mouseover="handleMouseOver('RedisVersion')"
                  @mouseleave="handleMouseLeave('RedisVersion')" />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-muted-foreground">MySQLVersion</dt>
              <dd class="flex items-center gap-2">
                {{ sysinfo.MySQLVersion }}
                <Icon
                  :icon="activeIcons['MySQLVersion'] ? 'material-symbols:check-circle-outline' : hoverIcons['MySQLVersion'] ? 'material-symbols:content-copy' : 'material-symbols:content-copy-outline'"
                  :class="activeIcons['MySQLVersion'] ? 'text-green-500' : 'text-gray-500'"
                  class="cursor-pointer hover:text-gray-700"
                  @click="copyItem('MySQLVersion', sysinfo.MySQLVersion)"
                  @mouseover="handleMouseOver('MySQLVersion')"
                  @mouseleave="handleMouseLeave('MySQLVersion')" />
              </dd>
            </div>
          </dl>
        </div>
      </CardContent>
      <CardFooter class="flex flex-row items-center px-6 py-3 border-t bg-muted/50">
        <div class="flex items-center gap-2 text-xs text-muted-foreground">
          <Icon icon="catppuccin:laravel" />
          Laravel Version: {{ sysinfo.LaravelVersion }}
        </div>
      </CardFooter>
    </Card>
  </div>
</template>
