import { useForm } from 'react-hook-form';
import { useState } from 'react';

const getChangedData = (dirtyFields, form) => {
  return Object.fromEntries(
    Object.keys(dirtyFields).map(key => [key, form.getValues(key)])
  );
};

const sanitizeNull = data => {
  return Object.fromEntries(
    Object.entries(data).map(([key, value]) => [key, value ?? ''])
  );
};

const filterEmptyValues = data => {
  return Object.fromEntries(
    Object.entries(data).filter(([_, value]) => value !== '')
  );
};

const buildFormData = ({ data, fieldName, isMultiple, method }) => {
  const formData = new FormData();

  if (method) formData.append('_method', method.toUpperCase());

  Object.keys(data).forEach(key => {
    const value = data[key];

    if (isMultiple && key === fieldName) {
      value.forEach(file => formData.append(`${key}[]`, file));
    } else {
      formData.append(key, value);
    }
  });

  return formData;
};

const buildPayload = (data, params) => {
  return params?.length
    ? {
        data,
        ...Object.fromEntries(params.map(({ name, value }) => [name, value])),
      }
    : data;
};

const useFormHandler = ({
  file,
  mutation,
  defaultValues,
  params = [],
  onSuccess,
  onError,
  isUpdate = false,
}) => {
  const [message, setMessage] = useState('');
  const [mutate, { isLoading, isError, error, isSuccess }] = mutation();
  const form = useForm({ defaultValues });
  const {
    handleSubmit,
    formState: { dirtyFields },
  } = form;

  const onSubmit = async data => {
    try {
      if (isUpdate) {
        data = getChangedData(dirtyFields, form);
      } else {
        data = filterEmptyValues(data);
      }

      if (file?.fieldName) {
        data = buildFormData({
          data,
          fieldName: file.fieldName,
          isMultiple: file.isMultiple,
          ...(isUpdate && { method: file.method }),
        });
      }

      const result = await mutate(buildPayload(data, params)).unwrap();

      if (isUpdate && result?.data) {
        form.reset(sanitizeNull(result.data), {
          keepDefaultValues: true,
        });
      } else {
        form.reset();
      }

      setMessage(result?.message);

      if (onSuccess) onSuccess(result);
    } catch (e) {
      if (e.errors) {
        Object.keys(e.errors).forEach(key => {
          const message = e.errors[key];
          form.setError(key, { type: 'manual', message });
        });
      }

      if (e.code !== 400) {
        setMessage(e.message);
        if (onError) onError(e);
      }
    }
  };

  return {
    form,
    handleSubmit: handleSubmit(onSubmit),
    isLoading,
    isError,
    error,
    isSuccess,
    message,
  };
};

export default useFormHandler;
