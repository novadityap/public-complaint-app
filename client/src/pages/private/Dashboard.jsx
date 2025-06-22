import {
  Card,
  CardHeader,
  CardContent,
  CardTitle,
  CardDescription,
} from '@/components/shadcn/card';
import { Skeleton } from '@/components/shadcn/skeleton';
import { useShowDashboardQuery } from '@/services/dashboardApi';
import BreadcrumbNav from '@/components/ui/BreadcrumbNav';
import { Link } from 'react-router';
import { cn } from '@/lib/utils';

const StatCard = ({ title, value }) => (
  <Card className="hover:shadow-xl transition-shadow">
    <CardHeader>
      <CardTitle className="text-lg font-semibold text-heading">
        {title}
      </CardTitle>
      <CardDescription
        className={cn('text-2xl font-semibold', {
          'text-body': value == null,
        })}
      >
        {value ?? 'No data available'}
      </CardDescription>
    </CardHeader>
  </Card>
);

const RecentItemsCard = ({ title, items, link, formatter }) => (
  <Card className="hover:shadow-xl transition-shadow">
    <CardHeader>
      <CardTitle className="text-xl font-semibold text-heading">
        {title}
      </CardTitle>
    </CardHeader>
    <CardContent>
      {items?.length ? (
        <ul className="space-y-2 text-body">{items.map(formatter)}</ul>
      ) : (
        <p className="text-body italic">
          No {title.split(' ')[1]?.toLowerCase()} available.
        </p>
      )}
      {items?.length > 0 && (
        <Link to={link} className="block mt-4 text-blue-600 hover:underline">
          View All {title.split(' ')[1]}
        </Link>
      )}
    </CardContent>
  </Card>
);

const Dashboard = () => {
  const { data, isLoading } = useShowDashboardQuery();

  const stats = [
    { title: 'Total Complaints', value: data?.data?.totalComplaints },
    { title: 'Total Users', value: data?.data?.totalUsers },
    { title: 'Total Roles', value: data?.data?.totalRoles },
  ];

  return (
    <>
      <BreadcrumbNav />
      <Card>
        <CardHeader>
          <CardTitle className="text-heading text-2xl">Dashboard</CardTitle>
          <CardDescription className="text-body">
            Overview of your dashboard
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {stats.map((_, index) => (
                <Skeleton key={index} className="h-24" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {stats.map((stat, index) => (
                <StatCard key={index} title={stat.title} value={stat.value} />
              ))}
            </div>
          )}
        </CardContent>
      </Card>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        {isLoading ? (
          <Skeleton className="h-40" />
        ) : (
          <RecentItemsCard
            title="Recent complaints"
            items={data?.data?.recentComplaints}
            link="/dashboard/complaints"
            formatter={complaint => (
              <li
                key={complaint.id}
                className="flex justify-between items-center py-1"
              >
                <Link
                  to={`/complaints/${complaint.id}`}
                  className="hover:underline text-blue-700 font-medium truncate"
                >
                  {complaint.name}
                </Link>
                <span className="text-xs text-body">
                  {new Date(complaint.createdAt).toLocaleDateString()}
                </span>
              </li>
            )}
          />
        )}
      </div>
    </>
  );
};

export default Dashboard;
