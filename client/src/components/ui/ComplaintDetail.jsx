import { useSelector } from 'react-redux';
import {
  useShowComplaintQuery,
  useShowResponseQuery,
  useListResponsesQuery,
  useCreateResponseMutation,
  useUpdateResponseMutation,
  useRemoveResponseMutation,
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
import {
  Card,
  CardContent,
} from '@/components/shadcn/card';
import { Separator } from '@/components/shadcn/separator';
import {
  TbPlus,
  TbMessage,
  TbHeading,
  TbFileText,
  TbUser,
  TbCalendarEvent,
  TbAlertTriangle,
  TbLoader,
  TbCircleCheck,
  TbEdit,
  TbTrash,
} from 'react-icons/tb';
import useFormHandler from '@/hooks/useFormHandler';
import dayjs from 'dayjs';
import { useState } from 'react';
import { Badge } from '@/components/shadcn/badge';
import { Textarea } from '@/components/shadcn/textarea';
import {
  Select,
  SelectContent,
  SelectTrigger,
  SelectValue,
  SelectItem,
} from '@/components/shadcn/select';
import RemoveConfirmModal from '@/components/ui/RemoveConfirmModal';
import CreateUpdateModal from '@/components/ui/CreateUpdateModal';
import { toast } from 'react-hot-toast';
import { useEffect, useMemo } from 'react';

const ComplaintDetailSkeleton = () => (
  <div className="space-y-4 mt-4">
    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
      {Array.from({ length: 4 }).map((_, i) => (
        <div key={i} className="flex items-start gap-3">
          <Skeleton className="h-6 w-6 rounded-full" />
          <div className="space-y-2 w-full">
            <Skeleton className="h-3 w-1/3" />
            <Skeleton className="h-4 w-full" />
          </div>
        </div>
      ))}
    </div>

    <Separator />

    <Skeleton className="h-5 w-32" />

    {Array.from({ length: 2 }).map((_, i) => (
      <Card key={i} className="rounded-xl">
        <CardContent className="space-y-3 py-4">
          <Skeleton className="h-4 w-full" />
          <Skeleton className="h-3 w-1/2" />
        </CardContent>
      </Card>
    ))}
  </div>
);

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

const ResponseList = ({ complaintId, responses }) => {
  const { currentUser } = useSelector(state => state.auth);
  const [removeResponse, { isLoading: isRemoveLoading }] =
    useRemoveResponseMutation();
  const [modalType, setModalType] = useState(null);
  const [selectedId, setSelectedId] = useState(null);
  const ResponseFormMemo = useMemo(() => {
    return props => <ResponseForm complaintId={complaintId} {...props} />;
  }, [complaintId]);

  const handleOpenModal = (type, id = null) => {
    setModalType(type);
    setSelectedId(id);
  };

  const handleCloseModal = () => {
    setModalType(null);
    setSelectedId(null);
  };

  const handleSubmitComplete = () => {
    handleCloseModal();
  };

  const handleRemoveConfirm = async () => {
    try {
      const result = await removeResponse({
        complaintId,
        responseId: selectedId,
      }).unwrap();
      toast.success(result.message);
      handleCloseModal();
    } catch (e) {
      toast.error('Failed to remove item');
    }
  };

  return (
    <>
      <div className="space-y-4 mt-6">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold">Responses</h3>
          {currentUser.role === 'admin' && (
            <Button
              size="sm"
              onClick={() => handleOpenModal('create')}
              className="gap-1"
            >
              <TbPlus className="size-5" />
              Add
            </Button>
          )}
        </div>

        {responses?.data?.length === 0 ? (
          <p className="text-muted-foreground text-center italic">
            There are no responses for this complaint yet.
          </p>
        ) : (
          responses?.data?.map(response => (
            <Card
              key={response.id}
              className="hover:shadow-md transition rounded-xl"
            >
              <CardContent className="space-y-2">
                <div className="flex items-start gap-3">
                  <TbMessage className="shrink-0 size-5 mt-1 text-primary" />
                  <p className="text-sm break-all">{response.message}</p>
                </div>

                <div className="text-sm flex flex-wrap gap-x-6 text-muted-foreground">
                  <span className="flex items-center gap-1">
                    <TbUser className="size-5 shrink-0 text-primary" />{' '}
                    {response.user.email}
                  </span>
                  <span className="flex items-center gap-1">
                    <TbCalendarEvent className="size-5 shrink-0 text-primary" />
                    {dayjs(response.created_at).format('DD MMM YYYY')}
                  </span>
                </div>

                {currentUser.role === 'admin' && (
                  <div className="flex justify-end gap-3 pt-2">
                    <TbEdit
                      className="size-5 cursor-pointer text-orange-600"
                      onClick={() => handleOpenModal('update', response.id)}
                    />
                    <TbTrash
                      className="size-5 cursor-pointer text-red-600"
                      onClick={() => handleOpenModal('remove', response.id)}
                    />
                  </div>
                )}
              </CardContent>
            </Card>
          ))
        )}
      </div>

      <CreateUpdateModal
        id={selectedId}
        entityName="response"
        isOpen={modalType === 'create' || modalType === 'update'}
        onClose={handleCloseModal}
        isCreate={modalType === 'create'}
        FormComponent={ResponseFormMemo}
        onSubmitComplete={handleSubmitComplete}
      />

      <RemoveConfirmModal
        isOpen={modalType === 'remove'}
        onConfirm={handleRemoveConfirm}
        onClose={handleCloseModal}
        isLoading={isRemoveLoading}
      />
    </>
  );
};

const ResponseForm = ({
  complaintId,
  id: responseId,
  isCreate,
  onCancel,
  onSubmitComplete,
}) => {
  const { data: response, isLoading: isResponseLoading } = useShowResponseQuery(
    { complaintId, responseId },
    { skip: isCreate || !complaintId || !responseId }
  );
  const { form, handleSubmit, isLoading } = useFormHandler({
    isCreate,
    mutation: isCreate ? useCreateResponseMutation : useUpdateResponseMutation,
    onSubmitComplete,
    defaultValues: {
      message: '',
      status: '',
    },
    params: [
      { name: 'complaintId', value: complaintId },
      ...(!isCreate ? [{ name: 'responseId', value: responseId }] : []),
    ],
  });

  useEffect(() => {
    if (!isCreate && response?.data) {
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
            <Button
              variant="secondary"
              type="button"
              onClick={onCancel}
            >
              Cancel
            </Button>
            <Button
              type="submit"
              disabled={isLoading}
            >
              {isLoading ? (
                <>
                  <TbLoader className="animate-spin" />
                  {isCreate ? 'Creating..' : 'Updating..'}
                </>
              ) : isCreate ? (
                'Create'
              ) : (
                'Update'
              )}
            </Button>
          </div>
        </form>
      </Form>
    </div>
  );
};

const ComplaintDetail = ({ id }) => {
  const { data: complaint, isLoading: isComplaintLoading } =
    useShowComplaintQuery(id);
  const { data: responses, isLoading: isResponsesLoading } =
    useListResponsesQuery(id);
  const { currentUser } = useSelector(state => state.auth);

  if (isComplaintLoading || isResponsesLoading)
    return <ComplaintDetailSkeleton />;

  return (
    <>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-muted/50 mb-10">
        <div className="flex items-start gap-3">
          <TbHeading className="size-5 text-primary mt-1 shrink-0" />
          <div>
            <p className="text-sm font-semibold text-muted-foreground">Subject</p>
            <p className="text-base break-all">{complaint?.data?.subject}</p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <TbFileText className="size-5 text-primary mt-1 shrink-0" />
          <div>
            <p className="text-sm font-semibold text-muted-foreground">
              Description
            </p>
            <p className="text-base break-all">
              {complaint?.data?.description}
            </p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <TbCalendarEvent className="size-5 text-primary mt-1 shrink-0" />
          <div>
            <p className="text-sm font-semibold text-muted-foreground">
              Created
            </p>
            <p className="text-base">
              {dayjs(complaint?.data?.createdAt).format('DD MMM YYYY')}
            </p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <TbCircleCheck className="size-5 text-primary mt-1 shrink-0" />
          <div>
            <p className="text-sm font-semibold text-muted-foreground">
              Status
            </p>
            {complaint?.data?.status === 'pending' && (
              <Badge variant="destructive" className="gap-1 mt-1">
                <TbAlertTriangle /> Pending
              </Badge>
            )}
            {complaint?.data?.status === 'in_progress' && (
              <Badge variant="default" className="gap-1 mt-1">
                <TbLoader /> In Progress
              </Badge>
            )}
            {complaint?.data?.status === 'resolved' && (
              <Badge variant="success" className="gap-1 mt-1">
                <TbCircleCheck /> Resolved
              </Badge>
            )}
          </div>
        </div>
        {currentUser.role === 'admin' && (
          <div className="flex items-start gap-3 col-span-full">
            <TbUser className="text-primary mt-1 shrink-0 size-5" />
            <div>
              <p className="text-sm font-semibold text-muted-foreground">
                Reporter
              </p>
              <p className="text-base">{complaint?.data?.user?.email}</p>
            </div>
          </div>
        )}
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {complaint.data?.images?.length > 0 &&
          complaint.data.images.map((image, index) => (
            <img
              key={index}
              src={image}
              alt="Complaint image"
              className="h-64 w-full object-cover rounded-md"
            />
          ))}
      </div>

      <Separator className="my-2" />
      <ResponseList complaintId={id} responses={responses} />
    </>
  );
};

export default ComplaintDetail;
