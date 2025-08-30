'use client';

import { useSelector } from 'react-redux';
import {
  useShowComplaintQuery,
  useListResponsesQuery,
} from '@/services/complaintApi';
import { Skeleton } from '@/components/shadcn/skeleton';
import { Card, CardContent } from '@/components/shadcn/card';
import { Separator } from '@/components/shadcn/separator';
import {
  TbHeading,
  TbFileText,
  TbUser,
  TbCalendarEvent,
  TbAlertTriangle,
  TbLoader,
  TbCircleCheck,
} from 'react-icons/tb';
import dayjs from 'dayjs';
import { Badge } from '@/components/shadcn/badge';
import Image from 'next/image';
import { AspectRatio } from '@/components/shadcn/aspect-ratio';
import Responses from '@/components/ui/Responses';

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
            <p className="text-sm font-semibold text-muted-foreground">
              Subject
            </p>
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
        {currentUser?.role === 'admin' && (
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
            <AspectRatio key={index} ratio={4 / 3} className="rounded-md">
              <Image
                fill
                src={image}
                alt="Complaint image"
                className="w-full object-cover rounded-md"
              />
            </AspectRatio>
          ))}
      </div>

      <Separator className="my-2" />
      <Responses complaintId={id} responses={responses} />
    </>
  );
};

export default ComplaintDetail;
