'use client';

import { Button } from '@/components/shadcn/button';
import { Input } from '@/components/shadcn/input';
import useFormHandler from '@/hooks/useFormHandler';
import { useListCategoriesQuery } from '@/services/categoryApi';
import {
  Select,
  SelectContent,
  SelectTrigger,
  SelectValue,
  SelectItem,
} from '@/components/shadcn/select';
import {
  Form,
  FormField,
  FormLabel,
  FormMessage,
  FormItem,
  FormControl,
} from '@/components/shadcn/form';
import { useState, useEffect } from 'react';
import { Textarea } from '@/components/shadcn/textarea';
import { cn } from '@/lib/utils';
import { TbLoader } from 'react-icons/tb';
import { toast } from 'react-hot-toast';
import {
  useUploadComplaintImageMutation,
  useRemoveComplaintImageMutation,
  useCreateComplaintMutation,
  useUpdateComplaintMutation,
  useShowComplaintQuery,
} from '@/services/complaintApi';
import { Skeleton } from '@/components/shadcn/skeleton';
import Image from 'next/image';

const ComplaintSkeleton = ({isCreate}) => (
  <div className="space-y-4">
    {!isCreate && (
      <div className="flex justify-center">
        <Skeleton className="h-32 w-32 rounded-full" />
      </div>
    )}
    <Skeleton className="h-4 w-[120px]" />
    <Skeleton className="h-10 w-full" />
    <Skeleton className="h-4 w-[120px]" />
    <Skeleton className="h-10 w-full" />
    <Skeleton className="h-4 w-[120px]" />
    <Skeleton className="h-10 w-full" />
    <Skeleton className="h-4 w-[120px]" />
    <Skeleton className="h-10 w-full" />
    <div className="flex justify-end gap-2">
      <Skeleton className="h-10 w-24 rounded-md" />
      <Skeleton className="h-10 w-24 rounded-md" />
    </div>
  </div>
);

