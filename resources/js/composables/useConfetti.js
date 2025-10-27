// composables/useConfetti.js
import confetti from "canvas-confetti";

export function useConfetti() {
	const celebrateStepCompletion = () => {
		// Create multiple bursts across the full width
		const burst1 = confetti({
			particleCount: 60,
			spread: 100,
			startVelocity: 45,
			origin: { y: 0, x: 0.2 }, // Left side
			colors: ["#ff006e", "#fb5607", "#ffbe0b", "#8338ec", "#3a86ff", "#06ffa5"],
		});

		const burst2 = confetti({
			particleCount: 60,
			spread: 100,
			startVelocity: 45,
			origin: { y: 0, x: 0.5 }, // Center
			colors: ["#ff4081", "#ff5722", "#ffc107", "#9c27b0", "#2196f3", "#00e676"],
		});

		const burst3 = confetti({
			particleCount: 60,
			spread: 100,
			startVelocity: 45,
			origin: { y: 0, x: 0.8 }, // Right side
			colors: ["#e91e63", "#ff9800", "#ffeb3b", "#673ab7", "#03a9f4", "#4caf50"],
		});

		return Promise.all([burst1, burst2, burst3]);
	};

	const celebrateAllStepsComplete = () => {
		// Gentle celebratory bursts with reduced intensity
		const duration = 2500;
		const end = Date.now() + duration;
		const colors = ["#ff1744", "#ff9800", "#ffc107", "#4caf50", "#00bcd4", "#2196f3", "#9c27b0", "#e91e63"];

		const frame = () => {
			// Reduced frequency and particle count for a more elegant effect
			if (Math.random() < 0.7) {
				// Only fire 70% of the time
				const side = Math.random();
				let origin;
				let colorSet;

				if (side < 0.33) {
					origin = { y: 0, x: Math.random() * 0.3 };
					colorSet = colors.slice(0, 3);
				} else if (side < 0.66) {
					origin = { y: 0, x: 0.35 + Math.random() * 0.3 };
					colorSet = colors.slice(2, 5);
				} else {
					origin = { y: 0, x: 0.7 + Math.random() * 0.3 };
					colorSet = colors.slice(4, 7);
				}

				confetti({
					particleCount: 15, // Reduced from 20-25
					spread: 70, // Reduced spread
					startVelocity: 25, // Reduced velocity
					origin: origin,
					colors: colorSet,
					gravity: 0.9,
				});
			}

			if (Date.now() < end) {
				requestAnimationFrame(frame);
			}
		};
		frame();
	};

	const celebrateWithFireworks = () => {
		// Firework-style burst across full width
		const firework = (x) => {
			confetti({
				particleCount: 80,
				spread: 120,
				startVelocity: 60,
				origin: { y: 0.1, x: x },
				colors: ["#ff0080", "#ff8c00", "#40e0d0", "#ee82ee", "#98fb98", "#f0e68c"],
				shapes: ["circle", "square"],
				scalar: 1.2,
			});
		};

		// Multiple fireworks across the width
		firework(0.1);
		setTimeout(() => firework(0.3), 150);
		setTimeout(() => firework(0.7), 300);
		setTimeout(() => firework(0.9), 450);
		setTimeout(() => firework(0.5), 600);
	};

	return {
		celebrateStepCompletion,
		celebrateAllStepsComplete,
		celebrateWithFireworks,
	};
}
