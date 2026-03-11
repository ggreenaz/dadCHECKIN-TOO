<div class="card" style="text-align: center; padding: 40px;">
    <div style="font-size: 3.5rem; margin-bottom: 16px; color: var(--success);">&#10003;</div>
    <div class="card-title" style="border: none; text-align: center; font-size: 1.3rem;">Setup Complete</div>
    <p style="color: var(--text-muted); margin-bottom: 28px;">
        Your dadCHECKIN-TOO platform is ready. Sign in with the admin account you just created.
    </p>

    <div style="background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius);
                padding: 20px; margin-bottom: 28px; text-align: left; font-size: 0.875rem;">
        <p style="font-weight: 600; margin-bottom: 12px;">Your URLs:</p>
        <p style="margin-bottom: 6px;"><strong>Visitor Check-In:</strong> <code>/checkin</code></p>
        <p style="margin-bottom: 6px;"><strong>Visitor Check-Out:</strong> <code>/depart</code></p>
        <p style="margin-bottom: 6px;"><strong>Live Board:</strong> <code>/board</code></p>
        <p><strong>Admin Panel:</strong> <code>/admin</code></p>
    </div>

    <form method="POST" action="/install/finish">
        <button type="submit" class="button">Go to Login &rarr;</button>
    </form>
</div>
