<?php $__env->startSection('content'); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --primary-light: rgba(37, 99, 235, 0.1);
        --bg-hero: #090e17;
        --bg-surface: #ffffff;
        --bg-canvas: #f8fafc;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --radius: 12px;
        --shadow-subtle: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        --shadow-card: 0 20px 40px -15px rgba(15, 23, 42, 0.08);
    }

    * {
        box-sizing: border-box;
    }

    body, html {
        margin: 0;
        padding: 0;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        background-color: var(--bg-canvas);
        color: var(--text-main);
        -webkit-font-smoothing: antialiased;
    }

    .auth-shell {
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        background-color: var(--bg-canvas);
    }

    /* Left Hero / Branding Panel */
    .auth-hero-pane {
        position: relative;
        background: radial-gradient(circle at 20% 20%, #1e293b 0%, #090e17 100%);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: clamp(32px, 5vw, 64px);
        overflow: hidden;
    }

    .auth-hero-pane::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
    }

    .hero-glow {
        position: absolute;
        top: 15%;
        left: 20%;
        width: 320px;
        height: 320px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, rgba(37, 99, 235, 0) 70%);
        filter: blur(60px);
        pointer-events: none;
    }

    .hero-header {
        position: relative;
        z-index: 2;
    }

    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        background: rgba(255, 255, 255, 0.07);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.03em;
        color: #f1f5f9;
    }

    .brand-icon-box {
        width: 24px;
        height: 24px;
        background: var(--primary);
        color: #fff;
        border-radius: 6px;
        display: grid;
        place-items: center;
        font-size: 11px;
    }

    .hero-body {
        position: relative;
        z-index: 2;
        max-width: 520px;
        margin: 40px 0;
    }

    .hero-tag {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: #60a5fa;
        margin-bottom: 16px;
    }

    .hero-title {
        font-size: clamp(32px, 3.8vw, 52px);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin: 0 0 20px;
        color: #ffffff;
    }

    .hero-title span {
        background: linear-gradient(135deg, #ffffff 40%, #93c5fd 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-description {
        font-size: 15px;
        line-height: 1.7;
        color: #94a3b8;
        margin: 0 0 36px;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    .feature-pill {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 14px 16px;
        backdrop-filter: blur(8px);
    }

    .feature-pill .val {
        font-size: 13px;
        font-weight: 700;
        color: #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .feature-pill .val i {
        color: #60a5fa;
        font-size: 12px;
    }

    .feature-pill .lbl {
        font-size: 11px;
        color: #64748b;
        font-weight: 500;
    }

    .hero-footer {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        font-size: 12px;
        color: #64748b;
    }

    /* Right Login Form Panel */
    .auth-form-pane {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(32px, 6vw, 80px);
        background: radial-gradient(circle at 100% 0%, #eff6ff 0%, var(--bg-canvas) 50%);
    }

    .form-box {
        width: 100%;
        max-width: 420px;
    }

    .form-header {
        margin-bottom: 32px;
    }

    .form-header h2 {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-main);
        margin: 0 0 8px;
    }

    .form-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
        letter-spacing: 0.01em;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .custom-input {
        width: 100%;
        height: 48px;
        padding: 0 42px 0 40px;
        font-family: inherit;
        font-size: 14px;
        color: var(--text-main);
        background: #ffffff;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        outline: none;
        transition: all 0.2s ease;
        box-shadow: var(--shadow-subtle);
    }

    .custom-input::placeholder {
        color: #cbd5e1;
    }

    .custom-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .custom-input:focus + .input-icon,
    .input-wrapper:focus-within .input-icon {
        color: var(--primary);
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #94a3b8;
        padding: 6px;
        cursor: pointer;
        display: grid;
        place-items: center;
        border-radius: 4px;
        transition: color 0.2s ease;
    }

    .toggle-password:hover {
        color: var(--text-main);
    }

    .form-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .checkbox-container {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 13px;
        color: var(--text-muted);
        user-select: none;
    }

    .checkbox-container input[type="checkbox"] {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .btn-submit {
        width: 100%;
        height: 48px;
        background: var(--primary);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-submit:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 20px;
        color: #991b1b;
        font-size: 13px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .alert-error ul {
        margin: 0;
        padding-left: 16px;
    }

    .alert-error li {
        margin-bottom: 2px;
    }

    .form-credits {
        margin-top: 36px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
        font-size: 12px;
        color: #94a3b8;
        text-align: center;
        line-height: 1.6;
    }

    /* Responsive Design */
    @media (max-width: 980px) {
        .auth-shell {
            grid-template-columns: 1fr;
        }

        .auth-hero-pane {
            padding: 40px 24px;
            min-height: auto;
        }

        .hero-body {
            margin: 20px 0;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }

        .auth-form-pane {
            padding: 40px 24px 60px;
        }
    }
</style>

<div class="auth-shell">
    <!-- Left Hero/Visual Section -->
    <section class="auth-hero-pane" aria-label="Overview">
        <div class="hero-glow"></div>

        <div class="hero-header">
            <div class="brand-pill">
                <span class="brand-icon-box"><i class="fas fa-cube"></i></span>
                <span>Norban / Assets Suite</span>
            </div>
        </div>

        <div class="hero-body">
            <div class="hero-tag">Operations &bull; Intelligence</div>
            <h1 class="hero-title">
                Intelligent control of <span>every asset.</span>
            </h1>
            <p class="hero-description">
                Centralized visibility, real-time lifecycle tracking, and end-to-end accountability across all departments and locations.
            </p>

            <div class="feature-grid">
                <div class="feature-pill">
                    <div class="val"><i class="fas fa-bolt"></i> Real-time</div>
                    <div class="lbl">Asset Tracking</div>
                </div>
                <div class="feature-pill">
                    <div class="val"><i class="fas fa-sync-alt"></i> Lifecycle</div>
                    <div class="lbl">Audit Trails</div>
                </div>
                <div class="feature-pill">
                    <div class="val"><i class="fas fa-shield-alt"></i> Secure</div>
                    <div class="lbl">Role Controls</div>
                </div>
            </div>
        </div>

        <div class="hero-footer">
            <span>Norban Group of Companies</span>
            <span>v2.0 Enterprise</span>
        </div>
    </section>

    <!-- Right Login Form Section -->
    <section class="auth-form-pane">
        <div class="form-box">
            <div class="form-header">
                <h2>Welcome Norban Group</h2>
                <p>Sign in with your work credentials to access the workspace</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert-error" role="alert">
                    <i class="fas fa-circle-exclamation" style="margin-top: 2px;"></i>
                    <ul>
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label class="form-label" for="email">Work Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon" aria-hidden="true"></i>
                        <input
                            id="email"
                            type="email"
                            class="custom-input"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            placeholder="name@norbangroup.com"
                            required
                            autofocus
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                        <input
                            id="password"
                            type="password"
                            class="custom-input"
                            name="password"
                            placeholder="••••••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>>
                        <span>Keep me logged in</span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Sign in</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

            <div class="form-credits">
                &copy; <?php echo e(date('Y')); ?> <strong>Norban Group of Companies</strong><br>
                Engineered by Data State Ltd.
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                if (isPassword) {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Asset\Asset-Management\resources\views/auth/login.blade.php ENDPATH**/ ?>