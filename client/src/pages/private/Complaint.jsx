import DataTable from '@/components/ui/DataTable';
import { createColumnHelper } from '@tanstack/react-table';
import {
  useSearchComplaintsQuery,
  useRemoveComplaintMutation
} from '@/services/complaintApi.js';
import ComplaintForm from '@/components/ui/ComplaintForm.jsx';
import { Badge } from '@/components/shadcn/badge';
import BreadcrumbNav from '@/components/ui/BreadcrumbNav';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/shadcn/card';
import ComplaintDetail from '@/components/ui/ComplaintDetail';

const Property = () => {
  const columnsHelper = createColumnHelper();
  const columns = [
    columnsHelper.accessor('images', {
      header: 'Image',
      size: 150,
      cell: info => {
        const images = info.getValue();
        if (!images || images.length === 0) return null;

        return (
          <div className="flex flex-wrap gap-1">
            {images.slice(0, 5).map((img, index) => (
              <img
                key={index}
                src={img}
                alt={`image-${index}`}
                className="size-10 object-cover rounded"
              />
            ))}
          </div>
        );
      },
    }),
    columnsHelper.accessor('user.email', {
      header: 'Reporter',
      size: 150,
      cell: info => (
        <div className="whitespace-normal break-words">{info.getValue()}</div>
      ),
    }),
    columnsHelper.accessor('category.name', {
      header: 'Category',
      size: 150,
      cell: info => (
        <div className="whitespace-normal break-words">{info.getValue()}</div>
      ),
    }),
    columnsHelper.accessor('subject', {
      header: 'Subject',
      size: 200,
      cell: info => (
        <div className="whitespace-normal break-words">{info.getValue()}</div>
      ),
    }),
    columnsHelper.accessor('description', {
      header: 'Description',
      size: 200,
      cell: info => (
        <div className="whitespace-normal break-words">{info.getValue()}</div>
      )
    }),
    columnsHelper.accessor('status', {
      header: 'Status',
      size: 100,
      cell: info => {
        const status = info.getValue();
        if (status === 'pending') return <Badge variant="destructive">Pending</Badge>;
        if (status === 'in_progress') return <Badge variant="default">In Progress</Badge>;
        if (status === 'resolved') return <Badge variant="success">Resolved</Badge>;
      },
    })
  ];

  return (
    <>
      <BreadcrumbNav />
      <Card>
        <CardHeader>
          <CardTitle className="text-heading">Complaints</CardTitle>
          <CardDescription>Manage complaints</CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable
            columns={columns}
            searchQuery={useSearchComplaintsQuery}
            removeMutation={useRemoveComplaintMutation}
            FormComponent={ComplaintForm}
            DetailComponent={ComplaintDetail}
            entityName="complaint"
            allowFileUpload={true}
            allowView={true}
          />
        </CardContent>
      </Card>
    </>
  );
};

export default Property;
