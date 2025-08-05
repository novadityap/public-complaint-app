'use client';

import { Button } from '@/components/shadcn/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/shadcn/card';
import Link from 'next/link';
import { useSelector } from 'react-redux';
import Image from 'next/image';

const Home = () => {
  const { currentUser } = useSelector(state => state.auth);

  return (
    <div className="max-w-5xl mx-auto px-6 py-12 space-y-16">
      <section className="grid grid-cols-1 md:grid-cols-2 items-center gap-10">
        <div className="space-y-6">
          <h1 className="text-4xl font-bold leading-tight">
            Empower Your Voice with the Public Complaint App
          </h1>
          <p className="text-muted-foreground text-lg">
            This platform enables you to report public issues directly to the
            local government. Whether it's infrastructure problems, safety
            concerns, or public service complaints — we help your voice reach
            the right place.
          </p>
          <Button>
            {!currentUser ? (
              <Link href="/signin">Sign In</Link>
            ) : currentUser?.role === 'user' ? (
              <Link href="/dashboard/complaints">Submit a Complaint</Link>
            ) : currentUser?.role === 'admin' ? (
              <Link href="/dashboard/complaints">Respond to Complaints</Link>
            ) : null}
          </Button>
        </div>

        <div className="hidden md:block">
          <Image
            width={500}
            height={500}
            src='/complaint.svg'
            alt="Public Complaint Illustration"
            className="w-full h-auto"
          />
        </div>
      </section>

      <section>
        <Card>
          <CardHeader>
            <CardTitle>What is This App?</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4 text-muted-foreground text-sm">
            <p>
              The Public Complaint App is a digital bridge between citizens and
              authorities. It simplifies the process of submitting complaints
              and ensures that each submission is tracked transparently.
            </p>
            <p>
              Users can create accounts, track the progress of their complaints,
              and receive updates when actions are taken. It promotes
              accountability and allows communities to participate actively in
              improving their surroundings.
            </p>
          </CardContent>
        </Card>
      </section>

      <section className="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Easy Reporting</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">
            Submit complaints in just a few clicks using a simple and
            user-friendly interface.
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Transparent Tracking</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">
            Monitor the status of your complaint and receive real-time updates
            as it is processed.
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-lg">Community Impact</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">
            Help improve public services and infrastructure by sharing real
            issues that matter to you.
          </CardContent>
        </Card>
      </section>
    </div>
  );
};

export default Home;
