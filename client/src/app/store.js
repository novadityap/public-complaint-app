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
import authReducer from '@/features/authSlice.js';
import authApi from '@/services/authApi.js';

const rootPersistConfig = {
  key: 'root',
  storage,
  whitelist: ['auth'],
}

const authPersistConfig = {
  key: 'auth',
  storage,
}

const rootReducer = combineReducers({
  [authApi.reducerPath]: authApi.reducer,
  auth: persistReducer(authPersistConfig, authReducer)
});

export const store = configureStore({
  reducer: persistReducer(rootPersistConfig, rootReducer),
  middleware: (getDefaultMiddleware) => getDefaultMiddleware({
    serializableCheck: {
      ignoredActions: [FLUSH, REHYDRATE, PAUSE, PERSIST, PURGE, REGISTER],
    }
  }).concat(
    authApi.middleware, 
  ),
});

export const persistor = persistStore(store);
