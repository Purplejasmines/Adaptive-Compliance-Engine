import axios from 'axios';

const API_URL = 'http://localhost:8000/api/v1/auth';

class AuthService {
  async login(username, password, rememberMe = false) {
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('grant_type', 'password');
    
    try {
      const response = await axios.post(`${API_URL}/token`, formData, {
        params: { remember_me: rememberMe },
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.access_token) {
        this.setAuthTokens(response.data);
        await this.setUserData();
      }
      
      return response.data;
    } catch (error) {
      this.clearAuth();
      throw this.handleError(error);
    }
  }

  async refreshToken() {
    const refreshToken = localStorage.getItem('refresh_token');
    if (!refreshToken) {
      throw new Error('No refresh token available');
    }

    try {
      const response = await axios.post(`${API_URL}/refresh-token`, { refresh_token: refreshToken });
      this.setAuthTokens(response.data);
      return response.data;
    } catch (error) {
      this.clearAuth();
      throw this.handleError(error);
    }
  }

  async logout() {
    try {
      await axios.post(`${API_URL}/logout`, {}, {
        headers: this.getAuthHeader(),
      });
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      this.clearAuth();
    }
  }

  async getCurrentUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  getAuthHeader() {
    const token = localStorage.getItem('token');
    return token ? { 'Authorization': `Bearer ${token}` } : {};
  }

  isAuthenticated() {
    const token = localStorage.getItem('token');
    return !!token;
  }

  setAuthTokens(data) {
    if (data.access_token) {
      localStorage.setItem('token', data.access_token);
    }
    if (data.refresh_token) {
      localStorage.setItem('refresh_token', data.refresh_token);
    }
  }

  async setUserData() {
    try {
      const response = await axios.get(`${API_URL}/me`, {
        headers: this.getAuthHeader(),
      });
      localStorage.setItem('user', JSON.stringify(response.data));
      return response.data;
    } catch (error) {
      console.error('Failed to fetch user data:', error);
      this.clearAuth();
      throw error;
    }
  }

  clearAuth() {
    localStorage.removeItem('token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('user');
  }

  handleError(error) {
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      const { status, data } = error.response;
      
      if (status === 401) {
        // Unauthorized - clear auth and redirect to login
        this.clearAuth();
        window.location.href = '/login';
      }
      
      return new Error(data.detail || 'An error occurred');
    } else if (error.request) {
      // The request was made but no response was received
      return new Error('No response from server. Please check your connection.');
    } else {
      // Something happened in setting up the request
      return new Error(error.message || 'An error occurred');
    }
  }
}

export default new AuthService();
