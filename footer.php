<footer class="site-footer">
    <div class="footer-container">
        <p class="footer-contact">
            Email: <a href="mailto:marketing@matatiele.co.za">marketing@matatiele.co.za</a> | 
            Facebook: <a href="https://www.facebook.com/matatieleonline" target="_blank">Matatiele Online</a>
        </p>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Matatiele Tourism. All rights reserved.</p>
    </div>
</footer>

<style>
.site-footer {
    background-color: #1c1c1c;
    color: #fff;
    padding: 8px 10px; /* minimal padding */
    font-family: 'Poppins', sans-serif;
    text-align: center;
}

.footer-contact {
    font-size: 0.85rem; /* smaller text */
    margin: 0 0 5px 0;   /* reduce spacing */
}

.footer-contact a {
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem; /* base link size */
    margin: 0 5px;       /* spacing between links */
    display: inline-block;
}

.footer-contact a:hover {
    text-decoration: underline;
    color: #f5a623;
}

.footer-bottom {
    font-size: 0.75rem; /* smaller copyright */
    color: #ccc;
    margin: 0;
}

/* Mobile adjustments */
@media (max-width: 480px) {
    .footer-contact {
        font-size: 0.75rem; /* even smaller on mobile */
    }

    .footer-contact a {
        font-size: 0.75rem;
        margin: 3px 4px; /* reduce spacing for mobile */
    }
}
</style>


<!-- ✅ Visitor Tracking Script --> 
<script src="visitor_tracker.js"></script> 
</body> 
</html>