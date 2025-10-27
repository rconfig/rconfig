// useToaster.ts
import { useToast } from "@/components/ui/toast/use-toast";

// function toastTest() {
//   toastDefault('Default Message', 'There was a problem with your request.');
//   toastError('Uh oh! Something went wrong.', 'There was a problem with your request.');
//   toastWarning('Uh oh! Something went wrong.', 'There was a problem with your request.');
//   toastInfo('Info: Happy days', 'There was a Info with your request.');
//   toastSuccess('Success: Happy days', 'There was a Success with your request.');
// }

export function useToaster() {
	const { toast } = useToast();

	function toastDefault(title, description, duration = 3000) {
		const { title: processedTitle, description: processedDescription } = setTitle(title, description);
		toast({
			title: processedTitle,
			description: processedDescription,
			variant: "info",
			duration: duration,
			class: "mt-2 text-white toast-info", // Updated for gradient info toast
		});
	}

	function toastSuccess(title, description, duration = 3000) {
		const { title: processedTitle, description: processedDescription } = setTitle(title, description);
		toast({
			title: processedTitle,
			description: processedDescription,
			variant: "success",
			duration: duration,
			class: "mt-2 text-white toast-success", // Updated for gradient success toast
		});
	}

	function toastError(title, description, duration = 3000) {
		const { title: processedTitle, description: processedDescription } = setTitle(title, description);
		toast({
			title: processedTitle,
			description: processedDescription,
			variant: "destructive",
			duration: duration,
			class: "mt-2 text-white toast-error", // Updated for gradient error toast
		});
	}

	function toastInfo(title, description, duration = 3000) {
		const { title: processedTitle, description: processedDescription } = setTitle(title, description);
		toast({
			title: processedTitle,
			description: processedDescription,
			variant: "info",
			duration: duration,
			class: "mt-2 text-white toast-info", // Updated for gradient info toast
		});
	}

	function toastWarning(title, description, duration = 3000) {
		const { title: processedTitle, description: processedDescription } = setTitle(title, description);
		toast({
			title: processedTitle,
			description: processedDescription,
			variant: "warning",
			duration: duration,
			class: "mt-2 text-white toast-warning", // Updated for gradient warning toast
		});
	}

	function setTitle(title, description) {
		if (title && !description) {
			return {
				title: null,
				description: title,
			};
		}
		return {
			title,
			description,
		};
	}

	return {
		toastDefault,
		toastSuccess,
		toastError,
		toastInfo,
		toastWarning,
		setTitle,
	};
}
