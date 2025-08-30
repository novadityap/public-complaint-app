'use client';

import { useSelector } from 'react-redux';
import { useRemoveResponseMutation } from '@/services/complaintApi';
import { Button } from '@/components/shadcn/button';
import { Card, CardContent } from '@/components/shadcn/card';
import {
  TbPlus,
  TbMessage,
  TbUser,
  TbCalendarEvent,
  TbEdit,
  TbTrash,
} from 'react-icons/tb';
import dayjs from 'dayjs';
import { useState } from 'react';
import RemoveConfirmModal from '@/components/ui/RemoveConfirmModal';
import CreateUpdateModal from '@/components/ui/CreateUpdateModal';
import { toast } from 'react-hot-toast';
import { useMemo } from 'react';
import ResponseForm from '@/components/ui/ResponseForm';

const Responses = ({ complaintId, responses }) => {
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

  const handleRemoveConfirm = async () => {
    try {
      const result = await removeResponse(selectedId).unwrap();
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
          {currentUser?.role === 'admin' && (
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

                {currentUser?.role === 'admin' && (
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
        isUpdate={modalType === 'update'}
        FormComponent={ResponseFormMemo}
        onSuccess={handleCloseModal}
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

export default Responses;