const ComplaintForm = ({ id, onSubmitComplete, onCancel, isCreate }) => {
  const { data: complaint, isLoading: isComplaintLoading } =
    useShowComplaintQuery(id, {
      skip: isCreate || !id,
    });
  const { data: categories, isLoading: isCategoriesLoading } =
    useListCategoriesQuery();
  const [previewImages, setPreviewImages] = useState([]);
  const [images, setImages] = useState([]);
  const [imageToRemove, setImageToRemove] = useState('');
  const [removeImage, { isLoading: isRemoveImageLoading }] =
    useRemoveComplaintImageMutation();
  const {
    form: formUpload,
    handleSubmit: handleSubmitUpload,
    isLoading: isUploadLoading,
  } = useFormHandler({
    isCreate: true,
    fileFieldname: 'images',
    isMultiple: true,
    params: [{ name: 'complaintId', value: id }],
    mutation: useUploadComplaintImageMutation,
  });
  const { form, handleSubmit, isLoading } = useFormHandler({
    isCreate,
    fileFieldname: 'images',
    isMultiple: true,
    mutation: isCreate
      ? useCreateComplaintMutation
      : useUpdateComplaintMutation,
    onSubmitComplete,
    defaultValues: {
      subject: '',
      description: '',
      categoryId: '',
    },
    ...(!isCreate && {
      params: [{ name: 'complaintId', value: id }],
      method: 'PATCH',
    }),
  });

  const handleImageChange = e => {
    if (e.target.files) {
      const fileArray = Array.from(e.target.files);
      const previewUrls = fileArray.map(file => URL.createObjectURL(file));

      setImages(prev => [...prev, ...fileArray]);
      setPreviewImages(prev => [...prev, ...previewUrls]);
    }
  };

  const handleRemoveImage = index => {
    const imageUrl = previewImages[index];
    setImageToRemove(imageUrl);

    if (imageUrl.startsWith('blob:')) {
      URL.revokeObjectURL(imageUrl);
      setPreviewImages(prev => {
        const copy = [...prev];
        copy.splice(index, 1);
        return copy;
      });
      setImages(prev => {
        const copy = [...prev];
        copy.splice(index, 1);
        return copy;
      });
      setImageToRemove('');
      return;
    }

    removeImage({
      complaintId: id,
      data: { image: imageUrl },
    })
      .unwrap()
      .then(res => {
        toast.success(res.message);

        setPreviewImages(prev => {
          const copy = [...prev];
          copy.splice(index, 1);
          return copy;
        });
        setImages(prev => {
          const copy = [...prev];
          copy.splice(index, 1);
          return copy;
        });
      })
      .catch(e => {
        toast.error(e.message);
      })
      .finally(() => {
        setImageToRemove('');
      });
  };

  useEffect(() => {
    if (isCreate) {
      form.setValue('images', images);
    } else {
      formUpload.setValue('images', images);
    }
  }, [images]);

  useEffect(() => {
    if (!isCreate && complaint?.data) {
      setPreviewImages(complaint.data.images);
      form.reset({
        subject: complaint.data.subject,
        description: complaint.data.description,
        categoryId: complaint.data.categoryId,
      });
    }
  }, [complaint]);

  if (isComplaintLoading || isCategoriesLoading) return <ComplaintSkeleton />;

  return (
    <div
      className={cn(
        'flex flex-col md:flex-row gap-x-6 gap-y-12',
        isCreate && 'sm:max-w-lg'
      )}
    >
      {!isCreate && (
        <Form {...formUpload}>
          <form
            className="flex flex-col space-y-4 flex-1"
            onSubmit={handleSubmitUpload}
          >
            <FormField
              control={formUpload.control}
              name="images"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Images</FormLabel>
                  <FormControl>
                    <Input
                      type="file"
                      accept="image/*"
                      multiple
                      onChange={e => handleImageChange(e)}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />

            {previewImages.length > 0 && (
              <div className="flex flex-wrap gap-2">
                {previewImages.map((src, index) => (
                  <div
                    key={index}
                    className="relative w-32 h-32 border rounded overflow-hidden"
                  >
                    {imageToRemove === src && isRemoveImageLoading ? (
                      <div className="absolute top-0 left-0 w-full h-full bg-black/50 flex items-center justify-center">
                        <TbLoader className="animate-spin" />
                      </div>
                    ) : (
                      <Image
                        width={150}
                        height={150}
                        src={src}
                        alt={`Preview ${index}`}
                        className="object-cover w-full h-full"
                      />
                    )}

                    <button
                      type="button"
                      disabled={isRemoveImageLoading}
                      onClick={() => handleRemoveImage(index)}
                      className="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 opacity-80 hover:opacity-100"
                    >
                      remove
                    </button>
                  </div>
                ))}
              </div>
            )}

            <div className="flex justify-end">
              <Button
                type="submit"
                disabled={isUploadLoading}
              >
                {isUploadLoading ? (
                  <>
                    <TbLoader className="animate-spin" />
                    Uploading..
                  </>
                ) : (
                  'Upload'
                )}
              </Button>
            </div>
          </form>
        </Form>
      )}

      <Form {...form}>
        <form className="space-y-4 flex-1" onSubmit={handleSubmit}>
          {isCreate && (
            <>
              <FormField
                control={form.control}
                name="images"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Images</FormLabel>
                    <FormControl>
                      <Input
                        type="file"
                        accept="image/*"
                        multiple
                        onChange={e => handleImageChange(e)}
                      />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              {previewImages.length > 0 && (
                <div className="flex flex-wrap gap-4 mt-4">
                  {previewImages.map((src, index) => (
                    <div
                      key={index}
                      className="relative w-32 h-32 border rounded overflow-hidden"
                    >
                      <Image
                        src={src}
                        alt={`Preview ${index}`}
                        className="object-cover w-full h-full"
                        width={150}
                        height={150}
                      />
                      <button
                        type="button"
                        onClick={() => handleRemoveImage(index)}
                        className="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 opacity-80 hover:opacity-100"
                      >
                        Remove
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </>
          )}
          <FormField
            control={form.control}
            name="subject"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Subject</FormLabel>
                <FormControl>
                  <Input {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="description"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Description</FormLabel>
                <FormControl>
                  <Textarea {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="categoryId"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Category</FormLabel>
                <Select
                  key={field.value}
                  value={field.value}
                  onValueChange={field.onChange}
                >
                  <FormControl>
                    <SelectTrigger className="w-full">
                      <SelectValue placeholder="Select a category of complaint" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent className="max-h-60 overflow-auto">
                    {categories?.data.map(category => (
                      <SelectItem key={category.id} value={category.id}>
                        {category.name}
                      </SelectItem>
                    ))}
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

export default ComplaintForm;
