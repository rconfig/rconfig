<script setup>
import { ref } from "vue";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Check, Copy } from "lucide-vue-next";
import { useCopy } from "@/composables/useCopy";
import { useRestApi } from "@/pages/Settings/Panels/Components/useRestApi";

const { createToken, isSubmitting } = useRestApi();
const { copyItem, activeCopyIcon } = useCopy();

const open = ref(false);
const tokenName = ref("");
const generatedToken = ref("");

async function generate() {
  if (!tokenName.value) {
    return;
  }
  const result = await createToken(tokenName.value);
  if (result?.api_token) {
    generatedToken.value = result.api_token;
  }
}

function onOpenChange(value) {
  open.value = value;
  if (!value) {
    // Reset on close; the plaintext token cannot be revealed again.
    tokenName.value = "";
    generatedToken.value = "";
  }
}
</script>

<template>
	<Dialog
		:open="open"
		@update:open="onOpenChange"
	>
		<DialogTrigger as-child>
			<Button>New Token</Button>
		</DialogTrigger>
		<DialogContent>
			<DialogHeader>
				<DialogTitle>Create API Token</DialogTitle>
				<DialogDescription>
					Give the token a memorable name. The token value is shown only once,
					so copy it now.
				</DialogDescription>
			</DialogHeader>

			<div
				v-if="!generatedToken"
				class="space-y-2"
			>
				<Label for="api_token_name">Token name</Label>
				<Input
					id="api_token_name"
					v-model="tokenName"
					placeholder="e.g. Automation script"
					@keyup.enter="generate"
				/>
			</div>

			<div
				v-else
				class="space-y-2"
			>
				<Label>Your new token</Label>
				<div class="flex items-center space-x-2">
					<Input
						:model-value="generatedToken"
						readonly
						class="font-mono text-xs"
					/>
					<Button
						variant="outline"
						size="icon"
						@click="copyItem('token', generatedToken)"
					>
						<Check
							v-if="activeCopyIcon['token']"
							class="w-4 h-4"
						/>
						<Copy
							v-else
							class="w-4 h-4"
						/>
					</Button>
				</div>
				<p class="text-xs text-muted-foreground">
					Store this token securely. You will not be able to see it again.
				</p>
			</div>

			<DialogFooter>
				<Button
					v-if="!generatedToken"
					:disabled="isSubmitting || !tokenName"
					@click="generate"
				>
					Generate Token
				</Button>
				<Button
					v-else
					variant="secondary"
					@click="onOpenChange(false)"
				>
					Done
				</Button>
			</DialogFooter>
		</DialogContent>
	</Dialog>
</template>
