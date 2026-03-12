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
        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius);
                    padding:16px 20px;margin-bottom:24px;text-align:left;">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;">
                <input type="checkbox" name="load_demo" value="1"
                       style="width:18px;height:18px;margin-top:2px;flex-shrink:0;">
                <div>
                    <div style="font-weight:700;font-size:0.9rem;margin-bottom:4px;">
                        Load demo data so I can explore the app
                    </div>
                    <div style="font-size:0.8rem;color:var(--text-muted);line-height:1.5;">
                        Adds 80 sample visitors, 15 hosts, 420 historical visits, and 8 active
                        check-ins so every page has realistic data to explore.
                        You can remove it any time from <strong>Admin &rarr; Settings &rarr; Expunge Demo Data</strong>.
                    </div>
                </div>
            </label>
        </div>
        <button type="submit" class="button" style="font-size:1rem;padding:12px 28px;">
            Go to Login &rarr;
        </button>
    </form>
</div>
