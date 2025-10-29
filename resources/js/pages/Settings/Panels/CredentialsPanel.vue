<script setup>
import CredentialsTable from '@/pages/Settings/Panels/Components/CredentialsTable.vue';
</script>

<template>
  <div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 md:w-full">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="flex items-center rc-panel-heading">
          <RcIcon name="credentials" class="mr-2" />
          Shared Credentials
        </h3>
        <p class="text-sm text-muted-foreground">Manage and store sets of credentials securely for re-use across device inventory.</p>

        <CredentialsTable />
      </div>
    </div>
  </div>
</template>
