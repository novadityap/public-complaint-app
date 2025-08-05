'use client';

import Link from 'next/link';
import { cn } from '@/lib/utils';

const Brand = ({ className }) => {
  return (
    <Link
      href="/"
      className={cn(
        'text-3xl font-bold text-white hover:text-gray-300 transition-colors duration-200',
        'text-center block cursor-pointer tracking-tight',
        className
      )}
    >
      Public Complaint
    </Link>
  );
};

export default Brand;
