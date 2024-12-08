// useToaster.ts
import { useToast } from '@/components/ui/toast/use-toast';

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
    toast({
      title,
      description,
      duration: duration
    });
  }

  function toastSuccess(title, description, duration = 3000) {
    toast({
      title,
      description,
      variant: 'success',
      duration: duration
    });
  }

  function toastError(title, description, duration = 3000) {
    toast({
      title,
      description,
      variant: 'destructive',
      duration: duration
    });
  }

  function toastInfo(title, description, duration = 3000) {
    toast({
      title,
      description,
      variant: 'info',
      duration: duration
    });
  }

  function toastWarning(title, description, duration = 3000) {
    toast({
      title,
      description,
      variant: 'warning',
      duration: duration
    });
  }

  return {
    toastDefault,
    toastSuccess,
    toastError,
    toastInfo,
    toastWarning
  };
}
