<script setup>
import { Separator } from "@/components/ui/separator";
import { Card, CardContent } from "@/components/ui/card";
import { ExternalLink, Boxes, Database, Network } from "lucide-vue-next";

const importOptions = [
	{
		key: "oxidized",
		name: "OXIDIZED",
		docsPath: "/integrations/oxidized-import/",
		logoSrc: "/images/logos/oxidized.svg",
		Icon: Boxes,
		description: "Bring your existing Oxidized inventory into rConfig quickly and safely. This CLI tool supports both bulk migrations and targeted imports of specific device groups, keeping your cutover predictable, repeatable, and easy to validate.",
	},
	{
		key: "rancid",
		name: "RANCID",
		docsPath: "/integrations/rancid-import/",
		logoSrc: "/images/logos/rancid.svg",
		Icon: Database,
		description: "Import devices from RANCID into rConfig with precision and speed. Migrate entire estates or specific device groups while preserving structure, making it simple to transition without surprises.",
	},
	{
		key: "netmri",
		name: "NETMRI",
		docsPath: "/integrations/netmri-import/",
		logoSrc: "/images/logos/netmri.svg",
		Icon: Network,
		description: "Move from NetMRI to rConfig with a clean, auditable import. Pull in device inventory and core metadata so you can start collecting configs immediately after cutover.",
	},
	{
		key: "solarwinds",
		name: "SolarWinds NCM",
		docsPath: "/integrations/solarwinds-ncm-import/",
		logoSrc: "/images/logos/solarwinds.svg",
		Icon: Network,
		description: "Seamlessly transition from SolarWinds NCM to rConfig. Import your device inventory and essential metadata to quickly establish your configuration management workflows in rConfig.",
	},
];

function openExtLink(url) {
	window.open(url, "_blank");
}
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 lg:w-4/5 xl:w-3/5">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<h3 class="rc-panel-heading">
					Data Migration from Other NCMs
				</h3>
				<p class="text-sm text-muted-foreground">
					Migrating to rConfig? These tools allow you to import devices from other Network Configuration Management (NCM) systems into rConfig. Choose from the available import options below to get started.
				</p>

				<Separator class="my-6" />

				<div class="flex items-start gap-4">
					<div class="w-full">
						<Card class="mt-4">
							<CardContent>
								<div class="space-y-6 mt-4">
									<div v-for="opt in importOptions" :key="opt.key" class="rounded-md border p-4">
										<div class="flex items-center">
											<div class="flex-shrink-0">
												<img :src="opt.logoSrc" :alt="opt.name + ' logo'" class="h-7 w-7" loading="lazy" />
											</div>
											<div class="ml-2 flex items-center justify-between w-full">
												<div class="flex items-center gap-2">
													<h3 class="text-sm font-medium text-muted-foreground">{{ opt.name }}</h3>
													<component :is="opt.Icon" class="h-4 w-4 text-muted-foreground" />
												</div>
												<div class="flex items-center justify-end">
													<Button variant="outline" size="sm" @click="openExtLink($rconfigDocsUrl + opt.docsPath)" class="flex items-center gap-1 text-xs">
														Configure
														<ExternalLink class="w-4 h-4" />
													</Button>
												</div>
											</div>
										</div>
										<p class="mt-2 text-sm text-rcgray-300">
											{{ opt.description }}
										</p>
									</div>
								</div>
							</CardContent>
						</Card>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
