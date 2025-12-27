<script setup>
import { ref, onMounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Mail, Sparkles, TrendingUp, Bell, Zap } from 'lucide-vue-next';

const STORAGE_KEY = 'rconfig_newsletter_shown';

const showModal = ref(false);
const isSubmitting = ref(false);

onMounted(() => {
	const hasShown = localStorage.getItem(STORAGE_KEY);
	if (!hasShown) {
		setTimeout(() => {
			showModal.value = true;
		}, 2000);
	}
});

const handleClose = () => {
	showModal.value = false;
	localStorage.setItem(STORAGE_KEY, 'true');
};

const handleSubmit = async () => {
	isSubmitting.value = true;
	
	try {
		const newsletterWindow = window.open('https://www.rconfig.com/newsletter', '_blank');
		
		if (!newsletterWindow || newsletterWindow.closed || typeof newsletterWindow.closed === 'undefined') {
			console.error('Popup blocked - trying direct navigation');
			window.location.href = 'https://www.rconfig.com/newsletter';
		}
		
		setTimeout(() => {
			handleClose();
			isSubmitting.value = false;
		}, 500);
		
	} catch (error) {
		console.error('Error opening newsletter:', error);
		window.location.href = 'https://www.rconfig.com/newsletter';
		isSubmitting.value = false;
	}
};

const handleMaybeLater = () => {
	handleClose();
};
</script>

<template>
  <Dialog :open="showModal" @update:open="(val) => showModal = val">
    <DialogContent class="sm:max-w-lg shadow-2xl bg-card p-6">
      
      <!-- Content -->
      <div class="space-y-5">
        <DialogHeader class="text-center space-y-3">
          <!-- Icon with glow effect -->
          <div class="flex items-center justify-center gap-3">
            <div class="relative">
              <div class="absolute inset-0 bg-blue-500/20 rounded-full blur-xl"></div>
              <div class="relative p-2.5 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg">
                <Mail class="w-6 h-6 text-white" />
              </div>
              <Sparkles class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 text-yellow-400 animate-pulse" />
            </div>
            
            <DialogTitle class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Join the rConfig Community
            </DialogTitle>
          </div>
          
          <DialogDescription class="text-sm text-muted-foreground">
            Get exclusive insights, tips, and updates delivered straight to your inbox
          </DialogDescription>
        </DialogHeader>

        <!-- Feature cards -->
        <div class="grid gap-2.5">
          <div class="flex items-start gap-3 p-2.5 rounded-xl bg-muted/30 hover:bg-muted/50 transition-colors group">
            <div class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/50 group-hover:scale-110 transition-transform">
              <TrendingUp class="w-4 h-4 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-sm mb-0.5">Product Updates</h4>
              <p class="text-xs text-muted-foreground leading-snug">Be first to know about new features and improvements</p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-2.5 rounded-xl bg-muted/30 hover:bg-muted/50 transition-colors group">
            <div class="p-1.5 rounded-lg bg-purple-100 dark:bg-purple-900/50 group-hover:scale-110 transition-transform">
              <Zap class="w-4 h-4 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-sm mb-0.5">Best Practices</h4>
              <p class="text-xs text-muted-foreground leading-snug">Expert tips for network automation and configuration management</p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-2.5 rounded-xl bg-muted/30 hover:bg-muted/50 transition-colors group">
            <div class="p-1.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 group-hover:scale-110 transition-transform">
              <Bell class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-sm mb-0.5">Exclusive Content</h4>
              <p class="text-xs text-muted-foreground leading-snug">Tutorials, case studies, and industry insights</p>
            </div>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col gap-2.5 pt-1">
          <Button 
            @click="handleSubmit"
            class="w-full px-4 py-2.5 text-sm font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-[1.02]"
            size="sm"
            :disabled="isSubmitting"
          >
            <Mail class="w-4 h-4 mr-2" />
            {{ isSubmitting ? 'Opening Newsletter...' : 'Subscribe Now' }}
          </Button>
          
          <Button 
            @click="handleMaybeLater"
            variant="ghost"
            size="sm"
            class="w-full px-4 py-1.5 text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
          >
            Maybe Later
          </Button>
        </div>

        <!-- Trust indicator -->
        <div class="text-center pt-2 border-t border-muted/50">
          <p class="text-xs text-muted-foreground flex items-center justify-center gap-2">
            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Join 5,000+ network professionals • Unsubscribe anytime
          </p>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.animate-pulse {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth animations */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.grid > div {
  animation: slideUp 0.3s ease-out forwards;
}

.grid > div:nth-child(2) {
  animation-delay: 0.1s;
}

.grid > div:nth-child(3) {
  animation-delay: 0.2s;
}
</style>