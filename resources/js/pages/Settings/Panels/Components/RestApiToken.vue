<script setup>
import { onMounted } from "vue";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Trash2 } from "lucide-vue-next";
import RestApiTokenModal from "@/pages/Settings/Panels/Components/RestApiTokenModal.vue";
import { useRestApi } from "@/pages/Settings/Panels/Components/useRestApi";

const { tokens, isLoading, fetchTokens, deleteToken } = useRestApi();

onMounted(fetchTokens);

function confirmDelete(token) {
  if (
    window.confirm(
      `Delete API token "${token.api_token_name}"? Any client using it will stop working.`,
    )
  ) {
    deleteToken(token.id);
  }
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">
        Tokens authenticate external requests to the REST API via the
        <code>apitoken</code> header.
      </p>
      <RestApiTokenModal />
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Name</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Created</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-if="isLoading">
          <TableCell colspan="4" class="text-center text-muted-foreground"
            >Loading…</TableCell
          >
        </TableRow>
        <TableRow v-else-if="tokens.length === 0">
          <TableCell colspan="4" class="text-center text-muted-foreground"
            >No API tokens yet.</TableCell
          >
        </TableRow>
        <TableRow v-for="token in tokens" :key="token.id">
          <TableCell class="font-medium">{{ token.api_token_name }}</TableCell>
          <TableCell>
            <Badge :variant="token.api_token_status ? 'default' : 'secondary'">
              {{ token.api_token_status ? "Active" : "Disabled" }}
            </Badge>
          </TableCell>
          <TableCell>{{ token.created_at }}</TableCell>
          <TableCell class="text-right">
            <Button variant="ghost" size="icon" @click="confirmDelete(token)">
              <Trash2 class="w-4 h-4 text-destructive" />
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
