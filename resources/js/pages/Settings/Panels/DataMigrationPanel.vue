<script setup>
import { Separator } from "@/components/ui/separator";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { ExternalLink, Boxes, Database, TimerReset } from "lucide-vue-next";

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

				<div class="grid w-full max-w-full items-center gap-1.5 text-muted-foreground text-md"></div>

				<Separator class="my-6" />

				<div class="flex items-start gap-4">
					<div class="w-full">
						<Card class="mt-4">
							<CardContent>
								<div class="space-y-6 mt-4">
									<!-- DEVICE SYNC -->
									<div class="rounded-md border p-4">
										<div class="flex items-center">
											<div class="flex-shrink-0">
												<Boxes />
											</div>
											<div class="ml-2 flex items-center justify-between w-full">
												<div>
													<h3 class="text-sm font-medium text-muted-foreground">OXIDIZED</h3>
												</div>
												<div class="flex items-center justify-end w-full">
													<Button variant="outline" size="sm" @click="openExtLink($rconfigDocsUrl + '/integrations/data-import/oxydized-import/')" class="flex items-center gap-1 text-xs">
														Configure
														<ExternalLink class="w-4 h-4" />
													</Button>
												</div>
											</div>
										</div>
										<p class="mt-2 text-sm text-rcgray-300">
											Import devices from Oxidized into rConfig with precision and speed. This CLI tool enables both bulk migrations and targeted imports of specific device groups, giving you complete control over your transition.
										</p>
									</div>
								</div>
							</CardContent>
						</Card>
					</div>
				</div>
				<Separator class="my-6" />

                <div class="flex items-start gap-4">
					<div class="w-full">
						<Card class="mt-4">
							<CardContent>
								<div class="space-y-6 mt-4">
									<!-- DEVICE SYNC -->
									<div class="rounded-md border p-4">
										<div class="flex items-center">
											<div class="flex-shrink-0">
												<Database color="#f5a97f" />
											</div>
											<div class="ml-2 flex items-center justify-between w-full">
												<div>
													<h3 class="text-sm font-medium text-muted-foreground">RANCID</h3>
												</div>
												<div class="flex items-center justify-end w-full">
													<Button variant="outline" size="sm" @click="openExtLink($rconfigDocsUrl + '/integrations/data-import/rancid-import/')" class="flex items-center gap-1 text-xs">
														Configure
														<ExternalLink class="w-4 h-4" />
													</Button>
												</div>
											</div>
										</div>
										<p class="mt-2 text-sm text-rcgray-300">
											Import devices from Rancid into rConfig with precision and speed. This CLI tool enables both bulk migrations and targeted imports of specific device groups, giving you complete control over your transition.
										</p>
									</div>
								</div>
							</CardContent>
						</Card>
					</div>
				</div>
                <Separator class="my-6" />
				<!-- Cards Section - Similar to Integrations/Main.vue -->
				<div class="grid grid-cols-1 gap-6">
					<!-- Placeholder Card for Future Import Options -->
					<Card class="opacity-50">
						<CardHeader>
							<div class="flex items-center justify-between">
								<div>
									<CardTitle>More Options Coming</CardTitle>
									<CardDescription class="mt-1.5">Additional import tools</CardDescription>
								</div>
								<div class="h-8 w-8 rounded-full flex items-center justify-center bg-primary/10">
									<TimerReset class="h-4 w-4 text-primary" />
								</div>
							</div>
						</CardHeader>
						<CardContent>
							<p class="text-sm text-muted-foreground">
								More import options for additional NCM systems coming soon.
							</p>
						</CardContent>
						<!-- <CardFooter>
							<Button variant="outline" class="w-full" disabled>
								<span class="mr-auto">Coming Soon</span>
							</Button>
						</CardFooter> -->
					</Card>
				</div>
			</div>
		</div>
	</div>
</template>
