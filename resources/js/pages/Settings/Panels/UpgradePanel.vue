<script setup>
import { Separator } from "@/components/ui/separator";
</script>

<template>
	<div class="flex justify-center w-full">
		<div class="flex flex-col items-center w-full gap-4 xl:w-3/4 w-full">
			<div class="grid w-full max-w-full items-center gap-1.5">
				<div class="flex flex-col items-center pt-8 xl:flex-row">
					<div class="flex flex-col items-center mb-16 text-center lg:flex-grow xl:w-1/2 xl:pr-16 md:items-start md:text-left md:mb-0">
						<h1 class="mb-4 text-2xl font-medium title-font sm:text-4xl font-sora">Unlock the Full Power of rConfig</h1>
						<p class="text-justify mb-8 leading-relaxed font-jakarta">
						  Take your network management experience even further with your rConfig Professional subscription. You're already enjoying premium features like advanced NCM capabilities and priority support.
						</p>
						<p class="text-justify mb-8 leading-relaxed font-jakarta">
							For organizations looking to scale beyond 1,000 devices or requiring custom solutions, consider upgrading to rConfig Enterprise for dedicated account management, custom onboarding, enhanced support options, and full access to the rConfig Vector suite of products and capabilities, and infrastructure built for enterprise-scale deployments. Maximize the value of your current Professional license or explore our Enterprise solutions to meet your growing network demands.
						</p>
					</div>
					<div class="w-full sm:w-5/6 md:w-1/2 lg:max-w-lg mx-auto lg:mx-0 overflow-hidden rounded">
						<img class="object-cover object-center w-full h-auto shadow-[0_0_30px_rgba(59,130,246,0.3)] transition-all duration-700 hover:shadow-[0_0_40px_rgba(59,130,246,0.5)]" alt="hero" src="/images/brand_pic1.png" />
					</div>
				</div>
				<Separator class="my-6" />

				<div class="flex flex-col items-center justify-start w-full gap-4">
					<div class="flex flex-col md:flex-row items-stretch justify-center w-full gap-4">
						<div class="flex flex-1 flex-col items-start justify-start gap-5 w-full border border-[#45474A] p-4 rounded-2xl h-full">
							<!-- Core Section -->
							<div class="flex flex-col items-center justify-start w-full gap-4">
								<div class="flex flex-col items-start justify-start gap-2.5 w-full">
									<div class="relative flex">
										<div data-variant="enterprise" class="absolute top-0 left-0 flex items-center justify-center"></div>
										<img src="/images/brand/v8core/rconfig_v8_core_logo_without_strapline_gradient.svg" alt="enterprise" class="w-36 h-10" />
									</div>
									<div class="tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-sora">V8 Core</div>
								</div>
								<div class="flex flex-col items-start justify-start w-full gap-0">
									<div class="tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta">Free</div>
									<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta" style="color: rgb(134, 136, 141);">
										Unlimited devices and backups
									</div>
								</div>
							</div>
							<button type="button" class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-gray-700 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-gray-600">
								<div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">No Action</div>
							</button>
							<div class="flex flex-col items-start justify-start w-full gap-2 font-jakarta">
								<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" style="color: rgb(238, 239, 241);">
									For hobbyists, and SMEs
								</div>
								<div class="flex flex-col items-center justify-start gap-2.5">
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Unlimted device backups
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Search and restore
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											LDAP and SSO
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Open source
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="flex flex-1 flex-col items-start justify-start gap-5 w-full border border-[#45474A] p-4 rounded-2xl h-full">
							<!-- Pro Section -->
							<div class="flex flex-col items-center justify-start w-full gap-4">
								<div class="flex flex-col items-start justify-start gap-2.5 w-full">
									<div class="relative flex">
										<div data-variant="pro" class="absolute top-0 left-0 flex items-center justify-center"></div>
										<img src="/images/brand/v8core/rconfig_v8_pro_logo_without_strapline_gradient.svg" alt="professional" class="w-24 h-10" />
									</div>
									<div class="tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-sora">V8 Professional</div>
								</div>
								<div class="flex flex-col items-start justify-start w-full gap-0">
									<div class="tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta">€PAID</div>
									<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta" style="color: rgb(134, 136, 141);">
										billed annually
									</div>
								</div>
							</div>
							<a type="button" href="https://www.rconfig.com/contact-us" target="_blank" class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-blue-600 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-blue-500">
								<div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">Talk to sales</div>
							</a>
							<div class="flex flex-col items-start justify-start w-full gap-2 font-jakarta">
								<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" style="color: rgb(238, 239, 241);">
									For mid size companies
								</div>
								<div class="flex flex-col items-center justify-start gap-2.5">
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Up to 1,000 devices
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Advanced NCM features
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Advanced roadmap
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Priority support
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="flex flex-1 flex-col items-start justify-start gap-5 w-full border border-[#45474A] p-4 rounded-2xl h-full">
							<!-- Enterprise Section -->
							<div class="flex flex-col items-center justify-start w-full gap-4">
								<div class="flex flex-col items-start justify-start gap-2.5 w-full">
									<div class="relative flex">
										<div data-variant="enterprise" class="absolute top-0 left-0 flex items-center justify-center"></div>
										<img src="/images/brand/gradient_enterprise_logo.svg" alt="enterprise" class="w-36 h-10" />
									</div>
									<div class="tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-sora">rConfig Enterprise</div>
								</div>
								<div class="flex flex-col items-start justify-start w-full gap-0">
									<div class="tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta">Call us</div>
									<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit font-jakarta" style="color: rgb(134, 136, 141);">
										For custom pricing
									</div>
								</div>
							</div>
							<a type="button" href="https://www.rconfig.com/contact-us" target="_blank" class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-blue-600 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-blue-500">
								<div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">Talk to sales</div>
							</a>
							<div class="flex flex-col items-start justify-start w-full gap-2 font-jakarta">
								<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" style="color: rgb(238, 239, 241);">
									For mid to large organizations
								</div>
								<div class="flex flex-col items-center justify-start gap-2.5">
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Custom onboarding
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Priority feature requests
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Scale to 100,000+ devices
										</div>
									</div>
									<div class="flex flex-row items-center justify-start w-full gap-1">
										<div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
											<RcIcon name="tick" :color="'text-rcgray-400'" :height="16" />
										</div>
										<div class="tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit" data-numeric="false" data-uppercase="false">
											Dedicated account manager
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<Separator class="my-6" />
			</div>
		</div>
	</div>
</template>
