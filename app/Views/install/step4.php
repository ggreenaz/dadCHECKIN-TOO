<div class="card">
    <div class="card-title">Step 4 — Admin Account</div>
    <p style="margin-bottom: 20px; color: var(--text-muted);">
        This will be the primary administrator account for managing the system.
    </p>
    <form method="POST" action="/install/4/save">
        <div class="form-grid">
            <div class="form-group form-group-full">
                <label for="name">Your Name</label>
                <input type="text" name="name" id="name" placeholder="Full Name"
                       value="<?= \App\Core\View::e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group form-group-full">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email"
                       placeholder="admin@yourorg.com" required
                       value="<?= \App\Core\View::e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password"
                       placeholder="At least 8 characters" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" name="password_confirm" id="password_confirm"
                       placeholder="Repeat password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Create Account &amp; Continue</button>
            </div>
        </div>
    </form>
</div>
