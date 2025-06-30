import { createSlice } from '@reduxjs/toolkit';

const authSlice = createSlice({
  name: 'auth',
  initialState: {
    token: null,
    currentUser: null,
  },
  reducers: {
    setToken: (state, action) => {
      state.token = action.payload;
    },
    setCurrentUser: (state, action) => {
      state.currentUser = {
        'avatar': action.payload.avatar,
        'id': action.payload.id,
        'username': action.payload.username,
        'email': action.payload.email,
        'role': action.payload.role.name
      };
    },
    updateCurrentUser: (state, action) => {
      state.currentUser = {
        ...state.currentUser,
        ...action.payload,
      };
    },
    clearAuth: (state) => {
      state.token = null;
      state.currentUser = null;
    },
  },
});

export const { 
  setToken, 
  setCurrentUser, 
  updateCurrentUser,
  clearAuth
} = authSlice.actions;
export default authSlice.reducer;
