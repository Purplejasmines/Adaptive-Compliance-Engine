<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Login to ZRA System</h2>
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            id="username"
            v-model="username"
            type="text"
            class="form-control"
            required
            autocomplete="username"
            :disabled="loading"
          />
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            class="form-control"
            required
            autocomplete="current-password"
            :disabled="loading"
          />
          <div v-if="error" class="error-message">
            {{ error }}
            <span v-if="remainingAttempts !== null">
              <br>Remaining attempts: {{ remainingAttempts }}
            </span>
          </div>
        </div>

        <div class="form-group remember-me">
          <input
            id="rememberMe"
            v-model="rememberMe"
            type="checkbox"
            class="form-checkbox"
            :disabled="loading"
          />
          <label for="rememberMe">Remember me</label>
        </div>

        <button
          type="submit"
          class="login-button"
          :disabled="loading || isLocked"
        >
          <span v-if="loading" class="spinner"></span>
          {{ isLocked ? `Account Locked (${lockoutTime} seconds)` : 'Login' }}
        </button>

        <div class="forgot-password">
          <router-link to="/forgot-password">Forgot Password?</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthService from '@/services/auth.service';

export default {
  name: 'Login',
  setup() {
    const router = useRouter();
    const username = ref('');
    const password = ref('');
    const rememberMe = ref(false);
    const loading = ref(false);
    const error = ref('');
    const remainingAttempts = ref(null);
    const isLocked = ref(false);
    const lockoutTime = ref(0);
    let lockoutInterval = null;

    const handleLogin = async () => {
      if (isLocked.value) return;
      
      error.value = '';
      loading.value = true;

      try {
        await AuthService.login(
          username.value,
          password.value,
          rememberMe.value
        );
        
        // Redirect to dashboard on successful login
        const redirectPath = router.currentRoute.value.query.redirect || '/';
        await router.push(redirectPath);
      } catch (err) {
        if (err.response?.data?.error === 'Account temporarily locked') {
          startLockoutTimer(err.response.data.retry_after_seconds);
        } else if (err.response?.data?.remaining_attempts !== undefined) {
          remainingAttempts.value = err.response.data.remaining_attempts;
          error.value = err.response.data.message || 'Invalid credentials';
        } else {
          error.value = err.message || 'Login failed. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    };

    const startLockoutTimer = (seconds) => {
      isLocked.value = true;
      lockoutTime.value = seconds;
      
      lockoutInterval = setInterval(() => {
        lockoutTime.value--;
        
        if (lockoutTime.value <= 0) {
          clearInterval(lockoutInterval);
          isLocked.value = false;
          remainingAttempts.value = 5; // Reset attempts after lockout
        }
      }, 1000);
    };

    onMounted(() => {
      // Check if user is already logged in
      if (AuthService.isAuthenticated()) {
        router.push('/dashboard');
      }
    });

    onUnmounted(() => {
      if (lockoutInterval) {
        clearInterval(lockoutInterval);
      }
    });

    return {
      username,
      password,
      rememberMe,
      loading,
      error,
      remainingAttempts,
      isLocked,
      lockoutTime,
      handleLogin,
    };
  },
};
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f5f5f5;
  padding: 20px;
}

.login-box {
  background: white;
  padding: 2.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

.remember-me {
  display: flex;
  align-items: center;
  margin: 1.5rem 0;
}

.form-checkbox {
  margin-right: 0.5rem;
}

.login-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #4299e1;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 44px;
}

.login-button:hover:not(:disabled) {
  background-color: #3182ce;
}

.login-button:disabled {
  background-color: #a0aec0;
  cursor: not-allowed;
}

.error-message {
  color: #e53e3e;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

.forgot-password {
  text-align: center;
  margin-top: 1rem;
}

.forgot-password a {
  color: #4299e1;
  text-decoration: none;
  font-size: 0.875rem;
}

.forgot-password a:hover {
  text-decoration: underline;
}

/* Spinner animation */
.spinner {
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
  margin-right: 8px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
