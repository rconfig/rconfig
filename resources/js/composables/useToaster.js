// useToaster.ts
import { useToast } from '@/components/ui/toast/use-toast';

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
