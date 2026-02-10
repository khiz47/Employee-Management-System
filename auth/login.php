<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Brand -->
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <h3>Employee Portal</h3>
            <p>Sign in to continue</p>
        </div>
        <div id="loginAlert"></div>
        <!-- Login Form -->
        <form method="POST" id="loginForm" class="auth-form">

            <div class="form-group">
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 auth-btn">
                Sign In
            </button>
        </form>

        <!-- Footer -->
        <div class="auth-footer">
            © <?= date('Y'); ?> Employee Management System
        </div>
    </div>
</div>