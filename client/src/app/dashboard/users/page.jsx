'use client';

import DataTable from '@/components/ui/DataTable';
import { createColumnHelper } from '@tanstack/react-table';
import {
  useSearchUsersQuery,
  useRemoveUserMutation,
} from '@/services/userApi.js';
import UserForm from '@/components/ui/UserForm.jsx';
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/components/shadcn/avatar';
import { Badge } from '@/components/shadcn/badge';
import BreadcrumbNav from '@/components/ui/BreadcrumbNav';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/shadcn/card';
import AuthGuard from '@/components/auth/AuthGuard';

const User = () => {
  const columnsHelper = createColumnHelper();
  const columns = [
    columnsHelper.accessor('avatar', {
      header: 'Avatar',
      size: 80,
      cell: info => (
        <Avatar>
          <AvatarImage src={info.getValue()} />
          <AvatarFallback>
            {info.getValue()?.charAt(0).toUpperCase()}
          </AvatarFallback>
        </Avatar>
      ),
    }),
    columnsHelper.accessor('username', {
      header: 'Username',
      size: 150,
    }),
    columnsHelper.accessor('email', {
      header: 'Email',
      size: 200,
    }),
    columnsHelper.accessor('role', {
      header: 'Role',
      size: 100,
      cell: info => {
        const role = info.getValue();
        if (role.name === 'admin') {
          return <Badge variant="destructive">Admin</Badge>;
        } else {
          return <Badge variant="default">User</Badge>;
        }
      },
    })
  ];

  return (
    <AuthGuard requiredRoles={['admin']}>
      <BreadcrumbNav />
      <Card>
        <CardHeader>
          <CardTitle className="text-gray-800">Users</CardTitle>
          <CardDescription>Manage users</CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable
            columns={columns}
            searchQuery={useSearchUsersQuery}
            removeMutation={useRemoveUserMutation}
            FormComponent={UserForm}
            entityName="user"
          />
        </CardContent>
      </Card>
    </AuthGuard>
  );
};
export default User;
