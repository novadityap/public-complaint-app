'use client';

import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/shadcn/dialog';

const ViewDetailModal = ({
  isOpen,
  onClose,
  DetailComponent,
  id,
  entityName,
}) => (
  <Dialog open={isOpen} onOpenChange={onClose}>
    <DialogContent className="md:max-w-2xl overflow-hidden p-0">
      <DialogHeader className="px-6 pt-6">
        <DialogTitle>{`Detail ${entityName}`}</DialogTitle>
        <DialogDescription className="sr-only"></DialogDescription>
      </DialogHeader>
      <div className="max-h-[80vh] overflow-y-auto p-6">
        <DetailComponent id={id} onClose={onClose} />
      </div>
    </DialogContent>
  </Dialog>
);

export default ViewDetailModal;
