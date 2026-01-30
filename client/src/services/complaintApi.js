import { createApi } from '@reduxjs/toolkit/query/react';
import axiosBaseQuery from '@/lib/baseQuery.js';

const complaintApi = createApi({
  reducerPath: 'complaintApi',
  baseQuery: axiosBaseQuery(),
  tagTypes: ['Complaint', 'Response'],
  endpoints: builder => ({
    createComplaint: builder.mutation({
      query: data => ({
        url: '/complaints',
        method: 'POST',
        data,
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      invalidatesTags: [{ type: 'Complaint', id: 'LIST' }],
    }),
    searchComplaints: builder.query({
      query: params => ({
        url: '/complaints/search',
        method: 'GET',
        params,
      }),
      providesTags: result =>
        result
          ? [
              ...result.data.map(({ id }) => ({ type: 'Complaint', id })),
              { type: 'Complaint', id: 'LIST' },
            ]
          : [{ type: 'Complaint', id: 'LIST' }],
    }),
    showComplaint: builder.query({
      query: complaintId => ({
        url: `/complaints/${complaintId}`,
        method: 'GET',
      }),
      providesTags: (result, error, complaintId) => [
        { type: 'Complaint', id: complaintId },
      ],
    }),
    updateComplaint: builder.mutation({
      query: ({ data, complaintId }) => ({
        url: `/complaints/${complaintId}`,
        method: 'POST',
        data,
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      invalidatesTags: (result, error, { complaintId }) => [
        { type: 'Complaint', id: complaintId },
      ],
    }),
    removeComplaint: builder.mutation({
      query: complaintId => ({
        url: `/complaints/${complaintId}`,
        method: 'DELETE',
      }),
      invalidatesTags: (result, error, complaintId) => [
        { type: 'Complaint', id: complaintId },
      ],
    }),
    uploadComplaintImage: builder.mutation({
      query: ({ data, complaintId }) => ({
        url: `/complaints/${complaintId}/images`,
        method: 'POST',
        data,
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      invalidatesTags: (result, error, { complaintId }) => [
        { type: 'Complaint', id: complaintId },
      ],
    }),
    removeComplaintImage: builder.mutation({
      query: ({ complaintId, data }) => ({
        url: `/complaints/${complaintId}/images`,
        method: 'DELETE',
        data,
      }),
      invalidatesTags: (result, error, { complaintId }) => [
        { type: 'Complaint', id: complaintId },
      ],
    }),
    listResponses: builder.query({
      query: complaintId => ({
        url: `/complaints/${complaintId}/responses`,
        method: 'GET',
      }),
      providesTags: result =>
        result
          ? [
              ...result.data.map(({ id }) => ({ type: 'Response', id })),
              { type: 'Response', id: 'LIST' },
            ]
          : [{ type: 'Response', id: 'LIST' }],
    }),
    createResponse: builder.mutation({
      query: ({ data, complaintId }) => ({
        url: `/complaints/${complaintId}/responses`,
        method: 'POST',
        data,
      }),
      invalidatesTags: (result, error, { complaintId }) => [
        { type: 'Complaint', id: complaintId },
        { type: 'Response', id: 'LIST' }
      ],
    }),
    showResponse: builder.query({
      query: responseId => ({
        url: `/responses/${responseId}`,
        method: 'GET',
      }),
      providesTags: (result, error, { responseId }) => [
        { type: 'Response', id: responseId },
      ],
    }),
    updateResponse: builder.mutation({
      query: ({ data, responseId }) => ({
        url: `/responses/${responseId}`,
        method: 'PUT',
        data,
      }),
      invalidatesTags: (result, error, { responseId }) => [
        { type: 'Response', id: responseId },
        { type: 'Complaint', id: result.data.complaintId }
      ],
    }),
    removeResponse: builder.mutation({
      query: responseId  => ({
        url: `/responses/${responseId}`,
        method: 'DELETE',
      }),
      invalidatesTags: (result, error, { responseId }) => [
        { type: 'Response', id: responseId },
      ],
    }),
  }),
});

export const {
  useSearchComplaintsQuery,
  useLazySearchComplaintsQuery,
  useShowComplaintQuery,
  useLazyShowComplaintQuery,
  useCreateComplaintMutation,
  useUpdateComplaintMutation,
  useRemoveComplaintMutation,
  useUploadComplaintImageMutation,
  useRemoveComplaintImageMutation,
  useShowResponseQuery,
  useListResponsesQuery,
  useCreateResponseMutation,
  useUpdateResponseMutation,
  useRemoveResponseMutation,
} = complaintApi;

export default complaintApi;
