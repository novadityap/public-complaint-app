'use client';

import {
  useShowResponseQuery,
  useCreateResponseMutation,
  useUpdateResponseMutation,
} from '@/services/complaintApi';
import { Button } from '@/components/shadcn/button';
import {
  Form,
  FormField,
  FormLabel,
  FormMessage,
  FormItem,
  FormControl,
} from '@/components/shadcn/form';
import { Skeleton } from '@/components/shadcn/skeleton';
import { TbLoader } from 'react-icons/tb';
import useFormHandler from '@/hooks/useFormHandler';
import { Textarea } from '@/components/shadcn/textarea';
import {
  Select,
  SelectContent,
  SelectTrigger,
  SelectValue,
  SelectItem,
} from '@/components/shadcn/select';
import { toast } from 'react-hot-toast';
import { useEffect } from 'react';

const ResponseFormSkeleton = () => (
  <div className="space-y-4">
    <Skeleton className="h-4 w-20" />
    <Skeleton className="h-10 w-full" />
    <Skeleton className="h-4 w-20" />
    <Skeleton className="h-10 w-full" />
    <div className="flex justify-end gap-2">
      <Skeleton className="h-10 w-24 rounded-md" />
      <Skeleton className="h-10 w-24 rounded-md" />
    </div>
  </div>
);
const ResponseForm = ({
  complaintId,
  id: responseId,
  isUpdate,
  onClose,
  onSuccess,
}) => {
  const { data: response, isLoading: isResponseLoading } = useShowResponseQuery(
    responseId,
    {
      skip: !isUpdate || !complaintId || !responseId,
      refetchOnMountOrArgChange: true,
    }
  );
  const { form, handleSubmit, isLoading } = useFormHandler({
    isUpdate,
    mutation: isUpdate ? useUpdateResponseMutation : useCreateResponseMutation,
    onSuccess: result => {
      onSuccess();
      toast.success(result.message);
    },
    onError: e => toast.error(e.message),
    defaultValues: {
      message: '',
      status: '',
    },
    params: [
      ...(!isUpdate ? [{ name: 'complaintId', value: complaintId }] : []),
      ...(isUpdate ? [{ name: 'responseId', value: responseId }] : []),
    ],
  });

  useEffect(() => {
    if (isUpdate && response?.data) {
      form.reset({
        message: response.data.message,
        status: response.data.complaint.status,
      });
    }
  }, [response]);

  if (isResponseLoading) return <ResponseFormSkeleton />;

  return (
    <div className="mt-6">
      <Form {...form}>
        <form className="space-y-4" onSubmit={handleSubmit}>
          <FormField
            control={form.control}
            name="message"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Message</FormLabel>
                <FormControl>
                  <Textarea placeholder="Write your response..." {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="status"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Status</FormLabel>
                <Select
                  key={field.value}
                  value={field.value}
                  onValueChange={field.onChange}
                >
                  <FormControl>
                    <SelectTrigger className="w-full">
                      <SelectValue placeholder="Select a status of complaint" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="in_progress">In Progress</SelectItem>
                    <SelectItem value="resolved">Resolved</SelectItem>
                  </SelectContent>
                </Select>
                <FormMessage />
              </FormItem>
            )}
          />
          <div className="flex justify-end gap-x-2">
            <Button variant="secondary" type="button" onClick={onClose}>
              Cancel
            </Button>
            <Button type="submit" disabled={isLoading}>
              {isLoading ? (
                <>
                  <TbLoader className="animate-spin" />
                  {isUpdate ? 'Updating..' : 'Creating..'}
                </>
              ) : isUpdate ? (
                'Update'
              ) : (
                'Create'
              )}
            </Button>
          </div>
        </form>
      </Form>
    </div>
  );
};

export default ResponseForm;
