import {configureStore} from "@reduxjs/toolkit";
import { 
  persistStore, 
  persistReducer,
  FLUSH,
  REHYDRATE,
  PAUSE,
  PERSIST,
  PURGE,
  REGISTER,
} from 'redux-persist';
import { combineReducers } from 'redux';
import storage from 'redux-persist/lib/storage';
import authReducer from '@/lib/features/authSlice.js';
import authApi from '@/services/authApi.js';
import roleApi from '@/services/roleApi.js';
import userApi from '@/services/userApi.js';
import categoryApi from '@/services/categoryApi.js';
import complaintApi from '@/services/complaintApi.js';
import dashboardApi from '@/services/dashboardApi.js';

const rootPersistConfig = {
  key: 'root',
  storage,
  whitelist: ['auth'],
}

const rootReducer = combineReducers({
  [authApi.reducerPath]: authApi.reducer,
  [roleApi.reducerPath]: roleApi.reducer,
  [userApi.reducerPath]: userApi.reducer,
  [categoryApi.reducerPath]: categoryApi.reducer,
  [complaintApi.reducerPath]: complaintApi.reducer,
  [dashboardApi.reducerPath]: dashboardApi.reducer,
  auth: authReducer,
});

export const store = configureStore({
  reducer: persistReducer(rootPersistConfig, rootReducer),
  middleware: (getDefaultMiddleware) => getDefaultMiddleware({
    serializableCheck: {
      ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
    }
  }).concat(
    authApi.middleware, 
    roleApi.middleware,
    userApi.middleware,
    categoryApi.middleware,
    complaintApi.middleware,
    dashboardApi.middleware,
  ),
});

export const persistor = persistStore(store);
