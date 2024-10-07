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

  function toastDefault(title, description) {
    toast({
      title,
      description
    });
  }

  function toastSuccess(title, description) {
    toast({
      title,
      description,
      variant: 'success'
    });
  }

  function toastError(title, description) {
    toast({
      title,
      description,
      variant: 'destructive'
    });
  }

  function toastInfo(title, description) {
    toast({
      title,
      description,
      variant: 'info'
    });
  }

  function toastWarning(title, description) {
    toast({
      title,
      description,
      variant: 'warning'
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
