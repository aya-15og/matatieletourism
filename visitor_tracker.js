/**
 * Website Visitor Tracker - Client-side Script (Updated: No Geolocation Prompt)
 * 
 * This version collects visitor info and uses IP-based lookup for
 * approximate country/city — no browser permission prompt required.
 * 
 * Include this script in your site:
 * <script src="visitor_tracker.js"></script>
 */

(function () {
    'use strict';

    // Configuration
    const TRACKER_CONFIG = {
        trackingEndpoint: '/track_visitor.php', // URL to your tracking PHP script
        cookieName: 'visitor_id',
        cookieDuration: 365 * 24 * 60 * 60 * 1000, // 1 year
        debug: false // Set true to see console output
    };

    /**
     * Initialize the visitor tracker
     */
    function initTracker() {
        const visitorId = getOrCreateVisitorId();

        // Collect basic visitor data
        const visitorData = {
            visitor_id: visitorId,
            user_agent: navigator.userAgent,
            page_url: window.location.href,
            referrer: document.referrer,
            device_type: detectDeviceType(),
            timestamp: new Date().toISOString()
        };

        // 🔹 NEW: Get approximate location silently via IP
        fetch('https://ipapi.co/json/')
            .then(res => res.json())
            .then(ipData => {
                visitorData.country = ipData.country_name || 'Unknown';
                visitorData.city = ipData.city || 'Unknown';
                sendTrackerData(visitorData);
            })
            .catch(err => {
                if (TRACKER_CONFIG.debug) {
                    console.warn('IP location lookup failed:', err);
                }
                // Still send data even if IP lookup fails
                sendTrackerData(visitorData);
            });
    }

    /**
     * Get or create visitor ID
     */
    function getOrCreateVisitorId() {
        let visitorId = getCookie(TRACKER_CONFIG.cookieName);

        if (!visitorId) {
            visitorId = generateUUID();
            setCookie(TRACKER_CONFIG.cookieName, visitorId, TRACKER_CONFIG.cookieDuration);

            if (TRACKER_CONFIG.debug) {
                console.log('New visitor ID created:', visitorId);
            }
        } else if (TRACKER_CONFIG.debug) {
            console.log('Returning visitor ID:', visitorId);
        }

        return visitorId;
    }

    /**
     * Detect device type
     */
    function detectDeviceType() {
        const ua = navigator.userAgent.toLowerCase();
        const width = window.innerWidth;

        if (/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i.test(ua)) {
            return 'Mobile';
        } else if (/tablet|ipad|android(?!.*mobile)|kindle|playbook|silk/i.test(ua)) {
            return 'Tablet';
        } else if (width < 768) {
            return 'Mobile';
        } else if (width < 1024) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Send visitor data to the server
     */
    function sendTrackerData(data) {
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
            const ok = navigator.sendBeacon(TRACKER_CONFIG.trackingEndpoint, blob);

            if (TRACKER_CONFIG.debug) {
                console.log('Beacon sent:', ok ? 'Success' : 'Failed');
            }
        } else {
            fetch(TRACKER_CONFIG.trackingEndpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                keepalive: true
            })
                .then(res => {
                    if (TRACKER_CONFIG.debug) {
                        console.log('Tracker response:', res.status);
                    }
                })
                .catch(err => {
                    if (TRACKER_CONFIG.debug) {
                        console.error('Tracker error:', err);
                    }
                });
        }
    }

    /**
     * Generate a UUID v4
     */
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    /**
     * Get cookie by name
     */
    function getCookie(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        for (let c of cookies) {
            c = c.trim();
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length);
            }
        }
        return null;
    }

    /**
     * Set a cookie
     */
    function setCookie(name, value, duration) {
        const date = new Date();
        date.setTime(date.getTime() + duration);
        const expires = 'expires=' + date.toUTCString();
        document.cookie = `${name}=${value}; ${expires}; path=/`;
    }

    /**
     * Run tracker when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTracker);
    } else {
        initTracker();
    }

    // Optional: Track when user returns to tab (debug only)
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && TRACKER_CONFIG.debug) {
            console.log('User returned to page');
        }
    });

})();
