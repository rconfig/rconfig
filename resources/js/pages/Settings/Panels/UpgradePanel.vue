<script setup>
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
</script>

<template>
  <div class="flex justify-center w-full">
    <div class="flex flex-col items-center w-full gap-4 md:w-3/4">
      <div class="grid w-full max-w-full items-center gap-1.5">
        <div class="flex flex-col items-center pt-8 xl:flex-row">
          <div class="flex flex-col items-center mb-16 text-center lg:flex-grow xl:w-1/2 xl:pr-16 md:items-start md:text-left md:mb-0">
            <h1 class="mb-4 text-3xl font-medium title-font sm:text-4xl">Unlock the Full Power of rConfig</h1>
            <p class="mb-8 leading-relaxed">Experience unmatched control and automation with rConfig Pro & Enterprise. Scale effortlessly, access advanced features, and ensure your network's security with priority support. Upgrade now to simplify your operations and take your network management to the next level.</p>
          </div>
          <div class="w-5/6 lg:max-w-lg lg:w-full md:w-1/2">
            <img
              class="object-cover object-center rounded"
              alt="hero"
              src="https://www.rconfig.com/images/featuressvgs/Dashboard.svg" />
          </div>
        </div>
        <Separator class="my-6" />

        <div class="flex flex-col items-center justify-start w-full gap-4">
          <div class="flex flex-row items-end justify-center w-full gap-4">
            <div class="flex flex-1 flex-col items-start justify-start gap-5 w-full border border-[#45474A] p-4 rounded-2xl h-full">
              <!-- Core Section -->
              <div class="flex flex-col items-center justify-start w-full gap-4">
                <div class="flex flex-col items-start justify-start gap-2.5 w-full">
                  <div class="relative flex">
                    <div class="absolute top-0 left-0 flex items-center justify-center w-10 h-10"></div>
                    <img
                      src="/images/new/white/hex_logo_white_original_48.png"
                      alt="plus"
                      class="w-10 h-10" />
                  </div>
                  <div class="font-sans tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">V8 Core</div>
                </div>
                <div class="flex flex-col items-start justify-start w-full gap-0">
                  <div class="font-sans tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">Free</div>
                  <div
                    class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                    style="color: rgb(134, 136, 141)">
                    Unlimited devices and backups
                  </div>
                </div>
              </div>
              <button
                type="button"
                class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-gray-700 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-gray-600">
                <div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">No Action</div>
              </button>
              <div class="flex flex-col items-start justify-start w-full gap-2">
                <div
                  class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                  style="color: rgb(238, 239, 241)">
                  For hobbyists, and small business
                </div>
                <div class="flex flex-col items-center justify-start gap-2.5">
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Unlimted device backups
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Search and restore
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      LDAP and SSO
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
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
                    <div
                      data-variant="pro"
                      class="absolute top-0 left-0 flex items-center justify-center w-10 h-10"></div>
                    <img
                      src="/images/new/blue/hex_logo_blue_48.png"
                      alt="professional"
                      class="w-10 h-10" />
                  </div>
                  <div class="font-sans tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">V7 Professional</div>
                </div>
                <div class="flex flex-col items-start justify-start w-full gap-0">
                  <div class="font-sans tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">€2,499</div>
                  <div
                    class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                    style="color: rgb(134, 136, 141)">
                    billed annually
                  </div>
                </div>
              </div>
              <a
                type="button"
                href="https://www.rconfig.com/demo-request"
                target="_blank"
                class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-blue-600 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-blue-500">
                <div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">Upgrade to Pro</div>
              </a>
              <div class="flex flex-col items-start justify-start w-full gap-2">
                <div
                  class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                  style="color: rgb(238, 239, 241)">
                  For scaling businesses
                </div>
                <div class="flex flex-col items-center justify-start gap-2.5">
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Up to 1,000 devices
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Advanced NCM features
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Advanced roadmap
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
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
                    <div
                      data-variant="enterprise"
                      class="absolute top-0 left-0 flex items-center justify-center w-10 h-10"></div>
                    <img
                      src=" /images/indigo/hex_logo_indigo_96.png"
                      alt="enterprise"
                      class="w-10 h-10" />
                  </div>
                  <div class="font-sans tracking-[-0.02em]text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">rConfig Enterprise</div>
                </div>
                <div class="flex flex-col items-start justify-start w-full gap-0">
                  <div class="font-sans tracking-[-0.02em] text-[#EEEFF1] font-normal text-[14px] leading-5 text-left text-inherit">Call us</div>
                  <div
                    class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                    style="color: rgb(134, 136, 141)">
                    For custom pricing
                  </div>
                </div>
              </div>
              <a
                type="button"
                href="https://www.rconfig.com/demo-request"
                target="_blank"
                class="m-0 outline-none border-none no-underline relative flex shrink-0 grow-0 items-center justify-center opacity-100 cursor-pointer gap-1.5 w-full bg-blue-600 p-1 rounded-lg shadow-[inset_0_0_0_1px_rgba(244,245,246,0.1)] hover:bg-blue-500">
                <div class="inline-block align-bottom overflow-hidden text-ellipsis whitespace-nowrap text-[14px] leading-5 font-medium tracking-[-0.02em] text-[#EEEFF1]">Talk to sales</div>
              </a>
              <div class="flex flex-col items-start justify-start w-full gap-2">
                <div
                  class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                  style="color: rgb(238, 239, 241)">
                  For mid to large organizations
                </div>
                <div class="flex flex-col items-center justify-start gap-2.5">
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Custom onboarding
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Priority feature requests
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
                      Scale to 100,000+ devices
                    </div>
                  </div>
                  <div class="flex flex-row items-center justify-start w-full gap-1">
                    <div class="flex flex-col items-center justify-center w-5 h-5 gap-1 rounded-md">
                      <Icon
                        icon="mdi:tick"
                        class="text-rcgray-400" />
                    </div>
                    <div
                      class="font-sans tracking-[-0.02em] text-[#9FA1A7] font-normal text-[14px] leading-5 text-left text-inherit"
                      data-numeric="false"
                      data-uppercase="false">
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
