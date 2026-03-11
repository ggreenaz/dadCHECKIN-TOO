<?php use App\Core\View; ?>

<div class="card" style="max-width: 480px; margin: 0 auto;">
    <div class="card-title">Visitor Check-Out</div>
    <form method="POST" action="/depart">
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" placeholder="Enter your phone number" required autofocus>
        </div>
        <div class="form-actions mt-16">
            <button type="submit" class="button">Check Out</button>
            <a href="/checkin" class="button button-outline">Back to Check-In</a>
        </div>
    </form>
</div>
